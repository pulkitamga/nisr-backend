<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class APIGuestMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */

    // old 
    // public function handle(Request $request, Closure $next): mixed
    // {
    //     if ($request->header('Authorization') && app('auth')->guard('api')) {
    //         $request->merge(['user' => auth('api')->user()]);
    //         return $next($request);
    //     } elseif ($request->guest_id) {
    //         return $next($request);
    //     }

    //     return response()->json(['Unauthorized', 401]);
    // }



    // public function handle(Request $request, Closure $next): mixed
    // {


    //     // Logged-in API customer
    //     if ($request->bearerToken() && auth('api')->check()) {
    //         $request->setUserResolver(function () {
    //             return auth('api')->user();
    //         });

    //         return $next($request);
    //     }

    //     // Guest customer
    //     if ($request->filled('guest_id')) {
    //         return $next($request);
    //     }

    //     return response()->json(['message' => 'Access denied'], 403);
    // }

    public function handle(Request $request, Closure $next): mixed
    {
        // 1. Try to get the API user
        $user = auth('api')->user();

        if ($request->bearerToken() && $user) {
            // This satisfies Laravel's internal methods
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // IMPORTANT: This satisfies your Helper's 'isset($request->user)' check
            $request->merge(['user' => $user]);

            return $next($request);
        }

        // 2. Handle Guest Logic
        if ($request->filled('guest_id')) {
            // We force these keys so your Helper logic (payment_request_from) returns 'offline' 
            // instead of falling through to the end
            $request->merge([
                'payment_request_from' => 'app',
                'is_guest' => true
            ]);
            return $next($request);
        }

        return response()->json(['message' => 'Please login first'], 401);
    }
}
