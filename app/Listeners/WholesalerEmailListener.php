<?php

namespace App\Listeners;


use App\Events\WholesalerEmailEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Mail;
class WholesalerEmailListener
{
    use EmailTemplateTrait;

    public function handle(WholesalerEmailEvent $event): void
    {
        try {
            $this->sendingMail(
                sendMailTo: $event->email,
                userType: 'wholesaler', 
                templateName: $event->data['templateName'],
                data: $event->data
            );
        } catch (\Exception $e) {
            info("Wholesaler Email Error: " . $e->getMessage());
        }
    }
}
