<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\AccessGuard;

abstract class BaseAdminController extends BaseController
{
    protected AccessGuard $accessGuard;

    public function __construct(AccessGuard $accessGuard)
    {
        $this->accessGuard = $accessGuard;

        $this->accessGuard->ensureValid();
    }
}
