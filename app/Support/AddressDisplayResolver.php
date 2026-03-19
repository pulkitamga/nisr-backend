<?php

namespace App\Support;

use App\Enums\GlobalConstant;
use App\Models\Area;
use App\Models\City;
use App\Models\State;

class AddressDisplayResolver
{
    public static function resolve(object|array|null $address): array
    {
        return [
            'country' => self::resolveCountry(self::getValue($address, 'country')),
            'state' => self::resolveState(self::getValue($address, 'state')),
            'city' => self::resolveCity(self::getValue($address, 'city')),
            'area' => self::resolveArea(self::getValue($address, 'area')),
        ];
    }

    private static function resolveCountry(?string $value): string
    {
        $normalizedValue = self::normalize($value);
        if ($normalizedValue === null) {
            return '';
        }

        foreach (GlobalConstant::COUNTRIES as $country) {
            if (strcasecmp((string)($country['code'] ?? ''), $normalizedValue) === 0) {
                return self::translateValue((string)$country['name']);
            }

            if (strcasecmp((string)($country['name'] ?? ''), $normalizedValue) === 0) {
                return self::translateValue((string)$country['name']);
            }
        }

        return self::translateValue($normalizedValue);
    }

    private static function resolveState(?string $value): string
    {
        return self::resolveModelName($value, State::class);
    }

    private static function resolveCity(?string $value): string
    {
        return self::resolveModelName($value, City::class);
    }

    private static function resolveArea(?string $value): string
    {
        return self::resolveModelName($value, Area::class);
    }

    private static function resolveModelName(?string $value, string $modelClass): string
    {
        $normalizedValue = self::normalize($value);
        if ($normalizedValue === null) {
            return '';
        }

        $resolvedName = $normalizedValue;

        if (ctype_digit($normalizedValue)) {
            $resolvedName = (string)($modelClass::query()->find((int)$normalizedValue)?->name ?? $normalizedValue);
        }

        return self::translateValue($resolvedName);
    }

    private static function getValue(object|array|null $address, string $key): ?string
    {
        if (is_array($address)) {
            return isset($address[$key]) ? (string)$address[$key] : null;
        }

        if (is_object($address) && isset($address->{$key})) {
            return (string)$address->{$key};
        }

        return null;
    }

    private static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmedValue = trim($value);
        return $trimmedValue === '' ? null : $trimmedValue;
    }

    private static function translateValue(string $value): string
    {
        $locale = (string)(app()->getLocale() ?: 'en');

        if (function_exists('resolveAppLocale')) {
            $locale = resolveAppLocale($locale);
        }

        if (function_exists('getOrPutTranslateMessageValueByKey')) {
            $translatedValue = getOrPutTranslateMessageValueByKey($locale, $value);
            return is_string($translatedValue) && $translatedValue !== '' ? $translatedValue : $value;
        }

        if (function_exists('translate')) {
            try {
                $translatedValue = translate($value);
                return is_string($translatedValue) && $translatedValue !== '' ? $translatedValue : $value;
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }
}
