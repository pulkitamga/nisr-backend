<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Models\Warranty;
use App\Models\Blacklist;
use App\Models\ActivationReview;
use App\Models\Policy;
use App\Models\WarrantyTimelineEvent;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Models\Branch;

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

            return response()->json([
                'status' => true,
                'message' => 'Warranty activated successfully'
            ]);
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
            'status' => true,
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

        return response()->json([
            'status' => true,
            'message' => 'Warranty activated successfully'
        ]);
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
            'status' => true,
            'message' => 'OTP resent successfully'
        ]);
    }

    private function commitActivation($warranty, $request, $isGuest, $flagged, $flaggedReason)
    {
        $defaultDuration = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_months'])['value'] ?? '12';

        $duration = $warranty->product->warranty_duration ?? $defaultDuration;

        $start = now();
        $end = $start->copy()->addMonths($duration);

        $autoApprove = $this->businessSettingRepo
            ->getFirstWhere(['type' => 'warranty_auto_approve_off_platform'])['value'] ?? '0';

        $status = ($flagged && $autoApprove != '1') ? 'pending_review' : 'active';

        $warranty->update([
            'status' => $status,
            'activation_date' => now(),
            'start_date' => $start,
            'end_date' => $end,
            'purchase_date' => $request->purchase_date,
            'retailer_branch_id' => $request->retailer_branch_id ?? null,
            'retailer_name' => $request->retailer_name ?? null,
            'invoice_number' => $request->invoice_number,
            'activated_ip' => request()->ip(),
            'activation_method' => 'mobile_app',
            'policy_version' => Policy::published()
                ->orderByDesc('published_at')
                ->first()?->version ?? null,
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
