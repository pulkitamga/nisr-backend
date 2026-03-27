<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RepairCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'labor_notes' => 'nullable|string|max:2000',
            'parts_used' => 'nullable|string|max:1000',
        ];
    }
}
