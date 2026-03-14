<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequestFormRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'customer_id' => 'nullable|exists:users,id',
            'service_option' => 'required|string|in:in_shop,mobile',

            'country' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'state' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'city' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'area' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'address' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],

            'vehicle_type' => 'required|string',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',
            'vehicle_year' => 'required|integer',
            'vehicle_mileage' => 'required|integer',
            'vin' => 'nullable|string',
        ];
    }
}
