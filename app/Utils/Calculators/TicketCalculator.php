<?php

namespace App\Utils\Calculators;

use App\Models\SupportTicket;
use Carbon\Carbon;

class TicketCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery()
    {
        $query = SupportTicket::query();
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function supportTickets()
    {
        return $this->getQuery()->where('type', 'support')->count();
    }

    public function complaintTickets()
    {
        return $this->getQuery()->where('type', 'complaint')->count();
    }

    public function serviceTickets()
    {
        return $this->getQuery()->where('type', 'service')->count();
    }

    public function careerTickets()
    {
        return $this->getQuery()->where('type', 'career')->count();
    }

    public function retailTickets()
    {
        return $this->getQuery()->where('type', 'retail')->count();
    }

    public function wholesaleTickets()
    {
        return $this->getQuery()->where('type', 'wholesale')->count();
    }
}