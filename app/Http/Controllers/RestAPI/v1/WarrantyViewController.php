<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Events\DigitalProductOtpVerificationEvent;
use Illuminate\Http\Request;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimPayment;
use App\Models\ViewToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Support\OtpManager;
use App\Support\WarrantyLookupContactNormalizer;
use App\Utils\SMSModule;

class WarrantyViewController extends Controller
{
    public function __construct(
        private readonly FirebaseService $firebaseService,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    /**
     * Start warranty lookup (just instructions for app)
     */
    public function lookupStart()
    {
        return response()->json([
            'message' => 'Enter serial number and contact to start warranty lookup',
        ]);
    }

    /**
     * Submit warranty lookup (Firebase reCAPTCHA + OTP)
     */
    //    public function lookupSubmit(Request $request)
    // {
    //     $request->validate([
    //         'serial_number' => 'required|string|exists:warranties,serial_number',
    //         'contact' => 'required|string',
    //         'recaptcha_response' => 'required|string', // Changed from recaptcha_token
    //     ]);

    //     // Verify reCAPTCHA using the same method as web
    //     $recaptcha = getWebConfig(name: 'recaptcha');
    //     if (isset($recaptcha) && $recaptcha['status'] == 1) {
    //         try {
    //             $secret_key = $recaptcha['secret_key'];
    //             $response = $request->recaptcha_response;
    //             $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
    //             $verifyResponse = file_get_contents($url);
    //             $responseData = json_decode($verifyResponse);

    //             if (!$responseData->success) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => translate('reCAPTCHA verification failed'),
    //                 ], 422);
    //             }
    //         } catch (\Exception $exception) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => translate('reCAPTCHA verification error'),
    //             ], 500);
    //         }
    //     }

    //     // Rest of your method remains the same...
    // }

     public function lookupSubmit(Request $request)
    {
        // 1. Validation
        $request->validate([
            'serial_number' => 'required|string|exists:warranties,serial_number',
            'contact' => 'required|string',
        ]);
 
        // 2. Warranty Checks
        $warranty = Warranty::where('serial_number', $request->serial_number)->first();
        $normalizedContact = WarrantyLookupContactNormalizer::normalize((string)$request->contact);
 
        if ($warranty->status == 'preactivated') {
            return response()->json([
                'status' => 'error',
                'message' => translate('Warranty not found')
            ], 404);
        }
 
        $oldPhone = $warranty->final_user_id
            ? $warranty->user?->phone
            : $warranty->activated_by_phone;
        $normalizedOldPhone = WarrantyLookupContactNormalizer::normalize($oldPhone);
 
        if ($normalizedOldPhone && $normalizedContact && $normalizedOldPhone !== $normalizedContact) {
            return response()->json([
                'status' => 'error',
                'message' => translate('Phone number mismatch')
            ], 400);
        }
 
        // 3. OTP Logic (Stateless)
        $otpRequire = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_require_otp'])['value'] ?? '0';
 
        if ($otpRequire == '1') {
 
            $firebaseOtpSetting = getWebConfig('firebase_otp_verification');
            $otpMethod = ($firebaseOtpSetting && $firebaseOtpSetting['status'] == 1)
                ? 'firebase'
                : 'manual';
 
            $sessionData = [
                'warranty_id' => $warranty->id,
                'contact' => $normalizedContact ?? (string)$request->contact,
                'otp_method' => $otpMethod,
            ];
 
            if ($otpMethod === 'firebase') {
 
                $response = $this->firebaseService->sendOtp($normalizedContact ?? (string)$request->contact);
 
                if ($response && $response['status'] === 'success') {
                    $sessionData['otp_session'] = $response['sessionInfo'];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Firebase OTP failed'
                    ], 500);
                }
            } else {

                $otp = OtpManager::warrantyToken();

                Cache::put(
                    "warranty_lookup:{$warranty->id}:{$normalizedContact}",
                    $otp,
                    now()->addMinutes(5)
                );

                $this->dispatchLookupOtp($warranty, (string)$normalizedContact, (string)$otp);
            }
 
            return response()->json([
                'warranty_id' => $warranty->id,
                'status' => 'otp_required',
                'otp_method' => $otpMethod,
                'temp_token' => encrypt($sessionData),
                'masked_contact' => $this->maskContact((string)($normalizedContact ?? $request->contact)),
                'message' => translate('OTP sent successfully')
            ]);
        }
 
