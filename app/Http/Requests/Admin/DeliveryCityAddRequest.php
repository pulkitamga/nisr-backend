<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryCityAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'city.required' => translate('the_city_field_is_required'),
        ];
    }

}
