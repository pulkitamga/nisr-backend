<?php
// Updated WarrantyClaimController.php - Completed missing methods for status filters, decide, receive, submit. Added use statements.

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\Warranty;
use App\Models\WarrantyReplacement;
use App\Models\WorkOrder;
use App\Services\RMAService;
use App\Services\RepairService;
use App\Services\ReplacementService;
use App\Services\ClaimResolutionService;
use App\Jobs\TriageClaimJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use App\Utils\Helpers;
use Illuminate\Support\Facades\Validator;

class WarrantyClaimController extends Controller
{
    private function buildClaimsQuery(Request $request, ?string $status = null)
    {
        $query = WarrantyClaim::with('warranty.user', 'branch');

        if ($status !== null) {
            $query->where('status', $status);
        } elseif ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('searchValue')) {
            $search = $request->searchValue;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fhilter_date')) {
            $dates = explode(' - ', $request->fhilter_date);
            if (count($dates) === 2) {
                $query->whereBetween('submitted_at', [$dates[0], $dates[1]]);
            }
        }

        if ($request->filled('choose_first')) {
            $query->limit((int)$request->choose_first);
        }

        return $query;
    }

    private function renderStatusList(Request $request, string $status)
    {
        $claims = $this->buildClaimsQuery($request, $status)->paginate(20)->appends($request->query());
        return view('admin-views.warranty.claim-list', compact('claims'));
    }

    // All Claims
    public function all(Request $request)
    {
        $claims = $this->buildClaimsQuery($request)->paginate(20)->appends($request->query());

        return view('admin-views.warranty.claim-list', compact('claims'));
    }

    // WarrantyClaimController.php
    public function export(Request $request)
    {
        $query = WarrantyClaim::with([
            'warranty.product',
            'warranty.branch',
            'warranty',
            'technician'
        ]);

        if ($request->filled('fhilter_date')) {
            $dates = explode(' - ', $request->fhilter_date);
            $query->whereBetween('submitted_at', [$dates[0], $dates[1]]);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('searchValue')) {
            $query->where(function ($q) use ($request) {
                $q->where('claim_number', 'like', "%{$request->searchValue}%")
                    ->orWhere('serial_number', 'like', "%{$request->searchValue}%");
            });
        }

        if ($request->filled('choose_first')) {
            $query->limit($request->choose_first);
        }

        $claims = $query->get();
        $filename = 'claims_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://output', 'w');
        ob_clean();
        header('Content-Type:text/csv');
        header('Content-Disposition:attachment; filename="' . $filename . '"');

        fputcsv($handle, [
            'SL',
            'Claim #',
            'Serial',
            'Status',
            'Customer',
            'Product',
            'Distributor',
            'Branch',
            'Activation Method',
            'Submitted',
            'SLA Due',
            'Resolved Date',
            'Reopen Count',
            'Technician',
            'SLA Result'
        ]);

        foreach ($claims as $i => $c) {
            fputcsv($handle, [
                $i + 1,
                $c->claim_number,
                $c->serial_number,
                $c->status,
                $c->warranty->user?->name ?? $c->warranty->activated_by_name,
                $c->warranty->product?->name,
                $c->warranty->distributor_id ?? '-',
                $c->branch?->branch_name ?? '-',
                $c->warranty->activation_method,
                $c->submitted_at?->format('Y-m-d H:i'),
                $c->resolution_due?->format('Y-m-d H:i') ?? '-',
                $c->resolved_at?->format('Y-m-d H:i') ?? '-',
                $c->reopen_count,
                $c->technician?->name ?? '-',
                optional($c->resolution_due)?->lt(now()) ? 'SLA BREACHED' : 'WITHIN SLA'
            ]);
        }

        fclose($handle);
        exit;
    }

    // New Claims
    public function new(Request $request)
    {
        return $this->renderStatusList($request, 'new');
    }

    // Triage Pending
    public function triagePending(Request $request)
    {
        return $this->renderStatusList($request, 'triage_pending');
    }

    // Approved
    public function approved(Request $request)
    {
        return $this->renderStatusList($request, 'approved');
    }

    // RMA Issued
    public function rmaIssued(Request $request)
    {
        return $this->renderStatusList($request, 'rma_issued');
    }

    // Received
    public function received(Request $request)
    {
        return $this->renderStatusList($request, 'received');
    }

    // Repair Pending
    public function repairPending(Request $request)
    {
        return $this->renderStatusList($request, 'repair_pending');
    }

    public function replacementPending(Request $request)
    {
        return $this->renderStatusList($request, 'replacement_pending');
    }

    public function waitingCustomer(Request $request)
    {
        return $this->renderStatusList($request, 'waiting_customer');
    }

    public function waitingParts(Request $request)
    {
        return $this->renderStatusList($request, 'waiting_parts');
    }

    public function waitingPayment(Request $request)
    {
        return $this->renderStatusList($request, 'waiting_payment');
    }

    public function diagnosisPending(Request $request)
    {
        return $this->renderStatusList($request, 'diagnosis_pending');
    }

    public function qcPending(Request $request)
    {
        return $this->renderStatusList($request, 'qc_pending');
    }

    public function shippedReady(Request $request)
    {
        return $this->renderStatusList($request, 'shipped_ready');
    }

    public function dispatched(Request $request)
    {
        return $this->renderStatusList($request, 'dispatched');
    }

    // Resolved
    public function resolved(Request $request)
    {
        return $this->renderStatusList($request, 'resolved');
    }

    // Closed
    public function closed(Request $request)
    {
        return $this->renderStatusList($request, 'closed');
    }

    // Rejected
    public function rejected(Request $request)
    {
        return $this->renderStatusList($request, 'rejected');
    }

    // View Claim
    public function view(WarrantyClaim $claim)
    {
        $claim->load('warranty.user', 'workOrder', 'attachments'); // ← change 'photos' to 'attachments'
        $timeline = $claim->timelineEvents()->latest()->paginate(10);

        return view('admin-views.warranty.claim-view', compact('claim', 'timeline'));
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial_number' => 'required|exists:warranties,serial_number',
            'description' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $claimNumber = 'CLAIM-' . Str::upper(Str::random(8));
        $submittedAt = now();
        $firstResponseDue = $submittedAt->copy()->addHours(24);
        $resolutionDue = $submittedAt->copy()->addDays(3);

        $claim = WarrantyClaim::create([
            'warranty_id' => Warranty::where('serial_number', $request->serial_number)->first()->id,
            'serial_number' => $request->serial_number,
            'claim_number' => $claimNumber,
            'status' => 'new',
            'description' => $request->description,
            'submitted_at' => $submittedAt,
            'response_due' => $firstResponseDue,
            'resolution_due' => $resolutionDue,
        ]);

        // Handle attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('warranty/attachments', 'public');
                $claim->attachments()->create(['file_path' => $path, 'type' => $file->getClientOriginalExtension()]);
            }
        }

        TriageClaimJob::dispatch($claim);

        Toastr::success(translate('Claim submitted successfully.'));
        return redirect()->route('admin.warranty.claim.all');
    }

    // Receive
    public function receive(Request $request, WarrantyClaim $claim)
    {
        // Agar AJAX request hai → JSON return karo
        $isAjax = $request->ajax() || $request->wantsJson();

        if ($claim->status !== 'rma_issued') {
            $msg = translate('RMA not issued for this claim.');
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            Toastr::error($msg);
            return back();
        }

        $request->validate([
            'serial_number' => 'required|string',
            'branch_id'     => 'required|exists:branches,id',
            'received_notes' => 'nullable|string|max:1000',
        ]);

        // Serial Match
        if ($claim->serial_number !== $request->serial_number) {
            $msg = translate('Serial number does not match the RMA issued item.');
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            Toastr::error($msg);
            return back()->withInput();
        }

        // Branch Match
        if ($claim->branch_id != $request->branch_id) {
            $correctBranch = $claim->branch?->branch_name ?? 'Original Branch';
            $msg = translate('Invalid branch. Item must be returned to: ') . $correctBranch;
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            Toastr::error($msg);
            return back()->withInput();
        }

        $deadlineWarning = null;
        if ($claim->rma_deadline && now()->gt($claim->rma_deadline)) {
            $deadlineWarning = translate('RMA deadline expired on ') . $claim->rma_deadline->format('d M Y');
        }

        // Get Branch Name
        $branch = \App\Models\Branch::find($request->branch_id);

        // Update Claim
        $claim->update([
            'status' => 'received',
            'received_at' => now(),
            'diagnosis_notes' => $request->received_notes,
        ]);

        // Create Timeline Event

        $claim->timelineEvents()->create([
            'event_type' => 'item_received',
            'description' => "Item received | Serial: {$request->serial_number} | Branch: " .
                ($branch?->branch_name ?? 'N/A') .
                " | Notes: " . ($request->received_notes ?: 'None'),
            'user_id' =>  auth('admin')->id(),
        ]);

        $successMsg = translate('Item received successfully! Serial and branch verified.');

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'warning' => $deadlineWarning,
                'redirect' => route('admin.warranty.claim.view', $claim)
            ]);
        }

        if ($deadlineWarning) Toastr::warning($deadlineWarning);
        Toastr::success($successMsg);
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function decide(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'decision'       => ['required', 'in:approve,reject,waiting_customer'],
            'reason_code'    => ['required', 'string', 'max:50'],
            'reason_message' => ['required', 'string'],
        ]);

        $update = [];

        $description = "Decision: {$request->decision} | Code: {$request->reason_code} | Message: {$request->reason_message}";

        if ($request->decision === 'approve') {
            $update['status'] = 'approved';
        } elseif ($request->decision === 'reject') {
            $update['status'] = 'rejected';
        } else {
            $update['status'] = 'waiting_customer';
        }

        $existingNotes = trim((string)$claim->diagnosis_notes);
        $decisionNote = "Decision note ({$request->reason_code}): {$request->reason_message}";
        $update['diagnosis_notes'] = $existingNotes ? "{$existingNotes}\n{$decisionNote}" : $decisionNote;

        $claim->update($update);

        $claim->timelineEvents()->create([
            'event_type' => 'decision_made',
            'description' => $description,
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Decision recorded.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    // WarrantyClaimController.php
    public function paymentHandle(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'action'      => 'required|in:remind,paid,waive,reject',
            'charge_ids'  => 'required_if:action,paid|array',
            'charge_ids.*' => 'exists:warranty_claim_charges,id,warranty_claim_id,' . $claim->id,
            'notes'       => 'nullable|string',
        ]);

        $description = "Payment handling: {$request->action} | Notes: {$request->notes}";

        if ($request->action === 'paid') {
            // Mark selected charges as paid
            $paidCharges = $claim->charges()
                ->whereIn('id', $request->charge_ids)
                ->where('is_paid', false)
                ->get();

            if ($paidCharges->isEmpty()) {
                return back()->withErrors(['charge_ids' => 'No valid unpaid charges selected.']);
            }

            $paidCharges->each->update(['is_paid' => true]);

            $paidList = $paidCharges->map(fn($c) => "{$c->charge_type}: {$c->amount}")->implode(', ');
            $description .= " | Paid: {$paidList}";

            // Check if all charges are now paid → resume workflow
            $allPaid = $claim->charges()->where('is_paid', false)->count() === 0;

            if ($allPaid) {
                $nextStatus = $claim->repair_or_replace === 'repair'
                    ? 'repair_pending'
                    : 'replacement_pending';

                $claim->update(['status' => $nextStatus]);
                $description .= " | All charges paid. Resumed to {$nextStatus}";
            }
        } elseif ($request->action === 'waive') {
            // Waive ALL unpaid charges
            $waived = $claim->charges()->where('is_paid', false)->get();
            $waived->each->update(['is_paid' => true]);

            $waivedList = $waived->map(fn($c) => "{$c->charge_type}: {$c->amount}")->implode(', ');
            $description .= " | Waived: {$waivedList}";

            $nextStatus = $claim->repair_or_replace === 'repair'
                ? 'repair_pending'
                : 'replacement_pending';

            $claim->update([
                'status' => $nextStatus,
                'is_fee_waived' => true,
                'is_admin_override' => true,
                'override_reason' => $request->notes,
                'override_by_user_id' => auth('admin')->id(),
            ]);
            $description .= " | Resumed to {$nextStatus}";
        } elseif ($request->action === 'reject') {
            $claim->update([
                'status' => 'rejected',
                'diagnosis_notes' => 'Rejected due to non-payment: ' . $request->notes,
            ]);
            $description .= " | Claim rejected";
        } elseif ($request->action === 'remind') {
            $description .= " | Reminder sent to customer";
            // TODO: Send email/SMS
        }

        $claim->timelineEvents()->create([
            'event_type'  => 'payment_handled',
            'description' => $description,
            'user_id'     => auth('admin')->id(),
        ]);

        Toastr::success(translate('Payment handled successfully.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function diagnose(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'diagnosis_notes'        => 'required|string',
            'repair_or_replace'      => 'required|in:repair,replace,reject',
            'tamper_detected'        => 'boolean',
            'inspection_fee'         => 'nullable|numeric|min:0',
            'repair_fee'             => 'nullable|numeric|min:0',
            'replacement_mode'       => 'required_if:repair_or_replace,replace|in:remaining,full',
            'replacement_fee'        => 'nullable|numeric|min:0',
            'replacement_fee_option' => 'required_if:repair_or_replace,replace|in:free,fee_required',
        ]);

        $update = [
            'diagnosis_notes'   => $request->diagnosis_notes,
            'repair_or_replace' => $request->repair_or_replace,
            'tamper_detected'   => $request->boolean('tamper_detected'),
        ];

        // ——— AUTO REJECT IF TAMPER + REJECT ———
        if ($request->repair_or_replace === 'reject' || $request->boolean('tamper_detected')) {
            $claim->update(array_merge($update, ['status' => 'rejected']));
            $description = "Diagnosis: {$request->diagnosis_notes} | REJECTED | Tamper: " . ($request->boolean('tamper_detected') ? 'Yes' : 'No');
            $claim->timelineEvents()->create([
                'event_type'  => 'diagnosis_complete',
                'description' => $description,
                'user_id'     => auth('admin')->id(),
            ]);
            Toastr::success(translate('Claim rejected.'));
            return redirect()->route('admin.warranty.claim.view', $claim);
        }

        $charges = [];
        $hasCharges = false;

        // ——— REPLACEMENT: Store mode + optional fee ———
        if ($request->repair_or_replace === 'replace') {
            $update['replacement_mode'] = $request->replacement_mode;

            if ($request->replacement_fee_option === 'fee_required' && $request->replacement_fee > 0) {
                $charges[] = [
                    'charge_type' => 'replacement_fee',
                    'amount'      => $request->replacement_fee,
                    'is_paid'     => false,
                ];
                $hasCharges = true;
            }
        }

        // ——— REPAIR FEE ———
        if ($request->repair_or_replace === 'repair' && $request->filled('repair_fee') && $request->repair_fee > 0) {
            $charges[] = [
                'charge_type' => 'repair_fee',
                'amount'      => $request->repair_fee,
                'is_paid'     => false,
            ];
            $hasCharges = true;
        }

        // ——— INSPECTION FEE (TAMPER) ———
        if ($request->boolean('tamper_detected') && $request->filled('inspection_fee') && $request->inspection_fee > 0) {
            $charges[] = [
                'charge_type' => 'inspection_fee',
                'amount'      => $request->inspection_fee,
                'is_paid'     => false,
            ];
            $hasCharges = true;
        }

        // ——— SET STATUS ———
        $update['status'] = $hasCharges ? 'waiting_payment' : (
            $request->repair_or_replace === 'repair' ? 'repair_pending' : 'replacement_pending'
        );

        $claim->update($update);

        // ——— CREATE CHARGES ———
        if ($hasCharges) {
            foreach ($charges as $charge) {
                $claim->charges()->create($charge);
            }
        }

        // ——— TIMELINE ———
        $actionText = $request->repair_or_replace;
        if ($request->repair_or_replace === 'replace') {
            $actionText .= " ({$request->replacement_fee_option}, {$request->replacement_mode})";
        }

        $description = "Diagnosis: {$request->diagnosis_notes} | Action: {$actionText}";

        if ($hasCharges) {
            $feeTexts = collect($charges)->map(fn($c) => "{$c['charge_type']} = {$c['amount']}")->implode(', ');
            $description .= " | Charges: {$feeTexts}";
        }

        $claim->timelineEvents()->create([
            'event_type'  => 'diagnosis_complete',
            'description' => $description,
            'user_id'     => auth('admin')->id(),
        ]);

        Toastr::success(translate('Diagnosis submitted.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }
    public function repairComplete(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'labor_notes' => 'nullable|string',
            'parts_used'  => 'nullable|string',
        ]);

        $claim->update(['status' => 'qc_pending']);

        $partsArr = $request->parts_used
            ? array_map('trim', explode(',', $request->parts_used))
            : [];

        $parts = implode(', ', $partsArr);
        $description = "Repair completed. Parts: {$parts} | Notes: {$request->labor_notes}";

        $claim->timelineEvents()->create([
            'event_type' => 'repair_complete',
            'description' => $description,
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Repair completed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }


    public function qcPass(Request $request, WarrantyClaim $claim)
    {
        $claim->update([
            'qc_passed_at' => now(),
            'status' => 'shipped_ready',
        ]);

        $claim->timelineEvents()->create([
            'event_type' => 'qc_passed',
            'description' => 'QC passed, ready for dispatch.',
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('QC passed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function markDispatch(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'dispatch_mode'   => 'required|in:pickup,ship',
            'tracking_number' => $request->dispatch_mode == 'ship' ? 'required|string' : 'nullable|string',
        ]);

        $claim->update([
            'tracking_number' => $request->tracking_number ?? '',
            'dispatched_at'   => now(),
            'status'          => 'dispatched',
        ]);

        $description = "Dispatched via {$request->dispatch_mode}";
        if ($request->tracking_number) {
            $description .= " | Tracking: {$request->tracking_number}";
        }

        // YE LINE SAFE HAI – sirf create, no return
        $claim->timelineEvents()->create([
            'event_type'  => 'dispatched',
            'description' => $description,
            'user_id'     => auth('admin')->id(),
        ]);

        return response()->json(['message' => translate('Mark as Dispatched.')]);
    }

    public function issueRma(Request $request, WarrantyClaim $claim)
    {
        $request->validate([
            'rma_number'    => 'nullable|string|max:50',
            'return_days'   => 'required|integer|min:1',
            'instructions'  => 'required|string',
            'branch_id'     => 'required|exists:branches,id',
        ]);

        $rma = $request->rma_number ?: 'RMA-' . strtoupper(Str::random(6));
        $deadline = now()->addDays($request->return_days);

        $claim->update([
            'status'         => 'rma_issued',
            'rma_number'     => $rma,
            'rma_deadline'   => $deadline,
            'branch_id'      => $request->branch_id,
        ]);

        $claim->timelineEvents()->create([
            'event_type'  => 'rma_issued',
            'description' => "RMA {$rma} issued – deadline {$deadline->format('Y-m-d')}",
            'user_id'     => auth('admin')->id(),
        ]);

        return response()->json(['message' => translate('RMA issued.')]);
    }


    public function resume(Request $request, WarrantyClaim $claim)
    {
        $allowed = [
            'waiting_customer' => 'received',
            'waiting_parts'    => 'repair_pending',
            'waiting_payment'  => 'diagnosis_pending',
        ];

        $target = $request->target_status;
        if (!isset($allowed[$claim->status]) || $allowed[$claim->status] !== $target) {
            return response()->json(['message' => translate('Invalid target status.')], 422);
        }

        $fromStatus = $claim->status;
        $claim->update([
            'status' => $target,
        ]);

        $claim->timelineEvents()->create([
            'event_type'  => 'claim_resumed',
            'description' => "Resumed from {$fromStatus} → {$target}. Notes: {$request->notes}",
            'user_id'     => auth('admin')->id(),
        ]);

        return response()->json(['message' => translate('Claim resumed.')]);
    }


    public function replacementCommit(Request $request, WarrantyClaim $claim)
    {
        // Fetch old warranty first for compatibility validation
        $oldWarranty = $claim->warranty;
        if (!$oldWarranty) {
            Toastr::error(translate('Original warranty not found.'));
            return back();
        }

        $request->validate([
            'new_serial_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($oldWarranty) {
                    $newWarranty = Warranty::where('serial_number', $value)
                        ->where('status', 'preactivated')
                        ->whereNull('final_user_id')
                        ->first();

                    if (!$newWarranty) {
                        $fail('Serial number is invalid, already activated, or not preactivated.');
                        return;
                    }

                    if ((int)$newWarranty->product_id !== (int)$oldWarranty->product_id) {
                        $fail('Serial number belongs to a different product and cannot be used for this replacement.');
                    }
                },
            ],
            'notes' => 'nullable|string',
        ]);

        // Fetch new warranty (validated above)
        $newWarranty = Warranty::where('serial_number', $request->new_serial_number)
            ->where('status', 'preactivated')
            ->whereNull('final_user_id')
            ->where('product_id', $oldWarranty->product_id)
            ->firstOrFail();

        // Determine replacement mode
        $replacementModeConfig = getWebConfig('warranty_replacement_mode');
        $fallbackMode = is_array($replacementModeConfig)
            ? ($replacementModeConfig['value'] ?? null)
            : $replacementModeConfig;
        $mode = $claim->replacement_mode ?: $fallbackMode;
        $mode = $mode === 'full' ? 'full' : 'remaining';

        $newStart = now();
        $newEnd = null;

        if ($mode === 'full') {
            // Full term from product policy
            $fullMonths = (int)($newWarranty->warranty_months ?? 0);
            $newEnd = $newStart->copy()->addMonths(max(0, $fullMonths));
        } else {
            // Remaining term from old warranty, never over-grant expired claims
            $remainingDays = $oldWarranty->end_date
                ? max(0, $newStart->diffInDays($oldWarranty->end_date, false))
                : 0;
            $newEnd = $newStart->copy()->addDays($remainingDays);
        }

        // Activate new warranty with customer details
        $newWarranty->update([
            'status' => 'active',
            'activation_date' => now(),
            'start_date' => $newStart,
            'end_date' => $newEnd,
            'final_user_id' => $oldWarranty->final_user_id,
            'activated_by_name' => $oldWarranty->activated_by_name,
            'activated_by_phone' => $oldWarranty->activated_by_phone,
            'activated_by_email' => $oldWarranty->activated_by_email,
        ]);

        // Create replacement record
        WarrantyReplacement::create([
            'original_warranty_id' => $oldWarranty->id,
            'new_warranty_id' => $newWarranty->id,
            'replaced_at' => now(),
            'technician_id' => auth('admin')->id(),
            'notes' => $request->notes,
        ]);
        $oldWarranty->update(['status' => 'replaced']);
        $claim->update(['status' => 'shipped_ready']);
        $description = "Replacement committed: {$request->new_serial_number} | Mode: {$mode} | "
            . "Warranty: {$newStart->format('Y-m-d')} to {$newEnd->format('Y-m-d')} | Notes: {$request->notes}";

        $claim->timelineEvents()->create([
            'event_type' => 'replacement_committed',
            'description' => $description,
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Replacement committed and new serial activated.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }
    public function close(Request $request, WarrantyClaim $claim)
    {
        $request->validate(['resolution_notes' => 'nullable|string']);

        $forcedClose = $claim->status !== 'resolved';
        $claim->update([
            'status' => 'closed',
            'resolved_at' => $claim->resolved_at ?? now(),
        ]);

        $description = 'Claim closed';
        if ($forcedClose) {
            $description .= ' (forced by admin)';
        }
        $description .= '. Notes: ' . ($request->resolution_notes ?? 'N/A');

        $claim->timelineEvents()->create([
            'event_type' => 'closed',
            'description' => $description,
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Claim closed.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }

    public function resolve(Request $request, WarrantyClaim $claim)
    {
        $request->validate(['resolution_notes' => 'nullable|string']);

        $claim->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $claim->timelineEvents()->create([
            'event_type' => 'resolved',
            'description' => 'Claim resolved on delivery/collection. Notes: ' . ($request->resolution_notes ?? 'N/A'),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Claim resolved.'));
        return redirect()->route('admin.warranty.claim.view', $claim);
    }
}
