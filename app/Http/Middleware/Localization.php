<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sessionLocale = strtolower(trim((string)session('local', session('locale'))));
        if (
            $sessionLocale !== ''
            && !preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $sessionLocale)
        ) {
            $sessionLocale = '';
            session()->forget(['local', 'locale']);
        }

        if (empty($sessionLocale)) {
            $cookieLocale = strtolower(trim((string)($request->cookie('local') ?? $request->cookie('locale') ?? '')));
            if ($cookieLocale !== '') {
                // Validate locale format - prevent license keys or other invalid values
                if (preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $cookieLocale)) {
                    $sessionLocale = $cookieLocale;
                    session()->put('local', $cookieLocale);
                    session()->put('locale', $cookieLocale);
                }
            }
        }

        $localeToResolve = !empty($sessionLocale)
            ? (string)$sessionLocale
            : ((string)config('app.locale', 'en'));

        $resolvedLocale = function_exists('resolveAppLocale')
            ? resolveAppLocale($localeToResolve)
            : strtolower($localeToResolve);

        App::setLocale($resolvedLocale);
        session()->put('local', $resolvedLocale);
        session()->put('locale', $resolvedLocale);
        session()->put('direction', $this->resolveDirectionByLocale(
            rawLocale: $localeToResolve,
            resolvedLocale: $resolvedLocale
        ));

        return $next($request);
    }

    private function resolveDirectionByLocale(string $rawLocale, string $resolvedLocale): string
    {
        $defaultDirection = 'ltr';
        if (!function_exists('getWebConfig')) {
            return $defaultDirection;
        }

        if (!Schema::hasTable('business_settings')) {
            return $defaultDirection;
        }

        try {
            $languageList = getWebConfig('language');
        } catch (\Throwable) {
            return $defaultDirection;
        }

        if (!is_array($languageList)) {
            return $defaultDirection;
        }

        $rawLocale = strtolower(trim($rawLocale));
        $resolvedLocale = strtolower(trim($resolvedLocale));
        $rawBaseLocale = preg_split('/[_-]/', $rawLocale)[0] ?? '';
        $resolvedBaseLocale = preg_split('/[_-]/', $resolvedLocale)[0] ?? '';

        $lookupCodes = array_unique(array_filter([
            $rawLocale,
            $resolvedLocale,
            $rawBaseLocale,
            $resolvedBaseLocale,
        ]));

        foreach ($languageList as $languageData) {
            if (!is_array($languageData)) {
                continue;
            }

            $languageCode = strtolower(trim((string)($languageData['code'] ?? '')));
            if ($languageCode === '' || !in_array($languageCode, $lookupCodes, true)) {
                continue;
            }

            $direction = strtolower(trim((string)($languageData['direction'] ?? $defaultDirection)));
            return in_array($direction, ['ltr', 'rtl'], true) ? $direction : $defaultDirection;
        }

        return $defaultDirection;
    }
}
