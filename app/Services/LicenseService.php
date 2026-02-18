<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    public function validate()
    {
        Log::info('🔐 License check started');

        // Allow artisan commands
        if (app()->runningInConsole()) {
            Log::info('⚙ Running in console – license skipped');
            return true;
        }

        Log::info('License mode', [
            'mode' => config('license.mode'),
            'key'  => config('license.key'),
            'env'  => app()->environment(),
        ]);

        // Allow in development mode
        if (config('license.mode') === 'development') {
            Log::info('🟢 Development mode – license bypassed');
            return true;
        }

        $key = config('license.key');

        if (!$key || $key === 'DEV-UNLICENSED') {
            Log::warning('❌ License key missing');
            $this->violation('License key missing');
        }

        if (!$this->isValidKey($key)) {
            Log::warning('❌ Invalid license key', ['key' => $key]);
            $this->violation('Invalid license key');
        }

        if (!$this->isValidDomain()) {
            Log::warning('❌ Invalid domain', ['domain' => request()->getHost()]);
            $this->violation('Invalid domain');
        }

        Log::info('✅ License validated successfully');
        return true;
    }

    protected function isValidKey($key)
    {
        if (!is_string($key)) {
            return false;
        }

        $key = trim($key);

        // Minimum length check
        if (strlen($key) < 10) {
            return false;
        }

        // Optional format check
        if (!str_starts_with($key, 'LIC-')) {
            return false;
        }

        return true;
    }


    protected function isValidDomain()
    {
        $current = request()->getHost();
        $allowed = config('license.domain');

        Log::info('Checking domain', [
            'current' => $current,
            'allowed' => $allowed
        ]);

        if (!$allowed) {
            return false;
        }

        // Normalize domains
        $current = preg_replace('/^www\./', '', strtolower($current));
        $allowed = preg_replace('/^www\./', '', strtolower($allowed));

        return $current === $allowed;
    }


    protected function violation($reason)
    {
        Log::error('🚨 LICENSE VIOLATION', ['reason' => $reason]);
        $this->notify($reason);
        abort(403, 'Application license is invalid.');
    }

    protected function notify($reason)
    {
        try {
            Mail::raw(
                "LICENSE VIOLATION\n\nReason: {$reason}\nDomain: " . request()->getHost(),
                function ($msg) {
                    $msg->to('admin@gmail.com')
                        ->subject('License Alert');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Mail failed', ['error' => $e->getMessage()]);
        }
    }
}
