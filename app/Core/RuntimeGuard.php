<?php

namespace App\Core;

use App\Services\LicenseService;

class RuntimeGuard
{
    public static function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (app()->environment('local')) {
            return;
        }

        app(LicenseService::class)->ensureValid();
    }
}
