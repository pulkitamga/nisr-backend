<?php
namespace App\Events;

use App\Models\Warranty;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivationReviewNeededEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Warranty $warranty) {}

    public function handle()
    {
        $data = [
            'userName' => 'Agent',  // Or fetch agent name
            'subject' => translate('Activation Review Needed'),
            'title' => translate('Review warranty for serial') . $this->warranty->serial_number,
            'warrantySerial' => $this->warranty->serial_number,
            'reviewNotes' => 'Flagged for off-platform activation.',
            'userType' => 'admin',  // Internal
            'templateName' => 'activation-review',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            $agentEmail = getWebConfig('warranty_agent_email')['value'] ?? 'agent@yourapp.com';
            event(new \App\Events\EmailVerificationEvent($agentEmail, $data));
        }
        // SMS if enabled: Use your SMSModule
    }
}