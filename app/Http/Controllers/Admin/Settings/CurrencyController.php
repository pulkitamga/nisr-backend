<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\GlobalConstant;
use App\Enums\ViewPaths\Admin\Currency;
use App\Http\Controllers\BaseController;
use App\Services\SettingService;
use App\Traits\PaymentGatewayTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseController
{

    use PaymentGatewayTrait;

    public function __construct(
        private readonly CurrencyRepositoryInterface        $currencyRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly SettingRepositoryInterface         $settingRepo,
        private readonly SettingService                     $settingService,
        private readonly TranslationRepositoryInterface     $translationRepo,
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
        return $this->getListView(request: $request);
    }

    public function getListView(Request $request): View
    {
        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;
        $paymentGatewayUrl = $this->settingService->getVacationData(type: 'payment_setup');

        if ($paymentGatewayPublishedStatus) {
            $activePaymentGateway = $this->settingRepo->getListWhere(filters: ['settings_type' => 'payment_config', 'is_active' => 1], dataLimit: 'all');
        } else {
            $activePaymentGateway = $this->settingRepo->getListWhereIn(filters: ['settings_type' => 'payment_config', 'is_active' => 1], whereInFilters: ['key_name' => GlobalConstant::DEFAULT_PAYMENT_GATEWAYS], dataLimit: 'all');
        }
        $currencies = $this->currencyRepo->getListWhere(
            searchValue: $request['searchValue'],
            relations: ['translations'],
            dataLimit: getWebConfig(name: 'pagination_limit'),
        );

        $activeCurrencies = $this->currencyRepo->getListWhere(
            filters: ['status' => 1],
            relations: ['translations'],
            dataLimit: 'all',
        );

        $currencies->map(function ($currency) use ($currencies, $activePaymentGateway) {
            $checkedData = self::checkPaymentGatewaySupportedCurrencies($currency->code, $currencies, $activePaymentGateway);
            $currency['is_enabled_to_use'] = $checkedData['is_enabled_to_use'];
            $currency['total_supported_gateway'] = $checkedData['total_supported_gateway'];
            $currency['must_required_for_gateway'] = $checkedData['must_required_for_gateway'];
            $currency['supported_gateway'] = $checkedData['supported_gateway'];
        });

        $digitalPaymentStatus = getWebConfig(name: 'digital_payment')['status'] ?? 0;
        $currencyModel = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'currency_model']);
        $default = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'system_default_currency']);
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';
        return view(Currency::LIST[VIEW], compact('activeCurrencies', 'currencies', 'currencyModel', 'default', 'paymentGatewayPublishedStatus', 'paymentGatewayUrl', 'digitalPaymentStatus', 'languages', 'defaultLanguage'));
    }

    function checkPaymentGatewaySupportedCurrencies($currencyCode, $currencyCodes, $paymentGateways): array
    {
        $isEnabledToUse = 0;
        $totalSupportedGateway = 0;
        $supportForGateway = [];
        $mustRequiredForGateway = 1;
        foreach ($paymentGateways as $paymentGateway) {
            $getPaymentGatewaySupportedCurrencies = $this->getPaymentGatewaySupportedCurrencies(key: $paymentGateway->key_name);
            if ($getPaymentGatewaySupportedCurrencies && array_key_exists($currencyCode, $getPaymentGatewaySupportedCurrencies)) {
                $isEnabledToUse = 1;
                $totalSupportedGateway += 1;
                $supportForGateway[] = $paymentGateway->key_name;
                foreach ($currencyCodes as $singleCode) {
                    if ($singleCode->status == 1 && $singleCode->code != $currencyCode && array_key_exists($singleCode->code, $getPaymentGatewaySupportedCurrencies)) {
                        $mustRequiredForGateway = 0;
                    }
                }
            }
        }
        if (count($supportForGateway) == 0) {
            $mustRequiredForGateway = 0;
        }
        return [
            'is_enabled_to_use' => $isEnabledToUse,
            'total_supported_gateway' => $totalSupportedGateway,
            'must_required_for_gateway' => $mustRequiredForGateway,
            'supported_gateway' => $supportForGateway,
        ];
    }

    public function add(Request $request): RedirectResponse
    {
        $currencyExist = $this->currencyRepo->getFirstWhere(params: ['code' => $request['code']]);
        if ($currencyExist) {
            Toastr::warning(translate('Currency_already_exist'));
            return redirect()->back();
        }
        $translatableData = $this->getCurrencyTranslatableData(request: $request);
        if (!filled($translatableData['name']) || !filled($translatableData['symbol'])) {
            Toastr::warning(translate('Currency_name_is_required'));
            return redirect()->back();
        }
        $savedCurrency = $this->currencyRepo->add([
            'name' => $translatableData['name'],
            'symbol' => $translatableData['symbol'],
            'code' => $request['code'],
            'exchange_rate' => $request->has('exchange_rate') ? $request['exchange_rate'] : 1,
        ]);
        $this->saveCurrencyTranslations(currencyId: (string)$savedCurrency->id, translations: $translatableData['translations']);
        Toastr::success(translate('New_Currency_inserted_successfully'));
        return redirect()->back();
    }

    public function getUpdateView($id): View
    {
        $currencyModel = getWebConfig(name: 'currency_model');
        $currency = $this->currencyRepo->getFirstWhere(params: ['id' => $id], relations: ['translations']);
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';
        return view(Currency::UPDATE[VIEW], compact('currency', 'currencyModel', 'languages', 'defaultLanguage'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $currency = $this->currencyRepo->getFirstWhere(params: ['id' => $id]);
        if ($currency['code'] == 'BDT' && $request['code'] != 'BDT') {
            $config = $this->settingRepo->getFirstWhere(params: ['key_name'=>'ssl_commerz']);
            if ($config['is_active']) {
                Toastr::warning(translate('Before_update_BDT') . ", " . translate('disable_the_SSLCOMMERZ_payment_gateway.'));
                return back();
            }
        } elseif ($currency['code'] == 'INR' && $request['code'] != 'INR') {
            $config = $this->settingRepo->getFirstWhere(params: ['key_name'=>'razor_pay']);
            if ($config['is_active']) {
                Toastr::warning(translate('Before_update_INR') . ", " . translate('disable_the_RAZOR_PAY_payment_gateway.'));
                return back();
            }
        } elseif ($currency['code'] == 'MYR' && $request['code'] != 'MYR') {
            $config = $this->settingRepo->getFirstWhere(params: ['key_name'=>'senang_pay']);
            if ($config['is_active']) {
                Toastr::warning(translate('Before_update_MYR') . ", " . translate('disable_the_SENANG_PAY_payment_gateway.'));
                return back();
            }
        } elseif ($currency['code'] == 'ZAR' && $request['code'] != 'ZAR') {
            $config = $this->settingRepo->getFirstWhere(params: ['key_name'=>'paystack']);
            if ($config['is_active']) {
                Toastr::warning(translate('Before_update_ZAR') . ", " . translate('disable_the_PAYSTACK_payment_gateway.'));
                return back();
            }
        }

        $translatableData = $this->getCurrencyTranslatableData(request: $request);
        if (!filled($translatableData['name']) || !filled($translatableData['symbol'])) {
            Toastr::warning(translate('Currency_name_is_required'));
            return redirect()->back();
        }
        $dataArray = [
            'name' => $translatableData['name'],
            'symbol' => $translatableData['symbol'],
            'code' => $request['code'],
            'exchange_rate' => $request->has('exchange_rate') ? $request['exchange_rate'] : 1,
        ];
        $this->currencyRepo->update(id: $currency['id'], data: $dataArray);
        $this->saveCurrencyTranslations(currencyId: (string)$currency['id'], translations: $translatableData['translations']);

        Toastr::success(translate('currency_updated_successfully'));
        return redirect()->back();
    }

    public function delete(Request $request): JsonResponse
    {
        if (!in_array($request['id'], [1, 2, 3, 4, 5])) {
            $this->translationRepo->delete(model: 'App\Models\Currency', id: $request['id']);
            $this->currencyRepo->delete(params: ['id' => $request['id']]);
            return response()->json(['status' => 1]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function status(Request $request): JsonResponse
    {
        if ($request['status'] != 1) {
            $count = $this->currencyRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all')->count();
            if ($count == 1) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('You_can_not_disable_all_currencies.')
                ]);
            }

            $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;
            if ($paymentGatewayPublishedStatus) {
                $activePaymentGateway = $this->settingRepo->getListWhere(filters: ['settings_type' => 'payment_config', 'is_active' => 1], dataLimit: 'all');
            } else {
                $activePaymentGateway = $this->settingRepo->getListWhereIn(filters: ['settings_type' => 'payment_config', 'is_active' => 1], whereInFilters: ['key_name' => GlobalConstant::DEFAULT_PAYMENT_GATEWAYS], dataLimit: 'all');
            }
            $currencies = $this->currencyRepo->getListWhere(searchValue: $request['searchValue'], dataLimit: getWebConfig(name: 'pagination_limit'));
            $currency = $this->currencyRepo->getFirstWhere(params: ['id' => $request['id']]);
            $checkedData = self::checkPaymentGatewaySupportedCurrencies($currency->code, $currencies, $activePaymentGateway);
            if ($checkedData['must_required_for_gateway']) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('If_you_disable_this_currency_please_check_in_payment_gateway_settings_that_gateway_only_dependent_on_support_this_currency')
                ]);
            }
        }
        $this->currencyRepo->update(id: $request['id'], data: ['status' => $request->get('status', 0)]);
        return response()->json([
            'status' => 1,
            'message' => translate('Currency_status_successfully_changed.')
        ]);
    }

    public function updateSystemCurrency(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateWhere(params: ['type' => 'system_default_currency'], data: ['value' => $request['currency_id']]);
        $currencyModel = getWebConfig(name: 'currency_model');
        if ($currencyModel == 'multi_currency') {
            $default = $this->currencyRepo->getFirstWhere(params: ['id' => $request['currency_id']]);
            $allCurrencies = $this->currencyRepo->getListWhere(dataLimit: 'all');
            foreach ($allCurrencies as $data) {
                $this->currencyRepo->update(id: $data['id'], data: ['exchange_rate' => ($data['exchange_rate'] / $default['exchange_rate'])]);
            }
        }
        clearWebConfigCacheKeys();
        cacheRemoveByType(type: 'business_settings');
        session()->forget('usd');
        session()->forget('default');
        session()->forget('system_default_currency_info');
        session()->forget('currency_code');
        session()->forget('currency_symbol');
        session()->forget('currency_exchange_rate');
        Toastr::success(translate('System_Default_currency_updated_successfully'));
        return back();
    }

    private function getCurrencyTranslatableData(Request $request): array
    {
        $languages = $request->input('lang', []);
        $names = $request->input('name', []);
        $symbols = $request->input('symbol', []);

        if (!is_array($languages)) {
            $languages = [];
        }
        if (!is_array($names)) {
            $names = [$names];
        }
        if (!is_array($symbols)) {
            $symbols = [$symbols];
        }

        $defaultLanguage = (getWebConfig(name: 'pnc_language')[0] ?? 'en');
        $defaultIndex = array_search($defaultLanguage, $languages, true);
        $fallbackName = collect($names)->first(fn($value) => filled($value));
        $fallbackSymbol = collect($symbols)->first(fn($value) => filled($value));

        $defaultName = $defaultIndex !== false ? ($names[$defaultIndex] ?? null) : null;
        $defaultSymbol = $defaultIndex !== false ? ($symbols[$defaultIndex] ?? null) : null;

        $data = [
            'name' => $defaultName ?? $fallbackName ?? '',
            'symbol' => $defaultSymbol ?? $fallbackSymbol ?? '',
            'translations' => [],
        ];

        foreach ($languages as $index => $languageCode) {
            if ($languageCode === $defaultLanguage) {
                continue;
            }

            if (!empty($names[$index])) {
                $data['translations'][] = [
                    'locale' => $languageCode,
                    'key' => 'name',
                    'value' => $names[$index],
                ];
            }

            if (!empty($symbols[$index])) {
                $data['translations'][] = [
                    'locale' => $languageCode,
                    'key' => 'symbol',
                    'value' => $symbols[$index],
                ];
            }
        }

        return $data;
    }

    private function saveCurrencyTranslations(string $currencyId, array $translations): void
    {
        foreach ($translations as $translation) {
            $this->translationRepo->updateData(
                model: 'App\Models\Currency',
                id: $currencyId,
                lang: $translation['locale'],
                key: $translation['key'],
                value: $translation['value']
            );
        }
    }
}
