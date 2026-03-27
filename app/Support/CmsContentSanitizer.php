<?php

namespace App\Support;

use Illuminate\Support\Str;

class CmsContentSanitizer
{
    private const ALLOWED_RICH_TEXT_TAGS = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code>';

    public static function sanitizeRichText(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $sanitizedValue = preg_replace(
            '/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link)\b[^>]*>.*?<\s*\/\s*\1\s*>/is',
            '',
            $value
        ) ?? '';

        $sanitizedValue = preg_replace(
            '/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link)\b[^>]*\/?>/is',
            '',
            $sanitizedValue
        ) ?? '';

        $sanitizedValue = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/isu', '', $sanitizedValue) ?? '';
        $sanitizedValue = preg_replace('/\son\w+\s*=\s*[^\s>]+/isu', '', $sanitizedValue) ?? '';
        $sanitizedValue = preg_replace('/\sstyle\s*=\s*(["\']).*?\1/isu', '', $sanitizedValue) ?? '';

        $sanitizedValue = strip_tags($sanitizedValue, self::ALLOWED_RICH_TEXT_TAGS);

        $sanitizedValue = preg_replace_callback(
            '/\s(href|src)\s*=\s*(["\'])(.*?)\2/isu',
            static function (array $matches): string {
                $sanitizedUrl = self::sanitizeLink($matches[3]);

                if ($sanitizedUrl === '') {
                    return '';
                }

                return ' ' . $matches[1] . '=' . $matches[2] . htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8') . $matches[2];
            },
            $sanitizedValue
        ) ?? '';

        $sanitizedValue = preg_replace_callback(
            '/\s(href|src)\s*=\s*([^\s"\'<>`]+)/isu',
            static function (array $matches): string {
                $sanitizedUrl = self::sanitizeLink($matches[2]);

                if ($sanitizedUrl === '') {
                    return '';
                }

                return ' ' . $matches[1] . '="' . htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8') . '"';
            },
            $sanitizedValue
        ) ?? '';

        $sanitizedValue = preg_replace('/<(?!\/?(p|br|strong|b|em|i|u|a|ul|ol|li|h1|h2|h3|h4|h5|h6|blockquote|pre|code)\b)[^>]+>/i', '', $sanitizedValue) ?? '';

        return trim($sanitizedValue);
    }

    public static function sanitizePlainText(?string $value): string
    {
        return trim(strip_tags((string) $value));
    }

    public static function sanitizeRichTextArray(?array $values): array
    {
        if (!$values) {
            return [];
        }

        $sanitizedValues = [];

        foreach ($values as $key => $value) {
            $sanitizedValues[$key] = self::sanitizeRichText(is_string($value) ? $value : '');
        }

        return $sanitizedValues;
    }

    public static function sanitizePlainTextArray(?array $values): array
    {
        if (!$values) {
            return [];
        }

        $sanitizedValues = [];

        foreach ($values as $key => $value) {
            $sanitizedValues[$key] = self::sanitizePlainText(is_string($value) ? $value : '');
        }

        return $sanitizedValues;
    }

    public static function sanitizeLink(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $sanitizedValue = trim($value);

        if ($sanitizedValue === '') {
            return '';
        }

        $normalizedValue = str_replace(["\r", "\n"], '', $sanitizedValue);
        $lowerValue = Str::lower($normalizedValue);

        foreach (['javascript:', 'data:', 'vbscript:', 'file:', 'about:'] as $blockedProtocol) {
            if (Str::startsWith($lowerValue, $blockedProtocol)) {
                return '';
            }
        }

        if (Str::startsWith($normalizedValue, ['#', '/'])) {
            return $normalizedValue;
        }

        if (preg_match('/^www\./i', $normalizedValue) === 1) {
            $normalizedValue = 'https://' . $normalizedValue;
        }

        $scheme = parse_url($normalizedValue, PHP_URL_SCHEME);

        if (
            filter_var($normalizedValue, FILTER_VALIDATE_URL) !== false
            && is_string($scheme)
            && in_array(Str::lower($scheme), ['http', 'https'], true)
        ) {
            return $normalizedValue;
        }

        return '';
    }
}
