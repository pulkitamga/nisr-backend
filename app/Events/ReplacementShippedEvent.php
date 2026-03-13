<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use App\Models\Warranty;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplacementShippedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim, public Warranty $newWarranty) {}

    public function handle()
    {
        $data = [
            'userName' => $this->claim->warranty->user->name ?? 'Customer',
            'subject' => translate('Replacement Shipped'),
            'title' => translate('Your replacement serial') . $this->newWarranty->serial_number,
            'newSerial' => $this->newWarranty->serial_number,
            'claimNumber' => $this->claim->claim_number,
            'userType' => 'customer',
            'templateName' => 'replacement-shipped',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->claim->warranty->user->email, $data));
        }
    }
}