<?php
namespace App\Events;

use App\Models\WarrantyClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoStockEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    public function handle()
    {
        $data = [
            'userName' => 'Logistics',
            'subject' => translate('No Stock for Replacement'),
            'title' => translate('Stock needed for claim #') . $this->claim->claim_number,
            'claimNumber' => $this->claim->claim_number,
            'productId' => $this->claim->warranty->product_id,
            'userType' => 'admin',
            'templateName' => 'no-stock',
        ];
        $mailConfig = getWebConfig('mail_config');
        if ($mailConfig['status'] == 1) {
            $agentEmail = getWebConfig('warranty_agent_email')['value'] ?? 'agent@yourapp.com';
            event(new \App\Events\EmailVerificationEvent($agentEmail, $data));
        }
    }
}