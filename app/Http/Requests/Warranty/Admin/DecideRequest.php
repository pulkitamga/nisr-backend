<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DecideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approve,reject,waiting_customer'],
            'reason_code' => ['required', 'string', 'max:50'],
            'reason_message' => ['required', 'string', 'max:2000'],
        ];
    }
}
