<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Customer as CustomerExport;
use App\Enums\ViewPaths\Admin\Employee;
use App\Enums\WebConfigKey;
use App\Exports\EmployeeListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\AdminAddRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Models\Admin;
use App\Models\Departments;
use App\Support\AdminPermissionRegistry;
use App\Services\AdminService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Traits\PaginatorTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly AdminRepositoryInterface $adminRepo,
        private readonly DepartmentRepositoryInterface $departmentRepo,
        private readonly BranchRepositoryInterface $branchRepo,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $employee_roles = $this->getActiveAdminRoles();
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: [
                'role_id' => $request['role_id'] ?? 'all',
            ],
            relations: ['roles', 'branch', 'department'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        $departments = $this->departmentRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $branches = $this->branchRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        return view(Employee::LIST[VIEW], compact('employees', 'employee_roles', 'departments', 'branches'));
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $employees = $this->adminRepo->getEmployeeListWhere(
            searchValue: $request['searchValue'],
            filters: [
                'role_id' => $request['role'],
            ],
            relations: ['roles'],
            dataLimit: 'all'
        );
        $active = $employees->where('status', 1)->count();
        $inactive = $employees->where('status', 0)->count();

        $filter = 'all';
        if ($request->has('role') &&  $request['role'] != 'all') {
            $selectedRole = Role::query()
                ->where('guard_name', AdminPermissionRegistry::guard())
                ->find($request['role']);
            $filter = $selectedRole?->name ?? 'all';
        }

        $data = [
            'employees' => $employees,
            'search' => $request['searchValue'],
            'active' => $active,
            'inactive' => $inactive,
            'filter' => $filter
        ];

        return Excel::download(new EmployeeListExport($data), CustomerExport::EMPLOYEES_LIST_XLSX);
    }

    public function getAddView(): View
    {
        $employee_roles = $this->getActiveAdminRoles();
        $departments = $this->departmentRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $branches = $this->branchRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        return view(Employee::ADD[VIEW], compact('employee_roles', 'departments', 'branches'));
    }

    public function add(AdminAddRequest $request, AdminService $adminService): RedirectResponse
    {
        $role = $this->resolveAssignableRole((int)$request['role_id']);
        if (!$role) {
            Toastr::warning(translate('invalid_role_selected'));
            return back()->withInput();
        }

        if ($this->isSuperAdminRole($role) && !auth('admin')->user()?->isSuperAdmin()) {
            Toastr::warning(translate('access_denied'));
            return back()->withInput();
        }

        $data = [
            'name' => $request['name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'branch_id' => json_encode($request['branch_id']),
            'department_id' => $request['department_id'],
            'is_supervisor' => $request->boolean('is_supervisor'),
            'identify_type' => $request['identify_type'],
            'identify_number' => $request['identify_number'],
            'identify_image' => $adminService->getIdentityImages(request: $request),
            'password' => bcrypt($request['password']),
            'status' => 1,
            'image' => $adminService->getProceedImage(request: $request),
            'created_at' => now(),
            'updated_at' => now(),
        ];


        /** @var Admin $admin */
        $admin = $this->adminRepo->add(data: $data);
        if ($admin instanceof Admin) {
            $admin->syncRoles([$role->name]);

            if ($request->boolean('is_department_head') && (int)$request['department_id'] > 0) {
                Departments::query()
                    ->where('id', (int)$request['department_id'])
                    ->update(['head_id' => $admin->id]);
            }
        }
        Toastr::success(translate('employee_added_successfully'));
        return redirect()->route('admin.employee.list');
    }



    public function getView(Request $request): View
    {
        $employee = $this->adminRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['roles']);
        return view(Employee::VIEW[VIEW], compact('employee'));
    }

    public function getUpdateView($id): View
    {
        $employee = $this->adminRepo->getFirstWhere(params: ['id' => $id], relations: ['roles']);
        if ($employee && $employee->hasRole(AdminPermissionRegistry::superAdminRole()) && !auth('admin')->user()?->isSuperAdmin()) {
            abort(403);
        }

        $selectedBranches = is_array($employee->branch_id)
            ? $employee->branch_id
            : (json_decode($employee->branch_id, true) ?? []);
        $adminRoles = $this->getActiveAdminRoles();
        $selectedRoleId = $employee->roles->first()?->id;
        $departments = $this->departmentRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $branches = $this->branchRepo->getListWhere(
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $isDepartmentHead = Departments::query()
            ->where('head_id', (int)$id)
            ->where('id', (int)($employee->department_id ?? 0))
            ->exists();

        return view(Employee::UPDATE[VIEW], compact('adminRoles', 'employee', 'departments', 'branches', 'selectedBranches', 'selectedRoleId', 'isDepartmentHead'));
    }

    public function update(AdminUpdateRequest $request, AdminService $adminService): RedirectResponse
    {
        $employeeId = (int)$request->route('id');
        $role = $this->resolveAssignableRole((int)$request['role_id']);
        if (!$role) {
            Toastr::warning(translate('invalid_role_selected'));
            return back()->withInput();
        }

        if ($this->isSuperAdminRole($role) && !auth('admin')->user()?->isSuperAdmin()) {
            Toastr::warning(translate('access_denied'));
            return back()->withInput();
        }

        $employee = $this->adminRepo->getFirstWhere(params: ['id' => $employeeId]);
        $employee->load('roles');

        if ($employee->hasRole(AdminPermissionRegistry::superAdminRole()) && !auth('admin')->user()?->isSuperAdmin()) {
            Toastr::warning(translate('access_denied'));
            return back()->withInput();
        }

        if ($employee->hasRole(AdminPermissionRegistry::superAdminRole()) && !$this->isSuperAdminRole($role) && $this->countSuperAdminUsers() <= 1) {
            Toastr::warning(translate('last_super_admin_can_not_be_downgraded'));
            return back()->withInput();
        }

        $identity_image = [];
        if ($request->file('identity_image')) {
            $identity_image = $adminService->getIdentityImages(request: $request, oldImages: $employee);
        }

        $data = [
            'name' => $request['name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'branch_id' => $request['branch_id'],
            'department_id' => $request['department_id'],
            'is_supervisor' => $request->boolean('is_supervisor'),
            'password' => $request['password'] ? bcrypt($request['password']) : $employee['password'],
            'image' => $request->file('image') ? $adminService->getProceedImage(request: $request, oldImage: $employee['image']) : $employee['image'],
            'identify_image' => $request->file('identity_image') ? $identity_image : $employee['identify_image'],
            'identify_type' => $request['identify_type'],
            'identify_number' => $request['identify_number'],
            'updated_at' => now(),
        ];

        $this->adminRepo->update(id: $employeeId, data: $data);
        $employee->syncRoles([$role->name]);

        $newDepartmentId = (int)($request['department_id'] ?? 0);
        if ($request->boolean('is_department_head') && $newDepartmentId > 0) {
            Departments::query()
                ->where('head_id', $employeeId)
                ->where('id', '!=', $newDepartmentId)
                ->update(['head_id' => null]);

            Departments::query()
                ->where('id', $newDepartmentId)
                ->update(['head_id' => $employeeId]);
        } else {
            Departments::query()
                ->where('head_id', $employeeId)
                ->update(['head_id' => null]);
        }

        Toastr::success(translate('employee_updated_successfully'));
        return redirect()->route('admin.employee.list');
    }

    public function updateStatus(Request $request): RedirectResponse|JsonResponse
    {
        $admin = $this->adminRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['roles']);
        if ($admin && $admin->hasRole(AdminPermissionRegistry::superAdminRole()) && !auth('admin')->user()?->isSuperAdmin()) {
            $message = translate('access_denied');
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 403);
            }

            Toastr::warning($message);
            return back();
        }

        if ($admin && $admin->hasRole(AdminPermissionRegistry::superAdminRole()) && !$request->boolean('status') && $this->countSuperAdminUsers() <= 1) {
            $message = translate('last_super_admin_can_not_be_disabled');
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 422);
            }

            Toastr::warning($message);
            return back();
        }

        $this->adminRepo->update(id: $request['id'], data: ['status' => $request->get('status', 0)]);
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => translate('Status_Updated'),
            ]);
        }
        Toastr::success(translate('Status_Updated'));
        return back();
    }

    public function updateEmployeeBranch(Request $request): RedirectResponse|JsonResponse
    {
        // Validate the incoming data (make sure branch_id is an array)
        $validated = $request->validate([
            'employee_id' => 'required',  // Assuming admin is the model for employees
            'branch_id' => 'required|array',  // Ensure branch_id is an array
            'branch_id.*' => 'exists:branches,id',  // Validate each branch id
        ]);

        $branchId = json_encode($validated['branch_id']);  // Storing as JSON array

        $this->adminRepo->update(id: $request['employee_id'], data: ['branch_id' => $branchId]);

        // Return response
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => translate('branch_Updated'),
            ], 200);
        }

        Toastr::success(translate('branch_Updated'));
        return back();
    }


    public function updateEmployeeDepartment(Request $request): RedirectResponse|JsonResponse
    {
        $this->adminRepo->update(id: $request['employee_id'], data: ['department_id' => $request->get('department_id', 0)]);
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => translate('department_Updated'),
            ], 200);
        }
        Toastr::success(translate('department_Updated'));
        return back();
    }

    private function getActiveAdminRoles()
    {
        $superAdminRole = AdminPermissionRegistry::superAdminRole();
        $query = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->orderBy('name');

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        if (!auth('admin')->user()?->isSuperAdmin()) {
            $query->where('name', '!=', $superAdminRole);
        }

        return $query->get();
    }

    private function resolveAssignableRole(int $roleId): ?Role
    {
        $query = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->where('id', $roleId);

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        return $query->first();
    }

    private function isSuperAdminRole(Role $role): bool
    {
        return trim(strtolower($role->name)) === trim(strtolower(AdminPermissionRegistry::superAdminRole()));
    }

    private function countSuperAdminUsers(): int
    {
        return Admin::query()
            ->where('status', 1)
            ->whereHas('roles', function ($query) {
                $query->where('name', AdminPermissionRegistry::superAdminRole())
                    ->where('guard_name', AdminPermissionRegistry::guard());
            })
            ->count();
    }
}
