<?php

namespace App\Http\Requests\Warranty\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentHandleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $claim = $this->route('claim');
        $claimId = is_object($claim) ? $claim->id : $claim;

        return [
            'action' => 'required|in:remind,pos,cod,online_link,cod_collect,waive,client_reject_payment',
            'charge_ids' => 'required_if:action,pos,cod,online_link,cod_collect|array',
            'charge_ids.*' => 'exists:warranty_claim_charges,id,warranty_claim_id,' . $claimId,
            'payment_reference' => 'nullable|required_if:action,pos,cod_collect|string|max:100',
            'link_expire_hours' => 'nullable|required_if:action,online_link|integer|min:1|max:168',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
