<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturnPolicyController extends Controller
{
    public function index()
    {
        return view('admin-views.content-management.return-policy.index');
    }
}
