<?php

namespace App\Http\Middleware;

use App\Services\AccessGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAccess
{
    public function __construct(
        private readonly AccessGuard $accessGuard,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->accessGuard->shouldBypassHttpRequest()) {
            return $next($request);
        }

        $this->accessGuard->ensureValid();

        return $next($request);
    }
}
