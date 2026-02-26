<?php

namespace App\Utils\Calculators;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Carbon\Carbon;

class WarrantyCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getClaimQuery()
    {
        $query = WarrantyClaim::query();
        if ($this->type === 'today') {
            $query->whereDate('submitted_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('submitted_at', Carbon::now()->month)
                ->whereYear('submitted_at', Carbon::now()->year);
        }
        return $query;
    }

    private function getWarrantyQuery()
    {
        $query = Warranty::query()->where('status', 'active')->where('end_date', '>', now());
        if ($this->type === 'today') {
            $query->whereDate('activation_date', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('activation_date', Carbon::now()->month)
                ->whereYear('activation_date', Carbon::now()->year);
        }
        return $query;
    }

    public function warrantyClaims()
    {
        return $this->getClaimQuery()->count();
    }

    public function claimsApproved()
    {
        return $this->getClaimQuery()->where('status', 'approved')->count();
    }

    public function claimsPending()
    {
        return $this->getClaimQuery()
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->count();
    }

    public function activeWarranty()
    {
        return $this->getWarrantyQuery()->count();
    }
}
