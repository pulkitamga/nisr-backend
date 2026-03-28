<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

if (!function_exists('translate')) {
    function translate($key = null, array $replace = []): string|null
    {
        if (!$key) {
            return $key;
        }

        $resolvedLocale = getActiveTranslationLocale();
        if (App::currentLocale() !== $resolvedLocale) {
            App::setLocale($resolvedLocale);
        }

        $message = getTranslateMessageValueByKey(local: $resolvedLocale, key: $key, replace: $replace);
        return is_string($message) ? $message : null;
    }

    function getTranslateMessageValueByKey(string $local, string $key, array $replace = []): array|string|null
    {
        $key = str_replace('"', '', $key);

        try {
            $translationCatalog = getTranslationCatalogForLocale($local);
            $processedKey = formatTranslationFallback($key);

            if (array_key_exists($key, $translationCatalog['messages'])) {
                $message = $translationCatalog['messages'][$key];
            } elseif (array_key_exists($key, $translationCatalog['new-messages'])) {
                $message = $translationCatalog['new-messages'][$key];
            } else {
                $message = $processedKey;
            }
        } catch (\Throwable) {
            $message = formatTranslationFallback($key);
        }

        if (is_string($message)) {
            $message = applyTranslationReplacements($message, $replace);
            return $local == 'en' ? ucfirst($message) : $message;
        }

        return $message;
    }
}

if (!function_exists('getOrPutTranslateMessageValueByKey')) {
    function getOrPutTranslateMessageValueByKey(string $local, string $key, array $replace = []): array|string|null
    {
        return getTranslateMessageValueByKey($local, $key, $replace);
    }
}

if (!function_exists('getActiveTranslationLocale')) {
    function getActiveTranslationLocale(): string
    {
        static $cachedLocale = null;
        static $cachedFingerprint = null;

        $appLocale = normalizeTranslationLocaleCandidate(App::getLocale());
        $sessionLocale = normalizeTranslationLocaleCandidate((string)session('local', session('locale')));
        $isApiRequest = str_contains((string)url()->current(), '/api');
        $fingerprint = implode('|', [$appLocale, $sessionLocale, $isApiRequest ? '1' : '0']);

        if ($cachedLocale !== null && $cachedFingerprint === $fingerprint) {
            return $cachedLocale;
        }

        $localeToResolve = $isApiRequest && $appLocale !== ''
            ? $appLocale
            : ($sessionLocale !== '' ? $sessionLocale : $appLocale);

        if ($localeToResolve === '') {
            $localeToResolve = getDefaultLanguage();
        }

        $cachedLocale = resolveAppLocale($localeToResolve);
        $cachedFingerprint = $fingerprint;

        return $cachedLocale;
    }
}

if (!function_exists('normalizeTranslationLocaleCandidate')) {
    function normalizeTranslationLocaleCandidate(string|null $locale): string
    {
        $normalizedLocale = strtolower(trim((string)$locale));
        if (
            $normalizedLocale === ''
            || !preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $normalizedLocale)
        ) {
            return '';
        }

        return $normalizedLocale;
    }
}

if (!function_exists('getTranslationCatalogForLocale')) {
    function getTranslationCatalogForLocale(string $local): array
    {
        static $catalogCache = [];

        $resolvedLocale = resolveAppLocale($local);
        if (!array_key_exists($resolvedLocale, $catalogCache)) {
            $catalogCache[$resolvedLocale] = [
                'messages' => loadTranslationGroupCatalog($resolvedLocale, 'messages'),
                'new-messages' => loadTranslationGroupCatalog($resolvedLocale, 'new-messages'),
            ];
        }

        return $catalogCache[$resolvedLocale];
    }
}

if (!function_exists('translationKeyExistsInCatalog')) {
    function translationKeyExistsInCatalog(string $local, string $key): bool
    {
        $translationCatalog = getTranslationCatalogForLocale($local);

        return array_key_exists($key, $translationCatalog['messages'])
            || array_key_exists($key, $translationCatalog['new-messages']);
    }
}

if (!function_exists('loadTranslationGroupCatalog')) {
    function loadTranslationGroupCatalog(string $local, string $group): array
    {
        $path = base_path('resources/lang/' . $local . '/' . $group . '.php');
        $messages = file_exists($path) ? include($path) : [];
        return is_array($messages) ? $messages : [];
    }
}

