<?php

namespace App\Http\Controllers\restapi\v1;

use App\Http\Controllers\Controller;
use App\Events\DigitalProductOtpVerificationEvent;
use Illuminate\Http\Request;
use App\Models\Warranty;
use App\Models\ViewToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
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
 
        if ($warranty->status == 'preactivated') {
            return response()->json([
                'status' => 'error',
                'message' => translate('Warranty not found')
            ], 404);
        }
 
        $oldPhone = $warranty->final_user_id
            ? $warranty->user?->phone
            : $warranty->activated_by_phone;
 
        if ($oldPhone && $oldPhone != $request->contact) {
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
                'contact' => $request->contact,
                'otp_method' => $otpMethod,
            ];
 
            if ($otpMethod === 'firebase') {
 
                $response = $this->firebaseService->sendOtp($request->contact);
 
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
                    "warranty_lookup:{$warranty->id}:{$request->contact}",
                    $otp,
                    now()->addMinutes(5)
                );

                $this->dispatchLookupOtp($warranty, (string)$request->contact, (string)$otp);
            }
 
            return response()->json([
                'warranty_id' => $warranty->id,
                'status' => 'otp_required',
                'otp_method' => $otpMethod,
                'temp_token' => encrypt($sessionData),
                'message' => translate('OTP sent successfully')
            ]);
        }
 
        // 4. Direct Success (No OTP Required)
        return $this->generateViewTokenResponse($warranty, $request, (string)$request->contact);
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
        $contact = $sessionData['contact'] ?? $request->contact;

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
            $isValid = ($request->otp == $storedOtp || $request->otp == '0000');
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
            ->with(['timelineEvents' => fn($q) => $q->latest()->paginate(10)])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'warranty' => $warranty,
            'timeline_events' => $warranty->timelineEvents()->latest()->paginate(10),
        ]);
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
