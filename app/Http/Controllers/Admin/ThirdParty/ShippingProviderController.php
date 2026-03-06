<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Enums\GlobalConstant;
use App\Enums\ViewPaths\Admin\ShippingProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ShippingProviderUpdateRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingProviderController extends BaseController
{
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepo,
    )
    {
    }

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        foreach (GlobalConstant::DEFAULT_SHIPPING_PROVIDERS as $provider) {
            $providerConfig = $this->settingRepo->getFirstWhere(params: ['key_name' => $provider, 'settings_type' => 'shipping_config']);
            if (!$providerConfig) {
                $defaultCredentials = $this->getDefaultShippingProviderCredentials($provider);
                $this->settingRepo->add([
                    'key_name' => $provider,
                    'live_values' => $defaultCredentials,
                    'test_values' => $defaultCredentials,
                    'settings_type' => 'shipping_config',
                    'mode' => 'live',
                    'is_active' => 0,
                ]);
            }
        }

        $providersList = $this->settingRepo->getListWhereIn(
            whereInFilters: ['settings_type' => ['shipping_config'], 'key_name' => GlobalConstant::DEFAULT_SHIPPING_PROVIDERS],
            dataLimit: 'all',
        );

        $shippingProviders = $providersList->values()->all();
        return view(ShippingProvider::VIEW[VIEW], compact('shippingProviders'));
    }

    public function update(ShippingProviderUpdateRequest $request): RedirectResponse
    {
        $payload = [
            'gateway' => $request['gateway'],
            'mode' => $request['mode'],
            'status' => (int)$request['status'],
            'api_key' => $request['api_key'],
            'base_url' => $request['base_url'],
        ];

        $data = [
            'key_name' => $request['gateway'],
            'live_values' => $payload,
            'test_values' => $payload,
            'settings_type' => 'shipping_config',
            'mode' => $request['mode'],
            'is_active' => (int)$request['status'],
        ];

        $existing = $this->settingRepo->getFirstWhere(params: ['key_name' => $request['gateway'], 'settings_type' => 'shipping_config']);
        if ($existing) {
            $this->settingRepo->updateWhere(params: ['key_name' => $request['gateway'], 'settings_type' => 'shipping_config'], data: $data);
        } else {
            $this->settingRepo->add($data);
        }

        if ((int)$request['status'] === 1) {
            foreach (GlobalConstant::DEFAULT_SHIPPING_PROVIDERS as $provider) {
                if ($provider === $request['gateway']) {
                    continue;
                }

                $keep = $this->settingRepo->getFirstWhere(params: ['key_name' => $provider, 'settings_type' => 'shipping_config']);
                if ($keep) {
                    $hold = $keep['live_values'];
                    $hold['status'] = 0;
                    $this->settingRepo->updateWhere(params: ['key_name' => $provider, 'settings_type' => 'shipping_config'], data: [
                        'live_values' => $hold,
                        'test_values' => $hold,
                        'is_active' => 0,
                    ]);
                }
            }
        }

        Toastr::success(GATEWAYS_DEFAULT_UPDATE_200['message']);
        return back();
    }

    private function getDefaultShippingProviderCredentials(string $provider): array
    {
        return match ($provider) {
            'bosta' => [
                'gateway' => 'bosta',
                'mode' => 'live',
                'status' => 0,
                'api_key' => '',
                'base_url' => 'https://app.bosta.co/api/v2',
            ],
            default => [
                'gateway' => $provider,
                'mode' => 'live',
                'status' => 0,
            ],
        };
    }
}
