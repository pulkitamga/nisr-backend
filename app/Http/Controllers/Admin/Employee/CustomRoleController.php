<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Enums\ExportFileNames\Admin\Employee;
use App\Enums\ViewPaths\Admin\CustomRole;
use App\Exports\EmployeeRoleListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CustomRoleRequest;
use App\Models\Admin;
use App\Support\AdminPermissionRegistry;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CustomRoleController extends BaseController
{
    use PaginatorTrait;

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getAddView($request);
    }

    public function getAddView(Request $request): View
    {
        $roles = $this->rolesQuery($request)->with('permissions')->get();
        $permissionGroups = AdminPermissionRegistry::groupedPermissionsBySection();

        return view(CustomRole::ADD[VIEW], compact('roles', 'permissionGroups'));
    }

    public function viewRole(Request $request): View
    {
        $roles = $this->rolesQuery($request)->with('permissions')->get();
        $permissionGroups = AdminPermissionRegistry::groupedPermissionsBySection();

        return view(CustomRole::VIEW[VIEW], compact('roles', 'permissionGroups'));
    }

    public function add(CustomRoleRequest $request): RedirectResponse
    {
        if ($this->isSuperAdminRoleName($request->name) && !$this->currentAdminIsSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => [translate('access_Denied')],
            ]);
        }

        $permissions = $this->validatedRegistryPermissions($request->input('permissions', []));

        $role = Role::query()->create($this->roleCreatePayload($request->name));

        if ($this->isSuperAdminRoleName($role->name)) {
            $permissions = AdminPermissionRegistry::all();
        }

        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Toastr::success(translate('role_added_successfully'));
        return back();
    }

    public function getUpdateView(Role $role): View
    {
        $this->ensureAdminGuardRole($role);
        $role->load('permissions');
        $permissionGroups = AdminPermissionRegistry::groupedPermissionsBySection();

        return view(CustomRole::UPDATE[VIEW], compact('role', 'permissionGroups'));
    }

    public function update(CustomRoleRequest $request, Role $role): RedirectResponse
    {
        $this->ensureAdminGuardRole($role);

        if ($this->isSuperAdminRole($role) && !$this->currentAdminIsSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => [translate('access_Denied')],
            ]);
        }

        if ($this->isSuperAdminRoleName($request->name) && !$this->currentAdminIsSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => [translate('access_Denied')],
            ]);
        }

        if ($this->isSuperAdminRole($role) && $request->name !== $role->name) {
            throw ValidationException::withMessages([
                'name' => [translate('super_admin_role_name_can_not_be_changed')],
            ]);
        }

        $permissions = $this->validatedRegistryPermissions($request->input('permissions', []));
        if ($this->isSuperAdminRole($role)) {
            $permissions = AdminPermissionRegistry::all();
        }

        $role->name = $request->name;
        $role->save();
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Toastr::success(translate('role_updated_successfully'));
        return back();
    }


    public function updateStatus(Request $request): JsonResponse
    {
        $role = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->findOrFail((int)$request->get('id'));

        if ($this->isSuperAdminRole($role) && !$request->boolean('status')) {
            return response()->json([
                'success' => 0,
                'message' => translate('super_admin_role_can_not_be_disabled'),
            ], 422);
        }

        if ($this->roleHasStatusColumn()) {
            $role->status = $request->boolean('status');
            $role->save();
        }

        return response()->json([
            'success' => 1,
            'message' => translate('Status_updated_successfully'),
        ], 200);
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $roles = $this->rolesQuery($request)->with('permissions')->get();

        return Excel::download(new EmployeeRoleListExport([
            'roles' => $roles,
            'searchValue' => $request['searchValue'],
            'active' => count($roles->where('status', 1)),
            'inActive' => count($roles->where('status', 0)),
        ]), Employee::EMPLOYEE_ROLE_LIST);
    }

    public function delete(Request $request): JsonResponse
    {
        $role = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->findOrFail((int)$request->get('id'));

        if ($this->isSuperAdminRole($role)) {
            return response()->json([
                'success' => 0,
                'message' => translate('super_admin_role_can_not_be_deleted'),
            ], 422);
        }

        if ($this->assignedAdminCount($role) > 0) {
            return response()->json([
                'success' => 0,
                'message' => translate('role_is_assigned_to_admin_users_reassign_before_delete'),
            ], 422);
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'success' => 1,
            'message' => translate('role_deleted_successfully')
        ], 200);
    }

    private function rolesQuery(Request $request)
    {
        return Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->orderByDesc('id')
            ->when($request['searchValue'], function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['searchValue'] . '%');
            });
    }

    private function ensureAdminGuardRole(Role $role): void
    {
        abort_unless($role->guard_name === AdminPermissionRegistry::guard(), 404);
    }

    private function validatedRegistryPermissions(array $permissions): array
    {
        $permissions = array_values(array_unique(array_filter($permissions, 'is_string')));
        $unknown = array_values(array_filter($permissions, fn(string $permission) => !AdminPermissionRegistry::has($permission)));
        if (count($unknown) > 0) {
            logger()->warning('RBAC rejected unknown permission payload', [
                'unknown_permissions' => $unknown,
            ]);

            throw ValidationException::withMessages([
                'permissions' => [translate('invalid_permission_selected')],
            ]);
        }

        return $permissions;
    }

    private function assignedAdminCount(Role $role): int
    {
        $rolePivot = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $modelKey = config('permission.column_names.model_morph_key') ?: 'model_id';
        $table = config('permission.table_names.model_has_roles');

        return DB::table($table)
            ->where($rolePivot, $role->id)
            ->where('model_type', Admin::class)
            ->count();
    }

    private function roleCreatePayload(string $name): array
    {
        $payload = [
            'name' => $name,
            'guard_name' => AdminPermissionRegistry::guard(),
        ];
        if ($this->roleHasStatusColumn()) {
            $payload['status'] = true;
        }

        return $payload;
    }

    private function roleHasStatusColumn(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('roles', 'status');
    }

    private function isSuperAdminRole(Role $role): bool
    {
        return $this->isSuperAdminRoleName($role->name);
    }

    private function isSuperAdminRoleName(string $name): bool
    {
        return trim(strtolower($name)) === trim(strtolower(AdminPermissionRegistry::superAdminRole()));
    }

    private function currentAdminIsSuperAdmin(): bool
    {
        return auth('admin')->user()?->isSuperAdmin() === true;
    }
}
