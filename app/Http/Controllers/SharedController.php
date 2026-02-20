<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Http\Requests\Request;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SharedController extends Controller
{
    public function changeLanguage(Request $request): JsonResponse
    {
        $languageCode = strtolower(trim((string)$request->input('language_code')));
        if ($languageCode === '') {
            return response()->json(['message' => translate('language_code_is_required')], 422);
        }

        $direction = 'ltr';
        $languageList = getWebConfig('language');
        $isValidLanguage = false;
        if (is_array($languageList)) {
            foreach ($languageList as $languageData) {
                if (
                    is_array($languageData)
                    && strtolower((string)($languageData['code'] ?? '')) === $languageCode
                ) {
                    $direction = $languageData['direction'] ?? 'ltr';
                    $isValidLanguage = true;
                    break;
                }
            }
        }

        if (!$isValidLanguage) {
            return response()->json(['message' => translate('Invalid_language_code')], 422);
        }

        session()->forget('language_settings');
        Helpers::language_load();
        session()->put('local', $languageCode);
        session()->put('locale', $languageCode);
        Session::put('direction', $direction);
        App::setLocale(function_exists('resolveAppLocale') ? resolveAppLocale($languageCode) : $languageCode);

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
