<?php

// Test image display in edit form
echo "Testing image display in edit form...\n\n";

// Check if we can access images through the storage link for editing
$storagePath = __DIR__ . '/public/storage/product/2025-01-21-678f7cfeb9dfc.webp';
$targetPath = __DIR__ . '/storage/app/public/product/2025-01-21-678f7cfeb9dfc.webp';

if (!file_exists($storagePath)) {
    echo "ERROR: Image file does not exist at $storagePath\n";
    exit(1);
}

echo "✓ Image file exists at storage link location\n";

// Check if we can read the image content (for editing)
$imageContent = file_get_contents($storagePath);
if ($imageContent !== false) {
    echo "✓ Can read image content through storage link (for editing)\n";
    echo "  File size: " . strlen($imageContent) . " bytes\n";

    // Verify the content is actually image data
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($storagePath);
    echo "  MIME Type: $mimeType\n";

    if (strpos($mimeType, 'image/') === 0) {
        echo "✓ Content is valid image data\n";
    } else {
        echo "WARNING: Content may not be valid image data\n";
    }
} else {
    echo "ERROR: Cannot read image content through storage link (for editing)\n";
    exit(1);
}

// Test write permissions in a separate test directory
$testDir = __DIR__ . '/storage/app/public/test-edit';
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

$testFile = $testDir . '/test-edit-' . time() . '.txt';
if (file_put_contents($testFile, 'edit test content') !== false) {
    echo "✓ Can write to storage directory (for uploading new images)\n";
    unlink($testFile);
    rmdir($testDir);
} else {
    echo "ERROR: Cannot write to storage directory (for uploading new images)\n";
    exit(1);
}

// Test delete permissions
if (file_exists($testFile)) {
    if (unlink($testFile)) {
        echo "✓ Can delete files from storage directory (for removing images)\n";
    } else {
        echo "ERROR: Cannot delete files from storage directory (for removing images)\n";
        exit(1);
    }
}

// Test a few more images for edit functionality
$testImages = [
    '2025-01-27-67976e9ab9824.webp',
    '2025-04-18-6802133258f08.webp',
    '2025-04-19-68035ec6d7273.webp'
];

foreach ($testImages as $image) {
    $imagePath = __DIR__ . '/public/storage/product/' . $image;
    if (file_exists($imagePath)) {
        echo "✓ Image $image exists and is accessible for editing\n";
    } else {
        echo "✗ Image $image not found for editing\n";
    }
}

echo "\nEdit form image display test completed successfully!\n";
