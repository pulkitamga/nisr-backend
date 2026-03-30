<?php

namespace App\Http\Controllers\Admin\Department;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ViewPaths\Admin\Department;
use App\Enums\WebConfigKey;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\DepartmentAddRequest;
use App\Http\Requests\Admin\DepartmentUpdateRequest;
use App\Http\Requests\Admin\DepartmentUsersAddRequest;
use App\Support\AdminPermissionRegistry;
use App\Services\DepartmentService;
use App\Traits\CommonTrait;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use App\Traits\PushNotificationTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DepartmentController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;
    use PushNotificationTrait;
    use EmailTemplateTrait;

    public function __construct(
        private readonly DepartmentRepositoryInterface         $departmentRepo,
        private readonly DepartmentService                     $departmentService,
        private readonly TranslationRepositoryInterface        $translationRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $current_date = date('Y-m-d');
        $departments = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['employee'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        return view(Department::LIST[VIEW], compact('departments', 'current_date'));
    }

    public function getAddView(Request $request): View
    {
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];
        return view(Department::ADD[VIEW], compact('language', 'defaultLanguage'));
    }

    public function fViewBranchUsers(Request $request, $dept_id): View
    {
        $departments = $this->departmentRepo->getFirstWhere(params: ['id' => $dept_id]);
        $aDepartmentUsers = $this->departmentRepo->getUsersListWhere(
            orderBy: ['id' => 'ASC'],
            searchValue: $request['searchValue'],
            filters: [
                'department_id' => $dept_id,
                'status' => 'active',
            ],
            relations: ['roles'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(Department::USER_VIEW[VIEW], compact('departments', 'aDepartmentUsers', 'dept_id'));
    }

    public function fAddBranchUsers(Request $request, $dept_id): View
    {
        $aRoles = $this->getActiveAdminRoles();
        $departments = $this->departmentRepo->getFirstWhere(params: ['id' => $dept_id]);
        return view(Department::USER_ADD[VIEW], compact('departments', 'dept_id', 'aRoles'));
    }

    public function add(DepartmentAddRequest $request, DepartmentService $service): JsonResponse
    {
        $success = 1;
        $dataArray = $service->getAddData(request: $request);
        if (!array_key_exists('head_id', $dataArray)) {
            $dataArray['head_id'] = 0;
        }
        $savedRequest = $this->departmentRepo->add(data: $dataArray);
        $this->translationRepo->add(request: $request, model: 'App\Models\Departments', id: $savedRequest->id);
         return response()->json(['message' => translate('Department_added_successfully')]);
    }

    public function addDepartmentUsers(DepartmentUsersAddRequest $request, DepartmentService $service): JsonResponse
    {
        $role = $this->resolveAssignableRole((int)$request['role_id']);
        if (!$role) {
            return response()->json(['message' => translate('invalid_role_selected')], 422);
        }

        $departmentUser = $this->departmentRepo->addDepartmentUsers(
            data: $service->getAddDepartmentUsers($request, (int)$request['dept_id'])
        );
        if ($departmentUser) {
            $departmentUser->syncRoles([$role->name]);
        }
         return response()->json(['message' => translate('department_user_added_successfully')]);
    }

    public function getUpdateView($id): View
    {
        $department = $this->departmentRepo->getFirstWhere(
            params:['id' => $id],
            relations: ['employee', 'translations']
        );
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];
        return view(Department::UPDATE[VIEW], compact('department', 'language', 'defaultLanguage'));
    }

    public function update(DepartmentUpdateRequest $request, DepartmentService $departmentService): JsonResponse
    {
        $aDepartmentDetails = $this->departmentRepo->getFirstWhere(params:['id' => $request['id']]);
        if (!$aDepartmentDetails) {
            return response()->json(['message' => translate('department not found')]);
        }
        $this->departmentRepo->update(id:$request['id'], data: $this->departmentService->getAddData($request));
        $this->translationRepo->update(request: $request, model: 'App\Models\Departments', id: $request['id']);
        return response()->json(['message' => translate('department_updated_successfully')]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->departmentRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        if ($request['status'] == 1) {
            Toastr::success(translate('branch_has_been_approved_successfully'));
        } else if ($request['status'] == 0) {
            Toastr::info(translate('branch_has_been_rejected_successfully'));
        } else if ($request['status'] == "suspended") {
            $this->departmentRepo->update(id: $request['id'], data: ['auth_token' => Str::random(80)]);
            Toastr::info(translate('branch_has_been_suspended_successfully'));
        }
       
        return back();
    }

    public function delete(Request $request, DepartmentService $departmentService): RedirectResponse
    {
        $this->departmentRepo->updateDepartmentUsers(id: $request['id'], data: ['status' => 'inactive']);
        Toastr::success(translate('branch_user_deleted_successfully'));
        return redirect()->back();
    }

    private function getActiveAdminRoles()
    {
        $query = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->where('name', '!=', AdminPermissionRegistry::superAdminRole())
            ->orderBy('name');

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        return $query->get();
    }

    private function resolveAssignableRole(int $roleId): ?Role
    {
        $query = Role::query()
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->where('id', $roleId)
            ->where('name', '!=', AdminPermissionRegistry::superAdminRole());

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        return $query->first();
    }


}
