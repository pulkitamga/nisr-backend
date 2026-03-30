<?php

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return session()->get('direction') === 'rtl';
    }
}

if (!function_exists('get_direction')) {
    function get_direction(): string
    {
        return session()->get('direction') ?? 'ltr';
    }
}
