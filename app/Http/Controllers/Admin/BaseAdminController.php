<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;

class BaseAdminController extends Controller
{
    public function __construct()
    {
        if (!app()->runningInConsole() && !app()->environment('local')) {
            app(LicenseService::class)->ensureValid();
        }
    }
}