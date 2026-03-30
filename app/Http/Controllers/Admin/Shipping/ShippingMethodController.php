<?php

namespace App\Http\Controllers\Admin\Shipping;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CategoryShippingCostRepositoryInterface;
use App\Contracts\Repositories\ShippingMethodRepositoryInterface;
use App\Contracts\Repositories\ShippingMethodAreaRepositoryInterface;
use App\Contracts\Repositories\ShippingTypeRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ViewPaths\Admin\ShippingMethod;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Http\Requests\Admin\ShippingMethodAreaRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Services\CategoryShippingCostService;
use App\Services\ShippingMethodService;
use App\Services\ShippingMethodAreaService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ShippingMethodArea;
use App\Models\State;
use App\Models\City;
use App\Models\Area;

class ShippingMethodController extends BaseController
{
    /**
     * @param ShippingMethodRepositoryInterface $shippingMethodRepo
     * @param ShippingTypeRepositoryInterface $shippingTypeRepo
     * @param ShippingMethodService $shippingMethodService
     * @param CategoryRepositoryInterface $categoryRepo
     * @param CategoryShippingCostRepositoryInterface $categoryShippingCostRepo
     * @param CategoryShippingCostService $categoryShippingCostService
     * @param BusinessSettingRepositoryInterface $businessSettingRepo
     */
    public function __construct(
        private readonly ShippingMethodRepositoryInterface       $shippingMethodRepo,
        private readonly ShippingMethodAreaRepositoryInterface   $shippingMethodAreaRepo,
        private readonly ShippingTypeRepositoryInterface         $shippingTypeRepo,
        private readonly ShippingMethodService                   $shippingMethodService,
        private readonly ShippingMethodAreaService               $shippingMethodAreaService,
        private readonly CategoryRepositoryInterface             $categoryRepo,
        private readonly CategoryShippingCostRepositoryInterface $categoryShippingCostRepo,
        private readonly CategoryShippingCostService             $categoryShippingCostService,
        private readonly BusinessSettingRepositoryInterface      $businessSettingRepo,
        private readonly TranslationRepositoryInterface          $translationRepo,
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
        return $this->getShippingMethodsView();
    }

