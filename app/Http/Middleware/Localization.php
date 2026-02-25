<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

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

        if (!empty($sessionLocale)) {
            $resolvedLocale = function_exists('resolveAppLocale')
                ? resolveAppLocale((string)$sessionLocale)
                : strtolower((string)$sessionLocale);

            App::setLocale($resolvedLocale);
            session()->put('local', $resolvedLocale);
            session()->put('locale', $resolvedLocale);
        }

        return $next($request);
    }
}
