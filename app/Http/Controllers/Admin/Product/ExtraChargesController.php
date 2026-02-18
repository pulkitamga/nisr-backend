<?php

namespace App\Http\Controllers\Admin\Product;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ExtraChargesRepositoryInterface;
use App\Enums\ViewPaths\Admin\ExtraCharges;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ExtraChargesAddRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Services\ExtraChargesService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ExtraChargesController extends BaseController
{
    /**
     * @param ExtraChargesRepositoryInterface $extraChargesRepo
     * @param ExtraChargesService $extraChargesService
     * @param CategoryRepositoryInterface $categoryRepo
     */
    public function __construct(
        private readonly ExtraChargesRepositoryInterface       	 $extraChargesRepo,
        private readonly ExtraChargesService                   	 $extraChargesService,
        private readonly CategoryRepositoryInterface             $categoryRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getExtraChargesView(request: $request, type: $type);
    }

    /**
     * @return View
     */
    public function getExtraChargesView(Request $request, string $type): View
    {
        $aExtraCharges = $this->extraChargesRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['type' => $type],
            relations: ['category'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );

        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        return view(ExtraCharges::LIST[VIEW], compact('aExtraCharges', 'type', 'categories'));
    }

    /**
     * @param ExtraChargesAddRequest $request
     * @return RedirectResponse
     */
    public function add(ExtraChargesAddRequest $request): RedirectResponse
    {
    	$aExtraCharges = $this->extraChargesRepo->getListWhere(
            filters: ['type' => $request['type'], 'category_id' => $request['category']],
            dataLimit: 'all'
        );

        if($aExtraCharges->isNotEmpty()){
        	Toastr::error(translate('Charges_already_added_for_this_catgeory'));
        	return redirect()->back();
        }

        $this->extraChargesRepo->add($this->extraChargesService->getAddData(request: $request));
        Toastr::success(translate('successfully_added'));
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $this->extraChargesRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        return response()->json(['success' => 1,], status: 200);
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->extraChargesRepo->delete(params: ['id' => $request['id']]);
        return redirect()->back();
    }
}