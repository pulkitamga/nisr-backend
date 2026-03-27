<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ClaimStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warranty_public_id' => 'required|exists:warranties,warranty_public_id',
            'subject' => 'required|string|max:255',
            'details' => 'required|string|max:5000',
            'issue' => 'required|string|max:5000',
            'product_images' => 'required|array|min:1|max:5',
            'product_images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
