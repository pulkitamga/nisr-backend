<?php
namespace App\Notifications;

use App\Models\WarrantyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RMAIssued extends Notification
{
    use Queueable;

    public function __construct(public WarrantyClaim $claim, public string $instructions) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(translate('RMA Issued'))
            ->line(translate('Your RMA') . $this->claim->rma_number)
            ->line($this->instructions)
            ->action(translate('View Claim'), route('claim.view', $this->claim->id));
    }
}