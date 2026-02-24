<?php

return [
    'mode' => env('APP_LICENSE_MODE', 'development'), // development | production
    'product' => env('LICENSE_PRODUCT', 'alnisr2'),
    'file' => env('APP_LICENSE_FILE', storage_path('framework/.license')),
    'key' => env('APP_LICENSE_KEY', 'DEV-UNLICENSED'), // fallback only
];