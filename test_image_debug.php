<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

echo "=== IMAGE UPLOAD & DISPLAY DEBUG SCRIPT ===\n\n";

// 1. Check storage configuration
echo "1. STORAGE CONFIGURATION CHECK\n";
echo "Default filesystem disk: " . config('filesystems.default') . "\n";
echo "Public disk driver: " . config('filesystems.disks.public.driver') . "\n";
echo "Public disk root: " . config('filesystems.disks.public.root') . "\n";
echo "Public disk URL: " . config('filesystems.disks.public.url') . "\n";
echo "DOMAIN_POINTED_DIRECTORY: " . (defined('DOMAIN_POINTED_DIRECTORY') ? DOMAIN_POINTED_DIRECTORY : 'NOT DEFINED') . "\n\n";

// 2. Check storage link
echo "2. STORAGE LINK CHECK\n";
$publicStoragePath = public_path('storage');
$storageAppPublicPath = storage_path('app/public');

echo "Public storage path: $publicStoragePath\n";
echo "Storage app/public path: $storageAppPublicPath\n";
echo "Is link: " . (is_link($publicStoragePath) ? 'YES' : 'NO') . "\n";
echo "Link target: " . (is_link($publicStoragePath) ? @readlink($publicStoragePath) ?: 'N/A' : 'N/A') . "\n";
echo "Public storage exists: " . (file_exists($publicStoragePath) ? 'YES' : 'NO') . "\n";
echo "Storage app/public exists: " . (file_exists($storageAppPublicPath) ? 'YES' : 'NO') . "\n\n";

// 3. Test file existence in storage
echo "3. EXISTING FILES IN STORAGE\n";
$testPaths = [
    'product/thumbnail/2024-01-01-6593a1b2c3d4e.png',
    'product/main/2024-01-01-6593a1b2c3d4e.png',
    'brand/2024-01-01-6593a1b2c3d4e.png',
    'category/2024-01-01-6593a1b2c3d4e.png',
];

foreach ($testPaths as $path) {
    $exists = Storage::disk('public')->exists($path);
    $fullPath = storage_path('app/public/' . $path);
    $fileExists = file_exists($fullPath);
    echo "Path: $path\n";
    echo "  Storage::exists(): " . ($exists ? 'YES' : 'NO') . "\n";
    echo "  file_exists(): " . ($fileExists ? 'YES' : 'NO') . "\n";
    echo "  Full path: $fullPath\n\n";
}

// 4. Test helper functions
echo "4. HELPER FUNCTIONS TEST\n";

// Test dynamicStorage function
if (function_exists('dynamicStorage')) {
    $testPath = 'product/thumbnail/test.png';
    $url = dynamicStorage($testPath);
    echo "dynamicStorage('$testPath'): $url\n";

    // Check if URL is accessible
    $headers = @get_headers($url);
    $accessible = $headers && stripos($headers[0], '200 OK') !== false;
    echo "  URL accessible: " . ($accessible ? 'YES' : 'NO') . "\n\n";
} else {
    echo "dynamicStorage function not found\n\n";
}

// Test getStorageImages function
if (function_exists('getStorageImages')) {
    $testPath = ['status' => 200, 'path' => 'product/thumbnail/test.png'];
    $url = getStorageImages($testPath, 'product');
    echo "getStorageImages with status 200: $url\n";

    $testPath404 = ['status' => 404, 'path' => null];
    $url404 = getStorageImages($testPath404, 'product');
    echo "getStorageImages with status 404: $url404\n\n";
} else {
    echo "getStorageImages function not found\n\n";
}

// 5. Test file upload
echo "5. FILE UPLOAD TEST\n";

// Create a test image
$testImagePath = sys_get_temp_dir() . '/test_upload.png';
$image = imagecreatetruecolor(100, 100);
$bg = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $bg);
imagepng($image, $testImagePath);
imagedestroy($image);

// Create UploadedFile instance
$uploadedFile = new UploadedFile(
    $testImagePath,
    'test_upload.png',
    'image/png',
    null,
    true
);

echo "Test image created at: $testImagePath\n";
echo "UploadedFile created: YES\n";

// Test upload to different directories
$uploadPaths = [
    'product/thumbnail',
    'product/main',
    'brand',
    'category'
];

foreach ($uploadPaths as $path) {
    $filename = 'debug_' . time() . '_' . uniqid() . '.png';
    $fullPath = $path . '/' . $filename;

    try {
        $success = Storage::disk('public')->putFileAs($path, $uploadedFile, $filename);
        echo "Upload to $path: " . ($success ? 'SUCCESS' : 'FAILED') . "\n";

        if ($success) {
            $exists = Storage::disk('public')->exists($fullPath);
            echo "  File exists in storage: " . ($exists ? 'YES' : 'NO') . "\n";

            $url = dynamicStorage('storage/app/public/' . $fullPath);
            echo "  Generated URL: $url\n";

            $headers = @get_headers($url);
            $accessible = $headers && stripos($headers[0], '200 OK') !== false;
            echo "  URL accessible: " . ($accessible ? 'YES' : 'NO') . "\n";

            // Clean up
            Storage::disk('public')->delete($fullPath);
            echo "  Test file deleted\n";
        }
    } catch (Exception $e) {
        echo "Upload to $path: FAILED - " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Clean up test image
unlink($testImagePath);

// 6. Test StorageTrait
echo "6. STORAGE TRAIT TEST\n";

// Create a mock model using the trait
class TestModel
{
    use App\Traits\StorageTrait;

    public function testStorageLink($path, $data, $type = 'public')
    {
        return $this->storageLink($path, $data, $type);
    }

    public function testFileCheck($disk, $path)
    {
        return $this->fileCheck($disk, $path);
    }

    public function testGetFileUrl($path, $fallback = null)
    {
        return $this->getFileUrl($path, $fallback);
    }
}

$testModel = new TestModel();

// Test storageLink method
$result = $testModel->testStorageLink('product/thumbnail', 'test.png', 'public');
echo "storageLink result: " . json_encode($result) . "\n\n";

// Test fileCheck method
$exists = $testModel->testFileCheck('public', 'product/thumbnail/test.png');
echo "fileCheck for non-existent file: " . ($exists ? 'YES' : 'NO') . "\n\n";

// Test getFileUrl method
$url = $testModel->testGetFileUrl('product/thumbnail/test.png');
echo "getFileUrl for non-existent file: $url\n\n";

// 7. Check error logs
echo "7. ERROR LOGS CHECK\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    $recentLogs = substr($logContent, -2000); // Get last 2000 characters
    echo "Recent log entries (last 2000 chars):\n";
    echo $recentLogs . "\n\n";
} else {
    echo "Log file not found at: $logPath\n\n";
}

// 8. Check permissions
echo "8. PERMISSION CHECK\n";
$pathsToCheck = [
    public_path('storage'),
    storage_path('app/public'),
    storage_path('app/public/product'),
    storage_path('app/public/brand'),
    storage_path('app/public/category'),
];

foreach ($pathsToCheck as $path) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $octal = substr(sprintf('%o', $perms), -4);
        $writable = is_writable($path);
        $readable = is_readable($path);
        echo "Path: $path\n";
        echo "  Permissions: $octal\n";
        echo "  Writable: " . ($writable ? 'YES' : 'NO') . "\n";
        echo "  Readable: " . ($readable ? 'YES' : 'NO') . "\n\n";
    } else {
        echo "Path: $path - DOES NOT EXIST\n\n";
    }
}

echo "=== DEBUG COMPLETE ===\n";
