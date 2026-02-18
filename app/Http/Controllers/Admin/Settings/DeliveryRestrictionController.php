<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\DeliveryCountryCodeRepositoryInterface;
use App\Contracts\Repositories\DeliveryStateRepositoryInterface;
use App\Contracts\Repositories\DeliveryCityRepositoryInterface;
use App\Contracts\Repositories\DeliveryZipCodeRepositoryInterface;
use App\Contracts\Repositories\DeliveryAreaRepositoryInterface;
use App\Enums\ViewPaths\Admin\DeliveryRestriction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\DeliveryCountryCodeAddRequest;
use App\Http\Requests\Admin\DeliveryStateAddRequest;
use App\Http\Requests\Admin\DeliveryCityAddRequest;
use App\Http\Requests\Admin\DeliveryZipCodeAddRequest;
use App\Http\Requests\Admin\DeliveryAreaAddRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;
use App\Models\DeliveryArea;
use App\Models\Area;

class DeliveryRestrictionController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface     $businessSettingRepo,
        private readonly DeliveryCountryCodeRepositoryInterface $deliveryCountryCodeRepo,
        private readonly DeliveryStateRepositoryInterface     $deliveryStateRepo,
        private readonly DeliveryCityRepositoryInterface     $deliveryCityRepo,
        private readonly DeliveryZipCodeRepositoryInterface     $deliveryZipCodeRepo,
        private readonly DeliveryAreaRepositoryInterface     $deliveryAreaRepo,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView($request);
    }

       public function getView(): View
{
    $storedCountries = $this->deliveryCountryCodeRepo->getListWhere(orderBy: ['id' => 'desc'], dataLimit: getWebConfig(name: 'pagination_limit'));
    $storedStates = $this->deliveryStateRepo->getListWhere(
        orderBy: ['id' => 'desc'],
        dataLimit: getWebConfig(name: 'pagination_limit')
    );
    $storedArea = $this->deliveryAreaRepo->getListWhere(
        orderBy: ['id' => 'desc'],
        relations: ['areaInfo'],
        dataLimit: getWebConfig(name: 'pagination_limit')
    );

    $storedCities = $this->deliveryCityRepo->getListWhere(orderBy: ['id' => 'desc'], dataLimit: getWebConfig(name: 'pagination_limit'));
    $countryRestrictionStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_country_restriction']);
    $stateRestrictionStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_state_restriction']);
    $cityRestrictionStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_city_restriction']);
    $zipCodeAreaRestrictionStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_zip_code_area_restriction']);
    $areaRestrictionStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_area_restriction']);
    
    // Get stored IDs
    $storedCountryCode = $storedCountries->pluck('country_code')->toArray();
    $storedStatesIds = $storedStates->pluck('state_id')->toArray();
    $storedCitiesIds = $storedCities->pluck('city_id')->toArray();
    $storedAreasIds = $storedArea->pluck('area_id')->toArray();
    
    // Filter countries based on which have states in database
    $countryCodesWithStates = State::pluck('country')->unique()->toArray();
    $countries = array_filter(COUNTRIES, function($country) use ($countryCodesWithStates) {
        return in_array($country['code'], $countryCodesWithStates);
    }); 
    
    // Filter states to show only those from selected countries
    $states = State::whereIn('country', $storedCountryCode)->get();
    
    // Filter cities to show only those from selected states
    $cities = City::whereIn('state_id', $storedStatesIds)->get();
    
    // Filter areas to show only those from selected cities
    $areas = Area::whereIn('city_id', $storedCitiesIds)->get();
    
    $storedZip = $this->deliveryZipCodeRepo->getListWhere(orderBy: ['id' => 'desc'], dataLimit: getWebConfig(name: 'pagination_limit'));
    
    return view(DeliveryRestriction::VIEW[VIEW], compact(
        'countries', 
        'storedCountries', 
        'storedCountryCode', 
        'storedZip', 
        'countryRestrictionStatus', 
        'stateRestrictionStatus', 
        'cityRestrictionStatus', 
        'zipCodeAreaRestrictionStatus', 
        'areaRestrictionStatus', 
        'storedArea', 
        'states', 
        'cities', 
        'storedStates', 
        'storedCities', 
        'storedStatesIds', 
        'storedCitiesIds', 
        'areas', 
        'storedArea', 
        'storedAreasIds'
    ));
}

    public function add(DeliveryCountryCodeAddRequest $request): RedirectResponse
    {
        foreach ($request->input('country_code') as $code) {
            $this->deliveryCountryCodeRepo->add(data: ['country_code' => $code, 'created_at' => now()]);
        }
        Toastr::success(translate('delivery_country_added_successfully'));
        return back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->deliveryCountryCodeRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('delivery_country_deleted_successfully'));
        return back();
    }

    public function addState(DeliveryStateAddRequest $request): RedirectResponse
    {
        foreach ($request->input('state') as $code) {
            $this->deliveryStateRepo->add(data: ['state_id' => $code, 'created_at' => now()]);
        }
        Toastr::success(translate('delivery_state_added_successfully'));
        return back();
    }

    public function deleteState(Request $request): RedirectResponse
    {
        $this->deliveryStateRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('delivery_state_deleted_successfully'));
        return back();
    }

    public function addCity(DeliveryCityAddRequest $request): RedirectResponse
    {
        foreach ($request->input('city') as $code) {
            $this->deliveryCityRepo->add(data: ['city_id' => $code, 'created_at' => now()]);
        }
        Toastr::success(translate('delivery_city_added_successfully'));
        return back();
    }

    public function deleteCity(Request $request): RedirectResponse
    {
        $this->deliveryCityRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('delivery_state_deleted_successfully'));
        return back();
    }

    public function addZipCode(DeliveryZipCodeAddRequest $request): RedirectResponse
    {
        $zipCodes = explode(',', $request['zipcode']);
        $existingZipCodes = $this->deliveryZipCodeRepo->getListWhere(dataLimit: 'all')->pluck('zipcode')->toArray();
        $zipCodes = array_diff($zipCodes, $existingZipCodes);
        if (!$zipCodes) {
            Toastr::warning(translate('delivery_zip_code_already_exists'));
            return back();
        }
        foreach ($zipCodes as $code) {
            $this->deliveryZipCodeRepo->add(data: ['zipcode' => $code]);
        }
        Toastr::success(translate('delivery_zip_code_added_successfully'));
        return back();
    }

    public function deleteZipCode(Request $request): RedirectResponse
    {
        $this->deliveryZipCodeRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('delivery_zip_code_deleted_successfully'));
        return back();
    }

    public function addArea(DeliveryAreaAddRequest $request): RedirectResponse
    {
        foreach ($request->input('area') as $code) {
            $this->deliveryAreaRepo->add(data: ['area_id' => $code, 'created_at' => now()]);
        }
        Toastr::success(translate('delivery_area_added_successfully'));
        return back();
    }



    public function deleteArea(Request $request): RedirectResponse
    {
        $this->deliveryAreaRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('delivery_area_deleted_successfully'));
        return back();
    }

    public function countryRestrictionStatusChange(Request $request): JsonResponse|RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'delivery_country_restriction', value: $request->get('status', 0));
        if ($request->ajax()) {
            return response()->json([
                'message' => translate('delivery_country_restriction_status_changed_successfully'),
                'status' => true
            ]);
        }
        return back();
    }

    public function StateRestrictionStatusChange(Request $request): JsonResponse|RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'delivery_state_restriction', value: $request->get('status', 0));
        if ($request->ajax()) {
            return response()->json([
                'message' => translate('delivery_state_restriction_status_changed_successfully'),
                'status' => true
            ]);
        }
        return back();
    }

    public function cityRestrictionStatusChange(Request $request): JsonResponse|RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'delivery_city_restriction', value: $request->get('status', 0));
        if ($request->ajax()) {
            return response()->json([
                'message' => translate('delivery_city_restriction_status_changed_successfully'),
                'status' => true
            ]);
        }
        return back();
    }

    public function zipcodeRestrictionStatusChange(Request $request): JsonResponse|RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'delivery_zip_code_area_restriction', value: $request->get('status', 0));
        if ($request->ajax()) {
            return response()->json([
                'message' => translate('delivery_zip_code_restriction_status_changed_successfully'),
                'status' => true,
            ]);
        }
        clearWebConfigCacheKeys();
        return back();
    }

    public function areaRestrictionStatusChange(Request $request): JsonResponse|RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'delivery_area_restriction', value: $request->get('status', 0));
        if ($request->ajax()) {
            return response()->json([
                'message' => translate('delivery_area_restriction_status_changed_successfully'),
                'status' => true,
            ]);
        }
        clearWebConfigCacheKeys();
        return back();
    }
}
