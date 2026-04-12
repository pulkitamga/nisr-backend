<?php

namespace App\Support;

use Carbon\CarbonInterface;

class LocalizedExport
{
    public static function locale(): string
    {
        return (string) (session('local') ?? session('locale') ?? app()->getLocale());
    }

    public static function isRtl(): bool
    {
        return function_exists('get_direction')
            ? get_direction() === 'rtl'
            : str_starts_with(self::locale(), 'ar');
    }

    public static function fileName(string $baseLabel, string $extension = 'xlsx'): string
    {
        $safeBaseLabel = preg_replace('/[\\\\\\/:*?"<>|]+/u', '-', trim($baseLabel)) ?? 'export';
        $safeBaseLabel = preg_replace('/\\s+/u', '-', $safeBaseLabel) ?? 'export';
        $safeBaseLabel = trim($safeBaseLabel, '-_.');

        if ($safeBaseLabel === '') {
            $safeBaseLabel = 'export';
        }

        return $safeBaseLabel . '-' . now()->format('Ymd_His') . '.' . ltrim($extension, '.');
    }

    public static function exportedAtLabel(?CarbonInterface $dateTime = null): string
    {
        $dateTime ??= now();

        return $dateTime->locale(self::locale())->translatedFormat('Y-m-d H:i');
    }
}
