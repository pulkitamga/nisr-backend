<?php

namespace App\Utils\Calculators;

use App\Models\ServiceJob;
use App\Models\ServiceInvoice;
use Carbon\Carbon;

class ServiceChartCalculator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    private function getQuery($model)
    {
        $query = $model::query();
        if ($this->type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->type === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }
        return $query;
    }

    public function totalServices()
    {
        return $this->getQuery(ServiceJob::class)->where('status', 'completed')->count();
    }

    public function totalInvoice()
    {
        return $this->getQuery(ServiceInvoice::class)->sum('total');
    }
}