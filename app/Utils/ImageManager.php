<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use RuntimeException;

class ImageManager
{
    private static function getStorageDisk(): string
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        return $storage === 'local' ? 'public' : $storage;
    }

    public static function upload(string $dir, string $format, $image, $file_type = 'image'): string
    {
        $storage = self::getStorageDisk();
        if (!$image instanceof UploadedFile || !$image->isValid()) {
            return 'def.webp';
        }

        if (!Storage::disk($storage)->exists($dir)) {
            Storage::disk($storage)->makeDirectory($dir);
        }

        $originalExtension = strtolower($image->getClientOriginalExtension() ?: 'png');
        if (in_array($originalExtension, ['gif', 'svg'], true)) {
            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $originalExtension;
            $stored = Storage::disk($storage)->put($dir . $imageName, file_get_contents($image));
            if (!$stored) {
                return 'def.webp';
            }
            return $imageName;
        }

        if ($format === 'webp' && !(imagetypes() & IMG_WEBP)) {
            $format = 'png';
        }

        try {
            $imageWebp = Image::make($image)->encode($format, 90);
            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            $stored = Storage::disk($storage)->put($dir . $imageName, (string)$imageWebp);
            $imageWebp->destroy();
            if (!$stored) {
                throw new RuntimeException('Unable to store processed image');
            }
        } catch (\Throwable $exception) {
            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $originalExtension;
            $stored = Storage::disk($storage)->put($dir . $imageName, file_get_contents($image));
            if (!$stored) {
                return 'def.webp';
            }
        }

        return $imageName;
    }

    public static function file_upload(string $dir, string $format, $file = null)
    {
        $storage = self::getStorageDisk();
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return 'def.png';
        }

        $fileName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk($storage)->exists($dir)) {
            Storage::disk($storage)->makeDirectory($dir);
        }
        $stored = Storage::disk($storage)->put($dir . $fileName, file_get_contents($file));
        if (!$stored) {
            return 'def.png';
        }

        return $fileName;
    }

    public static function update(string $dir, $old_image, string $format, $image, $file_type = 'image')
    {
        if (self::checkFileExists(filePath: $dir.$old_image)['status']) {
            Storage::disk(self::checkFileExists(filePath: $dir . $old_image)['disk'])->delete($dir . $old_image);
        }

        $imageName = $file_type == 'file' ? ImageManager::file_upload($dir, $format, $image) : ImageManager::upload($dir, $format, $image);

        return $imageName;
    }

    public static function delete($full_path)
    {
        if (self::checkFileExists(filePath: $full_path)['status']) {
            Storage::disk(self::checkFileExists(filePath: $full_path)['disk'])->delete($full_path);
        }
        return [
            'success' => 1,
            'message' => 'Removed successfully !'
        ];

    }
    public static function checkFileExists(string $filePath): array
    {
        $defaultDisk = self::getStorageDisk();
        if (Storage::disk('public')->exists($filePath)) {
            return [
                'status' => true,
                'disk' => 'public'
            ];
        } elseif ($defaultDisk == 's3' && Storage::disk('s3')->exists($filePath)) {
            return [
                'status' => true,
                'disk' => 's3'
            ];
        } else {
            return [
                'status' => false,
                'disk' => $defaultDisk
            ];
        }
    }

}
