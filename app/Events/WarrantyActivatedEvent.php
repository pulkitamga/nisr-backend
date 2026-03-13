<?php
namespace App\Events;

use App\Models\Warranty;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarrantyActivatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Warranty $warranty) {}

    // Listener: app/Listeners/SendWarrantyEmail.php – send via your mail
    public function handle()
    {
        $data = [
            'userName' => $this->warranty->user->name ?? $this->warranty->activated_by_name,
            'subject' => translate('Warranty Activated'),
            'title' => translate('Your warranty is active'),
            'warrantySerial' => $this->warranty->serial_number,
            'endDate' => $this->warranty->end_date,
            'userType' => 'customer',
            'templateName' => 'warranty-activated',  // Create blade in emails
        ];
        $mailConfig = getWebConfig(name: 'mail_config');
        if ($mailConfig['status'] == 1) {
            // Send to warranty.user->email or activated_by_email
            event(new \App\Events\EmailVerificationEvent($this->warranty->user->email ?? $this->warranty->activated_by_email, $data));
        }
    }
}