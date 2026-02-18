<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimForReviewEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim, public string $agentEmail) {}

    public function handle()
    {
        $data = [
            'userName' => 'Agent',
            'subject' => translate('Claim Review Required'),
            'title' => translate('Review claim #') . $this->claim->claim_number,
            'claimNumber' => $this->claim->claim_number,
            'description' => $this->claim->description,
            'userType' => 'admin',
            'templateName' => 'claim-review',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->agentEmail, $data));
        }
    }
}