<?php

namespace App\Http\Controllers\Admin\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Traits\FileManagerTrait;
use App\Utils\ImageManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
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
        $areaNames = ShippingMethodArea::where('city_id', $request->city_id)
            ->pluck('area')
            ->unique()
            ->filter()
            ->values();

        $areas = $areaNames->map(function ($areaName) {
            if (ctype_digit((string) $areaName)) {
                $area = Area::with('translations')->find((int) $areaName);
                return $area ? $area->name : $areaName;
            }
            $area = Area::with('translations')
                ->whereRaw('LOWER(name) = ?', [strtolower($areaName)])
                ->first();
            return $area ? $area->name : $areaName;
        });

        return response()->json($areas);
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
}
