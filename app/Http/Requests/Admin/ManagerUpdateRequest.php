<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ManagerUpdateRequest extends FormRequest
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
            'name'   => 'required',
            'phone'         => 'required|max:20|min:4',
           
             
        ];
    }

    public function messages(): array
    {
    return [
            'name.required'     => translate('The_manager_name_field_is_required'),
            'phone.required'    => translate('The_phone_field_is_required'),
            'phone.unique'      => translate('The_phone_has_already_been_taken'),
            'phone.max'         => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'phone.min'         => translate('phone_number_with_a_minimum_length_requirement_of_4_characters'),
             
          ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
