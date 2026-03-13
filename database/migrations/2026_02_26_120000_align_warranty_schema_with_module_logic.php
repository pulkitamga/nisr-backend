<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ADDED_WARRANTY_STATUS = 'pending_review';
    private const ADDED_CLAIM_STATUSES = ['qc_pending', 'dispatched'];

    public function up(): void
    {
        $this->extendWarrantyStatusEnum();
        $this->extendClaimStatusEnum();

        $this->addWarrantyClaimCompatibilityColumns();
        $this->backfillWarrantyClaimDueColumns();

        $this->addActivationReviewCompatibilityColumns();
        $this->backfillActivationReviewSubmittedAt();
    }

    public function down(): void
    {
        // Keep compatibility columns on rollback to avoid dropping
        // columns that may have pre-existed in certain environments.
        // Down focuses on status contraction only.

        $this->mapStatusesForEnumRollback();
        $this->revertClaimStatusEnum();
        $this->revertWarrantyStatusEnum();
    }

    private function addWarrantyClaimCompatibilityColumns(): void
    {
        if (!Schema::hasTable('warranty_claims')) {
            return;
        }

        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'response_due')) {
                $table->timestamp('response_due')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'resolution_due')) {
                $table->timestamp('resolution_due')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'dispatch_due')) {
                $table->timestamp('dispatch_due')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'sla_paused_at')) {
                $table->timestamp('sla_paused_at')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'escalated_by')) {
                $table->unsignedBigInteger('escalated_by')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'escalation_level')) {
                $table->string('escalation_level', 20)->nullable()->default('none');
            }
            if (!Schema::hasColumn('warranty_claims', 'priority')) {
                $table->string('priority', 20)->nullable()->default('medium');
            }
        });
    }

    private function backfillWarrantyClaimDueColumns(): void
    {
        if (!Schema::hasTable('warranty_claims')) {
            return;
        }

        if (
            Schema::hasColumn('warranty_claims', 'response_due') &&
            Schema::hasColumn('warranty_claims', 'first_response_due')
        ) {
            DB::table('warranty_claims')
                ->whereNull('response_due')
                ->whereNotNull('first_response_due')
                ->update(['response_due' => DB::raw('first_response_due')]);
        }

        if (
            Schema::hasColumn('warranty_claims', 'resolution_due') &&
            Schema::hasColumn('warranty_claims', 'decision_due')
        ) {
            DB::table('warranty_claims')
                ->whereNull('resolution_due')
                ->whereNotNull('decision_due')
                ->update(['resolution_due' => DB::raw('decision_due')]);
        }
    }

    private function addActivationReviewCompatibilityColumns(): void
    {
        if (!Schema::hasTable('activation_reviews')) {
            return;
        }

        Schema::table('activation_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('activation_reviews', 'flagged_reason')) {
                $table->text('flagged_reason')->nullable();
            }
            if (!Schema::hasColumn('activation_reviews', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('activation_reviews', 'first_response_due')) {
                $table->timestamp('first_response_due')->nullable();
            }
            if (!Schema::hasColumn('activation_reviews', 'decision_due')) {
                $table->timestamp('decision_due')->nullable();
            }
        });
    }

    private function backfillActivationReviewSubmittedAt(): void
    {
        if (
            !Schema::hasTable('activation_reviews') ||
            !Schema::hasColumn('activation_reviews', 'submitted_at') ||
            !Schema::hasColumn('activation_reviews', 'created_at')
        ) {
            return;
        }

        DB::table('activation_reviews')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('created_at')]);
    }

    private function dropWarrantyClaimCompatibilityColumns(): void
    {
        if (!Schema::hasTable('warranty_claims')) {
            return;
        }

        $dropColumns = [];
        foreach (
            [
                'response_due',
                'resolution_due',
                'dispatch_due',
                'first_response_at',
                'sla_paused_at',
                'escalated_by',
                'escalated_at',
                'escalation_level',
                'priority',
            ] as $column
        ) {
            if (Schema::hasColumn('warranty_claims', $column)) {
                $dropColumns[] = $column;
            }
        }

        if (!empty($dropColumns)) {
            Schema::table('warranty_claims', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    private function dropActivationReviewCompatibilityColumns(): void
    {
        if (!Schema::hasTable('activation_reviews')) {
            return;
        }

        $dropColumns = [];
        foreach (
            [
                'flagged_reason',
                'submitted_at',
                'first_response_due',
                'decision_due',
            ] as $column
        ) {
            if (Schema::hasColumn('activation_reviews', $column)) {
                $dropColumns[] = $column;
            }
        }

        if (!empty($dropColumns)) {
            Schema::table('activation_reviews', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    private function mapStatusesForEnumRollback(): void
    {
        if (Schema::hasTable('warranties') && Schema::hasColumn('warranties', 'status')) {
            DB::table('warranties')
                ->where('status', 'pending_review')
                ->update(['status' => 'preactivated']);
        }

        if (Schema::hasTable('warranty_claims') && Schema::hasColumn('warranty_claims', 'status')) {
            DB::table('warranty_claims')
                ->whereIn('status', ['qc_pending', 'dispatched'])
                ->update(['status' => 'shipped_ready']);
        }
    }

    private function extendWarrantyStatusEnum(): void
    {
        if (
            !$this->supportsEnumAlter() ||
            !Schema::hasTable('warranties') ||
            !Schema::hasColumn('warranties', 'status')
        ) {
            return;
        }

        $current = $this->getCurrentEnumValues('warranties', 'status');
        if (empty($current)) {
            return;
        }

        $targetBase = ['preactivated', 'active', 'cancelled', 'replaced', 'expired'];
        $target = $this->mergeEnumValues($current, array_merge($targetBase, [self::ADDED_WARRANTY_STATUS]));

        $this->setEnumValues('warranties', 'status', $target, 'preactivated');
    }

    private function revertWarrantyStatusEnum(): void
    {
        if (
            !$this->supportsEnumAlter() ||
            !Schema::hasTable('warranties') ||
            !Schema::hasColumn('warranties', 'status')
        ) {
            return;
        }

        $current = $this->getCurrentEnumValues('warranties', 'status');
        if (empty($current)) {
            return;
        }

        $target = array_values(array_filter(
            $current,
            fn(string $value): bool => $value !== self::ADDED_WARRANTY_STATUS
        ));

        if (!in_array('preactivated', $target, true)) {
            $target[] = 'preactivated';
        }

        $this->setEnumValues('warranties', 'status', $target, 'preactivated');
    }

    private function extendClaimStatusEnum(): void
    {
        if (
            !$this->supportsEnumAlter() ||
            !Schema::hasTable('warranty_claims') ||
            !Schema::hasColumn('warranty_claims', 'status')
        ) {
            return;
        }

        $current = $this->getCurrentEnumValues('warranty_claims', 'status');
        if (empty($current)) {
            return;
        }

        $targetBase = [
            'new',
            'triage_pending',
            'approved',
            'rma_issued',
            'received',
            'diagnosis_pending',
            'repair_pending',
            'replacement_pending',
            'shipped_ready',
            'resolved',
            'closed',
            'rejected',
            'waiting_customer',
            'waiting_parts',
            'waiting_payment',
        ];

        $target = $this->mergeEnumValues($current, array_merge($targetBase, self::ADDED_CLAIM_STATUSES));
        $this->setEnumValues('warranty_claims', 'status', $target, 'new');
    }

    private function revertClaimStatusEnum(): void
    {
        if (
            !$this->supportsEnumAlter() ||
            !Schema::hasTable('warranty_claims') ||
            !Schema::hasColumn('warranty_claims', 'status')
        ) {
            return;
        }

        $current = $this->getCurrentEnumValues('warranty_claims', 'status');
        if (empty($current)) {
            return;
        }

        $target = array_values(array_filter(
            $current,
            fn(string $value): bool => !in_array($value, self::ADDED_CLAIM_STATUSES, true)
        ));

        if (!in_array('new', $target, true)) {
            $target[] = 'new';
        }

        $this->setEnumValues('warranty_claims', 'status', $target, 'new');
    }

    private function supportsEnumAlter(): bool
    {
        $driver = DB::getDriverName();
        return in_array($driver, ['mysql', 'mariadb'], true);
    }

    private function mergeEnumValues(array $current, array $required): array
    {
        $merged = $current;
        foreach ($required as $value) {
            if (!in_array($value, $merged, true)) {
                $merged[] = $value;
            }
        }
        return $merged;
    }

    private function getCurrentEnumValues(string $table, string $column): array
    {
        $dbName = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE as column_type
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$dbName, $table, $column]
        );

        if (!$row || !isset($row->column_type) || !is_string($row->column_type)) {
            return [];
        }

        preg_match_all("/'((?:\\\\'|[^'])*)'/", $row->column_type, $matches);
        if (empty($matches[1])) {
            return [];
        }

        return array_map(
            static fn(string $value): string => str_replace("\\'", "'", $value),
            $matches[1]
        );
    }

    private function setEnumValues(string $table, string $column, array $values, string $default): void
    {
        $values = array_values(array_unique($values));
        if (empty($values)) {
            return;
        }

        if (!in_array($default, $values, true)) {
            $values[] = $default;
        }

        $enumSql = implode(
            ',',
            array_map(
                static fn(string $value): string => "'" . str_replace("'", "\\'", $value) . "'",
                $values
            )
        );

        $escapedDefault = str_replace("'", "\\'", $default);
        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` ENUM({$enumSql}) NOT NULL DEFAULT '{$escapedDefault}'"
        );
    }
};
