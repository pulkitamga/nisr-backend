<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'warranty_claims';
    private const CLAIM_NUMBER_UNIQUE = 'warranty_claims_claim_number_unique_v2';
    private const RMA_NUMBER_UNIQUE = 'warranty_claims_rma_number_unique';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        $this->addUniqueIfMissing('claim_number', self::CLAIM_NUMBER_UNIQUE);
        $this->addUniqueIfMissing('rma_number', self::RMA_NUMBER_UNIQUE);
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table): void {
            if ($this->hasIndexNamed(self::CLAIM_NUMBER_UNIQUE)) {
                $table->dropUnique(self::CLAIM_NUMBER_UNIQUE);
            }

            if ($this->hasIndexNamed(self::RMA_NUMBER_UNIQUE)) {
                $table->dropUnique(self::RMA_NUMBER_UNIQUE);
            }
        });
    }

    private function addUniqueIfMissing(string $column, string $indexName): void
    {
        if (!Schema::hasColumn(self::TABLE, $column) || $this->hasUniqueIndexOnColumn($column)) {
            return;
        }

        $duplicateCount = DB::table(self::TABLE)
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateCount > 0) {
            throw new RuntimeException("Cannot add unique index {$indexName}; duplicate {$column} values already exist.");
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($column, $indexName): void {
            $table->unique($column, $indexName);
        });
    }

    private function hasUniqueIndexOnColumn(string $column): bool
    {
        foreach (Schema::getIndexes(self::TABLE) as $index) {
            $columns = array_values($index['columns'] ?? []);
            if (($index['unique'] ?? false) === true && $columns === [$column]) {
                return true;
            }
        }

        return false;
    }

    private function hasIndexNamed(string $name): bool
    {
        foreach (Schema::getIndexes(self::TABLE) as $index) {
            if (($index['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
};
