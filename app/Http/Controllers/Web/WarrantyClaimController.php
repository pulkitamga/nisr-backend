<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warranty\ClaimStoreRequest;
use App\Jobs\TriageClaimJob;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Models\WarrantyTimelineEvent;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;

class WarrantyClaimController extends Controller
{
    public function create($warranty_public_id)
    {
        $warranty = Warranty::where('warranty_public_id', $warranty_public_id)->firstOrFail();

        if (!$warranty->isActive()) {
            Toastr::error(translate('Warranty is not active or expired.'));
            return redirect()->back();
        }

        if ($warranty->claims()->open()->exists()) {
            Toastr::error(translate('There is already an open claim for this warranty.'));
            return redirect()->back();
        }

        return view(VIEW_FILE_NAMES['claim_form'], compact('warranty'));
    }

    public function store(ClaimStoreRequest $request)
    {
        $warranty = Warranty::where('warranty_public_id', $request->warranty_public_id)->firstOrFail();

        if (!$warranty->isActive()) {
            Toastr::error(translate('Warranty is not active or expired.'));
            return back();
        }

        if ($warranty->claims()->open()->exists()) {
            Toastr::error(translate('There is already an open claim for this warranty.'));
            return back();
        }

        $description = "Subject: {$request->subject}\nDetails: {$request->details}\nIssue: {$request->issue}";

        $submittedAt = Carbon::now();
        $firstResponseHours = (int) (getWebConfig(name: 'warranty_sla_first_response')['value'] ?? 24);
        $resolutionDays = (int) (getWebConfig(name: 'warranty_sla_decision')['value'] ?? 3);

        $claim = WarrantyClaim::create([
            'warranty_id' => $warranty->id,
            'serial_number' => $warranty->serial_number,
            'branch_id' => $warranty->branch_id,
            'claim_number' => WarrantyClaim::generateClaimNumber('CLM-'),
            'status' => 'new',
            'description' => $description,
            'submitted_at' => $submittedAt,
            'response_due' => $submittedAt->copy()->addHours($firstResponseHours),
            'resolution_due' => $submittedAt->copy()->addDays($resolutionDays),
        ]);

        foreach ($request->file('product_images') as $file) {
            $path = $file->store('warranty/attachments', 'public');
            WarrantyClaimAttachment::create([
                'warranty_claim_id' => $claim->id,
                'file_path' => $path,
                'type' => 'image',
            ]);
        }

        WarrantyTimelineEvent::create([
            'warranty_id' => $warranty->id,
            'warranty_claim_id' => $claim->id,
            'event_type' => 'claim_submitted',
            'description' => translate('Claim submitted by customer'),
            'timestamp' => now(),
        ]);

        TriageClaimJob::dispatch($claim);

        Toastr::success(translate('Claim submitted successfully. Claim number: ') . $claim->claim_number);
        return redirect()->route('warranty.claim.success', $claim->claim_number);
    }

    public function success($claim_number)
    {
        $claim = WarrantyClaim::where('claim_number', $claim_number)->firstOrFail();
        return view(VIEW_FILE_NAMES['claim_success'], compact('claim'));
    }
}
