<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\LicenseService;

abstract class BaseAdminController extends BaseController
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;

        // Validate license on every request to protected controllers
        $this->licenseService->validate();
    }
}