<?php

namespace App\Http\Requests\Admin;

use App\Contracts\Repositories\SettingRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $gateway
 */
class PaymentMethodUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function __construct(
        private readonly SettingRepositoryInterface         $settingRepo,
    )
    {
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validationRules = [
            'gateway' => 'required', Rule::in(['sixcash', 'worldpay', 'payfast', 'swish', 'esewa', 'maxicash', 'hubtel', 'viva_wallet', 'tap', 'thawani', 'moncash', 'pvit', 'ccavenue', 'foloosi', 'iyzi_pay', 'xendit', 'fatoorah', 'hyper_pay', 'amazon_pay', 'paytabs', 'paymob_accept', 'cash_after_service', 'digital_payment', 'momo']),
            'mode' => 'required|in:live,test',
        ];
        $additionalDataRules = $this->getAdditionalDataRules();
        return array_merge($validationRules, $additionalDataRules);
    }

    public function messages(): array
    {
        return [
            'gateway.required' => translate('the_gateway_field_is_required'),
            'gateway_title.required' => translate('the_gateway_title_field_is_required'),
            'gateway_image.required' => translate('gateway_image_is_required'),
        ];
    }

    protected function getAdditionalDataRules(): array
    {
        collect(['status'])->each(fn($item, $key) => $this[$item] = $this->has($item) ? (int)$this[$item] : 0);
        $settings = $this->settingRepo->getFirstWhere(params: ['key_name'=>$this['gateway'], 'settings_type'=>'payment_config']);
        $additionalDataImage = $settings['additional_data'] != null ? json_decode($settings['additional_data']) : null;

        $additionalDataRules = [];
        if ($this['gateway'] == 'paytabs') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'profile_id' => 'required',
                'server_key' => 'required',
                'base_url' => 'required'
            ];
        } elseif ($this['gateway'] == 'paymob_accept') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required',
                'api_key' => 'required',
                'iframe_id' => 'required',
                'integration_id' => 'required',
                'hmac' => 'required'
            ];
        } elseif ($this['gateway'] == 'cash_after_service') {
            $additionalDataRules = [
                'status' => 'required|in:1,0'
            ];
        } elseif ($this['gateway'] == 'digital_payment') {
            $additionalDataRules = [
                'status' => 'required|in:1,0'
            ];
        } elseif ($this['gateway'] == 'momo') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'api_key' => 'required',
                'api_user' => 'required',
                'subscription_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'hyper_pay') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'entity_id' => 'required',
                'access_code' => 'required',
            ];
        } elseif ($this['gateway'] == 'amazon_pay') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'pass_phrase' => 'required',
                'access_code' => 'required',
                'merchant_identifier' => 'required',
            ];
        } elseif ($this['gateway'] == 'sixcash') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'public_key' => 'required',
                'secret_key' => 'required',
                'merchant_number' => 'required',
                'base_url' => 'required',
            ];
        } elseif ($this['gateway'] == 'worldpay') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'OrgUnitId' => 'required',
                'jwt_issuer' => 'required',
                'mac' => 'required',
                'merchantCode' => 'required',
                'xml_password' => 'required',
            ];
        } elseif ($this['gateway'] == 'payfast') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'merchant_id' => 'required',
                'secured_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'swish') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'number' => 'required',
            ];
        } elseif ($this['gateway'] == 'esewa') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'merchantCode' => 'required',
            ];
        } elseif ($this['gateway'] == 'maxicash') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'merchantId' => 'required',
                'merchantPassword' => 'required',
            ];
        } elseif ($this['gateway'] == 'hubtel') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'account_number' => 'required',
                'api_id' => 'required',
                'api_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'viva_wallet') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'client_id' => 'required',
                'client_secret' => 'required',
                'source_code' => 'required',
            ];
        } elseif ($this['gateway'] == 'tap') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'secret_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'thawani') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'public_key' => 'required',
                'private_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'moncash') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'client_id' => 'required',
                'secret_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'pvit') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'mc_tel_merchant' => 'required',
                'access_token' => 'required',
                'mc_merchant_code' => 'required',
            ];
        } elseif ($this['gateway'] == 'ccavenue') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'merchant_id' => 'required',
                'working_key' => 'required',
                'access_code' => 'required',
            ];
        } elseif ($this['gateway'] == 'foloosi') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'merchant_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'iyzi_pay') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'api_key' => 'required',
                'secret_key' => 'required',
                'base_url' => 'required',
            ];
        } elseif ($this['gateway'] == 'xendit') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'api_key' => 'required',
            ];
        } elseif ($this['gateway'] == 'fatoorah') {
            $additionalDataRules = [
                'status' => 'required|in:1,0',
                'api_key' => 'required',
            ];
        }

        if (!$this->has('gateway_image') && (!$additionalDataImage || !isset($additionalDataImage->gateway_image) || (isset($additionalDataImage->gateway_image) && $additionalDataImage->gateway_image == '') || (isset($additionalDataImage->gateway_image) && !file_exists(base_path("storage/app/public/payment_modules/gateway_image/" . $additionalDataImage->gateway_image))))) {
            $additionalDataRules += [
                'gateway_image' => 'required',
            ];
        }

        return $additionalDataRules;
    }
}
