<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispatchReadyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim, public string $tracking) {}

    public function handle()
    {
        $data = [
            'userName' => $this->claim->warranty->user->name ?? 'Customer',
            'subject' => translate('Item Ready for Pickup/Ship'),
            'title' => translate('Track your return') . $this->tracking,
            'tracking' => $this->tracking,
            'claimNumber' => $this->claim->claim_number,
            'userType' => 'customer',
            'templateName' => 'dispatch-ready',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->claim->warranty->user->email, $data));
        }
        // SMS with tracking if enabled
    }
}