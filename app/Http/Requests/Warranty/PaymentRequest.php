<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_id' => 'required|exists:warranty_claim_payments,id',
            'payment_method' => 'required|string|max:100',
            'payment_platform' => 'required|in:web,app',
        ];
    }
}