if (!function_exists('applyTranslationReplacements')) {
    function applyTranslationReplacements(string $message, array $replace = []): string
    {
        if ($replace === []) {
            return $message;
        }

        $replacements = [];
        foreach ($replace as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $replacementValue = (string)$value;
            $placeholder = ':' . $key;
            $replacements[$placeholder] = $replacementValue;
            $replacements[':' . ucfirst((string)$key)] = ucfirst($replacementValue);
            $replacements[':' . strtoupper((string)$key)] = strtoupper($replacementValue);
        }

        return strtr($message, $replacements);
    }
}

if (!function_exists('formatTranslationFallback')) {
    function formatTranslationFallback(string|null $key): string
    {
        return ucfirst(str_replace('_', ' ', removeSpecialCharacters(str_replace('"', '', (string)$key))));
    }
}

if (!function_exists('getDirectoriesByGivenPath')) {
    function getDirectoriesByGivenPath(string $path): array
    {
        $directories = [];
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }
}
if (!function_exists('getTranslation')) {
    function getTranslation($translations, $lang, $key, $defaultValue) {
        $translation = $translations->first(function ($t) use ($lang, $key) {
            return $t->locale === $lang && $t->key === $key;
        });
        return $translation ? $translation->value : $defaultValue;
    }
}


if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters(string|null $text): string|null
    {
        return str_ireplace(['\'', '"', ',', ';', '<', '>', '?', '“', '”'], ' ', preg_replace('/\s\s+/', ' ', $text));
    }
}

if (!function_exists('getDefaultLanguage')) {
    function getDefaultLanguage(): string
    {
        static $cachedLanguage = null;
        static $cachedFingerprint = null;

        $appLocale = normalizeTranslationLocaleCandidate(App::getLocale());
        $sessionLocale = normalizeTranslationLocaleCandidate((string)session('local', session('locale')));
        $cookieLocale = normalizeTranslationLocaleCandidate((string)(request()->cookie('local') ?? request()->cookie('locale') ?? ''));
        $isApiRequest = str_contains((string)url()->current(), '/api');
        $fingerprint = implode('|', [$appLocale, $sessionLocale, $cookieLocale, $isApiRequest ? '1' : '0']);

        if ($cachedLanguage !== null && $cachedFingerprint === $fingerprint) {
            return $cachedLanguage;
        }

        $data = [];
        try {
            if (
                function_exists('getWebConfig')
                && class_exists(Schema::class)
                && Schema::hasTable('business_settings')
            ) {
                $data = getWebConfig('language');
            }
        } catch (\Throwable) {
            $data = [];
        }
        $data = is_array($data) ? $data : [];
        $defaultCode = 'en';
        $direction = 'ltr';

        $normalizeLocale = static function (string|null $locale, string $fallback = 'en'): string {
            $normalizedLocale = strtolower(trim((string) $locale));

            if (
                $normalizedLocale === ''
                || !preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $normalizedLocale)
            ) {
                return $fallback;
            }

            return resolveAppLocale($normalizedLocale);
        };

        foreach ($data as $ln) {
            if (is_array($ln) && array_key_exists('default', $ln) && $ln['default']) {
                $defaultCode = $ln['code'];
                if (array_key_exists('direction', $ln)) {
                    $direction = $ln['direction'];
                }
            }
        }
        $defaultCode = $normalizeLocale($defaultCode, 'en');

        if ($isApiRequest) {
            $lang = $normalizeLocale(App::getLocale(), $defaultCode);
        } elseif (session()->has('local') || session()->has('locale')) {
            $lang = $normalizeLocale((string) session('local', session('locale')), $defaultCode);
        } elseif (($cookieLocale = strtolower(trim((string)(request()->cookie('local') ?? request()->cookie('locale') ?? '')))) !== '') {
            $lang = $normalizeLocale($cookieLocale, $defaultCode);
            foreach ($data as $ln) {
                if (is_array($ln) && strtolower((string)($ln['code'] ?? '')) === $lang) {
                    $direction = $ln['direction'] ?? $direction;
                    break;
                }
            }
        } else {
            $lang = $defaultCode;
        }

        $langCode = strtolower(trim((string)$lang));
        $langBaseCode = preg_split('/[_-]/', $langCode)[0] ?? $langCode;
        foreach ($data as $ln) {
            if (!is_array($ln)) {
                continue;
            }

            $languageCode = strtolower(trim((string)($ln['code'] ?? '')));
            if ($languageCode !== $langCode && $languageCode !== $langBaseCode) {
                continue;
            }

            $matchedDirection = strtolower(trim((string)($ln['direction'] ?? $direction)));
            $direction = in_array($matchedDirection, ['ltr', 'rtl'], true) ? $matchedDirection : $direction;
            break;
        }

        if ((string)session('local') !== $lang) {
            session()->put('local', $lang);
        }
        if ((string)session('locale') !== $lang) {
            session()->put('locale', $lang);
        }
        if ((string)session('direction') !== $direction) {
            Session::put('direction', $direction);
        }

        $cachedLanguage = $lang;
        $cachedFingerprint = $fingerprint;
        return $lang;
    }
}

