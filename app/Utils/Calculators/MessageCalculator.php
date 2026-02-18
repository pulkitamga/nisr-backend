<?php

namespace App\Utils\Calculators;

use App\Models\InboxMessage;
use Carbon\Carbon;

class MessageCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery()
    {
        $query = InboxMessage::query();
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function inbound()
    {
        return $this->getQuery()->count();
    }

    public function newMessages()
    {
        return $this->getQuery()->where('status', 'new')->count();
    }

    public function convertedMessages()
    {
        return $this->getQuery()->where('status', 'converted')->count();
    }

    public function ignoredMessages()
    {
        return $this->getQuery()->where('status', 'ignored')->count();
    }
}