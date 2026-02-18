<?php

namespace App\Http\Middleware;

use App\Utils\Helpers;
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
        // Get current admin user role
        $user_role = auth('admin')->user()->role;

        if ($user_role->status != 1) {
            Toastr::error(translate('access_Denied') . '!');
            return back();
        }

        if (auth('admin')->user()->admin_role_id == 1) {
            return $next($request);
        }

        $module_permissions = json_decode($user_role->module_access, true); // array

        if (!$module_permissions || !isset($module_permissions[$module])) {
            Toastr::error(translate('access_Denied') . '!');
            return back();
        }

        if ($crud === null) {
            return $next($request);
        }

        if (in_array($crud, $module_permissions[$module])) {
            return $next($request);
        }

        Toastr::error(translate('access_Denied') . '!');
        return back();
    }
}
