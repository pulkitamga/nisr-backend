<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\ViewToken;
use App\Events\DigitalProductOtpVerificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Session;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Utils\SMSModule;

class WarrantyViewController extends Controller
{
    public function __construct(
        private readonly FirebaseService $firebaseService,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    public function lookupStart()
    {
        return view(VIEW_FILE_NAMES['warranty_lookup']);
    }
    public function warrantyTrack()
    {
        return view(VIEW_FILE_NAMES['warranty_track_page']);
    }

    public function lookupSubmit(Request $request)
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

        $request->validate([
            'serial_number' => 'required|string|exists:warranties,serial_number',
            'contact' => 'required|string',
        ]);

        $warranty = Warranty::where('serial_number', $request->serial_number)->firstOrFail();

     

        if ($warranty->status == 'preactivated') {
            Toastr::error(translate('Warranty not found'));
            return back()->withInput();
        }

        $oldPhone = $warranty->final_user_id ? $warranty->user?->phone : $warranty->activated_by_phone;

        if ($oldPhone && $oldPhone != $request->contact) {
            Toastr::error(translate('This phone number does not match the previously registered number for this serial.'));
            Log::warning('Warranty Phone mismatch', [
                'serial' => $warranty->serial_number ?? null,
                'old_phone' => $oldPhone,
                'incoming_phone' => $request->contact,
            ]);
            return back()->withInput();
        }

        // Check if OTP is required
        $otpRequire = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_require_otp'])['value'] ?? '0';

        if ($otpRequire == '1') {
            // OTP REQUIRED — proceed with OTP flow
            $firebaseOtpSetting = getWebConfig('firebase_otp_verification');
            $otpMethod = ($firebaseOtpSetting && $firebaseOtpSetting['status'] == 1) ? 'firebase' : 'email';

            if ($otpMethod === 'firebase') {
                $response = $this->firebaseService->sendOtp($request->contact);

                if ($response && isset($response['status']) && $response['status'] === 'success' && !empty($response['sessionInfo'])) {
                    Session::put('otp_session', $response['sessionInfo']);
                } else {
                    Toastr::error(translate('Failed to send OTP. Please try again.'));
                    return back()->withInput();
                }
            } else {
                $otp = rand(1000, 9999);
                Cache::put("warranty_lookup:{$warranty->id}:{$request->contact}", $otp, now()->addMinutes(5));
                $this->dispatchLookupOtp($warranty, (string)$request->contact, (string)$otp);
            }

            Session::put([
                'warranty_id' => $warranty->id,
                'contact' => $request->contact,
                'otp_method' => $otpMethod,
            ]);

            Toastr::success(translate('OTP sent successfully'));
            return redirect()->route('warranty.lookup.verify.form');
        }

        // OTP NOT REQUIRED — skip OTP and go straight to view
        return $this->generateViewTokenAndRedirect($warranty, $request);
    }

    public function lookupVerifyForm(Request $request)
    {
        $warranty_id = session('warranty_id');
        $contact = session('contact');

        if (!$warranty_id || !$contact) {
            return redirect()->route('warranty.lookup.start')->withErrors(['session' => 'Session expired. Try again.']);
        }

        return view(VIEW_FILE_NAMES['warranty_lookup_verify'], compact('warranty_id', 'contact'));
    }

