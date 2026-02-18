<?php
namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WarrantyClaim;
use Illuminate\Support\Facades\Storage;

class RepairService
{
    public static function startRepair(WorkOrder $workOrder)
    {
        $workOrder->claim->update(['status' => 'repair_pending']);
        // Reserve parts...
    }

    public static function completeRepair(WorkOrder $workOrder, bool $qcPassed)
    {
        $workOrder->update([
            'status' => $qcPassed ? 'completed' : 'qc_failed',
            'labor_hours' => $workOrder->labor_hours ?? 0,
            'parts_used' => $workOrder->parts_used ?? [],
        ]);

        $claim = $workOrder->claim;

        if ($qcPassed) {
            $claim->update(['status' => 'shipped_ready']);
            $slaDispatch = getWebConfig(name: 'warranty_sla_dispatch')['value'] ?? 2;
            $claim->update(['dispatch_due' => now()->addDays($slaDispatch)]);
            event(new \App\Events\RepairCompletedEvent($claim));
            \App\Jobs\DispatchReturnJob::dispatch($claim);
        } else {
            $claim->update(['status' => 'diagnosis_pending']);
            event(new \App\Events\RepairFailedEvent($claim));
        }
    }
}