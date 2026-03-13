<?php

namespace App\Http\Middleware;

use App\Traits\MaintenanceModeTrait;
use App\Utils\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class MaintenanceModeMiddleware
{
    use MaintenanceModeTrait;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        $currentLocale = strtolower(trim((string) App::getLocale()));
        if (
            $currentLocale === ''
            || !preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $currentLocale)
        ) {
            $currentLocale = strtolower(trim((string) config('app.locale', 'en')));
        }

        $resolvedLocale = function_exists('resolveAppLocale')
            ? resolveAppLocale($currentLocale)
            : $currentLocale;
        App::setLocale($resolvedLocale ?: 'en');

        if ($this->checkMaintenanceMode()) {
            if (
                $request->is('admin/*') ||
                $request->is('login') ||
                $request->is('login/*') ||
                $request->is('maintenance-mode') ||
                $request->is('change-language') ||
                $request->is('system/*') ||
                $request->is('get-session-recaptcha-code') ||
                $request->is('g-recaptcha-response-store')
            ) {
                return $next($request);
            }

            if ($request->is('vendor/*') || request('maintenance_system') == 'vendor') {
                return redirect()->route('maintenance-mode', ['maintenance_system' => 'vendor']);
            }

            return redirect()->route('maintenance-mode');
        }

        return $next($request);
    }

}
