<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class RunWarrantyEventHandler
{
    public function handle(object $event): void
    {
        if (!method_exists($event, 'handle')) {
            return;
        }

        try {
            $event->handle();
        } catch (\Throwable $exception) {
            Log::error('Warranty domain event execution failed', [
                'event' => get_class($event),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
