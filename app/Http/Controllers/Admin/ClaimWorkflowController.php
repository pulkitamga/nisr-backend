<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\Warranty;
use App\Models\WorkOrder;
use App\Events\RepairCompletedEvent;
use App\Events\RepairFailedEvent;
use App\Events\RMAIssuedEvent;
use App\Events\DispatchReadyEvent;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClaimWorkflowController extends Controller
{
    public function diagnose(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'diagnosis_notes' => 'required|string',
            'repair_or_replace' => 'required|in:repair,replace',
            'tamper_detected' => 'nullable|boolean',
            'inspection_fee' => 'nullable|numeric|min:0',
        ]);

        $claim->update([
            'status' => $request->repair_or_replace . '_pending',
            'diagnosis_notes' => $request->diagnosis_notes,
            'inspection_fee_amount' => $request->tamper_detected ? $request->inspection_fee : 0,
            'is_fee_waived' => !$request->tamper_detected,
        ]);

        $claim->timelineEvents()->create([
            'event_type' => 'diagnosis',
            'description' => "Diagnosed: {$request->repair_or_replace}, Notes: {$request->diagnosis_notes}",
            'user_id' => auth('admin')->id(),
        ]);

        if ($request->repair_or_replace === 'replace') {
            event(new RMAIssuedEvent($claim));
        }

        Toastr::success(translate('Diagnosis recorded.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function repairComplete(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'parts_used' => 'nullable|array',
            'labor_hours' => 'required|numeric|min:0',
            'qc_passed' => 'required|boolean',
            'tracking_number' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::firstOrCreate(
            ['warranty_claim_id' => $claim->id],
            ['status' => 'completed']
        );

        $workOrder->update([
            'parts_used' => $request->parts_used,
            'labor_hours' => $request->labor_hours,
        ]);

        $claim->update([
            'status' => $request->qc_passed ? 'shipped_ready' : 'repair_pending',
        ]);

        if ($request->qc_passed) {
            event(new RepairCompletedEvent($claim));
            if ($request->tracking_number) {
                event(new DispatchReadyEvent($claim, $request->tracking_number));
            }
        } else {
            event(new RepairFailedEvent($claim));
        }

        $claim->timelineEvents()->create([
            'event_type' => 'repair_completed',
            'description' => "QC: " . ($request->qc_passed ? 'Passed' : 'Failed'),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Repair completed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function replacementCommit(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'new_serial_number' => 'required|string|unique:warranties,serial_number',
            'pricing_type' => 'required|in:free,fee_required',
            'coverage_mode' => 'required|in:remaining,full',
            'replacement_fee' => 'required_if:pricing_type,fee_required|numeric|min:0',
        ]);

        $originalWarranty = $claim->warranty;
        $remainingDays = $originalWarranty->remaining_days;

        $newWarranty = Warranty::create([
            'serial_number' => $request->new_serial_number,
            'product_id' => $originalWarranty->product_id,
            'status' => 'active',
            'activation_date' => now(),
            'start_date' => now(),
            'end_date' => $request->coverage_mode === 'remaining' ? now()->addDays($remainingDays) : now()->addMonths($originalWarranty->warranty_months),
            'final_user_id' => $originalWarranty->final_user_id,
            'original_warranty_id' => $originalWarranty->id,
            'activation_method' => 'replacement',
        ]);

        $originalWarranty->update(['status' => 'replaced']);

        $claim->update([
            'status' => 'shipped_ready',
            'rma_number' => $claim->rma_number ?? 'RMA-' . Str::upper(Str::random(6)),
            'inspection_fee_amount' => $request->pricing_type === 'fee_required' ? $request->replacement_fee : 0,
        ]);

        $claim->timelineEvents()->create([
            'event_type' => 'replacement',
            'description' => "New serial: {$request->new_serial_number}, Mode: {$request->coverage_mode}",
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Replacement committed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function close(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'csat_score' => 'nullable|integer|min:1|max:5',
            'qa_notes' => 'nullable|string',
        ]);

        $claim->update([
            'status' => 'closed',
            'resolved_at' => $claim->resolved_at ?? now(),
        ]);

        $claim->timelineEvents()->create([
            'event_type' => 'closed',
            'description' => "Closed with CSAT: " . ($request->csat_score ?? 'N/A'),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Claim closed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }
}
