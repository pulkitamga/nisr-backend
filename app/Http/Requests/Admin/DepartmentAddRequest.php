<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DepartmentAddRequest extends FormRequest
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
            'name' => 'required|unique:departments',
            'status' => 'required',
            'head_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('The_department_name_field_is_required'),
            'name.unique' => translate('The_department_name_has_already_been_taken'),
            'status.required' => translate('The_status_field_is_required'),
            'head_id.required' => translate('The_department_head_field_is_required'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
