<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warranties')) {
            return;
        }

        $this->ensureDistributorLookupIndex();
        $this->ensureRetailerBranchForeignKey();
    }

    public function down(): void
    {
        // Catch-up migration: do not remove live warranty foreign keys or indexes on rollback.
    }

    private function ensureDistributorLookupIndex(): void
    {
        if (
            !Schema::hasColumn('warranties', 'distributor_id')
            || $this->indexExists('warranties', 'idx_warranties_distributor_id')
            || $this->indexExists('warranties', 'warranties_distributor_id_index')
        ) {
            return;
        }

        Schema::table('warranties', function (Blueprint $table): void {
            $table->index('distributor_id', 'idx_warranties_distributor_id');
        });
    }

    private function ensureRetailerBranchForeignKey(): void
    {
        if (
            !Schema::hasTable('branches')
            || !Schema::hasColumn('warranties', 'retailer_branch_id')
            || $this->foreignKeyExists('warranties', 'retailer_branch_id')
            || $this->hasInvalidForeignValues('warranties', 'retailer_branch_id', 'branches', 'id')
        ) {
            return;
        }

        Schema::table('warranties', function (Blueprint $table): void {
            $table->foreign('retailer_branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    private function hasInvalidForeignValues(
        string $sourceTable,
        string $sourceColumn,
        string $targetTable,
        string $targetColumn
    ): bool {
        return DB::table($sourceTable)
            ->whereNotNull($sourceColumn)
            ->whereNotIn($sourceColumn, DB::table($targetTable)->select($targetColumn))
            ->exists();
    }

    private function foreignKeyExists(string $tableName, string $columnName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('{$tableName}')");

            foreach ($foreignKeys as $foreignKey) {
                if (($foreignKey->from ?? null) === $columnName) {
                    return true;
                }
            }

            return false;
        }

        $databaseName = DB::getDatabaseName();
        $foreignKeys = DB::select(
            'SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$databaseName, $tableName, $columnName]
        );

        return !empty($foreignKeys);
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $indexes = DB::select("SHOW INDEX FROM `{$tableName}`");

        foreach ($indexes as $index) {
            if (($index->Key_name ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
