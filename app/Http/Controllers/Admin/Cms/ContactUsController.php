<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ContactUsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.business-settings.web-config.index');
    }

    public function edit(): RedirectResponse
    {
        return redirect()->route('admin.business-settings.web-config.index');
    }
}
