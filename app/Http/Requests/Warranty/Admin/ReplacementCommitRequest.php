<?php

namespace App\Http\Requests\Warranty\Admin;

use App\Models\Warranty;
use Illuminate\Foundation\Http\FormRequest;

class ReplacementCommitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $claim = $this->route('claim');
        $oldWarranty = $claim?->warranty;

        return [
            'new_serial_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($oldWarranty) {
                    if (!$oldWarranty) {
                        $fail(translate('Original warranty not found.'));
                        return;
                    }

                    $newWarranty = Warranty::where('serial_number', $value)
                        ->where('status', 'preactivated')
                        ->whereNull('final_user_id')
                        ->first();

                    if (!$newWarranty) {
                        $fail(translate('Serial number is invalid, already activated, or not preactivated.'));
                        return;
                    }

                    if ((int)$newWarranty->product_id !== (int)$oldWarranty->product_id) {
                        $fail(translate('Serial number belongs to a different product and cannot be used for this replacement.'));
                    }
                },
            ],
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
