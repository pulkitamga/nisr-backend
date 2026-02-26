<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissionRegistry;
use Brian2694\Toastr\Facades\Toastr;
use Closure;

class ModulePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module, $crud = null)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            Toastr::error(translate('access_Denied') . '!');
            return back();
        }

        if ($crud === null) {
            $module = trim((string)$module);
            $module = AdminPermissionRegistry::moduleAliases()[$module] ?? $module;
            $moduleActions = AdminPermissionRegistry::modules()[$module] ?? null;
            if (is_array($moduleActions) && count($moduleActions) > 0) {
                $permissions = array_map(
                    static fn(string $moduleAction) => sprintf('%s.%s', $module, $moduleAction),
                    $moduleActions
                );
                if ($admin->canAny($permissions)) {
                    return $next($request);
                }
            }
        }

        $permission = AdminPermissionRegistry::fromModuleAction((string)$module, $crud);
        if ($permission !== null && $admin->can($permission)) {
            return $next($request);
        }

        Toastr::error(translate('access_Denied') . '!');
        return back();
    }
}
