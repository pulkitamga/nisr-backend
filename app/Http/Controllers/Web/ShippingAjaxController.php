<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\DeliveryArea;
use App\Models\DeliveryCity;
use App\Models\DeliveryCountryCode;
use App\Models\DeliveryState;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingAjaxController extends Controller
{

    // public function getStates(Request $request)
    // {
    //     $stateIds = ShippingMethodArea::where('country', $request->country)
    //         ->pluck('state_id')->unique();

    //     $states = State::whereIn('id', $stateIds)->get(['id', 'name']);
    //     return response()->json($states);
    // }

    public function getStates(Request $request)
    {
        $states = State::query()
            ->where('country', $request->country)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'states' => $states->map(fn(State $state) => [
                'id' => (int)$state->id,
                'name' => (string)$state->name,
            ])->values(),
        ]);
    }

    public function getCities(Request $request)
    {
        $cities = City::query()
            ->where('state_id', $request->state_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'cities' => $cities->map(fn(City $city) => [
                'id' => (int)$city->id,
                'name' => (string)$city->name,
            ])->values(),
        ]);
    }


    // public function getCities(Request $request)
    // {
    //     $cityIds = ShippingMethodArea::where('state_id', $request->state_id)
    //         ->pluck('city_id')->unique();

    //     $cities = City::whereIn('id', $cityIds)->get(['id', 'name']);
    //     return response()->json($cities);
    // }

    public function getAreas(Request $request)
    {
        $areas = Area::query()
            ->where('city_id', $request->city_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'areas' => $areas->map(fn(Area $area) => [
                'id' => (int)$area->id,
                'name' => (string)$area->name,
            ])->values(),
        ]);
    }

    public function getBillingAreas(Request $request)
    {
        $areas = Area::where('city_id', $request->city_id)
            ->get()
            ->map(fn($area) => ['id' => $area->id, 'name' => $area->name])
            ->unique('name')
            ->values();
        return response()->json($areas);
    }

    // 2. lavel wise if state is blacklist then do not show any thing  Optimization 

    // STATES
    public function getStatesOnCheckout(Request $request)
    {
        $request->validate([
            'country' => 'required|string'
        ]);

        $countryCode = $this->normalizeCountryCodeFromInput($request->country);
        if (!$countryCode) {
            return response()->json(['states' => []]);
        }

        $countryRestrictionEnabled = (int)getWebConfig(name: 'delivery_country_restriction') === 1;
        if ($countryRestrictionEnabled) {
            $allowedCountryCodes = cache()->remember(
                'delivery_allowed_country_codes',
                300,
                fn() => DeliveryCountryCode::query()->pluck('country_code')->map(fn($code) => strtoupper((string)$code))->toArray()
            );

            if (!in_array($countryCode, $allowedCountryCodes, true)) {
                return response()->json(['states' => []]);
            }
        }

        $query = State::query()
            ->where('country', $countryCode);

        $stateRestrictionEnabled = (int)getWebConfig(name: 'delivery_state_restriction') === 1;
        if ($stateRestrictionEnabled) {
            $allowedStateIds = cache()->remember(
                'delivery_allowed_states',
                300,
                fn() => DeliveryState::query()->pluck('state_id')->toArray()
            );

            if (empty($allowedStateIds)) {
                return response()->json(['states' => []]);
            }

            $query->whereIn('id', $allowedStateIds);
        }

        $states = $query->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['states' => $states]);
    }

    // CITIES
    public function getCitiesOnCheckout(Request $request)
    {
        $request->validate([
            'state_id' => 'required|integer'
        ]);

        $stateId = (int)$request->state_id;
        $state = State::query()->find($stateId);
        if (!$state) {
            return response()->json(['cities' => []]);
        }

        $stateRestrictionEnabled = (int)getWebConfig(name: 'delivery_state_restriction') === 1;
        if ($stateRestrictionEnabled) {
            $allowedStateIds = cache()->remember(
                'delivery_allowed_states',
                300,
                fn() => DeliveryState::query()->pluck('state_id')->toArray()
            );
            if (!in_array($stateId, $allowedStateIds, true)) {
                return response()->json(['cities' => []]);
            }
        }

        $query = City::query()->where('state_id', $stateId);

        $cityRestrictionEnabled = (int)getWebConfig(name: 'delivery_city_restriction') === 1;
        if ($cityRestrictionEnabled) {
            $allowedCityIds = cache()->remember(
                'delivery_allowed_cities',
                300,
                fn() => DeliveryCity::query()->pluck('city_id')->toArray()
            );
            if (empty($allowedCityIds)) {
                return response()->json(['cities' => []]);
            }
            $query->whereIn('id', $allowedCityIds);
        }

        $cities = $query->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['cities' => $cities]);
    }

    // AREAS
    public function getAreasOnCheckout(Request $request)
    {
        $request->validate([
            'city_id' => 'required|integer'
        ]);

        $cityId = (int)$request->city_id;
        $city = City::query()->find($cityId);
        if (!$city) {
            return response()->json(['areas' => []]);
        }

        $cityRestrictionEnabled = (int)getWebConfig(name: 'delivery_city_restriction') === 1;
        if ($cityRestrictionEnabled) {
            $allowedCityIds = cache()->remember(
                'delivery_allowed_cities',
                300,
                fn() => DeliveryCity::query()->pluck('city_id')->toArray()
            );
            if (!in_array($cityId, $allowedCityIds, true)) {
                return response()->json(['areas' => []]);
            }
        }

        $query = Area::query()->where('city_id', $cityId);

        $areaRestrictionEnabled = (int)getWebConfig(name: 'delivery_area_restriction') === 1;
        if ($areaRestrictionEnabled) {
            $allowedAreaIds = cache()->remember(
                'delivery_allowed_areas',
                300,
                fn() => DeliveryArea::query()->pluck('area_id')->toArray()
            );
            if (empty($allowedAreaIds)) {
                return response()->json(['areas' => []]);
            }
            $query->whereIn('id', $allowedAreaIds);
        }

        $areas = $query->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['areas' => $areas]);
    }


    // --- BILLING (Unrestricted - Shows All) ---

    public function getBillingStatesOnCheckout(Request $request)
    {
        $countryCode = $this->normalizeCountryCodeFromInput($request->billing_country);
        if (!$countryCode) {
            return response()->json(['states' => []]);
        }

        $states = State::where('country', $countryCode)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['states' => $states]);
    }

    public function getBillingCitiesOnCheckout(Request $request)
    {
        $request->validate([
            'billing_state_id' => 'required|integer'
        ]);

        $cities = City::where('state_id', $request->billing_state_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['cities' => $cities]);
    }

    public function getBillingAreasOnCheckout(Request $request)
    {
        $cityId = $request->billing_city_id ?? $request->city_id;
        $areas = Area::where('city_id', $cityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['areas' => $areas]);
    }

    private function normalizeCountryCodeFromInput(?string $countryInput): ?string
    {
        $countryInput = strtoupper(trim((string)$countryInput));
        if ($countryInput === '') {
            return null;
        }

        if (strlen($countryInput) === 2) {
            return $countryInput;
        }

        foreach (COUNTRIES as $country) {
            if (strtoupper((string)($country['name'] ?? '')) === $countryInput) {
                return strtoupper((string)($country['code'] ?? ''));
            }
        }

        return null;
    }
}
