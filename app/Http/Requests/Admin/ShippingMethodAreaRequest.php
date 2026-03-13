<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShippingMethodAreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules():array
    {
        return [
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'area' => 'required',
            'cost' => 'nullable|numeric|min:0',
            'coordinates' => 'nullable|string'
        ];
    }
    /**
     * @return array
     * Get the validation error message
     */
    public function messages(): array
    {
        return [
            'country.required'=>translate('the_country_field_is_required'),
            'state.required'=>translate('the_state_field_is_required'),
            'city.required'=>translate('the_city_field_is_required'),
            'area.required'=>translate('the_area_field_is_required'),
            'cost.numeric'=>translate('the_cost_must_be_a_number')
        ];
    }
}
