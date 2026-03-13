<?php

namespace App\Events;

use App\Models\WalletTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WholesalerEmailEvent
{
     use Dispatchable;

    public $email;
    public $data;

    public function __construct($email, $data)
    {
        $this->email = $email;
        $this->data = $data;
    }
}
