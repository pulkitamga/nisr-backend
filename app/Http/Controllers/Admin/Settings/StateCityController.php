<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\TranslationRepositoryInterface;
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
    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepo,
    )
    {
    }


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
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];

        return view('admin-views.business-settings.state-city.index', compact('states', 'cities', 'countries', 'areas', 'language', 'defaultLanguage'));
    }

    // Store State
    public function storeState(Request $request): RedirectResponse
    {
        $request->validate([
            'country' => 'required|string|max:100',
            'name'    => 'required|array',
            'name.*'  => 'string|max:255',
        ]);

        $state = State::create([
            'country' => $request['country'],
            'name' => $request['name'][getDefaultLanguageIndex($request)],
        ]);
        $this->translationRepo->add(request: $request, model: 'App\Models\State', id: $state->id);
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
            'name'     => 'required|array',
            'name.*'   => 'string|max:255',
        ]);

        $city = City::create([
            'state_id' => $request['state_id'],
            'name' => $request['name'][getDefaultLanguageIndex($request)],
        ]);
        $this->translationRepo->add(request: $request, model: 'App\Models\City', id: $city->id);
        Toastr::success(translate('City added successfully'));
        return back();
    }
    public function storeArea(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name'     => 'required|array',
            'name.*'   => 'string|max:255',
        ]);

        $area = Area::create([
            'city_id' => $request['city_id'],
            'name' => $request['name'][getDefaultLanguageIndex($request)],
        ]);
        $this->translationRepo->add(request: $request, model: 'App\Models\Area', id: $area->id);
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
