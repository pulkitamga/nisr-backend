<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;

class LeadConvert
{
    public static function fromInboxMessage(InboxMessage $message, $subType = null, $departmentId = null): Lead
    {
        $authUser = Auth::guard('admin')->user();

        return Lead::create([
            'party_type'   => $subType ?? 'retail',
            'company_id'   => null,
            'contact_id'   => null,
            'department_id' => $departmentId,
            'employee_id'  => null,
            'source_id'    => null,
            'owner_id'   => $message->owner_id ?? null,
            'utm_source'   => $message->utm_source ?? null,
            'priority'     => $message->priority ?? null,
            'utm_campaign' => $message->utm_campaign ?? null,
            'utm_medium'   => $message->utm_medium ?? null,
            'utm_term'     => $message->utm_term ?? null,
            'utm_content'  => $message->utm_content ?? null,
            'status'       => 'new',
        ]);
    }
}
