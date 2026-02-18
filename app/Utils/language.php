<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

if (!function_exists('translate')) {
    function translate($key = null): string|null
    {
        $local = getDefaultLanguage();

        if ($key) {
            App::setLocale($local);
            $key = getOrPutTranslateMessageValueByKey(local: $local, key: $key);

            // if ($key && !\App\Utils\Helpers::isTranslated($key, $local)) {
            //     $key = autoTranslator($key, 'en', $local); // assuming 'en' is source
            // }
        }

        App::setLocale(getLanguageCode(country_code: $local));
        return $local == 'en' ? ucfirst($key) : $key;
    }

    function getOrPutTranslateMessageValueByKey(string $local, string $key): array|string|null
    {
        try {
            $translatedMessagesPath = base_path('resources/lang/' . $local . '/messages.php');
            $newMessagesPath = base_path('resources/lang/' . $local . '/new-messages.php');

            $translatedMessagesArray = file_exists($translatedMessagesPath) ? include($translatedMessagesPath) : [];
            $newMessagesArray = file_exists($newMessagesPath) ? include($newMessagesPath) : [];

            if (!is_array($translatedMessagesArray)) {
                $translatedMessagesArray = [];
            }
            if (!is_array($newMessagesArray)) {
                $newMessagesArray = [];
            }

            $key = str_replace('"', '', $key);
            $processedKey = ucfirst(str_replace('_', ' ', removeSpecialCharacters($key)));

            if (!array_key_exists($key, $translatedMessagesArray) && !array_key_exists($key, $newMessagesArray)) {
                $newMessagesArray[$key] = $processedKey;

                $languageFileContents = "<?php\n\nreturn [\n";
                foreach ($newMessagesArray as $languageKey => $value) {
                    $languageFileContents .= "\t\"" . $languageKey . "\" => \"" . $value . "\",\n";
                }
                $languageFileContents .= "];\n";

                $targetPath = $newMessagesPath;
                file_put_contents($targetPath, $languageFileContents);
                $message = $processedKey;
            } elseif (array_key_exists($key, $translatedMessagesArray)) {
                $message = __('messages.' . $key);
            } elseif (array_key_exists($key, $newMessagesArray)) {
                $message = __('new-messages.' . $key);
            } else {
                $message = __('messages.' . $key);
            }
        } catch (\Exception $exception) {
            $message = ucfirst(str_replace('_', ' ', removeSpecialCharacters(str_replace("\'", "'", $key))));
        }
        return $local == 'en' ? ucfirst($message) : $message;
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
        if (strpos(url()->current(), '/api')) {
            $lang = App::getLocale();
        } elseif (session()->has('local')) {
            $lang = session('local');
        } else {
            $data = getWebConfig('language');
            if (!is_array($data)) {
                $data = [];
            }
            $code = 'en';
            $direction = 'ltr';
            foreach ($data as $ln) {
                if (!is_array($ln)) {
                    continue;
                }
                if (array_key_exists('default', $ln) && $ln['default']) {
                    $code = $ln['code'];
                    if (array_key_exists('direction', $ln)) {
                        $direction = $ln['direction'];
                    }
                }
            }
            session()->put('local', $code);
            Session::put('direction', $direction);
            $lang = $code;
        }
        return $lang;
    }
}

if (!function_exists('getLanguageName')) {
    function getLanguageName(string $key): string
    {
        $values = getWebConfig('language');
        if (!is_array($values)) {
            return $key;
        }
        foreach ($values as $value) {
            if (is_array($value) && array_key_exists('code', $value) && $value['code'] == $key && array_key_exists('name', $value)) {
                $key = $value['name'];
            }
        }
        return $key;
    }
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $country_code): string
    {
        $normalizedInput = strtolower(trim($country_code));
        if ($normalizedInput === '') {
            return 'en';
        }

        // If caller already provided locale code (e.g. en, ar, fr), use it directly.
        if (is_dir(base_path('resources/lang/' . $normalizedInput))) {
            return $normalizedInput;
        }

        // If caller provided locale tag (e.g. en-US), return language segment.
        if (str_contains($normalizedInput, '-')) {
            return explode('-', $normalizedInput)[0];
        }

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

        // CASE 1: Eloquent Model with translations relation
        if (is_object($model) && method_exists($model, 'translations')) {
            if (!$model->relationLoaded('translations')) {
                $model->load('translations');
            }

            return $model->translations
                ->where('locale', $locale)
                ->where('key', $key)
                ->first()?->value ?? $fallback;
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

        // Fallback
        return $fallback;
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
