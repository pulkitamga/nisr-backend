<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serial_number' => 'required|string|max:100',
            'branch_id' => 'required|exists:branches,id',
            'received_notes' => 'nullable|string|max:1000',
        ];
    }
}