        // 4. Direct Success (No OTP Required)
        return $this->generateViewTokenResponse($warranty, $request, (string)($normalizedContact ?? $request->contact));
    }

    /**
     * Verify OTP for app
     */
    public function lookupVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
            'temp_token' => 'nullable|string',
            'warranty_id' => 'nullable|exists:warranties,id',
            'contact' => 'nullable|string',
        ]);

        $sessionData = [];
        if ($request->filled('temp_token')) {
            try {
                $sessionData = decrypt($request->temp_token);
            } catch (\Throwable $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification session',
                ], 422);
            }
        }

        $warrantyId = $sessionData['warranty_id'] ?? $request->warranty_id;
        $contact = WarrantyLookupContactNormalizer::normalize((string)($sessionData['contact'] ?? $request->contact));

        if (!$warrantyId || !$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Verification session expired',
            ], 422);
        }

        $otpMethod = $sessionData['otp_method'] ?? 'manual';
        $isValid = false;

        if ($otpMethod === 'firebase') {
            $sessionInfo = $sessionData['otp_session'] ?? null;
            $response = $this->firebaseService->verifyOtp($sessionInfo, $contact, $request->otp);
            $isValid = ($response['status'] ?? '') === 'success';
        } else {
            $storedOtp = Cache::get("warranty_lookup:{$warrantyId}:{$contact}");
            $isValid = OtpManager::matchesWarrantyToken((string)$request->otp, filled($storedOtp) ? (string)$storedOtp : null);
        }

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        if ($otpMethod !== 'firebase') {
            Cache::forget("warranty_lookup:{$warrantyId}:{$contact}");
        }

        $warranty = Warranty::findOrFail($warrantyId);

        return $this->generateViewTokenResponse($warranty, $request, $contact);
    }

    /**
     * Generate view token and return link for app
     */
    private function generateViewTokenResponse($warranty, $request, string $contact)
    {
        if (!$warranty->warranty_public_id) {
            $warranty->warranty_public_id = Str::uuid();
            $warranty->save();
        }

        $jti = Str::uuid();
        ViewToken::create([
            'jti' => $jti,
            'warranty_public_id' => $warranty->warranty_public_id,
            'recipient_hash' => hash_hmac('sha256', $contact, config('app.key')),
            'scope' => 'warranty:view',
            'issued_at' => now(),
            'expires_at' => now()->addMinutes(getWebConfig('view_token_ttl_minutes') ?? 10),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'warranty_id' => $warranty->id,
            'warranty_public_id' => $warranty->warranty_public_id,
            'view_token' => $jti,
            'view_url' => route('api.warranty.view', [
                'warranty_public_id' => $warranty->warranty_public_id,
                'vt' => $jti
            ]),
        ]);
    }

    /**
     * App view of warranty using token
     */
    public function view(Request $request, $warranty_public_id)
    {
        $token = ViewToken::where('warranty_public_id', $warranty_public_id)
            ->where('jti', $request->query('vt'))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ], 403);
        }

        $token->update(['used_at' => now()]);

        $warranty = Warranty::where('warranty_public_id', $warranty_public_id)
            ->with([
                'product:id,name,code',
                'user:id,f_name,l_name,email,phone',
                'claims' => fn($query) => $query
                    ->latest('updated_at')
                    ->with([
                        'attachments',
                        'payments',
                        'timelineEvents' => fn($timelineQuery) => $timelineQuery->latest('timestamp'),
                    ]),
                'timelineEvents' => fn($query) => $query->latest('timestamp'),
            ])
            ->firstOrFail();

        $isOwner = $request->user()
            && $warranty->final_user_id
            && (int)$request->user()->id === (int)$warranty->final_user_id;
        $openClaim = $warranty->claims->first(
            fn(WarrantyClaim $claim) => !in_array($claim->status, ['closed', 'rejected'], true)
        );
        $payment = $openClaim ? $this->resolveActiveClaimPayment($openClaim) : null;

        return response()->json([
            'success' => true,
            'warranty' => $this->formatWarrantyData($warranty, !$isOwner),
            'timeline_events' => $this->formatTimelineEvents($warranty->timelineEvents),
            'open_claim' => $openClaim ? $this->formatClaimSummary($openClaim) : null,
            'payment' => $openClaim ? $this->formatPaymentSummary($payment) : null,
            'available_actions' => [
                'can_claim' => $warranty->statusLabel() === 'active' && $openClaim === null,
                'can_pay' => $payment !== null && !empty($payment->payment_link),
                'can_view_claim' => $openClaim !== null,
            ],
        ]);
    }

    private function formatWarrantyData(Warranty $warranty, bool $maskOwner): array
    {
        $customerName = trim((string)($warranty->user?->f_name . ' ' . $warranty->user?->l_name));
        if ($customerName === '') {
            $customerName = (string)($warranty->activated_by_name ?? '');
        }

        $email = (string)($warranty->user?->email ?? $warranty->activated_by_email ?? '');
        $phone = (string)($warranty->user?->phone ?? $warranty->activated_by_phone ?? '');
        $warrantyStatusKey = $warranty->statusLabel();

        return [
            'warranty_public_id' => $warranty->warranty_public_id,
            'serial_number' => $warranty->serial_number,
            'status_key' => $warrantyStatusKey,
            'status' => $warrantyStatusKey,
            'status_label' => $this->translateWarrantyStatus($warrantyStatusKey),
            'activation_status' => $warranty->status,
            'activation_date' => optional($warranty->activation_date)?->toIso8601String(),
            'start_date' => optional($warranty->start_date)?->toIso8601String(),
            'end_date' => optional($warranty->end_date)?->toIso8601String(),
            'policy_version' => $warranty->policy_version,
            'remaining_days' => $warranty->remaining_days,
            'product_name' => $warranty->product?->name,
            'customer_name' => $maskOwner ? $this->maskName($customerName) : $customerName,
            'activated_by_name' => $maskOwner ? $this->maskName($customerName) : $customerName,
            'email' => $maskOwner ? $this->maskEmail($email) : $email,
            'activated_by_email' => $maskOwner ? $this->maskEmail($email) : $email,
            'phone' => $maskOwner ? $this->maskPhone($phone) : $phone,
            'activated_by_phone' => $maskOwner ? $this->maskPhone($phone) : $phone,
            'product' => [
                'id' => $warranty->product?->id,
                'name' => $warranty->product?->name,
                'code' => $warranty->product?->code,
            ],
        ];
    }

    private function formatClaimSummary(WarrantyClaim $claim): array
    {
        $latestEventAt = $claim->timelineEvents->first()?->timestamp
            ?? $claim->updated_at
            ?? $claim->submitted_at;
        $groupedStatusKey = $this->groupClaimStatusKey($claim->status);

        return [
            'claim_number' => $claim->claim_number,
            'status_key' => $claim->status,
            'status' => $this->claimStatusLabel($claim->status),
            'status_label' => $this->claimStatusLabel($claim->status),
            'grouped_status_key' => $groupedStatusKey,
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'customer_meaning' => method_exists($this, 'claimStatusMeaning')
                ? $this->claimStatusMeaning($claim->status)
                : null,
            'latest_event_at' => optional($latestEventAt)?->toIso8601String(),
            'updated_at' => optional($claim->updated_at)?->toIso8601String(),
        ];
    }

    private function formatPaymentSummary(?WarrantyClaimPayment $payment): ?array
    {
        if (!$payment) {
            return null;
        }

        return [
            'required' => $payment->payment_status === 'pending',
            'payment_id' => $payment->id,
            'status' => $payment->payment_status,
            'amount' => (float)$payment->amount,
            'amount_label' => number_format((float)$payment->amount, 2),
            'redirect_link' => $payment->payment_link,
            'expires_at' => optional($payment->payment_link_expires_at)?->toIso8601String(),
        ];
    }

    private function formatTimelineEvents($events): array
    {
        return $events
            ->take(20)
            ->map(fn($event) => [
                'event_type' => $event->event_type,
                'description' => $this->translateTimelineDescription($event),
                'description_raw' => $event->description,
                'timestamp' => optional($event->timestamp ?? $event->created_at)?->toIso8601String(),
                'created_at' => optional($event->created_at)?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function resolveActiveClaimPayment(WarrantyClaim $claim): ?WarrantyClaimPayment
    {
        return $claim->payments
            ->sortByDesc('id')
            ->first(function (WarrantyClaimPayment $payment) {
                if ($payment->payment_status !== 'pending') {
                    return false;
                }

                if ($payment->payment_link_expires_at && $payment->payment_link_expires_at->isPast()) {
                    return false;
                }

                return true;
            });
    }

    private function groupClaimStatusKey(string $status): string
    {
        return match ($status) {
            'new', 'triage_pending', 'approved', 'rma_issued' => 'submitted',
            'received', 'diagnosis_pending', 'repair_pending', 'replacement_pending', 'qc_pending' => 'in_service',
            'waiting_customer', 'waiting_parts', 'waiting_payment' => 'waiting',
            'shipped_ready', 'dispatched', 'resolved' => 'ready_delivered',
            'closed', 'rejected' => 'ended',
            default => 'submitted',
        };
    }

    private function groupClaimStatus(string $status): string
    {
        return translate('warranty_claim_group_' . $this->groupClaimStatusKey($status));
    }

    private function translateWarrantyStatus(string $status): string
    {
        return match ($status) {
            'preactivated' => translate('warranty_status_preactivated'),
            'active' => translate('warranty_status_active'),
            'expired' => translate('warranty_status_expired'),
            'replaced' => translate('warranty_status_replaced'),
            'cancelled' => translate('warranty_status_cancelled'),
            default => $this->humanizeStatus($status),
        };
    }

    private function claimStatusLabel(string $status): string
    {
        return match ($status) {
            'new' => translate('warranty_claim_status_new'),
            'triage_pending' => translate('warranty_claim_status_triage_pending'),
            'approved' => translate('warranty_claim_status_approved'),
            'rma_issued' => translate('warranty_claim_status_rma_issued'),
            'received' => translate('warranty_claim_status_received'),
            'diagnosis_pending' => translate('warranty_claim_status_diagnosis_pending'),
            'repair_pending' => translate('warranty_claim_status_repair_pending'),
            'replacement_pending' => translate('warranty_claim_status_replacement_pending'),
            'qc_pending' => translate('warranty_claim_status_qc_pending'),
            'waiting_customer' => translate('warranty_claim_status_waiting_customer'),
            'waiting_parts' => translate('warranty_claim_status_waiting_parts'),
            'waiting_payment' => translate('warranty_claim_status_waiting_payment'),
            'shipped_ready' => translate('warranty_claim_status_shipped_ready'),
            'dispatched' => translate('warranty_claim_status_dispatched'),
            'resolved' => translate('warranty_claim_status_resolved'),
            'closed' => translate('warranty_claim_status_closed'),
            'rejected' => translate('warranty_claim_status_rejected'),
            default => $this->humanizeStatus($status),
        };
    }

    private function translateTimelineDescription($event): string
    {
        $description = trim((string) $event->description);

        return match ($event->event_type) {
            'claim_submitted' => $this->translateClaimSubmittedEvent($description),
            'item_received' => $this->translateItemReceivedEvent($description),
            'decision_made' => $this->translateDecisionEvent($description),
            'payment_handled' => $this->translatePaymentHandledEvent($description),
            'diagnosis_complete' => $this->translateDiagnosisEvent($description),
            'repair_complete' => $this->translateRepairCompletedEvent($description),
            'qc_passed' => translate('warranty_timeline_qc_passed'),
            'dispatched' => $this->translateDispatchedEvent($description),
            'rma_issued' => $this->translateRmaIssuedEvent($description),
            'claim_resumed' => $this->translateClaimResumedEvent($description),
            'replacement_committed' => $this->translateReplacementCommittedEvent($description),
            'closed' => $this->translateClosedEvent($description),
            'resolved' => $this->translateResolvedEvent($description),
            default => $description,
        };
    }

    private function translateClaimSubmittedEvent(string $description): string
    {
        if (preg_match('/Serial(?: Number)?:\s*(.+)$/i', $description, $matches)) {
            return translate('warranty_timeline_claim_submitted') . ' | ' .
                translate('serial_number') . ': ' . trim($matches[1]);
        }

        return translate('warranty_timeline_claim_submitted');
    }

    private function translateItemReceivedEvent(string $description): string
    {
        if (preg_match('/Item received \| Serial:\s*(.*?)\s*\| Branch:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_item_received') . ' | ' .
                translate('serial_number') . ': ' . trim($matches[1]) . ' | ' .
                translate('branch') . ': ' . trim($matches[2]) . ' | ' .
                translate('notes') . ': ' . trim($matches[3]);
        }

        return translate('warranty_timeline_item_received');
    }

    private function translateDecisionEvent(string $description): string
    {
        if (preg_match('/Decision:\s*(.*?)\s*\| Code:\s*(.*?)\s*\| Message:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_decision') . ': ' .
                $this->translateDecisionValue(trim($matches[1])) . ' | ' .
                translate('code') . ': ' . trim($matches[2]) . ' | ' .
                translate('message') . ': ' . trim($matches[3]);
        }

        return $description;
    }

    private function translatePaymentHandledEvent(string $description): string
    {
        $segments = array_values(array_filter(array_map('trim', explode('|', $description))));

        return implode(' | ', array_map(fn(string $segment) => $this->translatePaymentSegment($segment), $segments));
    }

    private function translatePaymentSegment(string $segment): string
    {
        if (str_starts_with($segment, 'Payment handling:')) {
            return translate('warranty_timeline_payment_handling') . ': ' .
                $this->translatePaymentAction(trim(substr($segment, strlen('Payment handling:'))));
        }
        if (str_starts_with($segment, 'Notes:')) {
            return translate('notes') . ': ' . trim(substr($segment, strlen('Notes:')));
        }
        if (str_starts_with($segment, 'COD payment collected:')) {
            return translate('warranty_timeline_cod_payment_collected') . ': ' .
                $this->translateChargeList(trim(substr($segment, strlen('COD payment collected:'))));
        }
        if (str_starts_with($segment, 'COD approved:')) {
            return translate('warranty_timeline_cod_approved') . ': ' .
                $this->translateChargeList(trim(substr($segment, strlen('COD approved:'))));
        }
        if (str_starts_with($segment, 'POS payment recorded:')) {
            return translate('warranty_timeline_pos_payment_recorded') . ': ' .
                $this->translateChargeList(trim(substr($segment, strlen('POS payment recorded:'))));
        }
        if (str_starts_with($segment, 'Reminder dispatched to customer')) {
            return translate('warranty_timeline_reminder_dispatched_to_customer');
        }
        if (str_starts_with($segment, 'Dispatch:')) {
            return $this->translateTimelineLabel('dispatch') . ': ' .
                $this->translatePaymentDispatchSummary(trim(substr($segment, strlen('Dispatch:'))));
        }
        if (str_starts_with($segment, 'Active link:')) {
            return $this->translateTimelineLabel('active_link') . ': ' . trim(substr($segment, strlen('Active link:')));
        }
        if (str_starts_with($segment, 'Online payment link generated:')) {
            return translate('warranty_timeline_online_payment_link_generated') . ': ' .
                trim(substr($segment, strlen('Online payment link generated:')));
        }
        if (str_starts_with($segment, 'Resumed to')) {
            return translate('warranty_timeline_resumed_to') . ' ' .
                $this->claimStatusLabel(trim(substr($segment, strlen('Resumed to'))));
        }
        if (str_starts_with($segment, 'Resumed from waiting payment')) {
            return translate('warranty_timeline_resumed_from_waiting_payment');
        }
        if (str_starts_with($segment, 'Online payment received')) {
            return translate('warranty_timeline_online_payment_received');
        }
        if (str_starts_with($segment, 'All unpaid charges waived')) {
            return translate('warranty_timeline_all_unpaid_charges_waived');
        }
        if (str_starts_with($segment, 'Charges waived without status transition')) {
            return translate('warranty_timeline_charges_waived_without_status_transition');
        }
        if (str_starts_with($segment, 'Client rejected payment')) {
            return translate('warranty_timeline_client_rejected_payment');
        }
        if (str_starts_with($segment, 'Battery returned without repair')) {
            return translate('warranty_timeline_battery_returned_without_repair');
        }
        if (str_starts_with($segment, 'Amount:')) {
            return translate('amount') . ': ' . trim(substr($segment, strlen('Amount:')));
        }
        if (str_starts_with($segment, 'Payment ID:')) {
            return translate('payment_id') . ': ' . trim(substr($segment, strlen('Payment ID:')));
        }
        if (str_starts_with($segment, 'Gateway TX:')) {
            return translate('warranty_gateway_transaction') . ': ' . trim(substr($segment, strlen('Gateway TX:')));
        }

        return $segment;
    }

    private function translateDiagnosisEvent(string $description): string
    {
        if (preg_match('/Diagnosis:\s*(.*?)\s*\| REJECTED \| Tamper:\s*(Yes|No)$/i', $description, $matches)) {
            return translate('warranty_timeline_diagnosis') . ': ' . trim($matches[1]) . ' | ' .
                translate('warranty_decision_rejected') . ' | ' .
                translate('warranty_tamper') . ': ' . $this->translateYesNo(trim($matches[2]));
        }

        if (preg_match('/Diagnosis:\s*(.*?)\s*\| Action:\s*(.*?)\s*\| Tamper:\s*(Yes|No)(?:\s*\| Charges:\s*(.*))?$/i', $description, $matches)) {
            $translated = translate('warranty_timeline_diagnosis') . ': ' . trim($matches[1]) . ' | ' .
                translate('action') . ': ' . $this->translateClaimAction(trim($matches[2])) . ' | ' .
                translate('warranty_tamper') . ': ' . $this->translateYesNo(trim($matches[3]));

            if (!empty($matches[4])) {
                $translated .= ' | ' . translate('charges') . ': ' . $this->translateChargeList(trim($matches[4]), '=');
            }

            return $translated;
        }

        return $description;
    }

    private function translateRepairCompletedEvent(string $description): string
    {
        if (preg_match('/Repair completed\. Parts:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_repair_completed') . ' | ' .
                translate('warranty_parts_used') . ': ' . trim($matches[1]) . ' | ' .
                translate('notes') . ': ' . trim($matches[2]);
        }

        return translate('warranty_timeline_repair_completed');
    }

    private function translateDispatchedEvent(string $description): string
    {
        if (preg_match('/Dispatched via\s*(.*?)(?:\s*\| Tracking:\s*(.*))?$/i', $description, $matches)) {
            $translated = translate('warranty_timeline_dispatched_via') . ' ' . trim($matches[1]);
            if (!empty($matches[2])) {
                $translated .= ' | ' . translate('tracking_number') . ': ' . trim($matches[2]);
            }
            return $translated;
        }

        return translate('warranty_timeline_dispatched');
    }

    private function translateRmaIssuedEvent(string $description): string
    {
        if (preg_match('/RMA\s*(.*?)\s*issued\s*\| Branch:\s*(.*?)\s*\| Deadline:\s*(.*?)\s*\| Instructions:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_rma_issued') . ': ' . trim($matches[1]) . ' | ' .
                translate('branch') . ': ' . trim($matches[2]) . ' | ' .
                translate('deadline') . ': ' . trim($matches[3]) . ' | ' .
                translate('instructions') . ': ' . trim($matches[4]);
        }

        return translate('warranty_timeline_rma_issued');
    }

    private function translateClaimResumedEvent(string $description): string
    {
        if (preg_match('/Resumed from\s*(.*?)\s*→\s*(.*?)\.\s*Notes:\s*(.*)$/u', $description, $matches)) {
            return translate('warranty_timeline_claim_resumed') . ' | ' .
                translate('from') . ': ' . $this->claimStatusLabel(trim($matches[1])) . ' | ' .
                translate('to') . ': ' . $this->claimStatusLabel(trim($matches[2])) . ' | ' .
                translate('notes') . ': ' . trim($matches[3]);
        }

        return translate('warranty_timeline_claim_resumed');
    }

    private function translateReplacementCommittedEvent(string $description): string
    {
        if (preg_match('/Replacement committed:\s*(.*?)\s*\| Mode:\s*(.*?)\s*\| Warranty:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_replacement_committed') . ': ' . trim($matches[1]) . ' | ' .
                translate('mode') . ': ' . trim($matches[2]) . ' | ' .
                translate('warranty') . ': ' . trim($matches[3]) . ' | ' .
                translate('notes') . ': ' . trim($matches[4]);
        }

        return translate('warranty_timeline_replacement_committed');
    }

    private function translateClosedEvent(string $description): string
    {
        if (preg_match('/Claim closed(?:\s*\((forced by admin)\))?\.\s*Notes:\s*(.*)$/i', $description, $matches)) {
            $translated = translate('warranty_timeline_claim_closed');

            if (!empty($matches[1])) {
                $translated .= ' (' . translate('warranty_timeline_forced_by_admin') . ')';
            }

            return $translated . ' | ' . translate('notes') . ': ' . trim($matches[2]);
        }

        return str_replace('Claim closed', translate('warranty_timeline_claim_closed'), $description);
    }

    private function translateResolvedEvent(string $description): string
    {
        if (preg_match('/Claim resolved on delivery\/collection\.\s*Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_claim_resolved') . ' | ' .
                translate('notes') . ': ' . trim($matches[1]);
        }

        return str_replace('Claim resolved on delivery/collection.', translate('warranty_timeline_claim_resolved'), $description);
    }

    private function translatePaymentAction(string $action): string
    {
        return match ($action) {
            'remind' => translate('warranty_payment_action_remind'),
            'pos' => translate('warranty_payment_action_pos'),
            'cod' => translate('warranty_payment_action_cod'),
            'online_link' => translate('warranty_payment_action_online_link'),
            'cod_collect' => translate('warranty_payment_action_cod_collect'),
            'waive' => translate('warranty_payment_action_waive'),
            'client_reject_payment' => translate('warranty_payment_action_client_reject'),
            default => $this->humanizeStatus($action),
        };
    }

    private function translateDecisionValue(string $decision): string
    {
        return match ($decision) {
            'approve' => translate('warranty_decision_approved'),
            'reject' => translate('warranty_decision_rejected'),
            'waiting_customer' => translate('warranty_claim_status_waiting_customer'),
            default => $this->humanizeStatus($decision),
        };
    }

    private function translateClaimAction(string $action): string
    {
        return match ($action) {
            'repair' => translate('warranty_action_repair'),
            'replace' => translate('warranty_action_replace'),
            'reject' => translate('warranty_decision_rejected'),
            default => str_contains($action, 'replace')
                ? str_replace('replace', translate('warranty_action_replace'), $action)
                : $this->humanizeStatus($action),
        };
    }

    private function translateChargeList(string $value, string $separator = ':'): string
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $value))));

        return implode(', ', array_map(function (string $item) use ($separator) {
            if (!str_contains($item, $separator)) {
                return $item;
            }

            [$chargeType, $amount] = array_map('trim', explode($separator, $item, 2));
            return $this->translateChargeType($chargeType) . ': ' . $amount;
        }, $items));
    }

    private function translateChargeType(string $chargeType): string
    {
        return match ($chargeType) {
            'repair_fee' => translate('warranty_charge_repair_fee'),
            'replacement_fee' => translate('warranty_charge_replacement_fee'),
            'inspection_fee' => translate('warranty_charge_inspection_fee'),
            default => $this->humanizeStatus($chargeType),
        };
    }

    private function translateYesNo(string $value): string
    {
        return strtolower($value) === 'yes' ? translate('yes') : translate('no');
    }

    private function translatePaymentDispatchSummary(string $summary): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $summary))));

        return implode(', ', array_map(function (string $part) {
            if (!str_contains($part, '=')) {
                return $part;
            }

            [$channel, $status] = array_map('trim', explode('=', $part, 2));
            return $this->humanizeStatus($channel) . '=' . $this->translateTimelineLabel($status);
        }, $parts));
    }

    private function translateTimelineLabel(string $key): string
    {
        $translated = translate($key);

        return $translated === $key ? $this->humanizeStatus($key) : $translated;
    }

    private function humanizeStatus(string $value): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }

    private function maskContact(string $contact): string
    {
        if ($contact === '') {
            return '****';
        }

        if (str_contains($contact, '@')) {
            return $this->maskEmail($contact);
        }

        return $this->maskPhone($contact);
    }

    private function maskEmail(string $email): string
    {
        if ($email === '' || !str_contains($email, '@')) {
            return $email;
        }

        [$localPart, $domain] = explode('@', $email, 2);
        if (strlen($localPart) <= 2) {
            return substr($localPart, 0, 1) . '***@' . $domain;
        }

        return substr($localPart, 0, 2) . '***@' . $domain;
    }

    private function maskPhone(string $phone): string
    {
        $digitsOnly = preg_replace('/\D+/', '', $phone ?? '');
        if (!$digitsOnly || strlen($digitsOnly) <= 4) {
            return '****';
        }

        return str_repeat('*', strlen($digitsOnly) - 4) . substr($digitsOnly, -4);
    }

    private function maskName(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return '';
        }

        return collect(preg_split('/\s+/', $trimmed) ?: [])
            ->map(function (string $part) {
                if (strlen($part) <= 1) {
                    return $part . '*';
                }

                return substr($part, 0, 1) . str_repeat('*', max(strlen($part) - 1, 1));
            })
            ->implode(' ');
    }

    private function dispatchLookupOtp(Warranty $warranty, string $contact, string $otp): void
    {
        $smsResponse = SMSModule::sendCentralizedSMS($contact, $otp);
        if ($smsResponse !== 'success') {
            Log::warning('Warranty API lookup OTP SMS delivery failed', [
                'serial_number' => $warranty->serial_number,
                'contact' => $contact,
                'response' => $smsResponse,
            ]);
        }

        $email = $warranty->user?->email ?: $warranty->activated_by_email;
        $mailConfig = getWebConfig(name: 'mail_config');
        $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);

        if ($mailEnabled && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            event(new DigitalProductOtpVerificationEvent(email: $email, data: [
                'userName' => $warranty->user?->f_name ?: ($warranty->activated_by_name ?? 'Customer'),
                'userType' => 'customer',
                'templateName' => 'digital-product-otp',
                'subject' => translate('verification_Code'),
                'title' => translate('verification_Code') . '!',
                'verificationCode' => $otp,
            ]));
        }
    }
}
