<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }
    /**
     * Send OTP via Firebase
     */
    public function sendOtp($phoneNumber): array
    {
        $fcmCredentials = getWebConfig('fcm_credentials') ?? [];
        $apiKey = $fcmCredentials['apiKey'] ?? '';

        Log::info('Firebase OTP: Attempting to send OTP', [
            'phone' => $phoneNumber,
            'api_key_present' => !empty($apiKey),
            'fcm_data' => $fcmCredentials,
        ]);

        try {
            $response = Http::post(
                'https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key=' . $apiKey,
                [
                    'phoneNumber' => $phoneNumber,
                    'recaptchaToken' => request('g-recaptcha-response') ?? session('g-recaptcha-response'),
                ]
            );

            $responseBody = $response->json();
            Log::info('Firebase OTP: Response received', [
                'status' => $response->status(),
                'body' => $responseBody,
            ]);

            return [
                'result' => $responseBody,
                'sessionInfo' => trim($responseBody['sessionInfo'] ?? ''),
                'status' => $response->successful() ? 'success' : 'error',
                'message' => $responseBody['message'] ?? 'Something went wrong',
                'errors' => $responseBody['error']['message'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Firebase OTP: Exception while sending OTP', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => [],
                'sessionInfo' => '',
                'status' => 'error',
                'message' => 'Firebase sendOtp exception: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify OTP via Firebase
     */
    public function verifyOtp($sessionInfo, $phoneNumber, $otp): array
    {
        $fcmCredentials = getWebConfig('fcm_credentials') ?? [];
        $apiKey = $fcmCredentials['apiKey'] ?? '';

        Log::info('Firebase OTP: Attempting to verify OTP', [
            'phone' => $phoneNumber,
            'sessionInfo' => $sessionInfo ? substr($sessionInfo, 0, 15) . '...' : null,
            'otp' => $otp,
        ]);

        try {
            $response = Http::post(
                'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key=' . $apiKey,
                [
                    'sessionInfo' => $sessionInfo,
                    'code' => $otp,
                    'phoneNumber' => $phoneNumber,
                ]
            );

            $responseBody = $response->json();

            Log::info('Firebase OTP: Verify response received', [
                'status' => $response->status(),
                'body' => $responseBody,
            ]);

            return [
                'result' => $responseBody,
                'sessionInfo' => trim($responseBody['sessionInfo'] ?? ''),
                'status' => $response->successful() ? 'success' : 'error',
                'message' => $responseBody['message'] ?? 'Something went wrong',
                'errors' => $responseBody['error']['message'] ?? 'No specific error message',
            ];
        } catch (\Throwable $e) {
            Log::error('Firebase OTP: Exception while verifying OTP', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => [],
                'sessionInfo' => '',
                'status' => 'error',
                'message' => 'Firebase verifyOtp exception: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ];
        }
    }
}
