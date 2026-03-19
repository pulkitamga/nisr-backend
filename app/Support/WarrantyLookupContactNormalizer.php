<?php

namespace App\Support;

class WarrantyLookupContactNormalizer
{
    public static function normalize(?string $contact): ?string
    {
        $trimmedContact = trim((string)$contact);
        if ($trimmedContact === '') {
            return null;
        }

        if (filter_var($trimmedContact, FILTER_VALIDATE_EMAIL)) {
            return strtolower($trimmedContact);
        }

        $digits = preg_replace('/\D+/', '', $trimmedContact);
        if ($digits === '') {
            return $trimmedContact;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '20') && isset($digits[2]) && $digits[2] === '0') {
            $digits = '20' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '20' . substr($digits, 1);
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '20' . $digits;
        }

        return '+' . $digits;
    }
}