if (!function_exists('getLanguageName')) {
    function getLanguageName(string $key): string
    {
        $values = getWebConfig('language');
        foreach ($values as $value) {
            if ($value['code'] == $key) {
                $key = $value['name'];
            }
        }
        return $key;
    }
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $country_code): string
    {
        $locales = array(
            'af-ZA',
            'am-ET',
            'ar-AE',
            'ar-BH',
            'ar-DZ',
            'ar-EG',
            'ar-IQ',
            'ar-JO',
            'ar-KW',
            'ar-LB',
            'ar-LY',
            'ar-MA',
            'ar-OM',
            'ar-QA',
            'ar-SA',
            'ar-SY',
            'ar-TN',
            'ar-YE',
            'az-Cyrl-AZ',
            'az-Latn-AZ',
            'be-BY',
            'bg-BG',
            'bn-BD',
            'bs-Cyrl-BA',
            'bs-Latn-BA',
            'cs-CZ',
            'da-DK',
            'de-AT',
            'de-CH',
            'de-DE',
            'de-LI',
            'de-LU',
            'dv-MV',
            'el-GR',
            'en-AU',
            'en-BZ',
            'en-CA',
            'en-GB',
            'en-IE',
            'en-JM',
            'en-MY',
            'en-NZ',
            'en-SG',
            'en-TT',
            'en-US',
            'en-ZA',
            'en-ZW',
            'es-AR',
            'es-BO',
            'es-CL',
            'es-CO',
            'es-CR',
            'es-DO',
            'es-EC',
            'es-ES',
            'es-GT',
            'es-HN',
            'es-MX',
            'es-NI',
            'es-PA',
            'es-PE',
            'es-PR',
            'es-PY',
            'es-SV',
            'es-US',
            'es-UY',
            'es-VE',
            'et-EE',
            'fa-IR',
            'fi-FI',
            'fil-PH',
            'fo-FO',
            'fr-BE',
            'fr-CA',
            'fr-CH',
            'fr-FR',
            'fr-LU',
            'fr-MC',
            'he-IL',
            'hi-IN',
            'hr-BA',
            'hr-HR',
            'hu-HU',
            'hy-AM',
            'id-ID',
            'ig-NG',
            'is-IS',
            'it-CH',
            'it-IT',
            'ja-JP',
            'ka-GE',
            'kk-KZ',
            'kl-GL',
            'km-KH',
            'ko-KR',
            'ky-KG',
            'lb-LU',
            'lo-LA',
            'lt-LT',
            'lv-LV',
            'mi-NZ',
            'mk-MK',
            'mn-MN',
            'ms-BN',
            'ms-MY',
            'mt-MT',
            'nb-NO',
            'ne-NP',
            'nl-BE',
            'nl-NL',
            'pl-PL',
            'prs-AF',
            'ps-AF',
            'pt-BR',
            'pt-PT',
            'ro-RO',
            'ru-RU',
            'rw-RW',
            'sv-SE',
            'si-LK',
            'sk-SK',
            'sl-SI',
            'sq-AL',
            'sr-Cyrl-BA',
            'sr-Cyrl-CS',
            'sr-Cyrl-ME',
            'sr-Cyrl-RS',
            'sr-Latn-BA',
            'sr-Latn-CS',
            'sr-Latn-ME',
            'sr-Latn-RS',
            'sw-KE',
            'tg-Cyrl-TJ',
            'th-TH',
            'tk-TM',
            'tr-TR',
            'uk-UA',
            'ur-PK',
            'uz-Cyrl-UZ',
            'uz-Latn-UZ',
            'vi-VN',
            'wo-SN',
            'yo-NG',
            'zh-CN',
            'zh-HK',
            'zh-MO',
            'zh-SG',
            'zh-TW'
        );

        foreach ($locales as $locale) {
            $locale_region = explode('-', $locale);
            if (strtoupper($country_code) == $locale_region[1]) {
                return $locale_region[0];
            }
        }

        return "en";
    }
}

