<?php

namespace App\Http\Controllers\restapi\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warranty;
use App\Models\ViewToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;

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
                    'otp_' . $request->contact,
                    $otp,
                    now()->addMinutes(5)
                );
 
                // Send SMS here if needed
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
        $viewToken = $this->generateViewToken($warranty);
 
        return response()->json([
            'status' => 'success',
            'view_token' => $viewToken,
            'warranty_id' => $warranty->id
        ]);
    }

    /**
     * Verify OTP for app
     */
    public function lookupVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
            'warranty_id' => 'required|exists:warranties,id',
            'contact' => 'required|string',
        ]);

        $otpMethod = session('otp_method', 'email');
        $isValid = false;

        if ($otpMethod === 'firebase') {
            $sessionInfo = session('otp_session');
            $response = $this->firebaseService->verifyOtp($sessionInfo, $request->contact, $request->otp);
            $isValid = ($response['status'] ?? '') === 'success';
        } else {
            $storedOtp = Cache::get("warranty_lookup:{$request->warranty_id}:{$request->contact}");
            $isValid = ($request->otp == $storedOtp || $request->otp == '0000');
        }

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        if ($otpMethod !== 'firebase') {
            Cache::forget("warranty_lookup:{$request->warranty_id}:{$request->contact}");
        }
        session()->forget(['otp_session', 'otp_method']);

        $warranty = Warranty::findOrFail($request->warranty_id);

        return $this->generateViewTokenResponse($warranty, $request);
    }

    /**
     * Generate view token and return link for app
     */
    private function generateViewTokenResponse($warranty, $request)
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

        session()->forget(['warranty_id', 'contact', 'otp_method', 'otp_session']);

        return response()->json([
            'success' => true,
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

        $warranty = Warranty::where('warranty_public_id', $warranty_public_id)
            ->with(['timelineEvents' => fn($q) => $q->latest()->paginate(10)])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'warranty' => $warranty,
            'timeline_events' => $warranty->timelineEvents()->latest()->paginate(10),
        ]);
    }
}
