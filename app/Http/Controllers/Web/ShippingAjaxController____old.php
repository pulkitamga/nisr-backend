<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Traits\FileManagerTrait;
use App\Utils\ImageManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function React\Promise\all;
use App\Models\ShippingMethodArea;
use App\Models\State;
use App\Models\City;
use App\Models\Area;

class ShippingAjaxController extends Controller
{

    public function getStates(Request $request)
    {
        $stateIds = ShippingMethodArea::where('country', $request->country)
            ->pluck('state_id')->unique();

        $states = State::whereIn('id', $stateIds)->get(['id', 'name']);
        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $cityIds = ShippingMethodArea::where('state_id', $request->state_id)
            ->pluck('city_id')->unique();

        $cities = City::whereIn('id', $cityIds)->get(['id', 'name']);
        return response()->json($cities);
    }

    public function getAreas(Request $request)
    {
        $areas = ShippingMethodArea::where('city_id', $request->city_id)
            ->pluck('area')->unique();
        return response()->json($areas);
    }

    public function getBillingAreas(Request $request)
    {
        $areas = Area::where('city_id', $request->city_id)
            ->pluck('name')->unique();
        return response()->json($areas);
    }

    // 2. lavel wise if state is blacklist then do not show any thing  Optimization 

    // STATES
    public function getStatesOnCheckout(Request $request)
    {
        $request->validate([
            'country' => 'required|string|size:2'
        ]);

        $blockedStates = cache()->remember(
            'delivery_blocked_states',
            300,
            fn() =>
            DB::table('delivery_states')->pluck('state_id')->toArray()
        );

        $states = State::where('country', strtoupper($request->country))
            ->whereNotIn('id', $blockedStates)
            ->select('id', 'name')
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

        $blockedStates = cache()->remember(
            'delivery_blocked_states',
            300,
            fn() =>
            DB::table('delivery_states')->pluck('state_id')->toArray()
        );

        if (in_array($request->state_id, $blockedStates)) {
            return response()->json(['cities' => []]);
        }

        $blockedCities = cache()->remember(
            'delivery_blocked_cities',
            300,
            fn() =>
            DB::table('delivery_cities')->pluck('city_id')->toArray()
        );

        $cities = City::where('state_id', $request->state_id)
            ->whereNotIn('id', $blockedCities)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['cities' => $cities]);
    }

    // AREAS
    public function getAreasOnCheckout(Request $request)
    {
        $request->validate(['city_id' => 'required|integer']);

        $blockedAreas = DB::table('delivery_areas')->pluck('area_id')->toArray();

        $areas = Area::where('city_id', $request->city_id)
            ->whereNotIn('id', $blockedAreas) // This removes Kamlapati
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['areas' => $areas]);
    }

    // --- BILLING (Unrestricted - Shows All) ---

    public function getBillingStatesOnCheckout(Request $request)
    {
        $states = State::where('country', $request->billing_country)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['states' => $states]);
    }

    public function getBillingCitiesOnCheckout(Request $request)
    {
        $cities = City::where('state_id', $request->billing_state_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['cities' => $cities]);
    }

    public function getBillingAreasOnCheckout(Request $request)
    {
        // Ensure the key matches the AJAX param: billing_city_id
        $areas = Area::where('city_id', $request->billing_city_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get(); // Does NOT use whereNotIn, so it shows Kamlapati + Bhawanbharti

        return response()->json(['areas' => $areas]);
    }
}
