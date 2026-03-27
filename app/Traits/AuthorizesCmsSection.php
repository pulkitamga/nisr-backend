<?php

namespace App\Traits;

use Closure;

trait AuthorizesCmsSection
{
    protected function cmsPermissionMiddleware(string $permission): Closure
    {
        return function ($request, $next) use ($permission) {
            $admin = auth('admin')->user();

            abort_unless($admin && $admin->can($permission), 403);

            return $next($request);
        };
    }
}
