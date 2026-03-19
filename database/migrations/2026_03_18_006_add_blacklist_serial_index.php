<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blacklists') || !Schema::hasColumn('blacklists', 'serial_number')) {
            return;
        }

        if ($this->indexExists('blacklists', 'blacklists_serial_number_unique')) {
            return;
        }

        if ($this->hasDuplicateSerialNumbers()) {
            if ($this->indexExists('blacklists', 'idx_blacklists_serial_number')) {
                return;
            }

            Schema::table('blacklists', function (Blueprint $table): void {
                $table->index('serial_number', 'idx_blacklists_serial_number');
            });

            return;
        }

        Schema::table('blacklists', function (Blueprint $table): void {
            $table->unique('serial_number');
        });
    }

    public function down(): void
    {
        // Catch-up migration: do not remove live blacklist indexes on rollback.
    }

    private function hasDuplicateSerialNumbers(): bool
    {
        return DB::table('blacklists')
            ->select('serial_number')
            ->whereNotNull('serial_number')
            ->groupBy('serial_number')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
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
