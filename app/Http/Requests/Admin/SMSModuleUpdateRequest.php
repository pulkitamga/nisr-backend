<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property string $gateway
 */
class SMSModuleUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gateway' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.required' => translate('the_gateway_field_is_required'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                collect(['status'])->each(fn($item, $key) => $this[$item] = $this->has($item) ? (int)$this[$item] : 0);

                $validation = [
                    'gateway' => 'required|in:twilio,nexmo,sms_com_eg',
                    'mode' => 'required|in:live,test'
                ];
                $additionalData = [];
                if ($this['gateway'] == 'twilio') {
                    $additionalData = [
                        'status' => 'required|in:1,0',
                        'sid' => 'required',
                        'messaging_service_sid' => 'required',
                        'token' => 'required',
                        'from' => 'required',
                        'otp_template' => 'required'
                    ];
                } elseif ($this['gateway'] == 'nexmo') {
                    $additionalData = [
                        'status' => 'required|in:1,0',
                        'api_key' => 'required',
                        'api_secret' => 'required',
                        'token' => 'required',
                        'from' => 'required',
                        'otp_template' => 'required'
                    ];
                } elseif ($this['gateway'] == 'sms_com_eg') {
                    $additionalData = [
                        'status' => 'required|in:1,0',
                        'username' => 'required',
                        'password' => 'required',
                        'sender' => 'required',
                        'language' => 'required|in:1,2',
                        'otp_template' => 'required',
                    ];
                }
                $this->validate(array_merge($validation, $additionalData));
            }
        ];
    }
}
