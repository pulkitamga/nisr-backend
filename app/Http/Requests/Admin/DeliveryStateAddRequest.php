<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryStateAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'state.required' => translate('the_state_field_is_required'),
        ];
    }

}
