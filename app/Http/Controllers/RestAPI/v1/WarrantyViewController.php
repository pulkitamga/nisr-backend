<?php

namespace App\Http\Controllers\restapi\v1;

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

                $otp = rand(1000, 9999);

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
            $isValid = filled($storedOtp) && hash_equals((string)$storedOtp, (string)$request->otp);
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

        return [
            'warranty_public_id' => $warranty->warranty_public_id,
            'serial_number' => $warranty->serial_number,
            'status' => $warranty->statusLabel(),
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

        return [
            'claim_number' => $claim->claim_number,
            'status' => $claim->status,
            'grouped_status' => $this->groupClaimStatus($claim->status),
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
                'description' => $event->description,
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

    private function groupClaimStatus(string $status): string
    {
        return match ($status) {
            'new', 'triage_pending', 'approved', 'rma_issued' => 'Submitted',
            'received', 'diagnosis_pending', 'repair_pending', 'replacement_pending', 'qc_pending' => 'In Service',
            'waiting_customer', 'waiting_parts', 'waiting_payment' => 'Waiting',
            'shipped_ready', 'dispatched', 'resolved' => 'Ready/Delivered',
            'closed', 'rejected' => 'Ended',
            default => 'Submitted',
        };
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
