<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RepairCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    public function handle()
    {
        $data = [
            'userName' => $this->claim->warranty->user->name ?? 'Customer',
            'subject' => translate('Repair Completed'),
            'title' => translate('Your repair is ready'),
            'claimNumber' => $this->claim->claim_number,
            'userType' => 'customer',
            'templateName' => 'repair-completed',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            event(new \App\Events\EmailVerificationEvent($this->claim->warranty->user->email, $data));
        }
    }
}