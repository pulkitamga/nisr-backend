<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequestFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['country', 'state', 'city', 'area', 'address'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = trim($value);
            }
        }

        foreach (['state', 'city', 'area'] as $field) {
            $manualField = $field . '_manual';
            $manualValue = trim((string)$this->input($manualField, ''));

            if ($manualValue !== '') {
                $normalized[$field] = $manualValue;
            }
        }

        $this->merge($normalized);
    }

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
            'service_id' => 'required',
            'service_reference' => 'nullable|string',
            'customer_id' => 'nullable|exists:users,id',
            'service_option' => 'required|string|in:in_shop,mobile',
            'agree_terms' => 'accepted',

            'country' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'state' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'city' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'area' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'address' => ['nullable', 'string', Rule::requiredIf($this->input('service_option') === 'mobile')],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],

            'vehicle_type' => 'nullable|string',
            'vehicle_make_id' => 'nullable|integer',
            'vehicle_make' => 'nullable',
            'vehicle_model_id' => 'nullable|integer',
            'vehicle_model' => 'nullable',
            'vehicle_year_id' => 'nullable|integer',
            'vehicle_year' => 'nullable|integer',
            'vehicle_mileage' => 'nullable|integer',
            'vin' => 'nullable|string',
            'problem_description' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
