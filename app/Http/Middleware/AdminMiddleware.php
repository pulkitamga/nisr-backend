<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        // Ensure Gate/Blade authorization directives resolve against the admin guard
        // for the full lifetime of admin-panel requests.
        Auth::shouldUse('admin');

        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        abort(404);
    }
}
