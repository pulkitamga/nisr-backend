<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

            'country' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'state' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'city' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'area' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'address' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'latitude' => ['exclude_unless:service_option,mobile', 'required', 'string'],
            'longitude' => ['exclude_unless:service_option,mobile', 'required', 'string'],

            'vehicle_type' => 'required|string',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',
            'vehicle_year' => 'required|integer',
            'vehicle_mileage' => 'required|integer',
            'vin' => 'nullable|string',
        ];
    }
}
