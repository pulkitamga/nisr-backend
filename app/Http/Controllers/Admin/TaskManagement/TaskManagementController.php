<?php

namespace App\Http\Controllers\Admin\TaskManagement;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRoleRepositoryInterface;
use App\Enums\ViewPaths\Admin\TaskManagement;
use App\Enums\WebConfigKey;
use App\Exports\BranchListExport;
use App\Http\Controllers\BaseController;
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

class TaskManagementController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;
    use PushNotificationTrait;
    use EmailTemplateTrait;

    public function __construct(
        private readonly DepartmentRepositoryInterface         $departmentRepo,
        private readonly AdminRoleRepositoryInterface          $adminRoleRepo,   
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
            relations: ['users' => function ($query) {
                $query->where('user_type', 8);
            }],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(TaskManagement::INDEX[VIEW], compact('departments', 'current_date'));
    }
}
