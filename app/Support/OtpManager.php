<?php

namespace App\Support;

class OtpManager
{
    public static function testModeEnabled(): bool
    {
        return (bool) config('otp.test_mode_enabled', env('APP_MODE') !== 'live');
    }

    public static function numericToken(int $digits): string
    {
        if (self::testModeEnabled()) {
            return self::testToken($digits);
        }

        $min = 10 ** ($digits - 1);
        $max = (10 ** $digits) - 1;

        return (string) random_int($min, $max);
    }

    public static function warrantyToken(): string
    {
        if (self::testModeEnabled()) {
            return (string) config('otp.test_tokens.warranty', '0000');
        }

        return self::numericToken(4);
    }

    public static function matchesWarrantyToken(string $otp, ?string $storedOtp = null): bool
    {
        if ($storedOtp !== null && hash_equals((string) $storedOtp, $otp)) {
            return true;
        }

        return self::testModeEnabled()
            && hash_equals((string) config('otp.test_tokens.warranty', '0000'), $otp);
    }

    public static function testToken(int $digits): string
    {
        $token = (string) config('otp.test_tokens.4', '1234');

        if ($digits <= 4) {
            return substr($token, 0, $digits);
        }

        return str_pad($token, $digits, '0');
    }
}
