<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BusinessSettingRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'basic_lang' => 'required|array',
            'basic_lang.*' => 'required|string',
            'text_lang' => 'required|array',
            'text_lang.*' => 'required|string',
            'company_name' => 'required|array',
            'company_name.*' => 'nullable|string',
            'shop_address' => 'required|array',
            'shop_address.*' => 'nullable|string',
            'company_copyright_text' => 'required|array',
            'company_copyright_text.*' => 'nullable|string',
            'currency_symbol_space' => 'required|in:0,1',
            'pagination_limit' => 'numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => translate('company_name_is_required'),
            'shop_address.required' => translate('shop_address_is_required'),
            'company_copyright_text.required' => translate('company_copyright_text_is_required'),
            'pagination_limit.numeric' => translate('the_pagination_limit_must_be_numeric'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->hasAtLeastOneValue($this->input('company_name', []))) {
                $validator->errors()->add('company_name', translate('company_name_is_required'));
            }

            if (!$this->hasAtLeastOneValue($this->input('shop_address', []))) {
                $validator->errors()->add('shop_address', translate('shop_address_is_required'));
            }

            if (!$this->hasAtLeastOneValue($this->input('company_copyright_text', []))) {
                $validator->errors()->add('company_copyright_text', translate('company_copyright_text_is_required'));
            }
        });
    }

    private function hasAtLeastOneValue(array $values): bool
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return true;
            }
        }

        return false;
    }

}
