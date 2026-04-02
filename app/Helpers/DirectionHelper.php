<?php

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return get_direction() === 'rtl';
    }
}

if (!function_exists('get_direction')) {
    function get_direction(): string
    {
        $direction = strtolower(trim((string) session()->get('direction')));
        if (in_array($direction, ['ltr', 'rtl'], true)) {
            return $direction;
        }

        $languages = [];
        try {
            if (
                function_exists('getWebConfig')
                && class_exists(\Illuminate\Support\Facades\Schema::class)
                && \Illuminate\Support\Facades\Schema::hasTable('business_settings')
            ) {
                $languages = getWebConfig('language');
            }
        } catch (\Throwable) {
            $languages = [];
        }

        $languages = is_array($languages) ? $languages : [];
        $activeLocale = strtolower(trim((string) (
            function_exists('getActiveTranslationLocale')
                ? getActiveTranslationLocale()
                : app()->getLocale()
        )));
        $defaultLocale = strtolower(trim((string) (
            function_exists('getConfiguredDefaultLanguage')
                ? getConfiguredDefaultLanguage()
                : config('app.locale', 'en')
        )));
        $localeCandidates = array_values(array_unique(array_filter([
            $activeLocale,
            preg_split('/[_-]/', $activeLocale)[0] ?? '',
            $defaultLocale,
            preg_split('/[_-]/', $defaultLocale)[0] ?? '',
        ])));

        foreach ($localeCandidates as $localeCandidate) {
            foreach ($languages as $language) {
                if (!is_array($language)) {
                    continue;
                }

                $code = strtolower(trim((string) ($language['code'] ?? '')));
                if ($code !== $localeCandidate) {
                    continue;
                }

                $matchedDirection = strtolower(trim((string) ($language['direction'] ?? '')));
                if (in_array($matchedDirection, ['ltr', 'rtl'], true)) {
                    session()->put('direction', $matchedDirection);
                    return $matchedDirection;
                }
            }
        }

        $direction = str_starts_with($activeLocale, 'ar') ? 'rtl' : 'ltr';
        session()->put('direction', $direction);

        return $direction;
    }
}
