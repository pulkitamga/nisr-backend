<?php

namespace App\Http\Middleware;

use App\Utils\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class APILocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $requestedLocale = trim((string)$request->header('lang', ''));

        if ($requestedLocale === '') {
            $requestedLocale = $this->resolveUserLanguage($request) ?? Helpers::default_lang();
        }

        $resolvedLocale = function_exists('resolveAppLocale')
            ? resolveAppLocale($requestedLocale)
            : strtolower($requestedLocale);

        App::setLocale($resolvedLocale ?: 'en');
        return $next($request);
    }

    private function resolveUserLanguage(Request $request): ?string
    {
        $authUser = $request->user();
        if (is_object($authUser) && !empty($authUser->app_language)) {
            return (string)$authUser->app_language;
        }

        $seller = $request->get('seller');
        if (is_object($seller) && !empty($seller->app_language)) {
            return (string)$seller->app_language;
        }

        $deliveryMan = $request->get('delivery_man');
        if (is_object($deliveryMan) && !empty($deliveryMan->app_language)) {
            return (string)$deliveryMan->app_language;
        }

        return null;
    }
}
