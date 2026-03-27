<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;

class LookupVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp' => 'required|digits:4',
            'warranty_id' => 'required|exists:warranties,id',
            'contact' => 'required|string|max:100',
        ];
    }
}
