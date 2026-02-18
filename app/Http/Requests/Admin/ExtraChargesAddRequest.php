<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExtraChargesAddRequest extends FormRequest
{
    use ResponseHandler;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category'         => 'required',
            'charges'         => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => translate('The_category_field_is_required'),
            'charges.required' => translate('The_charges_field_is_required'),
            'charges.numeric'=>translate('the_cost_must_be_a_number')            
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
