<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IssueRmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rma_number' => 'nullable|string|max:50',
            'return_days' => 'required|integer|min:1|max:90',
            'instructions' => 'required|string|max:2000',
            'branch_id' => 'required|exists:branches,id|not_in:1',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.not_in' => translate('System branch cannot be used for customer RMA returns.'),
        ];
    }
}
