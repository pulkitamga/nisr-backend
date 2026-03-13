<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\BaseController;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StateCityController extends BaseController
{


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
        $states = State::orderBy('name')->get();
        $cities = City::with('state')->orderBy('name')->get();
        $areas = Area::with('city')->orderBy('name')->get();
        $countries = COUNTRIES;

        return view('admin-views.business-settings.state-city.index', compact('states', 'cities', 'countries', 'areas'));
    }

    // Store State
    public function storeState(Request $request): RedirectResponse
    {
        $request->validate([
            'country' => 'required|string|max:100',
            'name'    => 'required|string|max:255|unique:states,name,NULL,id,country,' . $request->country,
        ]);

        State::create($request->only('country', 'name'));
        Toastr::success(translate('State added successfully'));
        return back();
    }

    // Delete State
    public function deleteState($id): RedirectResponse
    {
        State::findOrFail($id)->delete();
        Toastr::success(translate('State deleted successfully'));
        return back();
    }

    // Store City
    public function storeCity(Request $request): RedirectResponse
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name'     => 'required|string|max:255',
        ]);

        City::create($request->only('state_id', 'name'));
        Toastr::success(translate('City added successfully'));
        return back();
    }
    public function storeArea(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name'     => 'required|string|max:255',
        ]);

        Area::create($request->only('city_id', 'name'));
        Toastr::success(translate('City added successfully'));
        return back();
    }

    // Delete City
    public function deleteCity($id): RedirectResponse
    {
        City::findOrFail($id)->delete();
        Toastr::success(translate('City deleted successfully'));
        return back();
    }
    public function deleteArea($id): RedirectResponse
    {
        Area::findOrFail($id)->delete();
        Toastr::success(translate('Area deleted successfully'));
        return back();
    }
}
