<?php

namespace App\Utils\Calculators;

use App\Models\Deal;
use Carbon\Carbon;

class DealCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery()
    {
        $query = Deal::query();
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function openRetailDeals()
    {
        return $this->getQuery()->where('related_party_type', 'contact')->where('status', 'open')->count();
    }

    public function wonRetailDeals()
    {
        return $this->getQuery()->where('related_party_type', 'contact')->where('status', 'won')->count();
    }

    public function lostRetailDeals()
    {
        return $this->getQuery()->where('related_party_type', 'contact')->where('status', 'lost')->count();
    }

    public function openWholesaleDeals()
    {
        return $this->getQuery()->where('related_party_type', 'company')->where('status', 'open')->count();
    }

    public function wonWholesaleDeals()
    {
        return $this->getQuery()->where('related_party_type', 'company')->where('status', 'won')->count();
    }

    public function lostWholesaleDeals()
    {
        return $this->getQuery()->where('related_party_type', 'company')->where('status', 'lost')->count();
    }
}