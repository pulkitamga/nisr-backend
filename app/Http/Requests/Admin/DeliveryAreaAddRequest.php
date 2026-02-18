<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryAreaAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'area.required' => translate('the_area_field_is_required'),
        ];
    }

}