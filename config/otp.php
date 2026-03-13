<?php

return [
    /*
    |----------------------------------------------------------------------
    | Test OTP mode
    |----------------------------------------------------------------------
    | Final production hardening: set OTP_TEST_MODE=false in the environment
    | to disable all non-live fallback OTPs without changing code.
    */
    'test_mode_enabled' => env('OTP_TEST_MODE', env('APP_MODE') !== 'live'),

    'test_tokens' => [
        '4' => env('OTP_TEST_TOKEN_4', '1234'),
        'warranty' => env('WARRANTY_TEST_OTP', '0000'),
    ],
];
