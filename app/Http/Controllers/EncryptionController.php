<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EncryptionController extends Controller
{
    private $boltPath;
    private $storagePath;

    public function __construct()
    {
        $this->boltPath = base_path('vendor/bin/bolt');
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

    /**
     * Encrypt the project
     */
    public function encrypt(Request $request)
    {
        $request->validate([
            'key' => 'required|string|min:8',
            'folders' => 'required|array',
        ]);

        $key = $request->key;
        $selectedFolders = $request->folders;
        $timestamp = now()->format('Y-m-d_H-i-s');
        $outputDir = $this->storagePath . "/encrypted_{$timestamp}";
        $zipFile = $this->storagePath . "/encrypted_project_{$timestamp}.zip";

        // Store key in session for this session only
        session(['encryption_key' => $key]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        // Available folders to encrypt
        $folders = [
            'app' => base_path('app'),
            'bootstrap' => base_path('bootstrap'),
            'config' => base_path('config'),
            'database' => base_path('database'),
            'resources/views' => base_path('resources/views'),
            'routes' => base_path('routes'),
            'Modules' => base_path('Modules'),
        ];

        foreach ($selectedFolders as $folder) {
            if (isset($folders[$folder]) && File::exists($folders[$folder])) {
                $source = $folders[$folder];
                $destination = $outputDir . '/' . $folder;

                try {
                    $this->encryptDirectory($source, $destination, $key);
                    $results['success'][] = $folder;
                } catch (\Exception $e) {
                    $results['failed'][] = $folder . ': ' . $e->getMessage();
                }
            }
        }

        // Copy essential unencrypted files
        $this->copyEssentialFiles($outputDir);

        // Create zip archive
        $this->createZip($outputDir, $zipFile);

        // Cleanup
        File::deleteDirectory($outputDir);

        return response()->json([
            'success' => true,
            'message' => 'Encryption completed',
            'results' => $results,
            'download_url' => route('encryption.download', ['filename' => basename($zipFile)])
        ]);
    }

    public function encryptControllers(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'controllers' => 'required|array',
        ]);

        $password = $request->password;
        $selectedControllers = $request->controllers;
        $timestamp = now()->format('Y-m-d_H-i-s');
        $outputDir = $this->storagePath . "/controllers_encrypt_{$timestamp}";
        $zipFile = $this->storagePath . "/controllers_encrypt_{$timestamp}.zip";

        $results = ['success' => [], 'failed' => []];

        foreach ($selectedControllers as $controller) {
            $controllerPath = app_path("Http/Controllers/{$controller}.php");

            if (File::exists($controllerPath)) {
                try {
                    // Create directory structure
                    $destDir = $outputDir . '/app/Http/Controllers';
                    if (!File::exists($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }

                    $destFile = $destDir . '/' . $controller . '.php';

                    // Encrypt using OpenSSL
                    $data = file_get_contents($controllerPath);
                    $key = hash('sha256', $password, true);
                    $iv = random_bytes(16);
                    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    $final = base64_encode($iv . $encrypted);

                    file_put_contents($destFile, $final);
                    $results['success'][] = $controller;
                } catch (\Exception $e) {
                    $results['failed'][] = $controller . ': ' . $e->getMessage();
                }
            } else {
                $results['failed'][] = $controller . ': File not found';
            }
        }

        // Create zip
        $this->createZip($outputDir, $zipFile);
        File::deleteDirectory($outputDir);

        return response()->json([
            'success' => true,
            'message' => 'Controllers encrypted successfully!',
            'results' => $results,
            'download_url' => route('encryption.download', ['filename' => basename($zipFile)])
        ]);
    }
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

        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($source);

            foreach ($files as $file) {
                $relativePath = substr($file->getPathname(), strlen($source) + 1);
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
