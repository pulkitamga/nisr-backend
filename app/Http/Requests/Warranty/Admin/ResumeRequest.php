<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_status' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
