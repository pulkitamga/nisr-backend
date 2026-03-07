<?php

namespace App\Http\Controllers\TestUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingUserController extends Controller
{
    public function index()
    {
        echo("Hello");
    }
}
