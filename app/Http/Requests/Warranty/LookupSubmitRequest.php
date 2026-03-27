<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;

class LookupSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serial_number' => 'required|string|max:100|exists:warranties,serial_number',
            'contact' => 'required|string|max:100',
        ];
    }
}
