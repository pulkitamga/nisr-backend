<?php

define('LARAVEL_START', microtime(true));

const DOMAIN_POINTED_DIRECTORY = 'public';
require __DIR__ . '/vendor/autoload.php';  // The 'vendor' folder is now inside 'public' folder

$app = require_once __DIR__ . '/bootstrap/app.php';  // Same for the 'bootstrap' folder

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
