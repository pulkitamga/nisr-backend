<?php

return [
    'key'  => env('APP_LICENSE_KEY', 'DEV-UNLICENSED'),
    'mode' => env('APP_LICENSE_MODE', 'development'), // development | production
    'domain' => env('APP_LICENSE_DOMAIN', null),
];
