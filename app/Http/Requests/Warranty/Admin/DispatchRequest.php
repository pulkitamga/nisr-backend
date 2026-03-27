<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dispatchMode = (string)$this->input('dispatch_mode');

        return [
            'dispatch_mode' => 'required|in:pickup,ship',
            'tracking_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($dispatchMode === 'ship'),
            ],
        ];
    }
}
