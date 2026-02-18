<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Http\Requests\Request;
use App\Utils\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SharedController extends Controller
{
    public function changeLanguage(Request $request): JsonResponse
    {
        $direction = 'ltr';
        $language = getWebConfig('language');
        foreach ($language as $data) {
            if ($data['code'] == $request['language_code']) {
                $direction = $data['direction'] ?? 'ltr';
            }
        }
        session()->forget('language_settings');
        Helpers::language_load();
        session()->put('local', $request['language_code']);
        Session::put('direction', $direction);

        $currencyCode = session('currency_code');
        if ($currencyCode) {
            $currency = Currency::where('code', $currencyCode)->first();
            if ($currency) {
                session()->put('currency_symbol', $currency->symbol);
            }
        } elseif (session()->has('system_default_currency_info')) {
            $defaultCurrencyId = session('system_default_currency_info')->id ?? null;
            if ($defaultCurrencyId) {
                $defaultCurrency = Currency::find($defaultCurrencyId);
                if ($defaultCurrency) {
                    session()->put('currency_symbol', $defaultCurrency->symbol);
                }
            }
        }

        Artisan::call('cache:clear');
        return response()->json(['message' => translate('language_change_successfully') . '.']);
    }

    public function getSessionRecaptchaCode(Request $request): JsonResponse
    {
        if (env('APP_MODE') == 'dev' && session()->has($request['sessionKey'])) {
            $code = session($request['sessionKey']);
        }
        return response()->json(['code' => $code ?? '']);
    }

    public function storeRecaptchaResponse(Request $request): JsonResponse
    {
        $response = $request->get('g_recaptcha_response', null);
        if ($response) {
            session()->put('g-recaptcha-response', $response);
        }
        return response()->json(['recaptcha' => $response]);
    }
}
