<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SlaBreachNotification extends Notification {
    use Queueable;

    protected $breach;
    protected $entity;

    public function __construct($breach, $entity) {
        $this->breach = $breach;
        $this->entity = $entity;
    }

    public function via($notifiable) {
        return ['mail'];
    }

    public function toMail($notifiable) {
        $entityType = str_replace('_', ' ', ucwords($this->breach->entity_type, '_'));
        return (new MailMessage)
            ->subject("SLA Breach Alert - {$entityType}")
            ->line("Breach Type: {$this->breach->breach_type} at Level {$this->breach->escalation_level}")
            ->line("Entity: {$entityType} #{$this->entity->id}")
            ->action('View Details', url("/admin/{$this->breach->entity_type}s/{$this->entity->id}"));
    }
}