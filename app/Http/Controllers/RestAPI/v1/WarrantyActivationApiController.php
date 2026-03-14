<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Models\Warranty;
use App\Models\Blacklist;
use App\Models\ActivationReview;
use App\Models\Policy;
use App\Models\ViewToken;
use App\Models\WarrantyTimelineEvent;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Models\Branch;
use Illuminate\Support\Str;

class WarrantyActivationApiController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly FirebaseService $firebaseService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | STEP 1 – Validate Serial & Send OTP
    |--------------------------------------------------------------------------
    */


    public function getBranches()
    {
        $branches = Branch::where('status', 1)
            ->where('id', '!=', 1) // exclude system branch
            ->get(['id', 'branch_name']);

        return response()->json([
            'status' => true,
            'data' => $branches
        ]);
    }
    public function initiate(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Rate Limiting (Same as Web)
        |--------------------------------------------------------------------------
        */
        $key = 'warranty-initiate:' . $request->ip();
        $maxAttempts = 5;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'status' => false,
                'message' => 'Too many attempts. Please try again later.'
            ], 429);
        }

        RateLimiter::hit($key, 60);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'serial_number'    => 'required|string|max:255',
            'purchase_date'    => 'required|date',
            'consent_checked'  => 'required|accepted',
            'retailer_source'  => 'required|in:branch,distributor',
            'invoice_number'   => 'nullable|string|max:255',
            'receipt'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'required|email|max:255',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Purchase Date Validation (Same as Web)
        |--------------------------------------------------------------------------
        */
        $purchaseDate = Carbon::parse($request->purchase_date);

        if ($purchaseDate->isFuture()) {
            return response()->json([
                'status' => false,
                'message' => 'Purchase date cannot be in the future.'
            ], 422);
        }

        if ($purchaseDate->lt(now()->subYears(2))) {
            return response()->json([
                'status' => false,
                'message' => 'Purchase date cannot be older than 2 years.'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Serial Status Check (STRICT like Web)
        |--------------------------------------------------------------------------
        */
        $warranty = Warranty::where('serial_number', $request->serial_number)
            ->whereIn('status', ['preactivated', 'cancelled'])
            ->first();

        if (!$warranty) {
            return response()->json([
                'status' => false,
                'message' => 'Serial not eligible for activation.'
            ], 404);
        }

        $alreadyActive = Warranty::where('serial_number', $request->serial_number)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'status' => false,
                'message' => 'Warranty already activated.'
            ], 400);
        }
        if (Blacklist::where('serial_number', $request->serial_number)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This serial number is blacklisted.'
            ], 403);
        }
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user && Blacklist::where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This user is blacklisted.'
            ], 403);
        }

        if ($request->retailer_source === 'branch') {
            $request->validate([
                'retailer_branch_id' => 'required|exists:branches,id'
            ]);
        } else {
            $request->validate([
                'retailer_name' => 'required|string|max:255'
            ]);
        }

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('warranty-receipts', 'public');
        }

        $flagged = false;
        $flaggedReason = [];

        if ($request->retailer_source !== 'branch') {
            $flagged = true;
            $flaggedReason[] = 'Retailer not official branch';
        }

        if (
            $request->retailer_source === 'distributor' &&
            !$receiptPath
        ) {
            $flagged = true;
            $flaggedReason[] = 'Distributor purchase without receipt';
        }

        $cacheKey = "activation_data:{$request->serial_number}:{$request->email}";

        $data = $request->except('receipt');
        $data['receipt_path'] = $receiptPath;
        $data['flagged'] = $flagged;
        $data['flagged_reason'] = $flaggedReason;

        Cache::put($cacheKey, $data, now()->addMinutes(10));
        $requireOtp = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_require_otp'])['value'] ?? '0';

        if ($requireOtp != '1') {

            $isGuest = true;

            $this->commitActivation(
                $warranty,
                new Request($data),
                $isGuest,
                $flagged,
                $flaggedReason
            );

            Cache::forget($cacheKey);

            return response()->json(
                $this->buildActivationResponse(
                    $warranty->fresh(['product']),
                    (string)$request->email,
                    $flagged
                )
            );
        }


        $otp = $this->generateWarrantyOtp();

        Cache::put(
            "otp:{$request->serial_number}:{$request->email}",
            $otp,
            now()->addMinutes(5)
        );
        Cache::put("otp_method:{$request->serial_number}:{$request->email}", 'email', now()->addMinutes(10));
        Cache::put("contact_for_otp:{$request->serial_number}:{$request->email}", $request->phone, now()->addMinutes(10));

        $mailConfig = getWebConfig(name: 'mail_config');
        $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);
        if ($mailEnabled && is_string($request->email) && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            event(new DigitalProductOtpVerificationEvent(email: $request->email, data: [
                'userName' => $request->name ?? 'Customer',
                'userType' => 'customer',
                'templateName' => 'digital-product-otp',
                'subject' => translate('verification_Code'),
                'title' => translate('verification_Code') . '!',
                'verificationCode' => (string)$otp,
            ]));
        }



        return response()->json([
            'success' => true,
            'status' => 'otp_required',
            'next_step' => 'otp',
            'masked_contact' => $this->maskContact((string)$request->email),
            'warranty_public_id' => $warranty->warranty_public_id,
            'policy_version' => $this->resolvePublishedPolicyVersion(),
            'message' => 'OTP sent successfully'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STEP 2 – Verify OTP & Activate
    |--------------------------------------------------------------------------
    */
    public function verify(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'email' => 'required|email',
            'otp' => 'required|digits:4',
        ]);

        $otpMethod = Cache::get("otp_method:{$request->serial_number}:{$request->email}", 'email');
        $contact = Cache::get("contact_for_otp:{$request->serial_number}:{$request->email}");
        $isValid = false;

        if ($otpMethod === 'firebase') {

            $sessionInfo = Cache::get("otp_session:{$request->serial_number}:{$request->email}");

            $response = $this->firebaseService->verifyOtp(
                $sessionInfo,
                $contact,
                $request->otp
            );

            $isValid = ($response['status'] === 'success');
        } else {

            $storedOtp = Cache::get("otp:{$request->serial_number}:{$request->email}");

            $isValid = filled($storedOtp) && hash_equals((string)$storedOtp, (string)$request->otp);
        }

        if (!$isValid && $this->isWarrantyTestOtpAllowed()) {
            $isValid = \App\Support\OtpManager::matchesWarrantyToken((string)$request->otp);
        }

        if (!$isValid) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }

        // Remove OTP
        Cache::forget("otp:{$request->serial_number}:{$request->email}");

        // Get stored activation data
        $data = Cache::get("activation_data:{$request->serial_number}:{$request->email}");

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Activation session expired'
            ], 400);
        }

        $warranty = Warranty::where('serial_number', $request->serial_number)->firstOrFail();

        $isGuest = true; // API usually guest
        $flagged = $data['flagged'] ?? false;
        $flaggedReason = $data['flagged_reason'] ?? [];

        $sessionData = new Request($data);

        // SAME BUSINESS LOGIC
        $this->commitActivation($warranty, $sessionData, $isGuest, $flagged, $flaggedReason);

        // Clear cache
        Cache::forget("activation_data:{$request->serial_number}:{$request->email}");
        Cache::forget("otp_method:{$request->serial_number}:{$request->email}");
        Cache::forget("otp_session:{$request->serial_number}:{$request->email}");
        Cache::forget("contact_for_otp:{$request->serial_number}:{$request->email}");

        return response()->json(
            $this->buildActivationResponse(
                $warranty->fresh(['product']),
                (string)$request->email,
                (bool)$flagged
            )
        );
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'email' => 'required|email'
        ]);

        $cacheKey = "activation_data:{$request->serial_number}:{$request->email}";

        // Check if activation session exists
        if (!Cache::has($cacheKey)) {
            return response()->json([
                'status' => false,
                'message' => 'Activation session expired. Please restart process.'
            ], 400);
        }

        // Rate limit resend (very important)
        $rateKey = "resend-otp:{$request->serial_number}:{$request->email}";

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            return response()->json([
                'status' => false,
                'message' => 'Too many resend attempts. Try again later.'
            ], 429);
        }

        RateLimiter::hit($rateKey, 60); // 3 attempts per 60 sec

        $otp = $this->generateWarrantyOtp();

        Cache::put(
            "otp:{$request->serial_number}:{$request->email}",
            $otp,
            now()->addMinutes(5)
        );

        $mailConfig = getWebConfig(name: 'mail_config');
        $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);
        if ($mailEnabled && is_string($request->email) && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $sessionData = Cache::get($cacheKey, []);
            event(new DigitalProductOtpVerificationEvent(email: $request->email, data: [
                'userName' => $sessionData['name'] ?? 'Customer',
                'userType' => 'customer',
                'templateName' => 'digital-product-otp',
                'subject' => translate('verification_Code'),
                'title' => translate('verification_Code') . '!',
                'verificationCode' => (string)$otp,
            ]));
        }

        return response()->json([
            'success' => true,
            'status' => 'otp_required',
            'masked_contact' => $this->maskContact((string)$request->email),
            'message' => 'OTP resent successfully'
        ]);
    }

    private function commitActivation($warranty, $request, $isGuest, $flagged, $flaggedReason)
    {
        $defaultDuration = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_months'])['value'] ?? '12';

        $duration = $warranty->product->warranty_duration ?? $defaultDuration;

        $purchaseDate = Carbon::parse($request->purchase_date);
        $start = $purchaseDate->copy();
        $end = $purchaseDate->copy()->addMonths($duration);

        $autoApprove = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_auto_approve_off_platform'])['value'] ?? '0';

        $status = ($flagged && $autoApprove != '1') ? 'pending_review' : 'active';

        $warranty->update([
            'status' => $status,
            'activation_date' => $purchaseDate,
            'start_date' => $start,
            'end_date' => $end,
            'purchase_date' => $purchaseDate,
            'retailer_branch_id' => $request->retailer_branch_id ?? null,
            'retailer_name' => $request->retailer_name ?? null,
            'invoice_number' => $request->invoice_number,
            'activated_ip' => request()->ip(),
            'activation_method' => 'mobile_app',
            'policy_version' => $this->resolvePublishedPolicyVersion(),
            'consent_checked' => true,
            'consent_timestamp' => now(),
            'consent_ip' => request()->ip(),
            'activated_by_name' => $request->name,
            'activated_by_phone' => $request->phone ?? null,
            'activated_by_email' => $request->email,
        ]);

        // Receipt handling (API version)
        if (!empty($request->receipt_path)) {
            $warranty->update(['receipt_path' => $request->receipt_path]);
        }

        WarrantyTimelineEvent::create([
            'warranty_id' => $warranty->id,
            'event_type' => 'activated',
            'description' => 'Activated via mobile app',
            'timestamp' => now(),
            'user_id' => null,
        ]);

        if ($flagged && $autoApprove != '1') {

            $reasons = is_array($flaggedReason)
                ? array_filter($flaggedReason)
                : ($flaggedReason ? array_filter(explode(', ', $flaggedReason)) : []);
            $submittedAt = now();

            ActivationReview::create([
                'warranty_id' => $warranty->id,
                'status' => 'pending',
                'review_notes' => 'Auto-created from mobile activation; awaiting admin review.',
                'flagged_reason' => !empty($reasons)
                    ? implode(', ', $reasons)
                    : 'No reason specified',
                'submitted_at' => $submittedAt,
                'first_response_due' => $submittedAt->copy()->addHours(24),
                'decision_due' => $submittedAt->copy()->addDays(3),
            ]);
        }
    }

    private function buildActivationResponse(
        Warranty $warranty,
        string $contact,
        bool $flagged,
    ): array {
        $viewToken = $this->createViewToken($warranty, $contact);
        $status = $warranty->status;
        $message = $status === 'pending_review'
            ? 'Warranty activation submitted for review'
            : 'Warranty activated successfully';

        return [
            'success' => true,
            'status' => $status,
            'activation_status' => $status,
            'next_step' => $status === 'pending_review' ? 'pending_review' : 'success',
            'message' => $message,
            'masked_contact' => $this->maskContact($contact),
            'requires_review' => $flagged || $status === 'pending_review',
            'warranty_public_id' => $warranty->warranty_public_id,
            'view_token' => $viewToken,
            'serial_number' => $warranty->serial_number,
            'start_date' => optional($warranty->start_date)?->toIso8601String(),
            'end_date' => optional($warranty->end_date)?->toIso8601String(),
            'valid_until' => optional($warranty->end_date)?->toIso8601String(),
            'policy_version' => $warranty->policy_version,
            'available_actions' => [
                'can_claim' => $status === 'active',
                'can_pay' => false,
                'can_view_claim' => false,
            ],
        ];
    }

    private function createViewToken(Warranty $warranty, string $contact): string
    {
        if (!$warranty->warranty_public_id) {
            $warranty->warranty_public_id = (string)Str::uuid();
            $warranty->save();
        }

        $token = (string)Str::uuid();

        ViewToken::create([
            'jti' => $token,
            'warranty_public_id' => $warranty->warranty_public_id,
            'recipient_hash' => hash_hmac('sha256', $contact, config('app.key')),
            'scope' => 'warranty:view',
            'issued_at' => now(),
            'expires_at' => now()->addMinutes(getWebConfig('view_token_ttl_minutes') ?? 10),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $token;
    }

    private function resolvePublishedPolicyVersion(): ?string
    {
        return Policy::query()
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->value('version');
    }

    private function maskContact(string $contact): string
    {
        if ($contact === '') {
            return '****';
        }

        if (str_contains($contact, '@')) {
            [$localPart, $domain] = explode('@', $contact, 2);
            if (strlen($localPart) <= 2) {
                return substr($localPart, 0, 1) . '***@' . $domain;
            }

            return substr($localPart, 0, 2) . '***@' . $domain;
        }

        if (strlen($contact) <= 4) {
            return '****';
        }

        return str_repeat('*', strlen($contact) - 4) . substr($contact, -4);
    }

    private function generateWarrantyOtp(): string
    {
        return \App\Support\OtpManager::warrantyToken();
    }

    private function isWarrantyTestOtpAllowed(): bool
    {
        return \App\Support\OtpManager::testModeEnabled();
    }

    private function warrantyTestOtp(): string
    {
        return \App\Support\OtpManager::warrantyToken();
    }
}
