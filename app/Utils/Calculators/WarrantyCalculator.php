<?php

namespace App\Utils\Calculators;

use App\Models\SupportTicket;
use Carbon\Carbon;

class WarrantyCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery()
    {
        $query = SupportTicket::query()->where('type', 'warranty');
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function warrantyClaims()
    {
        return $this->getQuery()->count();
    }

    public function claimsApproved()
    {
        return $this->getQuery()->where('status', 'approved')->count();
    }

    public function claimsPending()
    {
        return $this->getQuery()->where('status', 'pending')->count();
    }

    public function activeWarranty()
    {
        return $this->getQuery()->where('status', 'active')->count();
    }
}