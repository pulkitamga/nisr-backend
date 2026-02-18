<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use RuntimeException;

trait FileManagerTrait
{
    private function getStorageDisk(): string
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        return $storage === 'local' ? 'public' : $storage;
    }

    /**
     * upload method working for image
     * @param string $dir
     * @param string $format
     * @param $image
     * @return string
     */
    protected function upload(string $dir, string $format, $image = null): string
    {
        $storage = $this->getStorageDisk();

        if (!$image instanceof UploadedFile || !$image->isValid()) {
            return 'def.png';
        }

        if (!$this->checkFileExists($dir)['status']) {
            Storage::disk($storage)->makeDirectory($dir);
        }

        $originalExtension = strtolower($image->getClientOriginalExtension() ?: 'png');
        $isOriginalImage = in_array($originalExtension, ['gif', 'svg'], true);
        if ($isOriginalImage) {
            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $originalExtension;
            $stored = Storage::disk($storage)->put($dir . $imageName, file_get_contents($image));
            if (!$stored) {
                return 'def.png';
            }
            return $imageName;
        }

        if ($format === 'webp' && !(imagetypes() & IMG_WEBP)) {
            $format = 'png';
        }

        try {
            $imageWebp = Image::make($image)->encode($format);
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
                return 'def.png';
            }
        }

        return $imageName;
    }

    /**
     * @param string $dir
     * @param string $format
     * @param $file
     * @return string
     */
    public function fileUpload(string $dir, string $format, $file = null): string
    {
        $storage = $this->getStorageDisk();
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return 'def.png';
        }

        $fileName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!$this->checkFileExists($dir)['status']) {
            Storage::disk($storage)->makeDirectory($dir);
        }
        $stored = Storage::disk($storage)->put($dir . $fileName, file_get_contents($file));
        if (!$stored) {
            return 'def.png';
        }

        return $fileName;
    }

    /**
     * @param string $dir
     * @param $oldImage
     * @param string $format
     * @param $image
     * @param string $fileType image/file
     * @return string
     */
    public function update(string $dir, $oldImage, string $format, $image, string $fileType = 'image'): string
    {
        if ($this->checkFileExists(filePath: $dir . $oldImage)['status']) {
            Storage::disk($this->checkFileExists(filePath: $dir . $oldImage)['disk'])->delete($dir . $oldImage);
        }
        return $fileType == 'file' ? $this->fileUpload($dir, $format, $image) : $this->upload($dir, $format, $image);
    }

    /**
     * @param string $filePath
     * @return array
     */
    protected function  delete(string $filePath): array
    {
        if ($this->checkFileExists(filePath: $filePath)['status']) {
            Storage::disk($this->checkFileExists(filePath: $filePath)['disk'])->delete($filePath);
        }
        return [
            'success' => 1,
            'message' => translate('Removed_successfully')
        ];
    }

    public function setStorageConnectionEnvironment(): void
    {
        $storageConnectionType = getWebConfig(name: 'storage_connection_type') ?? 'public';
        if ($storageConnectionType === 'local') {
            $storageConnectionType = 'public';
        }
        Config::set('filesystems.disks.default', $storageConnectionType);
        $storageConnectionS3Credential = getWebConfig(name: 'storage_connection_s3_credential');
        if ($storageConnectionType == 's3' && !empty($storageConnectionS3Credential)) {
            Config::set('filesystems.disks.' . $storageConnectionType, $storageConnectionS3Credential);
        }
    }

    private function checkFileExists(string $filePath): array
    {
        $defaultDisk = $this->getStorageDisk();
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
