<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimSubmittedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    // Listener: Send mail via EmailVerificationEvent
    public function handle()
    {
        $data = [
            'userName' => $this->claim->warranty->user->name ?? 'Customer',
            'subject' => translate('Claim Submitted'),
            'title' => translate('Your claim #') . $this->claim->claim_number,
            'claimNumber' => $this->claim->claim_number,
            'userType' => 'customer',
            'templateName' => 'claim-submitted',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->claim->warranty->user->email, $data));
        }
    }
}