if (!function_exists('resolveAppLocale')) {
    function resolveAppLocale(string|null $locale): string
    {
        static $resolvedLocales = [];

        $normalizedLocale = strtolower(trim((string)$locale));
        if ($normalizedLocale === '') {
            return 'en';
        }

        if (array_key_exists($normalizedLocale, $resolvedLocales)) {
            return $resolvedLocales[$normalizedLocale];
        }

        if (is_dir(base_path('resources/lang/' . $normalizedLocale))) {
            return $resolvedLocales[$normalizedLocale] = $normalizedLocale;
        }

        $mappedLanguageCode = getLanguageCode(country_code: $normalizedLocale);
        if (is_dir(base_path('resources/lang/' . $mappedLanguageCode))) {
            return $resolvedLocales[$normalizedLocale] = $mappedLanguageCode;
        }

        if (str_contains($normalizedLocale, '-')) {
            $languagePart = explode('-', $normalizedLocale)[0];
            if (is_dir(base_path('resources/lang/' . $languagePart))) {
                return $resolvedLocales[$normalizedLocale] = $languagePart;
            }
        }

        return $resolvedLocales[$normalizedLocale] = 'en';
    }
}

if (!function_exists('getLanguageFlagCode')) {
    function getLanguageFlagCode(array $languageData): string
    {
        $flagCode = $languageData['country_code'] ?? $languageData['code'] ?? 'en';

        return strtolower(trim((string) $flagCode));
    }
}

if (!function_exists('autoTranslator')) {
    function autoTranslator($q, $sl, $tl): array|string
    {
        $res = file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=" . $sl . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q), $_SERVER['DOCUMENT_ROOT'] . "/transes.html");
        $res = json_decode($res);
        return str_replace('_', ' ', $res[0][0][0]);
    }
}


if (!function_exists('getTranslatedValue')) {
    function getTranslatedValue($model, string $key, string $fallback = ''): string
    {
        $locale = getDefaultLanguage();
        $resolvedLocale = resolveAppLocale($locale);
        $defaultLocale = resolveAppLocale(config('app.locale', 'en'));

        // CASE 1: Eloquent Model with translations relation
        if (is_object($model) && method_exists($model, 'translations')) {
            if (!$model->relationLoaded('translations')) {
                $model->load('translations');
            }

            $translatedValue = $model->translations
                ->where('locale', $locale)
                ->where('key', $key)
                ->first()?->value;

            if ($translatedValue !== null && $translatedValue !== '') {
                return $translatedValue;
            }
        }

        // CASE 2: Array with embedded translations
        if (is_array($model) && isset($model['translations']) && is_array($model['translations'])) {
            foreach ($model['translations'] as $translation) {
                if (
                    isset($translation['locale'], $translation['key'], $translation['value']) &&
                    $translation['locale'] === $locale &&
                    $translation['key'] === $key
                ) {
                    return $translation['value'];
                }
            }
        }

        if (
            $fallback !== ''
            && $resolvedLocale !== $defaultLocale
            && translationKeyExistsInCatalog($resolvedLocale, $fallback)
        ) {
            return getTranslateMessageValueByKey($resolvedLocale, $fallback);
        }

        // Fallback
        return $fallback;
    }
}

if (!function_exists('richTextToPlainText')) {
    function richTextToPlainText(?string $value): string
    {
        $decodedValue = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizedWhitespace = str_replace(["\u{00A0}", '&nbsp;'], ' ', $decodedValue);
        $plainText = strip_tags($normalizedWhitespace);
        $plainText = preg_replace('/\s+/u', ' ', $plainText) ?? $plainText;

        return trim($plainText);
    }
}


if (!function_exists('getBusinessSettingTranslation')) {
      function getBusinessSettingTranslation(string $type, string $key = 'value', string $fallback = null): string
{
    $locale = getDefaultLanguage();
    $setting = \App\Models\BusinessSetting::where('type', $type)->first();

    if (!$setting) return $fallback ?? '';

    if ($locale === config('app.locale')) {
        $decoded = json_decode($setting->$key, true);
        return is_array($decoded) ? ($decoded['content'] ?? $fallback ?? '') : ($setting->$key ?? $fallback ?? '');
    }

    $translation = \App\Models\Translation::where('translationable_type', \App\Models\BusinessSetting::class)
        ->where('translationable_id', $setting->id)
        ->where('locale', $locale)
        ->where('key', $key)
        ->first();

    if ($translation && !empty($translation->value)) {
        $translatedJson = json_decode($translation->value, true);
        if (is_array($translatedJson) && isset($translatedJson['content'])) {
            return $translatedJson['content'];
        }

        return $translation->value;
    }

    $decoded = json_decode($setting->$key, true);
    return is_array($decoded) ? ($decoded['content'] ?? $fallback ?? '') : ($setting->$key ?? $fallback ?? '');
}

}
