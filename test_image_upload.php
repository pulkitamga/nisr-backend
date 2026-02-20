<?php

// Test image upload functionality
echo "Testing image upload functionality...\n\n";

// Check if storage link exists and is accessible
$storagePath = __DIR__ . '/public/storage';
$targetPath = __DIR__ . '/storage/app/public';

if (!is_dir($storagePath)) {
    echo "ERROR: public/storage directory does not exist\n";
    exit(1);
}

echo "✓ public/storage directory exists\n";

// Check if we can access the target through the link
if (is_dir($storagePath . '/product')) {
    echo "✓ Can access product directory through storage link\n";
} else {
    echo "ERROR: Cannot access product directory through storage link\n";
    exit(1);
}

// Check if we can write to the storage directory
$testFile = $targetPath . '/test-upload-' . time() . '.txt';
if (file_put_contents($testFile, 'test content') !== false) {
    echo "✓ Can write to storage directory\n";
    unlink($testFile);
} else {
    echo "ERROR: Cannot write to storage directory\n";
    exit(1);
}

// Check if we can read from the storage directory
$existingImage = $targetPath . '/product/2025-01-21-678f7cfeb9dfc.webp';
if (file_exists($existingImage)) {
    echo "✓ Can read existing image from storage\n";
    $imageContent = file_get_contents($existingImage);
    if ($imageContent !== false) {
        echo "✓ Image content is accessible\n";
    } else {
        echo "ERROR: Cannot read image content\n";
        exit(1);
    }
} else {
    echo "WARNING: No existing image found to test reading\n";
}

echo "\nImage upload functionality test completed successfully!\n";
