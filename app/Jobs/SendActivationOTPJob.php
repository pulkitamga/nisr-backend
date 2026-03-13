<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendActivationOTPJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public string $otp, public string $contact, public int $warrantyId) {}

    public function handle()
    {
        $data = [
            'userName' => 'Customer',
            'subject' => translate('Warranty OTP'),
            'title' => translate('Your OTP is') . $this->otp,
            'verificationCode' => $this->otp,
            'userType' => 'customer',
            'templateName' => 'otp-verification',  // Your template
        ];
        // Use your mail config
        $mailConfig = getWebConfig(name: 'mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->contact, $data));  // Reuse your event
        }
        // SMS similar if enabled
        session(["otp_for_{$this->warrantyId}" => $this->otp]);
    }
}