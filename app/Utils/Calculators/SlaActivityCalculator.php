<?php

namespace App\Utils\Calculators;

use App\Models\SupportTicket;
use App\Models\InboxCall;
use App\Models\SupportTicketStatusMaster;
use Carbon\Carbon;

class SlaActivityCalculator
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

    public function overdueSLAs()
    {
        $openStatusIds = SupportTicketStatusMaster::query()
            ->whereIn('name', ['open', 'in_progress'])
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        return $this->getQuery(SupportTicket::class)
            ->whereRaw('NOW() > DATE_ADD(created_at, INTERVAL sla_hours HOUR)')
            ->where(function ($query) use ($openStatusIds) {
                $query->whereIn('status', ['open', 'in_progress']);
                if (!empty($openStatusIds)) {
                    $query->orWhereIn('status', $openStatusIds);
                }
            })
            ->count();
    }

    public function pendingActivities()
    {
        return $this->getQuery(\App\Models\InboxTask::class)
            ->where('status', 'pending')
            ->count();
    }

    public function voipCallsToday()
    {
        return InboxCall::whereDate('created_at', Carbon::today())->count();
    }
}
