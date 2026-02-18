<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RMAIssuedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    public function handle()
    {
        $data = [
            'userName' => $this->claim->warranty->user->name ?? 'Customer',
            'subject' => translate('RMA Issued'),
            'title' => translate('Your RMA #') . $this->claim->rma_number,
            'rmaNumber' => $this->claim->rma_number,
            'instructions' => 'Return to branch. Deadline: ' . $this->claim->rma_deadline,
            'userType' => 'customer',
            'templateName' => 'rma-issued',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->claim->warranty->user->email, $data));
        }
        // SMS: Use your SMSModule if enabled
    }
}