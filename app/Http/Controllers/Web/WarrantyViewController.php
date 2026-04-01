<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warranty\LookupSubmitRequest;
use App\Http\Requests\Warranty\LookupVerifyRequest;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimPayment;
use App\Models\ViewToken;
use App\Events\DigitalProductOtpVerificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Session;
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

    public function lookupStart()
    {
        return view(VIEW_FILE_NAMES['warranty_lookup']);
    }
    public function warrantyTrack()
    {
        return view(VIEW_FILE_NAMES['warranty_track_page']);
    }

    public function lookupSubmit(LookupSubmitRequest $request)
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

        $warranty = Warranty::where('serial_number', $request->serial_number)->firstOrFail();
        $normalizedContact = WarrantyLookupContactNormalizer::normalize((string)$request->contact);


        if ($warranty->status == 'preactivated') {
            Toastr::error(translate('Warranty not found'));
            return back()->withInput();
        }

        $oldPhone = $warranty->final_user_id ? $warranty->user?->phone : $warranty->activated_by_phone;
        $normalizedOldPhone = WarrantyLookupContactNormalizer::normalize($oldPhone);

        if ($normalizedOldPhone && $normalizedContact && $normalizedOldPhone !== $normalizedContact) {
            Toastr::error(translate('This phone number does not match the previously registered number for this serial.'));
            Log::warning('Warranty Phone mismatch', [
                'serial' => $warranty->serial_number ?? null,
                'old_phone' => $oldPhone,
                'incoming_phone' => $request->contact,
                'normalized_old_phone' => $normalizedOldPhone,
                'normalized_incoming_phone' => $normalizedContact,
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
                $response = $this->firebaseService->sendOtp($normalizedContact ?? (string)$request->contact);

                if ($response && isset($response['status']) && $response['status'] === 'success' && !empty($response['sessionInfo'])) {
                    Session::put('otp_session', $response['sessionInfo']);
                } else {
                    Toastr::error(translate('Failed to send OTP. Please try again.'));
                    return back()->withInput();
                }
            } else {
                $otp = OtpManager::warrantyToken();
                Cache::put("warranty_lookup:{$warranty->id}:{$normalizedContact}", $otp, now()->addMinutes(5));
                $this->dispatchLookupOtp($warranty, (string)$normalizedContact, (string)$otp);
            }

            Session::put([
                'warranty_id' => $warranty->id,
                'contact' => $normalizedContact ?? (string)$request->contact,
                'otp_method' => $otpMethod,
            ]);

            Toastr::success(translate('OTP sent successfully'));
            return redirect()->route('warranty.lookup.verify.form');
        }

        // OTP NOT REQUIRED — skip OTP and go straight to view
        return $this->generateViewTokenAndRedirect($warranty, $request, $normalizedContact ?? (string)$request->contact);
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

    public function lookupVerify(LookupVerifyRequest $request)
    {
        $normalizedContact = WarrantyLookupContactNormalizer::normalize((string)$request->contact);

        $otpMethod = Session::get('otp_method', 'email');
        $isValid = false;

        if ($otpMethod === 'firebase') {
            $sessionInfo = Session::get('otp_session');
            $response = $this->firebaseService->verifyOtp($sessionInfo, $normalizedContact ?? (string)$request->contact, $request->otp);
            $isValid = ($response['status'] === 'success');
        } else {
            $storedOtp = Cache::get("warranty_lookup:{$request->warranty_id}:{$normalizedContact}");
            $isValid = OtpManager::matchesWarrantyToken((string)$request->otp, filled($storedOtp) ? (string)$storedOtp : null);
        }

        if (!$isValid) {
            return back()->withErrors(['otp' => translate('Invalid OTP')]);
        }

        // Clear OTP cache/session
        if ($otpMethod !== 'firebase') {
            Cache::forget("warranty_lookup:{$request->warranty_id}:{$normalizedContact}");
        }
        Session::forget(['otp_session', 'otp_method']);

        $warranty = Warranty::findOrFail($request->warranty_id);

        return $this->generateViewTokenAndRedirect($warranty, $request, $normalizedContact ?? (string)$request->contact);
    }

    /**
     * Generate view token and redirect to warranty view page
     */
    private function generateViewTokenAndRedirect($warranty, $request, ?string $contact = null)
    {
        if (!$warranty->warranty_public_id) {
            $warranty->warranty_public_id = Str::uuid();
            $warranty->save();
        }

        $normalizedContact = $contact ?? WarrantyLookupContactNormalizer::normalize((string)$request->contact) ?? (string)$request->contact;
        $jti = Str::uuid();
        ViewToken::create([
            'jti' => $jti,
            'warranty_public_id' => $warranty->warranty_public_id,
            'recipient_hash' => hash_hmac('sha256', $normalizedContact, config('app.key')),
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
        $isOwner = $this->authorizeWarrantyAccess($request, (string) $warranty_public_id);

        $warranty = Warranty::where('warranty_public_id', $warranty_public_id)
            ->with([
                'user:id,f_name,l_name,email,phone',
                'timelineEvents' => fn($q) => $q->latest()->paginate(10),
                'claims' => fn($q) => $q->latest('submitted_at'),
            ])
            ->firstOrFail();

        $timelineEvents = $warranty->timelineEvents()
            ->latest()
            ->paginate(10);
        $latestClaim = $warranty->claims->first();
        $openClaim = $warranty->claims->first(fn($claim) => !in_array($claim->status, ['closed', 'rejected'], true));
        $warranty->product_name = $warranty->product_id
            ? DB::table('products')->where('id', $warranty->product_id)->value('name')
            : null;

        $customerName = trim(implode(' ', array_filter([
            $warranty->user?->f_name,
            $warranty->user?->l_name,
        ])));

        if ($isOwner) {
            $warranty->activated_by_name = $customerName !== '' ? $customerName : $warranty->activated_by_name;
            $warranty->activated_by_email = $warranty->user?->email ?? $warranty->activated_by_email;
            $warranty->activated_by_phone = $warranty->user?->phone ?? $warranty->activated_by_phone;
        } else {
            $warranty->activated_by_name = '****';
            $warranty->activated_by_email = '****';
            $warranty->activated_by_phone = '****';
        }

        return view(VIEW_FILE_NAMES['warranty_view'], compact('warranty', 'isOwner', 'timelineEvents', 'latestClaim', 'openClaim'));
    }

    public function claimView(Request $request, string $warranty_public_id, string $claim_number)
    {
        $isOwner = $this->authorizeWarrantyAccess($request, $warranty_public_id);

        $claim = WarrantyClaim::query()
            ->with([
                'attachments',
                'payments',
                'timelineEvents' => fn($query) => $query->latest('timestamp'),
                'warranty.product:id,name,code',
                'warranty.user:id,f_name,l_name,email,phone',
            ])
            ->where('claim_number', $claim_number)
            ->whereHas('warranty', fn($query) => $query->where('warranty_public_id', $warranty_public_id))
            ->firstOrFail();

        $payment = $this->resolveActiveClaimPayment($claim);
        $parsedDescription = $this->parseClaimDescription((string) $claim->description);
        $claimViewData = [
            'claim_number' => $claim->claim_number,
            'status' => $this->claimStatusLabel($claim->status),
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'customer_meaning' => $this->claimStatusMeaning($claim->status),
            'subject' => $parsedDescription['subject'],
            'details' => $parsedDescription['details'],
            'issue' => $parsedDescription['issue'],
            'submitted_at' => $claim->submitted_at,
            'updated_at' => $claim->updated_at,
            'serial_number' => $claim->serial_number,
            'attachments' => collect($claim->attachments_full_url ?? [])
                ->values()
                ->map(fn($url, $index) => ['id' => $index + 1, 'url' => $url]),
            'timeline_events' => $claim->timelineEvents,
            'payment' => $payment,
            'can_pay' => $isOwner && auth('customer')->check() && $payment !== null && !empty($payment->payment_link),
        ];

        $warranty = $claim->warranty;
        if (!$isOwner) {
            $customerName = trim((string) (($warranty->user?->f_name ?? '') . ' ' . ($warranty->user?->l_name ?? '')));
            $email = (string) ($warranty->user?->email ?? $warranty->activated_by_email ?? '');
            $phone = (string) ($warranty->user?->phone ?? $warranty->activated_by_phone ?? '');
            $warranty->activated_by_name = $this->maskName($customerName !== '' ? $customerName : (string) $warranty->activated_by_name);
            $warranty->activated_by_email = $this->maskEmail($email);
            $warranty->activated_by_phone = $this->maskPhone($phone);
        }

        return view('web-views.pages.warranty-claim-view', compact('claim', 'claimViewData', 'warranty', 'isOwner'));
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

    private function authorizeWarrantyAccess(Request $request, string $warrantyPublicId): bool
    {
        $ownerId = auth()->id();
        if (auth('customer')->check()) {
            $ownerId = auth('customer')->id();
        }

        $isOwner = !empty($ownerId) && Warranty::where('warranty_public_id', $warrantyPublicId)
            ->where('final_user_id', $ownerId)
            ->exists();

        if ($isOwner) {
            return true;
        }

        if ($this->hasWarrantySessionAccess($request, $warrantyPublicId)) {
            return false;
        }

        $token = $this->findValidViewToken($warrantyPublicId, (string) $request->query('vt'));
        if (!$token) {
            abort(403, translate('invalid/expired token'));
        }

        $this->grantWarrantySessionAccess($request, $warrantyPublicId, $token->expires_at);
        $token->update(['used_at' => now()]);

        return false;
    }

    private function findValidViewToken(string $warrantyPublicId, string $jti): ?ViewToken
    {
        if ($jti === '') {
            return null;
        }

        return ViewToken::where('warranty_public_id', $warrantyPublicId)
            ->where('jti', $jti)
            ->where('scope', 'warranty:view')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    private function grantWarrantySessionAccess(Request $request, string $warrantyPublicId, $expiresAt): void
    {
        $accessList = $request->session()->get('warranty_view_access', []);
        $accessList[$warrantyPublicId] = optional($expiresAt)->toIso8601String();
        $request->session()->put('warranty_view_access', $accessList);
    }

    private function hasWarrantySessionAccess(Request $request, string $warrantyPublicId): bool
    {
        $accessList = collect($request->session()->get('warranty_view_access', []))
            ->filter(fn($expiresAt) => filled($expiresAt) && now()->lt($expiresAt))
            ->all();

        $request->session()->put('warranty_view_access', $accessList);

        return array_key_exists($warrantyPublicId, $accessList);
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

    private function parseClaimDescription(string $description): array
    {
        $parsed = ['subject' => '', 'details' => '', 'issue' => ''];
        foreach (preg_split("/(\r\n|\n|\r)/", $description) ?: [] as $line) {
            if (str_starts_with($line, 'Subject: ')) {
                $parsed['subject'] = trim(substr($line, 9));
            } elseif (str_starts_with($line, 'Details: ')) {
                $parsed['details'] = trim(substr($line, 9));
            } elseif (str_starts_with($line, 'Issue: ')) {
                $parsed['issue'] = trim(substr($line, 7));
            }
        }

        if ($parsed['details'] === '' && $description !== '') {
            $parsed['details'] = $description;
        }

        return $parsed;
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
            default => str($status)->replace('_', ' ')->title()->value(),
        };
    }

    private function claimStatusMeaning(string $status): string
    {
        return match ($status) {
            'new' => translate('warranty_claim_meaning_new'),
            'triage_pending' => translate('warranty_claim_meaning_triage_pending'),
            'approved' => translate('warranty_claim_meaning_approved'),
            'rma_issued' => translate('warranty_claim_meaning_rma_issued'),
            'received' => translate('warranty_claim_meaning_received'),
            'diagnosis_pending' => translate('warranty_claim_meaning_diagnosis_pending'),
            'repair_pending' => translate('warranty_claim_meaning_repair_pending'),
            'replacement_pending' => translate('warranty_claim_meaning_replacement_pending'),
            'qc_pending' => translate('warranty_claim_meaning_qc_pending'),
            'waiting_customer' => translate('warranty_claim_meaning_waiting_customer'),
            'waiting_parts' => translate('warranty_claim_meaning_waiting_parts'),
            'waiting_payment' => translate('warranty_claim_meaning_waiting_payment'),
            'shipped_ready' => translate('warranty_claim_meaning_shipped_ready'),
            'dispatched' => translate('warranty_claim_meaning_dispatched'),
            'resolved' => translate('warranty_claim_meaning_resolved'),
            'closed' => translate('warranty_claim_meaning_closed'),
            'rejected' => translate('warranty_claim_meaning_rejected'),
            default => translate('warranty_claim_meaning_updated'),
        };
    }

    private function maskName(string $value): string
    {
        if ($value === '') {
            return '****';
        }

        return mb_substr($value, 0, 1) . str_repeat('*', max(3, mb_strlen($value) - 1));
    }

    private function maskEmail(string $value): string
    {
        if ($value === '' || !str_contains($value, '@')) {
            return '****';
        }

        [$localPart, $domain] = explode('@', $value, 2);
        $visible = mb_substr($localPart, 0, 1);

        return $visible . str_repeat('*', max(3, mb_strlen($localPart) - 1)) . '@' . $domain;
    }

    private function maskPhone(string $value): string
    {
        if ($value === '') {
            return '****';
        }

        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', max(3, $length - 4)) . substr($value, -2);
    }
}
