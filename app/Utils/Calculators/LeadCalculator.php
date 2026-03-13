<?php

namespace App\Utils\Calculators;

use App\Models\Lead;
use Carbon\Carbon;

class LeadCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery()
    {
        $query = Lead::query();
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function totalLeads()
    {
        return $this->getQuery()->count();
    }

    public function workingLeads()
    {
        return $this->getQuery()->where('status', '!=', 'new')->where('status', '!=', 'disqualified')->count();
    }

    public function qualifiedLeads()
    {
        return $this->getQuery()->where('status', 'qualified')->count();
    }

    public function convertedLeads()
    {
        return $this->getQuery()->where('status', 'converted')->count();
    }
}