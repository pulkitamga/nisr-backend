<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\Warranty;
use App\Jobs\TriageClaimJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use App\Utils\Helpers;

class WarrantyClaimController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'description' => 'required|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        ]);

        $warranty = Warranty::active()->where('serial_number', $request->serial_number)->firstOrFail();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('warranty/attachments', 'public');
            }
        }

        $slaFirst = getWebConfig(name: 'warranty_sla_first_response')['value'] ?? 1;
        $slaDecision = getWebConfig(name: 'warranty_sla_decision')['value'] ?? 3;

        $claim = WarrantyClaim::create([
            'warranty_id' => $warranty->id,
            'serial_number' => $request->serial_number,
            'claim_number' => 'CLAIM-' . Str::upper(Str::random(10)),
            'status' => 'new',
            'description' => $request->description,
            'submitted_at' => now(),
            'attachments' => $attachments,
        ]);

        event(new \App\Events\ClaimSubmittedEvent($claim));  // Your event

        TriageClaimJob::dispatch($claim);

        Toastr::success(translate('Claim submitted!'));
        return back()->with('claim_number', $claim->claim_number);
    }

    // decide, receive, etc. – similar, use Toastr, authorize via roles in user
}