<?php

namespace Tests\Unit;

use App\Services\FileManagerPathGuard;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class FileManagerPathGuardTest extends TestCase
{
    public function test_public_root_is_preserved_for_local_storage(): void
    {
        $guard = new FileManagerPathGuard();

        $this->assertSame('public', $guard->resolveEncodedPath('cHVibGlj', 'public', 'public'));
        $this->assertSame('public/products', $guard->resolvePlainPath('public/products', 'public', 'public'));
    }

    public function test_traversal_segments_are_rejected(): void
    {
        $guard = new FileManagerPathGuard();

        $this->expectException(AccessDeniedHttpException::class);
        $guard->resolveEncodedPath(base64_encode('../.env'), 'public', 'public');
    }

    public function test_invalid_base64_paths_are_rejected(): void
    {
        $guard = new FileManagerPathGuard();

        $this->expectException(NotFoundHttpException::class);
        $guard->resolveEncodedPath('***invalid***', 'public', 'public');
    }
}
