<?php

return [
    'product' => env('APP_GUARD_PRODUCT', 'Elnisr'),
    'file' => env('APP_GUARD_FILE', storage_path('framework/.runtime_state')),
    'public_key' => env('APP_GUARD_PUBLIC_KEY', 'lYjvutdiVxS44rhlsWFqnvHdFk5NiTVsLj0TCLpiFF4='),
    'state_prefix' => env('APP_GUARD_PREFIX', 'RTS-'),
    'accepted_prefixes' => [
        env('APP_GUARD_PREFIX', 'RTS-'),
        'LIC-',
    ],
    'runtime_host' => env('APP_RUNTIME_HOST'),
    'runtime_ip' => env('APP_RUNTIME_IP'),
    'build_meta' => base_path('bootstrap/cache/build-meta.php'),
    'protected_paths' => [
        'admin',
        'admin/*',
    ],
    'protected_console_commands' => [
        'queue:work',
        'queue:listen',
        'queue:restart',
        'schedule:run',
        'schedule:work',
    ],
];
