<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Models\WarrantyTimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warranty_public_id' => 'required|exists:warranties,warranty_public_id',
            'subject' => 'required|string|max:255',
            'details' => 'required|string',
            'issue' => 'required|string',
            'product_images' => 'required|array|min:1',
            'product_images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $warranty = Warranty::where('warranty_public_id', $request->warranty_public_id)->firstOrFail();

        if (!$warranty->isActive()) {
            Toastr::error(translate('Warranty is not active or expired.'));
            return back();
        }

        $description = "Subject: {$request->subject}\nDetails: {$request->details}\nIssue: {$request->issue}";

        $submittedAt = Carbon::now();

        $claim = WarrantyClaim::create([
            'warranty_id' => $warranty->id,
            'serial_number' => $warranty->serial_number,
            'claim_number' => 'CLM-' . strtoupper(Str::random(8)),
            'status' => 'new',
            'description' => $description,
            'submitted_at' => $submittedAt,
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
            'description' => 'Claim submitted by customer',
            'timestamp' => now(),
        ]);

        Toastr::success(translate('Claim submitted successfully. Claim number: ') . $claim->claim_number);
        return redirect()->route('warranty.claim.success', $claim->claim_number);
    }

    public function success($claim_number)
    {
        $claim = WarrantyClaim::where('claim_number', $claim_number)->firstOrFail();
        return view(VIEW_FILE_NAMES['claim_success'], compact('claim'));
    }
}
