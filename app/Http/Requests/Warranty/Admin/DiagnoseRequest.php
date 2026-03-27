<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DiagnoseRequest extends FormRequest
{
    private const MAX_CLAIM_FEE_AMOUNT = 100000;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diagnosis_notes' => 'required|string|max:5000',
            'repair_or_replace' => 'required|in:repair,replace,reject',
            'tamper_detected' => 'boolean',
            'inspection_fee' => 'nullable|numeric|min:0|max:' . self::MAX_CLAIM_FEE_AMOUNT,
            'repair_fee' => 'nullable|numeric|min:0|max:' . self::MAX_CLAIM_FEE_AMOUNT,
            'replacement_mode' => 'required_if:repair_or_replace,replace|in:remaining,full',
            'replacement_fee' => 'nullable|numeric|min:0|max:' . self::MAX_CLAIM_FEE_AMOUNT,
            'replacement_fee_option' => 'required_if:repair_or_replace,replace|in:free,fee_required',
        ];
    }
}
