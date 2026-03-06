<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShippingProviderUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'gateway' => 'required|in:bosta',
            'mode' => 'required|in:live,test',
            'status' => 'required|in:0,1',
            'api_key' => 'required|string',
            'base_url' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.required' => translate('the_gateway_field_is_required'),
        ];
    }
}

