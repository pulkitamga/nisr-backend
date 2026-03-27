<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileManagerPathGuard
{
    private const DEFAULT_ENCODED_ROOT = 'cHVibGlj';

    public function resolveDisk(string $storage, string $storageConnectionType): string
    {
        return $storage === 's3' && $storageConnectionType === 's3' ? 's3' : 'local';
    }

    public function resolveEncodedPath(?string $encodedPath, string $storage, string $storageConnectionType): string
    {
        $decodedPath = base64_decode($encodedPath ?: self::DEFAULT_ENCODED_ROOT, true);
        if ($decodedPath === false) {
            throw new NotFoundHttpException('Invalid file manager path.');
        }

        return $this->normalizePath($decodedPath, $this->resolveDisk($storage, $storageConnectionType));
    }

    public function resolvePlainPath(?string $path, string $storage, string $storageConnectionType): string
    {
        return $this->normalizePath((string)$path, $this->resolveDisk($storage, $storageConnectionType));
    }

    public function getDisplayPath(string $path, string $disk): string
    {
        if ($disk === 'local' && ($path === '' || $path === 'public')) {
            return 'public';
        }

        return trim($path, '/');
    }

    private function normalizePath(string $path, string $disk): string
    {
        $normalizedPath = str_replace('\\', '/', trim($path));
        $normalizedPath = preg_replace('#/+#', '/', $normalizedPath) ?: '';
        $normalizedPath = trim($normalizedPath, '/');

        $segments = array_values(array_filter(explode('/', $normalizedPath), static fn ($segment) => $segment !== ''));
        if (in_array('..', $segments, true) || in_array('.', $segments, true)) {
            $this->reportBlockedPath($path, $disk, 'dot_segments');
            throw new AccessDeniedHttpException('Invalid file manager path.');
        }

        if ($disk === 'local') {
            if ($normalizedPath === '' || $normalizedPath === 'public') {
                return 'public';
            }

            if (!str_starts_with($normalizedPath, 'public/')) {
                $this->reportBlockedPath($path, $disk, 'outside_public_root');
                throw new AccessDeniedHttpException('Invalid file manager path.');
            }

            return $normalizedPath;
        }

        if ($normalizedPath === 'public') {
            return '';
        }

        return $normalizedPath;
    }

    private function reportBlockedPath(string $path, string $disk, string $reason): void
    {
        Log::warning('Blocked file manager path traversal attempt.', [
            'path' => $path,
            'disk' => $disk,
            'reason' => $reason,
            'ip' => request()->ip(),
        ]);
    }
}
