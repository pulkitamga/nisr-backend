<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('warranty_timeline_events')
            || !Schema::hasColumn('warranty_timeline_events', 'user_id')
            || $this->foreignKeyExists('warranty_timeline_events', 'user_id')
            || !Schema::hasTable('admins')
            || $this->hasInvalidForeignValues('warranty_timeline_events', 'user_id', 'admins', 'id')
        ) {
            return;
        }

        Schema::table('warranty_timeline_events', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Catch-up migration: do not remove live warranty timeline foreign keys on rollback.
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
};