    /**
     * @return View
     */
    public function getShippingMethodsView(): View
    {
        $shippingMethods = $this->shippingMethodRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['creator_type' => 'admin'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        $shippingMethodsArea = $this->shippingMethodAreaRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['creator_type' => 'admin'],
            relations: ['state', 'city'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        // dd($shippingMethodsArea);
        $allCategoryIds = $this->categoryRepo->getListWhere(filters: ['position' => 0])->pluck('id')->toArray();
        $allCategoryShippingCostArray = $this->categoryShippingCostRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['seller_id' => 0],
        )->pluck('category_id')->toArray();
        foreach ($allCategoryIds as $id) {
            if (!in_array($id, $allCategoryShippingCostArray)) {
                $this->categoryShippingCostRepo->add(
                    data: $this->categoryShippingCostService->getAddCategoryWiseShippingCostData(
                        addedBy: 'admin',
                        id: $id
                    )
                );
            }
        }
        $adminShipping = $this->shippingTypeRepo->getFirstWhere(
            params: ['seller_id' => 0]
        );
        $allCategoryShippingCost = $this->categoryShippingCostRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['seller_id' => 0],
            relations: ['category']
        );
        $states = State::all();  // Assuming you have a State model to get the states
        $cities = City::all();
        $areas = Area::all();
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];
        return view(ShippingMethod::INDEX[VIEW], compact('allCategoryShippingCost', 'shippingMethods', 'shippingMethodsArea', 'adminShipping', 'states', 'cities', 'areas', 'language', 'defaultLanguage'));
    }

    /**
     * @param ShippingMethodRequest $request
     * @return RedirectResponse
     */
    public function add(ShippingMethodRequest $request): RedirectResponse
    {
        $saved = $this->shippingMethodRepo->add($this->shippingMethodService->addShippingMethodData(request: $request, addedBy: 'admin'));
        $this->translationRepo->add(request: $request, model: 'App\Models\ShippingMethod', id: $saved->id);
        Toastr::success(translate('successfully_added'));
        return redirect()->back();
    }

    /**
     * @param ShippingMethodAreaRequest $request
     * @return RedirectResponse
     */
    public function addAreaWiseShipping(ShippingMethodAreaRequest $request): RedirectResponse
    {
        $this->shippingMethodAreaRepo->add($this->shippingMethodAreaService->addShippingMethodAreaData(request: $request, addedBy: 'admin'));
        Toastr::success(translate('successfully_added'));
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $this->shippingMethodRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        return response()->json(['success' => 1,], status: 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAreaStatus(Request $request): JsonResponse
    {
        $this->shippingMethodAreaRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        return response()->json(['success' => 1,], status: 200);
    }

    /**
     * @param string|int $id
     * @return View|RedirectResponse
     */
    public function getUpdateView(string|int $id): View|RedirectResponse
    {
        if ($id != 1) {
            $method = $this->shippingMethodRepo->getFirstWhere(params: ['id' => $id]);
            $language = getWebConfig(name: 'pnc_language') ?? null;
            $defaultLanguage = $language[0];
            return view(ShippingMethod::UPDATE[VIEW], compact('method', 'language', 'defaultLanguage'));
        }
        Toastr::success(translate('can_not_update_first_records'));
        return back();
    }

    /**
     * @param string|int $id
     * @return View|RedirectResponse
     */
    public function getAreaUpdateView(string|int $id): View|RedirectResponse
    {
        $states = State::all();  // Assuming you have a State model to get the states
        $cities = City::all();
        if ($id != 1) {
            $method = $this->shippingMethodAreaRepo->getFirstWhere(params: ['id' => $id]);
            $language = getWebConfig(name: 'pnc_language') ?? null;
            $defaultLanguage = $language[0];
            return view(ShippingMethod::AREA_UPDATE[VIEW], compact('method', 'states', 'cities', 'language', 'defaultLanguage'));
        }
        return back();
    }

    /**
     * @param ShippingMethodRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function update(ShippingMethodRequest $request, string|int $id): RedirectResponse
    {
        $this->shippingMethodRepo->update(id: $id, data: $this->shippingMethodService->addShippingMethodData(request: $request, addedBy: 'admin'));
        $this->translationRepo->update(request: $request, model: 'App\Models\ShippingMethod', id: $id);
        Toastr::success(translate('successfully_updated'));
        return redirect()->route(ShippingMethod::INDEX[ROUTE]);
    }

    /**
     * @param ShippingMethodRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function updateArea(ShippingMethodAreaRequest $request, string|int $id): RedirectResponse
    {
        $this->shippingMethodAreaRepo->update(id: $id, data: $this->shippingMethodAreaService->addShippingMethodAreaData(request: $request, addedBy: 'admin'));
        Toastr::success(translate('successfully_updated'));
        return redirect()->route(ShippingMethod::INDEX[ROUTE]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $this->shippingMethodRepo->delete(params: ['id' => $request['id']]);
        return redirect()->back();
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteArea(Request $request): RedirectResponse
    {
        $this->shippingMethodAreaRepo->delete(params: ['id' => $request['id']]);
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateShippingResponsibility(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'shipping_method', value: $request['shipping_method']);
        Toastr::success(translate('successfully_updated'));
        return redirect()->back();
    }

    public function fGetCountryState(Request $request)
    {
        $success = 1;
        $sCountryCode = $request->input('sCountryCode');
        $aStatesData = State::where('country', $sCountryCode)->get();

        return response()->json([
            'success' => $success,
            'data' => $aStatesData
        ], 200);
    }

    public function fGetStateCities(Request $request)
    {
        $success = 1;
        $iStateId = $request->input('iStateId');
        $aCitiesData = City::where('state_id', $iStateId)->get();

        return response()->json([
            'success' => $success,
            'data' => $aCitiesData
        ], 200);
    }
    public function fGetCitiesArea(Request $request)
    {
        $success = 1;
        $iCityId = $request->input('iCityId');
        $aAreaData = Area::where('city_id', $iCityId)->get();

        return response()->json([
            'success' => $success,
            'data' => $aAreaData
        ], 200);
    }

}
