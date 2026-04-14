<?php

use App\Support\ComplaintTicketWorkflow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'support_ticket_status_master';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        $statusDefinitions = [
            ['id' => ComplaintTicketWorkflow::STATUS_NEW, 'name' => 'New', 'position' => 1],
            ['id' => ComplaintTicketWorkflow::STATUS_OPEN, 'name' => 'Open', 'position' => 2],
            ['id' => ComplaintTicketWorkflow::STATUS_ASSIGNED, 'name' => 'Assigned', 'position' => 3],
            ['id' => ComplaintTicketWorkflow::STATUS_IN_PROGRESS, 'name' => 'In Progress', 'position' => 4],
            ['id' => ComplaintTicketWorkflow::STATUS_WAITING, 'name' => 'Waiting', 'position' => 5],
            ['id' => ComplaintTicketWorkflow::STATUS_RESOLVED, 'name' => 'Resolved', 'position' => 6],
            ['id' => ComplaintTicketWorkflow::STATUS_CLOSED, 'name' => 'Closed', 'position' => 7],
        ];

        $hasMasterId = Schema::hasColumn(self::TABLE, 'master_id');
        $hasStatus = Schema::hasColumn(self::TABLE, 'status');
        $hasIsActive = Schema::hasColumn(self::TABLE, 'is_active');
        $hasPosition = Schema::hasColumn(self::TABLE, 'position');
        $hasType = Schema::hasColumn(self::TABLE, 'type');
        $hasCreatedAt = Schema::hasColumn(self::TABLE, 'created_at');
        $hasUpdatedAt = Schema::hasColumn(self::TABLE, 'updated_at');
        $now = now();

        foreach ($statusDefinitions as $definition) {
            if (DB::table(self::TABLE)->where('id', $definition['id'])->exists()) {
                continue;
            }

            $payload = [
                'id' => $definition['id'],
                'name' => $definition['name'],
            ];

            if ($hasMasterId) {
                $payload['master_id'] = ComplaintTicketWorkflow::STATUS_MASTER_ID;
            }

            if ($hasStatus) {
                $payload['status'] = 'active';
            }

            if ($hasIsActive) {
                $payload['is_active'] = 1;
            }

            if ($hasPosition) {
                $payload['position'] = $definition['position'];
            }

            if ($hasType) {
                $payload['type'] = 'complaint';
            }

            if ($hasCreatedAt) {
                $payload['created_at'] = $now;
            }

            if ($hasUpdatedAt) {
                $payload['updated_at'] = $now;
            }

            DB::table(self::TABLE)->insert($payload);
        }
    }

    public function down(): void
    {
        // Historical data repair. Do not remove complaint status masters on rollback.
    }
};
