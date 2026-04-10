<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RefundPolicyController extends Controller
{
    public function index()
    {
     
        return view('admin-views.content-management.refund-policy.index');
    }

    public function edit(): RedirectResponse
    {
        return redirect()->route('admin.content-management.refund-policy');
    }
}
