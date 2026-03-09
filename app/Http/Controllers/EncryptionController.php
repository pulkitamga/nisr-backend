<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use ZipArchive;

class EncryptionController extends Controller
{
    private $boltPath;
    private $storagePath;

    public function __construct()
    {
        $this->boltPath = base_path('bolt-runner.php');
        $this->storagePath = storage_path('app/encryption');

        // Create directories if they don't exist
        if (!File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }
    }

    /**
     * Show encryption dashboard
     */
    public function index()
    {
        $encryptedFiles = File::files($this->storagePath . '/encrypted');
        $key = session('encryption_key', '');

        return view('encryption.index', compact('encryptedFiles', 'key'));
    }
public function encrypt(Request $request)
{
    $request->validate([
        'key' => 'required|string|min:8',
        'folders' => 'sometimes|array',
        'files' => 'sometimes|array',
    ]);

    $key = $request->key;
    $selectedFolders = $request->input('folders', []);
    $selectedFiles = $request->input('files', []);

    // First, let's list all available commands
    $listProcess = new Process([
        PHP_BINARY,
        $this->boltPath,
        'list'  // Symfony Console command to list all commands
    ]);
    $listProcess->run();
    \Log::info('Available Bolt commands: ' . $listProcess->getOutput());
    \Log::info('List command error: ' . $listProcess->getErrorOutput());

    // Also try --help
    $helpProcess = new Process([
        PHP_BINARY,
        $this->boltPath,
        '--help'
    ]);
    $helpProcess->run();
    \Log::info('Bolt help: ' . $helpProcess->getOutput());

    // Check if we have anything to encrypt
    if (empty($selectedFolders) && empty($selectedFiles)) {
        return response()->json([
            'success' => false,
            'message' => 'No folders or files provided'
        ], 400);
    }

    $timestamp = now()->format('Y-m_d-H-i-s');
    $outputDir = $this->storagePath . DIRECTORY_SEPARATOR . "encrypted_{$timestamp}";
    $zipFile = $this->storagePath . DIRECTORY_SEPARATOR . "encrypted_project_{$timestamp}.zip";

    // Create output directory
    if (!File::exists($outputDir)) {
        File::makeDirectory($outputDir, 0755, true);
    }

    session(['encryption_key' => $key]);

    $results = [
        'success' => [],
        'failed' => []
    ];

    $filesToEncrypt = [];

    // --- Handle folders first - collect all PHP files ---
    foreach ($selectedFolders as $folder) {
        $folderPath = base_path($folder);

        if (!File::exists($folderPath) || !File::isDirectory($folderPath)) {
            $results['failed'][] = $folder . ': Folder not found';
            continue;
        }

        $allFiles = File::allFiles($folderPath);
        foreach ($allFiles as $file) {
            if ($file->getExtension() === 'php') {
                // Get relative path from project root
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                // Store relative path with forward slashes for consistency
                $filesToEncrypt[] = str_replace('\\', '/', $relativePath);
            }
        }
    }

    // Add individually selected files (avoid duplicates)
    foreach ($selectedFiles as $file) {
        $normalizedFile = str_replace('\\', '/', $file);
        if (!in_array($normalizedFile, $filesToEncrypt)) {
            $filesToEncrypt[] = $normalizedFile;
        }
    }

    // If no files to encrypt, return error
    if (empty($filesToEncrypt)) {
        File::deleteDirectory($outputDir);
        return response()->json([
            'success' => false,
            'message' => 'No PHP files found to encrypt',
            'results' => $results
        ], 400);
    }

    // --- Encrypt all collected files ---
    foreach ($filesToEncrypt as $relativePath) {
        $sourceFile = base_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

        if (!File::exists($sourceFile)) {
            $results['failed'][] = $relativePath . ': File not found';
            continue;
        }

        try {
            // Create temporary directories with unique names
            $tempId = uniqid();
            $tempInputDir = storage_path('app' . DIRECTORY_SEPARATOR . 'encryption' . DIRECTORY_SEPARATOR . "input_{$tempId}");
            $tempOutputDir = storage_path('app' . DIRECTORY_SEPARATOR . 'encryption' . DIRECTORY_SEPARATOR . "output_{$tempId}");

            File::makeDirectory($tempInputDir, 0755, true);
            File::makeDirectory($tempOutputDir, 0755, true);

            // Parse the relative path
            $relativePathParts = explode('/', $relativePath);
            $filename = array_pop($relativePathParts);
            
            // Create a flat structure in temp input
            $tempInputFile = $tempInputDir . DIRECTORY_SEPARATOR . $filename;
            
            File::copy($sourceFile, $tempInputFile);
            
            \Log::info('Input file copied to: ' . $tempInputFile);

            // Try different command formats that might work with Symfony Console
            $commandFormats = [
                ['encrypt:file', $tempInputFile, $key, $tempOutputDir],  // Command with colon
                ['encrypt', $tempInputFile, $key, $tempOutputDir],       // Simple command
                ['file:encrypt', $tempInputFile, $key, $tempOutputDir],  // Alternative format
                ['encode', $tempInputFile, $key, $tempOutputDir],        // Encode command
                ['crypt', $tempInputFile, $key, $tempOutputDir],         // Crypt command
            ];

            $success = false;
            $lastError = '';

            foreach ($commandFormats as $index => $format) {
                $command = array_merge([PHP_BINARY, $this->boltPath], $format);
                
                $process = new Process($command);
                $process->setWorkingDirectory(base_path());
                $process->run();

                \Log::info("Trying format {$index}: " . $process->getCommandLine());
                \Log::info("Output: " . $process->getOutput());
                \Log::info("Error: " . $process->getErrorOutput());
                \Log::info("Exit code: " . $process->getExitCode());

                // Check if output directory has files
                if (File::exists($tempOutputDir)) {
                    $files = File::allFiles($tempOutputDir);
                    if (!empty($files)) {
                        $success = true;
                        \Log::info("Format {$index} created files!");
                        break;
                    }
                }
                
                $lastError = $process->getErrorOutput() ?: $process->getOutput();
                
                // Small delay between attempts
                usleep(100000);
            }

            if (!$success) {
                throw new \Exception("All command formats failed to create encrypted files. Last error: " . $lastError);
            }

            // List all files in output directory
            $outputFiles = [];
            if (File::exists($tempOutputDir)) {
                $allOutputFiles = File::allFiles($tempOutputDir);
                foreach ($allOutputFiles as $file) {
                    $outputFiles[] = $file->getPathname();
                    \Log::info('Found in output: ' . $file->getPathname());
                }
            }

            // Look for any .bolt file
            $foundEncryptedFile = null;
            foreach ($outputFiles as $outputFile) {
                if (pathinfo($outputFile, PATHINFO_EXTENSION) === 'bolt') {
                    $foundEncryptedFile = $outputFile;
                    \Log::info('Found bolt file: ' . $outputFile);
                    break;
                }
            }

            if ($foundEncryptedFile && File::exists($foundEncryptedFile)) {
                // Create destination directory in final output
                $destDir = $outputDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, dirname($relativePath));
                File::makeDirectory($destDir, 0755, true);
                
                // Copy encrypted file to final destination
                $destFile = $outputDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath) . '.bolt';
                File::copy($foundEncryptedFile, $destFile);
                
                $results['success'][] = $relativePath;
                \Log::info('Successfully encrypted: ' . $relativePath);
            } else {
                $results['failed'][] = $relativePath . ': No .bolt file created';
            }

            // Cleanup
            File::deleteDirectory($tempInputDir);
            File::deleteDirectory($tempOutputDir);

        } catch (\Exception $e) {
            $results['failed'][] = $relativePath . ': ' . $e->getMessage();
            \Log::error('Encryption exception: ' . $e->getMessage());
            
            // Cleanup on error
            if (isset($tempInputDir) && File::exists($tempInputDir)) {
                File::deleteDirectory($tempInputDir);
            }
            if (isset($tempOutputDir) && File::exists($tempOutputDir)) {
                File::deleteDirectory($tempOutputDir);
            }
        }
    }

    // Copy essential unencrypted files
    $this->copyEssentialFiles($outputDir);

    // Only create zip if we have successful encryptions
    if (empty($results['success'])) {
        File::deleteDirectory($outputDir);
        return response()->json([
            'success' => false,
            'message' => 'Encryption failed: No files were successfully encrypted',
            'results' => $results
        ], 400);
    }

    $this->createZip($outputDir, $zipFile);
    File::deleteDirectory($outputDir);

    return response()->json([
        'success' => true,
        'message' => 'Encryption completed',
        'results' => $results,
        'download_url' => route('encryption.download', ['filename' => basename($zipFile)])
    ]);
}
/**
     * Encrypt the project
     */ public function encryptol(Request $request)
    {
        $request->validate([
            'key' => 'required|string|min:8',
            'folders' => 'sometimes|array',
            'files' => 'sometimes|array',
        ]);

        $key = $request->key;
        $selectedFolders = $request->input('folders', []);
        $selectedFiles = $request->input('files', []);

        // Check if we have anything to encrypt
        if (empty($selectedFolders) && empty($selectedFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'No folders or files provided'
            ], 400);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $outputDir = $this->storagePath . "/encrypted_{$timestamp}";
        $zipFile = $this->storagePath . "/encrypted_project_{$timestamp}.zip";

        // Create output directory
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        session(['encryption_key' => $key]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        // Handle individual files
        // foreach ($selectedFiles as $file) {
        //     $sourceFile = base_path($file);
        //     if (File::exists($sourceFile)) {
        //         try {


        //             $relativePath = $file;

        //             // $destFile = $outputDir . '/' . $relativePath;
        //             $destFile = $outputDir . '/' . $relativePath . '.bolt';
        //             $destDir = dirname($destFile);

        //             if (!File::exists($destDir)) {
        //                 File::makeDirectory($destDir, 0755, true);
        //             }

        //             $tempDir = storage_path('app/encryption/tmp_' . uniqid());
        //             $tempOutput = storage_path('app/encryption/tmp_enc_' . uniqid());

        //             // create temp folders
        //             File::makeDirectory($tempDir, 0755, true);
        //             File::makeDirectory($tempOutput, 0755, true);

        //             // copy file into temp folder
        //             $tempFile = $tempDir . '/' . basename($sourceFile);
        //             File::copy($sourceFile, $tempFile);

        //             // run bolt on folder
        //             $process = new Process([
        //                 PHP_BINARY,
        //                 $this->boltPath,
        //                 '-encrypt',
        //                 $tempDir,
        //                 $key,
        //                 $tempOutput
        //             ]);

        //             $process->run();

        //             if (!$process->isSuccessful()) {
        //                 throw new ProcessFailedException($process);
        //             }

        //             // encrypted file path
        //             // $encryptedFile = $tempOutput . '/' . basename($sourceFile);
        //             $encryptedFile = $tempOutput . '/' . basename($tempDir) . '/' . basename($sourceFile) . '.bolt';

        //             if (File::exists($encryptedFile)) {
        //                 File::copy($encryptedFile, $destFile);
        //             } else {
        //                 throw new \Exception("Encrypted file not created");
        //             }

        //             // cleanup temp folders
        //             File::deleteDirectory($tempDir);
        //             File::deleteDirectory($tempOutput);
        //             // $process = new Process([
        //             //     PHP_BINARY,
        //             //     $this->boltPath,
        //             //     'encrypt',
        //             //     $sourceFile,
        //             //     $key,
        //             //     $destFile
        //             // ]);
        //             // $process->mustRun();

        //             if (!File::exists($destFile)) {
        //                 $results['failed'][] = $file . ' : encrypted file not created';
        //                 continue;
        //             }

        //             $results['success'][] = $file;
        //         } catch (\Exception $e) {
        //             $results['failed'][] = $file . ': ' . $e->getMessage();
        //         }
        //     } else {
        //         $results['failed'][] = $file . ': File not found';
        //     }
        // }
        
        // Handle individual files
foreach ($selectedFiles as $file) {
    $sourceFile = base_path($file);
    if (File::exists($sourceFile)) {
        try {
            $relativePath = $file;
            $destFile = $outputDir . '/' . $relativePath . '.bolt';
            $destDir = dirname($destFile);

            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            $tempDir = storage_path('app/encryption/tmp_' . uniqid());
            $tempOutput = storage_path('app/encryption/tmp_enc_' . uniqid());

            // Create temp folders
            File::makeDirectory($tempDir, 0755, true);
            File::makeDirectory($tempOutput, 0755, true);

            // Create the same folder structure in temp dir
            $tempFilePath = $tempDir . '/' . $relativePath;
            File::ensureDirectoryExists(dirname($tempFilePath));
            File::copy($sourceFile, $tempFilePath);

            // Run bolt on the parent directory of the file
$process = new Process([
    PHP_BINARY,
    $this->boltPath,
    '-encrypt',
    $tempDir,     // folder
    $key,
    $tempOutput   // output folder
]);

            $process->run();

            // DEBUG: Capture all output and files
            $debug = [
                'command' => $process->getCommandLine(),
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
                'exit_code' => $process->getExitCode(),
                'temp_dir' => $tempDir,
                'temp_output' => $tempOutput,
                'files_in_temp_output' => []
            ];

            // List all files in tempOutput
            if (File::exists($tempOutput)) {
                $allFiles = File::allFiles($tempOutput);
                foreach ($allFiles as $f) {
                    $debug['files_in_temp_output'][] = [
                        'path' => $f->getPathname(),
                        'name' => $f->getFilename(),
                        'relative' => $f->getRelativePathname()
                    ];
                }
            }

            // Log the debug info
            \Log::info('Bolt Debug', $debug);

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Try multiple possible locations for the encrypted file
            $encryptedFileName = basename($sourceFile) . '.bolt';
            $foundEncryptedFile = null;
            
            // Possible locations to check
            $possibleLocations = [
                $tempOutput . '/' . $encryptedFileName,
                $tempOutput . '/' . basename($tempDir) . '/' . $encryptedFileName,
                $tempOutput . '/' . str_replace(base_path(), '', dirname($sourceFile)) . '/' . $encryptedFileName,
                $tempOutput . '/' . $relativePath . '.bolt'
            ];
            
            foreach ($possibleLocations as $location) {
                if (File::exists($location)) {
                    $foundEncryptedFile = $location;
                    break;
                }
            }
            
            // If still not found, search recursively
            if (!$foundEncryptedFile && File::exists($tempOutput)) {
                $encryptedFiles = File::allFiles($tempOutput);
                foreach ($encryptedFiles as $encFile) {
                    if ($encFile->getFilename() === $encryptedFileName) {
                        $foundEncryptedFile = $encFile->getPathname();
                        break;
                    }
                }
            }

            if ($foundEncryptedFile && File::exists($foundEncryptedFile)) {
                File::copy($foundEncryptedFile, $destFile);
                
                if (!File::exists($destFile)) {
                    throw new \Exception("Failed to copy encrypted file to destination");
                }
                
                $results['success'][] = $file;
            } else {
                // Return debug info in the response
                return response()->json([
                    'success' => false,
                    'message' => 'Encryption failed - Bolt output debug',
                    'debug' => $debug,
                    'file' => $file
                ], 400);
            }

            // Cleanup temp folders
            File::deleteDirectory($tempDir);
            File::deleteDirectory($tempOutput);

        } catch (\Exception $e) {
            $results['failed'][] = $file . ': ' . $e->getMessage();
            
            // Cleanup on error
            if (File::exists($tempDir)) File::deleteDirectory($tempDir);
            if (File::exists($tempOutput)) File::deleteDirectory($tempOutput);
        }
    } else {
        $results['failed'][] = $file . ': File not found';
    }
}
        // Handle folders (similar pattern)
        // foreach ($selectedFolders as $folder) {
        //     // ... folder handling code ...
        // }

        // Copy essential unencrypted files
        $this->copyEssentialFiles($outputDir);

        // 👇 IMPORTANT: Only create zip and return download URL if there are successful encryptions
        if (empty($results['success'])) {
            // Clean up the empty directory
            File::deleteDirectory($outputDir);

            return response()->json([
                'success' => false,
                'message' => 'Encryption failed: No files were successfully encrypted',
                'results' => $results
            ], 400);
        }

        $debugFiles = File::allFiles($outputDir);
        // Create zip archive (only if we have successful encryptions)
        $this->createZip($outputDir, $zipFile);

        // Cleanup
        File::deleteDirectory($outputDir);

        return response()->json([
            'success' => true,
            'message' => 'Encryption completed',
            'results' => $results,
            'download_url' => route('encryption.download', ['filename' => basename($zipFile)]),
            'debug_files' => collect($debugFiles)->map(fn($f) => $f->getRelativePathname())
        ]);
    }
    // public function encryptControllers(Request $request)
    // {
    //     $request->validate([
    //         'password' => 'required|string|min:8',
    //         'controllers' => 'required|array',
    //     ]);

    //     $password = $request->password;
    //     $selectedControllers = $request->controllers;
    //     $timestamp = now()->format('Y-m-d_H-i-s');
    //     $outputDir = $this->storagePath . "/controllers_encrypt_{$timestamp}";
    //     $zipFile = $this->storagePath . "/controllers_encrypt_{$timestamp}.zip";

    //     $results = ['success' => [], 'failed' => []];

    //     foreach ($selectedControllers as $controller) {
    //         $controllerPath = app_path("Http/Controllers/{$controller}.php");

    //         if (File::exists($controllerPath)) {
    //             try {
    //                 // Create directory structure
    //                 $destDir = $outputDir . '/app/Http/Controllers';
    //                 if (!File::exists($destDir)) {
    //                     File::makeDirectory($destDir, 0755, true);
    //                 }

    //                 $destFile = $destDir . '/' . $controller . '.php';

    //                 // Encrypt using OpenSSL
    //                 $data = file_get_contents($controllerPath);
    //                 $key = hash('sha256', $password, true);
    //                 $iv = random_bytes(16);
    //                 $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    //                 $final = base64_encode($iv . $encrypted);

    //                 file_put_contents($destFile, $final);
    //                 $results['success'][] = $controller;
    //             } catch (\Exception $e) {
    //                 $results['failed'][] = $controller . ': ' . $e->getMessage();
    //             }
    //         } else {
    //             $results['failed'][] = $controller . ': File not found';
    //         }
    //     }

    //     // Create zip
    //     $this->createZip($outputDir, $zipFile);
    //     File::deleteDirectory($outputDir);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Controllers encrypted successfully!',
    //         'results' => $results,
    //         'download_url' => route('encryption.download', ['filename' => basename($zipFile)])
    //     ]);
    // }
    /**
     * Decrypt the project
     */
    public function decrypt(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip',
            'key' => 'required|string',
        ]);

        $key = $request->key;
        $uploadedFile = $request->file('file');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extractPath = $this->storagePath . "/extract_{$timestamp}";
        $decryptPath = $this->storagePath . "/decrypted_{$timestamp}";
        $outputZip = $this->storagePath . "/decrypted_project_{$timestamp}.zip";

        try {
            // Extract uploaded zip
            $zip = new ZipArchive;
            $zip->open($uploadedFile->path());
            $zip->extractTo($extractPath);
            $zip->close();

            // Decrypt files
            $this->decryptDirectory($extractPath, $decryptPath, $key);

            // Create new zip with decrypted files
            $this->createZip($decryptPath, $outputZip);

            // Cleanup
            File::deleteDirectory($extractPath);
            File::deleteDirectory($decryptPath);

            return response()->json([
                'success' => true,
                'message' => 'Decryption completed',
                'download_url' => route('encryption.download', ['filename' => basename($outputZip)])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Decryption failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download encrypted/decrypted file
     */
    public function download($filename)
    {
        $path = $this->storagePath . '/' . $filename;

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Save encryption key to .env (optional)
     */
    public function saveKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string|min:8',
        ]);

        // Save to .env file
        $this->setEnvironmentValue('ENCRYPTION_KEY', $request->key);

        return response()->json([
            'success' => true,
            'message' => 'Encryption key saved to .env'
        ]);
    }

    /**
     * Encrypt a directory
     */
    private function encryptDirectory($source, $destination, $key)
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $files = File::allFiles($source);

        foreach ($files as $file) {
            if ($file->getExtension() == 'php') {
                $relativePath = $file->getRelativePathname();
                $destFile = $destination . '/' . $relativePath;
                $destDir = dirname($destFile);

                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }

                $process = new Process([
                    PHP_BINARY,
                    $this->boltPath,
                    '-encrypt',
                    $file->getPathname(),
                    $key,
                    $destFile
                ]);

                $process->mustRun();
            }
        }
    }

    /**
     * Decrypt a directory
     */
    private function decryptDirectory($source, $destination, $key)
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $files = File::allFiles($source);

        foreach ($files as $file) {
            if ($file->getExtension() == 'bolt') {
                $relativePath = $file->getRelativePathname();
                $relativePath = str_replace('.bolt', '.php', $relativePath);
                $destFile = $destination . '/' . $relativePath;
                $destDir = dirname($destFile);

                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }

                $process = new Process([
                    PHP_BINARY,
                    $this->boltPath,
                    '-decrypt',
                    $file->getPathname(),
                    $key,
                    $destFile
                ]);

                $process->mustRun();
            }
        }
    }

    /**
     * Copy essential unencrypted files
     */
    private function copyEssentialFiles($outputDir)
    {
        $essentialFiles = [
            'index.php',
            '.htaccess',
            'artisan',
            'composer.json',
            'composer.lock',
            'package.json',
            'webpack.mix.js',
            'server.php'
        ];

        $essentialDirs = [
            'public' => base_path('public'),
        ];

        // Copy files
        foreach ($essentialFiles as $file) {
            $source = base_path($file);
            if (File::exists($source)) {
                File::copy($source, $outputDir . '/' . $file);
            }
        }

        // Copy directories
        foreach ($essentialDirs as $name => $source) {
            if (File::exists($source)) {
                File::copyDirectory($source, $outputDir . '/' . $name);
            }
        }
    }

    /**
     * Create zip archive
     */
    private function createZip($source, $destination)
    {
        $zip = new ZipArchive();

        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

            $files = File::allFiles($source);

            foreach ($files as $file) {

                $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname());

                $zip->addFile($file->getPathname(), $relativePath);
            }

            $zip->close();
        }
    }

    /**
     * Set environment value in .env file
     */
    private function setEnvironmentValue($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $content = File::get($path);

            if (strpos($content, $key) !== false) {
                $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}\n";
            }

            File::put($path, $content);
        }
    }

    public function simpleEncryptTest()
    {
        $controllerPath = app_path('Http/Controllers/TestEncryptionController.php');
        $password = 'simple-password-123';
        $timestamp = now()->format('Y-m-d_H-i-s');
        $outputDir = $this->storagePath . "/simple_test_{$timestamp}";
        $encryptedFile = $outputDir . '/TestEncryptionController.php.enc';
        $decryptedFile = $outputDir . '/TestEncryptionController_decrypted.php';
        $zipFile = $this->storagePath . "/simple_test_{$timestamp}.zip";

        $debug = [];

        // Check if controller exists
        if (!File::exists($controllerPath)) {
            return response()->json([
                'success' => false,
                'message' => 'TestEncryptionController not found!',
                'path' => $controllerPath
            ], 404);
        }

        $debug['controller_exists'] = true;
        $debug['controller_size'] = filesize($controllerPath);

        try {
            // Create output directory
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0755, true);
                $debug['directory_created'] = $outputDir;
            }

            // Read the file
            $data = file_get_contents($controllerPath);
            $debug['file_read'] = strlen($data) . ' bytes';

            // Generate encryption key
            $key = hash('sha256', $password, true);
            $iv = random_bytes(16);

            // Encrypt
            $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $debug['encryption_complete'] = strlen($encrypted) . ' bytes';

            // Combine and save
            $final = base64_encode($iv . $encrypted);
            $bytesWritten = file_put_contents($encryptedFile, $final);
            $debug['file_written'] = $bytesWritten . ' bytes';

            // Check if file exists
            if (file_exists($encryptedFile)) {
                $debug['encrypted_file_exists'] = true;
                $debug['encrypted_size'] = filesize($encryptedFile);

                // Try to decrypt to verify
                $encContent = file_get_contents($encryptedFile);
                $encContent = base64_decode($encContent);
                $iv = substr($encContent, 0, 16);
                $encData = substr($encContent, 16);
                $decrypted = openssl_decrypt($encData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                file_put_contents($decryptedFile, $decrypted);

                if (file_exists($decryptedFile)) {
                    $debug['decrypted_size'] = filesize($decryptedFile);
                    $debug['verification'] = (md5_file($controllerPath) === md5_file($decryptedFile)) ? 'PASSED' : 'FAILED';
                }

                // Create zip
                $zip = new ZipArchive();
                if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                    $zip->addFile($encryptedFile, 'TestEncryptionController.php.enc');
                    $zip->close();
                    $debug['zip_created'] = true;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'File encrypted successfully!',
                    'password' => $password,
                    'debug' => $debug,
                    'download_url' => route('encryption.download', ['filename' => basename($zipFile)])
                ]);
            } else {
                throw new \Exception("Failed to create encrypted file");
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Encryption failed: ' . $e->getMessage(),
                'debug' => $debug
            ], 500);
        }
    }
}