    public function lookupVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
            'warranty_id' => 'required|exists:warranties,id',
            'contact' => 'required|string',
        ]);

        $otpMethod = Session::get('otp_method', 'email');
        $isValid = false;

        if ($otpMethod === 'firebase') {
            $sessionInfo = Session::get('otp_session');
            $response = $this->firebaseService->verifyOtp($sessionInfo, $request->contact, $request->otp);
            $isValid = ($response['status'] === 'success');
        } else {
            $storedOtp = Cache::get("warranty_lookup:{$request->warranty_id}:{$request->contact}");
            $isValid = filled($storedOtp) && hash_equals((string)$storedOtp, (string)$request->otp);
        }

        if (!$isValid) {
            return back()->withErrors(['otp' => translate('Invalid OTP')]);
        }

        // Clear OTP cache/session
        if ($otpMethod !== 'firebase') {
            Cache::forget("warranty_lookup:{$request->warranty_id}:{$request->contact}");
        }
        Session::forget(['otp_session', 'otp_method']);

        $warranty = Warranty::findOrFail($request->warranty_id);

        return $this->generateViewTokenAndRedirect($warranty, $request);
    }

    /**
     * Generate view token and redirect to warranty view page
     */
    private function generateViewTokenAndRedirect($warranty, $request)
    {
        if (!$warranty->warranty_public_id) {
            $warranty->warranty_public_id = Str::uuid();
            $warranty->save();
        }

        $jti = Str::uuid();
        ViewToken::create([
            'jti' => $jti,
            'warranty_public_id' => $warranty->warranty_public_id,
            'recipient_hash' => hash_hmac('sha256', $request->contact, config('app.key')),
            'scope' => 'warranty:view',
            'issued_at' => now(),
            'expires_at' => now()->addMinutes(getWebConfig('view_token_ttl_minutes') ?? 10),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Clean up session
        Session::forget(['warranty_id', 'contact', 'otp_method', 'otp_session']);

        return redirect()->to(
            route('warranty.view', ['warranty_public_id' => $warranty->warranty_public_id]) . '?vt=' . $jti
        );
    }

    public function view(Request $request, $warranty_public_id)
    {
        $token = ViewToken::where('warranty_public_id', $warranty_public_id)
            ->where('jti', $request->query('vt'))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        $ownerId = auth()->id();
        if (auth('customer')->check()) {
            $ownerId = auth('customer')->id();
        }

        $isOwner = !empty($ownerId) && Warranty::where('warranty_public_id', $warranty_public_id)
            ->where('final_user_id', $ownerId)
            ->exists();

        if (!$token && !$isOwner) {
            abort(403, translate('invalid/expired token'));
        }

        if ($token) {
            $token->update(['used_at' => now()]);
        }

        $warranty = Warranty::where('warranty_public_id', $warranty_public_id)
            ->with([
                'timelineEvents' => fn($q) => $q->latest()->paginate(10),
                'claims' => fn($q) => $q->latest('submitted_at'),
            ])
            ->firstOrFail();

        $timelineEvents = $warranty->timelineEvents()
            ->latest()
            ->paginate(10);
        $latestClaim = $warranty->claims->first();
        $openClaim = $warranty->claims->first(fn($claim) => !in_array($claim->status, ['closed', 'rejected'], true));

        if (!$isOwner) {
            $warranty->activated_by_name = '****';
            $warranty->activated_by_email = '****';
            $warranty->activated_by_phone = '****';
        }

        return view(VIEW_FILE_NAMES['warranty_view'], compact('warranty', 'isOwner', 'timelineEvents', 'latestClaim', 'openClaim'));
    }

    public function share(Request $request, Warranty $warranty)
    {
        $this->authorize('view', $warranty);

        $jti = Str::uuid();
        $publicId = $warranty->warranty_public_id ?? Str::uuid();
        $warranty->warranty_public_id = $publicId;
        $warranty->save();

        $token = ViewToken::create([
            'jti' => $jti,
            'warranty_public_id' => $publicId,
            'recipient_hash' => null,
            'scope' => 'warranty:view',
            'issued_at' => now(),
            'expires_at' => now()->addMinutes(getWebConfig('view_token_ttl_minutes') ?? 10),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $url = route('warranty.view', ['warranty_public_id' => $publicId]) . '?vt=' . $jti;
        Toastr::success(translate('Shareable link generated: ') . $url);
        return redirect($url);
    }

    private function dispatchLookupOtp(Warranty $warranty, string $contact, string $otp): void
    {
        $smsResponse = SMSModule::sendCentralizedSMS($contact, $otp);
        if ($smsResponse !== 'success') {
            Log::warning('Warranty lookup OTP SMS delivery failed', [
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
