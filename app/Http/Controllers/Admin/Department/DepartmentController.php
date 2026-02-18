<?php

namespace App\Http\Controllers\Admin\Department;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRoleRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Enums\ViewPaths\Admin\Department;
use App\Enums\WebConfigKey;
use App\Exports\BranchListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\DepartmentAddRequest;
use App\Http\Requests\Admin\DepartmentUpdateRequest;
use App\Http\Requests\Admin\DepartmentUsersAddRequest;
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
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\ShippingMethodArea;

class DepartmentController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;
    use PushNotificationTrait;
    use EmailTemplateTrait;

    public function __construct(
        private readonly DepartmentRepositoryInterface         $departmentRepo,
        private readonly AdminRoleRepositoryInterface          $adminRoleRepo,
        private readonly AdminRepositoryInterface              $adminRepo,
        private readonly DepartmentService                     $departmentService,
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
        $aRoles = $this->adminRoleRepo->getEmployeeRoleList(dataLimit: 'all');
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy:['id'=>'desc'],
            searchValue: $request['searchValue'],
            relations: [],
            dataLimit:'all'
        );

        return view(Department::ADD[VIEW], compact('aRoles', 'employees'));
    }

    public function fViewBranchUsers(Request $request, $dept_id): View
    {
        $departments = $this->departmentRepo->getFirstWhere(params: ['id' => $dept_id]);
        $aDepartmentUsers = $this->departmentRepo->getUsersListWhere(
            orderBy: ['id' => 'ASC'],
            searchValue: $request['searchValue'],
            filters: ['department_id' => $dept_id],
            relations: ['user_role'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        // dd($aDepartmentUsers[0]['user_role']['name']);

        return view(Department::USER_VIEW[VIEW], compact('departments', 'aDepartmentUsers', 'dept_id'));
    }

    public function fAddBranchUsers(Request $request, $dept_id): View
    {
        $aRoles = $this->adminRoleRepo->getEmployeeRoleList(dataLimit: 'all');
        $departments = $this->departmentRepo->getFirstWhere(params: ['id' => $dept_id]);
        return view(Department::USER_ADD[VIEW], compact('departments', 'dept_id', 'aRoles'));
    }

    public function add(DepartmentAddRequest $request, DepartmentService $service): JsonResponse
    {
        $success = 1;
        $dataArray = $service->getAddData(request: $request);
        $savedRequest = $this->departmentRepo->add(data: $dataArray);
         return response()->json(['message' => translate('Department_added_successfully')]);
    }

    public function addDepartmentUsers(DepartmentUsersAddRequest $request, DepartmentService $service): JsonResponse
    {
        // dd($request);
        $success = 1;
        $this->departmentRepo->addDepartmentUsers(data: $service->getAddDepartmentUsers($request, $request['dept_id']));
         return response()->json(['message' => translate('department_user_added_successfully')]);
    }

    public function getUpdateView($id): View
    {
        $department = $this->departmentRepo->getFirstWhere(params:['id' => $id]);
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy:['id'=>'desc'],
            relations: [],
            dataLimit:'all'
        );
        return view(Department::UPDATE[VIEW], compact('department', 'employees'));
    }

    public function update(DepartmentUpdateRequest $request, DepartmentService $departmentService): JsonResponse
    {
        $aDepartmentDetails = $this->departmentRepo->getFirstWhere(params:['id' => $request['id']]);
        if (!$aDepartmentDetails) {
            return response()->json(['message' => translate('department not found')]);
        }
        $this->departmentRepo->update(id:$request['id'], data: $this->departmentService->getAddData($request));
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


}
