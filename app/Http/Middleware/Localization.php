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
        $sessionLocale = null;

        if (session()->has('local')) {
            $sessionLocale = session()->get('local');
        } elseif (session()->has('locale')) {
            $sessionLocale = session()->get('locale');
        }

        if (!empty($sessionLocale)) {
            $resolvedLocale = function_exists('resolveAppLocale')
                ? resolveAppLocale((string)$sessionLocale)
                : strtolower((string)$sessionLocale);

            App::setLocale($resolvedLocale);
        }

        return $next($request);
    }
}
