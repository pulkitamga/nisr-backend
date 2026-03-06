<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Models\ActivationReview;
use App\Models\Blacklist;
use App\Models\OrderDetail;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\Warranty;
use App\Models\WarrantyTimelineEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;
use Brian2694\Toastr\Facades\Toastr;

class WarrantyActivationController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly FirebaseService $firebaseService,
    ) {}

    /* --------------------------------------------------------------
       FORM DISPLAY
    -------------------------------------------------------------- */
    public function index(Request $request)
    {
        $enablePublicForm = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_enable_public_form']);
        if (!$enablePublicForm || $enablePublicForm['value'] != '1') {
            abort(404, 'Public form is disabled');
        }

        $branches = Branch::where('status', 1)->get(['id', 'branch_name']);

        $isLoggedIn = Auth::check();
        $userData = $isLoggedIn ? [
            'name' => Auth::user()->f_name . ' ' . Auth::user()->l_name,
            'phone' => Auth::user()->phone,
            'email' => Auth::user()->email,
        ] : null;

        return view(VIEW_FILE_NAMES['warranty_form'], compact('branches', 'isLoggedIn', 'userData'));
    }

    /* --------------------------------------------------------------
       STORE / VALIDATE
    -------------------------------------------------------------- */
    public function store(Request $request)
    {
        $recaptcha = getWebConfig(name: 'recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            try {
                $request->validate([
                    'g-recaptcha-response' => [
                        function ($attribute, $value, $fail) {
                            $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                            $response = $value;
                            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
                            $response = \file_get_contents($url);
                            $response = json_decode($response);
                            if (!$response->success) {
                                $fail(translate('ReCAPTCHA Failed'));
                            }
                        },
                    ],
                ]);
            } catch (\Exception $exception) {
                return back()->withErrors(translate('Captcha Failed'))->withInput($request->input());
            }
        } else {
            if (strtolower($request->default_captcha_value) != strtolower(Session('default_captcha_code'))) {
                Session::forget('default_captcha_code');
                Toastr::error(translate('captcha_failed'));
                return back()->withInput($request->input());
            }
        }

        $ip = $request->ip();
        $key = "warranty-activate:{$ip}";
        $window = (int)($this->businessSettingRepo->getFirstWhere(['type' => 'warranty_rate_limit_window'])['value'] ?? 2);
        $max = (int)($this->businessSettingRepo->getFirstWhere(['type' => 'warranty_rate_limit_max_attempts'])['value'] ?? 5);
        $decay = $window * 60;

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return back()->withErrors(['rate_limit' => translate('Too many activation attempts. Please try again later.')]);
        }
        RateLimiter::hit($key, $decay);

        /* ---- Validation ---- */
        $rules = [
            'serial_number' => 'required|string|max:255',
            'purchase_date' => 'required|date|before_or_equal:' . now()->format('Y-m-d'),
            'consent_checked' => 'required|accepted',
            'retailer_source' => 'required|in:branch,distributor',
            'invoice_number' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ];

        if ($request->retailer_source === 'branch') {
            $rules['retailer_branch_id'] = 'required|exists:branches,id';
        } else {
            $rules['retailer_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $purchaseDate = Carbon::parse($request->purchase_date);
        if ($purchaseDate->lt(now()->subYears(2))) {
            return back()->withErrors(['purchase_date' => translate('Purchase date cannot be older than 2 years.')]);
        }
        if ($purchaseDate->gt(now())) {
            return back()->withErrors(['purchase_date' => translate('Purchase date cannot be in the future.')]);
        }

        /* ---- Serial Number Checks ---- */
        $warranty = Warranty::where('serial_number', $request->serial_number)
            ->whereIn('status', ['preactivated', 'cancelled'])
            ->first();

        if (!$warranty) {
            Toastr::error(translate('Invalid serial number or status not eligible for activation.'));
            return back()->withInput();
        }

        if (Warranty::where('serial_number', $request->serial_number)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->exists()
        ) {
            Toastr::error(translate('An active warranty already exists for this serial.'));
            return back()->withInput();
        }

        if (Blacklist::where('serial_number', $request->serial_number)->exists()) {
            Toastr::error(translate('This serial number is blacklisted and cannot be activated.'));
            return back()->withInput();
        }

        $flagged = false;
        $flaggedReason = [];

        if ($request->retailer_source !== 'branch') {
            $flagged = true;
            $flaggedReason[] = 'Distributor (unknown retailer)';
        }

        if ($request->retailer_source !== 'branch' && !$request->hasFile('receipt')) {
            $flagged = true;
            $flaggedReason[] = 'Missing proof of purchase';
        }

        $requireOtp = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_require_otp'])['value'] ?? '0';

        if ($requireOtp == '1') {
            $contact = $request->phone ?? null;
            $email = $request->email ?? null;
            $otpMethod = 'email';
            $otpSession = null;

            if ($otpMethod === 'email') {
                $otp = $this->generateWarrantyOtp();
                Cache::put("otp:{$email}", $otp, now()->addMinutes(5));
                $mailConfig = getWebConfig(name: 'mail_config');
                $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);
                if ($mailEnabled && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    event(new DigitalProductOtpVerificationEvent(email: $email, data: [
                        'userName' => $request->name ?? 'Customer',
                        'userType' => 'customer',
                        'templateName' => 'digital-product-otp',
                        'subject' => translate('verification_Code'),
                        'title' => translate('verification_Code') . '!',
                        'verificationCode' => (string)$otp,
                    ]));
                }
            }

            Session::put([
                'pending_warranty_id' => $warranty->id,
                'purchase_date' => $request->purchase_date,
                'retailer_source' => $request->retailer_source,
                'retailer_branch_id' => $request->retailer_branch_id ?? null,
                'retailer_name' => $request->retailer_name ?? null,
                'invoice_number' => $request->invoice_number,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'flagged' => $flagged,
                'flagged_reason' => $flaggedReason,
                'contact_for_otp' => $contact,
                'otp_method' => $otpMethod,
                'otp_session' => $otpSession,
                'activation_ip' => $request->ip(),
            ]);

            if ($request->hasFile('receipt')) {
                $path = $request->file('receipt')->store('warranty/receipts', 'public');
                Session::put('receipt_path', $path);
            }

            return redirect()->route('warranty.verify-otp');
        }

        $this->commitActivation($warranty, $request, !$request->user(), $flagged, $flaggedReason);
        RateLimiter::clear($key);

        return redirect()->route('warranty.success', ['serial' => $request->serial_number]);
    }

    public function showOtpVerify(Request $request)
    {
        if (!Session::has('pending_warranty_id')) {
            return redirect()->route('warranty.activate');
        }
        $contact = Session::get('contact_for_otp');
        return view(VIEW_FILE_NAMES['warranty_otp'], compact('contact'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:4']);

        $otpMethod = Session::get('otp_method', 'email');
        $contact = Session::get('contact_for_otp');
        $email = Session::get('email');
        $isValid = false;

        if ($otpMethod === 'firebase') {
            $sessionInfo = Session::get('otp_session');
            $response = $this->firebaseService->verifyOtp($sessionInfo, $contact, $request->otp);
            $isValid = ($response['status'] === 'success');
        } else {
            $storedOtp = Cache::get("otp:{$email}");
            $isValid = filled($storedOtp) && hash_equals((string)$storedOtp, (string)$request->otp);
        }

        if (!$isValid && $this->isWarrantyTestOtpAllowed()) {
            $isValid = hash_equals($this->warrantyTestOtp(), (string)$request->otp);
        }

        if (!$isValid) {
            return back()->withErrors(['otp' => translate('Invalid OTP. Please try again.')]);
        }

        if ($otpMethod !== 'firebase') {
            Cache::forget("otp:{$email}");
        }

        $warrantyId = Session::get('pending_warranty_id');
        $warranty = Warranty::findOrFail($warrantyId);
        $isGuest = Session::get('name') !== null;
        $flagged = Session::get('flagged', false);
        $flaggedReason = Session::get('flagged_reason', []);
        if (is_string($flaggedReason)) {
            $flaggedReason = $flaggedReason ? array_filter(explode(', ', $flaggedReason)) : [];
        } elseif (!is_array($flaggedReason)) {
            $flaggedReason = [];
        }
        $sessionData = new Request([
            'purchase_date' => Session::get('purchase_date'),
            'retailer_source' => Session::get('retailer_source'),
            'retailer_branch_id' => Session::get('retailer_branch_id'),
            'retailer_name' => Session::get('retailer_name'),
            'invoice_number' => Session::get('invoice_number'),
            'receipt_path' => Session::get('receipt_path'),
            'name' => Session::get('name'),
            'phone' => Session::get('phone'),
            'email' => Session::get('email'),
            'activation_ip' => Session::get('activation_ip'),
        ]);

        $this->commitActivation($warranty, $sessionData, $isGuest, $flagged, $flaggedReason);

        Session::forget([
            'pending_warranty_id',
            'purchase_date',
            'retailer_source',
            'retailer_branch_id',
            'retailer_name',
            'invoice_number',
            'name',
            'phone',
            'email',
            'flagged',
            'flagged_reason',
            'contact_for_otp',
            'receipt_path',
            'otp_method',
            'otp_session',
            'activation_ip',
        ]);

        return redirect()->route('warranty.success', ['serial' => $warranty->serial_number]);
    }

    /* --------------------------------------------------------------
       COMMIT ACTIVATION
    -------------------------------------------------------------- */
    private function commitActivation($warranty, $request, $isGuest, $flagged, $flaggedReason)
    {
        $defaultDuration = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_months'])['value'] ?? '12';
        $duration = $warranty->product->warranty_duration ?? $defaultDuration;
        $start = now();
        $end = $start->copy()->addMonths($duration);
        $autoApprove = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_auto_approve_off_platform'])['value'] ?? '0';

        $status = ($flagged && $autoApprove != '1') ? 'pending_review' : 'active';

        $activationIp = $request->input('activation_ip') ?: request()->ip();

        $warranty->update([
            'status' => $status,
            'activation_date' => now(),
            'start_date' => $start,
            'end_date' => $end,
            'purchase_date' => $request->purchase_date,
            'retailer_branch_id' => $request->retailer_branch_id ?? null,
            'retailer_name' => $request->retailer_name ?? null,
            'invoice_number' => $request->invoice_number,
            'activated_ip' => $activationIp,
            'activation_method' => 'user_public_form',
            'policy_version' => Policy::published()->orderByDesc('published_at')->first()?->version,
            'consent_checked' => true,
            'consent_timestamp' => now(),
            'consent_ip' => $activationIp,
            'activated_by_name' => $request->name,
            'activated_by_phone' => $request->phone ?? null,
            'activated_by_email' => $request->email,
        ]);

        if ($request->hasFile('receipt') || Session::has('receipt_path')) {
            $path = $request->hasFile('receipt')
                ? $request->file('receipt')->store('warranty/receipts', 'public')
                : Session::get('receipt_path');
            $warranty->update(['receipt_path' => $path]);
        }

        WarrantyTimelineEvent::create([
            'warranty_id' => $warranty->id,
            'event_type' => 'activated',
            'description' => 'Activated via public form' . ($isGuest ? ' (guest)' : ''),
            'timestamp' => now(),
            'user_id' => auth('admin')->check() ? auth('admin')->id() : null,
        ]);

        if ($flagged && $autoApprove != '1') {
            $reasons = is_array($flaggedReason)
                ? array_filter($flaggedReason) // remove empty values
                : ($flaggedReason ? array_filter(explode(', ', $flaggedReason)) : []);
            $submittedAt = now();
            ActivationReview::create([
                'warranty_id' => $warranty->id,
                'status' => 'pending',
                'review_notes' => 'Auto-created from public activation; awaiting admin review.',
                'flagged_reason' => !empty($reasons) ? implode(', ', $reasons) : 'No reason specified',
                'submitted_at' => $submittedAt,
                'first_response_due' => $submittedAt->copy()->addHours(24),
                'decision_due' => $submittedAt->copy()->addDays(3),
            ]);
        }
    }

    public function resendOtp(Request $request)
    {
        if (!Session::has('pending_warranty_id')) {
            return redirect()->route('warranty.activate');
        }

        $contact = Session::get('contact_for_otp');
        $email = Session::get('email');
        $otpMethod = Session::get('otp_method', 'email');

        try {
            if ($otpMethod === 'firebase') {
                $response = $this->firebaseService->sendOtp($contact);
                if ($response['status'] === 'success' && !empty($response['sessionInfo'])) {
                    Session::put('otp_session', $response['sessionInfo']);
                    return back()->with('success', translate('OTP resent successfully!'));
                } else {
                    return back()->withErrors(['otp' => translate('Failed to resend OTP. Please try again.')]);
                }
            } else {
                $otp = $this->generateWarrantyOtp();
                Cache::put("otp:{$email}", $otp, now()->addMinutes(5));
                $mailConfig = getWebConfig(name: 'mail_config');
                $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);
                if ($mailEnabled && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    event(new DigitalProductOtpVerificationEvent(email: $email, data: [
                        'userName' => Session::get('name', 'Customer'),
                        'userType' => 'customer',
                        'templateName' => 'digital-product-otp',
                        'subject' => translate('verification_Code'),
                        'title' => translate('verification_Code') . '!',
                        'verificationCode' => (string)$otp,
                    ]));
                }
                return back()->with('success', translate('OTP resent successfully to your email.'));
            }
        } catch (\Exception $e) {
            Log::error('OTP resend failed: ' . $e->getMessage());
            return back()->withErrors(['otp' => translate('Something went wrong while resending OTP.')]);
        }
    }

    public function success($serial)
    {
        $warranty = Warranty::where('serial_number', $serial)->firstOrFail();
        return view(VIEW_FILE_NAMES['warranty_success'], compact('warranty'));
    }

    private function generateWarrantyOtp(): string
    {
        return $this->isWarrantyTestOtpAllowed()
            ? $this->warrantyTestOtp()
            : (string) rand(1000, 9999);
    }

    private function isWarrantyTestOtpAllowed(): bool
    {
        return env('APP_MODE') !== 'live';
    }

    private function warrantyTestOtp(): string
    {
        return '0000';
    }

    public function activateFromOrder(Request $request)
    {
        if (!is_array($request->input('serial_no'))) {
            $request->merge([
                'serial_no' => [$request->input('serial_no')]
            ]);
        }

        $request->validate([
            'order_detail_id' => 'required|exists:order_details,id',
            'serial_no' => 'required|array',
            'serial_no.*' => 'nullable|string|max:255',
            'agree_terms' => 'required|accepted',
        ]);

        $detail = OrderDetail::with(['product', 'order'])->find($request->order_detail_id);
        if (!$detail || !$detail->order || (int)$detail->order->customer_id !== (int)auth('customer')->id()) {
            Toastr::error(translate('invalid_order'));
            return back();
        }

        $activatedCount = Warranty::where('invoice_number', $detail->order_id)
            ->where('product_id', $detail->product_id)
            ->where('final_user_id', auth('customer')->id())
            ->where('activation_method', 'order_activation')
            ->whereNotNull('activation_date')
            ->count();

        $remainingQty = max(0, (int)$detail->qty - $activatedCount);
        if ($remainingQty <= 0) {
            Toastr::warning(translate('all_warranty_units_for_this_item_are_already_activated'));
            return back();
        }

        $serialNumbers = collect($request->input('serial_no', []))
            ->map(fn($serial) => trim((string)$serial))
            ->filter(fn($serial) => $serial !== '')
            ->unique()
            ->values();

        if ($serialNumbers->isEmpty()) {
            Toastr::error(translate('please_enter_at_least_one_serial_number'));
            return back()->withInput();
        }

        if ($serialNumbers->count() > $remainingQty) {
            Toastr::error(translate('you_can_activate_up_to') . ' ' . $remainingQty . ' ' . translate('serial_numbers_for_this_item'));
            return back()->withInput();
        }

        $defaultDuration = (int)($this->businessSettingRepo->getFirstWhere(['type' => 'warranty_months'])['value'] ?? 12);
        $start = $detail->updated_at ?? now();
        $end = Carbon::parse($start)->copy()->addMonths($defaultDuration);

        $activatedSerials = [];
        $failedSerials = [];

        foreach ($serialNumbers as $serialNumber) {
            $warranty = Warranty::where('serial_number', $serialNumber)
                ->whereIn('status', ['preactivated', 'cancelled'])
                ->first();

            if (!$warranty) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            if ((int)$warranty->product_id !== (int)$detail->product_id) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            if (Warranty::where('serial_number', $serialNumber)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->exists()
            ) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            if (Blacklist::where('serial_number', $serialNumber)->exists()) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            $warranty->update([
                'status' => 'active',
                'activation_date' => now(),
                'start_date' => $start,
                'end_date' => $end,
                'purchase_date' => $detail->updated_at,
                'invoice_number' => $detail->order_id,
                'final_user_id' => auth('customer')->id(),
                'activation_method' => 'order_activation',
                'consent_checked' => true,
                'consent_timestamp' => now(),
                'consent_ip' => $request->ip(),
                'policy_version' => Policy::published()->orderByDesc('published_at')->first()?->version,
            ]);

            WarrantyTimelineEvent::create([
                'warranty_id' => $warranty->id,
                'event_type' => 'activated',
                'description' => 'Activated via order details',
                'timestamp' => now(),
                'user_id' => auth('admin')->check() ? auth('admin')->id() : null,
            ]);

            $activatedSerials[] = $serialNumber;
        }

        if (!empty($activatedSerials)) {
            $detail->warranty_status = ($activatedCount + count($activatedSerials)) >= (int)$detail->qty ? 1 : 0;
            $detail->save();
        }

        if (!empty($activatedSerials) && !empty($failedSerials)) {
            $failedPreview = implode(', ', array_slice($failedSerials, 0, 5));
            if (count($failedSerials) > 5) {
                $failedPreview .= '...';
            }
            Toastr::warning(
                translate('warranty_activated_for') . ' ' . count($activatedSerials) . ' ' . translate('serial_numbers') .
                '. ' . translate('some_serial_numbers_failed') . ': ' . $failedPreview
            );
            return back();
        }

        if (!empty($activatedSerials)) {
            Toastr::success(translate('warranty_activated_for') . ' ' . count($activatedSerials) . ' ' . translate('serial_numbers'));
            return back();
        }

        Toastr::error(translate('no_serial_number_could_be_activated'));
        return back();
    }
}
