<?php

namespace App\Core;

use App\Services\AccessGuard;

class BootCheck
{
    public function __construct(
        private readonly AccessGuard $accessGuard,
    ) {
    }

    public function boot(): void
    {
        if ($this->accessGuard->shouldEnforce()) {
            $this->accessGuard->ensureValid();
        }
    }
}
