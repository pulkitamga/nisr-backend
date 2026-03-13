<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

trait StorageTrait
{
    public function storage(): MorphMany
    {
        return $this->morphMany(\App\Models\Storage::class, 'data');
    }
    protected function storageLink($path, $data, $type): string|array
    {
        // Ensure that $data is a string, if it's an array, convert it to string
        if (is_array($data)) {
            $data = implode(',', $data);  // or any other logic to handle arrays
        }

        // Similarly handle the path
        if (is_array($path)) {
            $path = implode('/', $path);
        }

        // S3 handling
        if ($type == 's3' && $this->storageConnectionCheck() == 's3') {
            $fullPath = ltrim($path . '/' . $data, '/');
            if ($this->fileCheck(disk: 's3', path: $fullPath) && !empty($data)) {
                return [
                    'key' => $data,
                    'path' => Storage::disk('s3')->url($fullPath),
                    'status' => 200,
                ];
            }
        } else {
            // Local storage handling
            if ($this->fileCheck(disk: 'public', path: $path . '/' . $data) && !empty($data)) {
                return [
                    'key' => $data,
                    'path' => dynamicStorage('storage/app/public/' . $path . '/' . $data),
                    'status' => 200,
                ];
            }
        }

        return [
            'key' => $data,
            'path' => null,
            'status' => 404,
        ];
    }


    private function fileCheck($disk, $path): bool
    {
        try{
            return Storage::disk($disk)->exists($path);
        }catch (\Exception $exception){
            // Log the error for debugging but return false for normal operation
            \Log::error('Storage file check failed: ' . $exception->getMessage(), [
                'disk' => $disk,
                'path' => $path,
                'exception' => $exception
            ]);
            return false;
        }
    }

    /**
     * Get file URL with error handling and fallback
     * @param string $path
     * @param string|null $fallback
     * @return string
     */
    protected function getFileUrl(string $path, ?string $fallback = null): string
    {
        try {
            if ($this->fileCheck(disk: 'public', path: $path)) {
                return dynamicStorage('storage/app/public/' . $path);
            }

            // Return fallback if provided, otherwise use default placeholder
            return $fallback ?? dynamicStorage('public/assets/front-end/img/placeholder/placeholder-2-1.png');
        } catch (\Exception $exception) {
            \Log::error('File URL generation failed: ' . $exception->getMessage(), [
                'path' => $path,
                'exception' => $exception
            ]);
            return $fallback ?? dynamicStorage('public/assets/front-end/img/placeholder/placeholder-2-1.png');
        }
    }

    protected function storageConnectionCheck(): string
    {
        return config('filesystems.disks.default') ?? 'public';
    }
}
