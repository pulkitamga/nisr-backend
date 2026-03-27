<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResolutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resolution_notes' => 'nullable|string|max:2000',
        ];
    }
}
