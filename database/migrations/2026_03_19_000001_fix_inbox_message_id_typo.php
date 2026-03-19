<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix typo: Rename massage_id to message_id in all inbox tables
 * This fixes the typo that was causing confusion in relationship definitions.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeMessageIdColumn('inbox_activities');
        $this->normalizeMessageIdColumn('inbox_notes');
        $this->normalizeMessageIdColumn('inbox_tasks');
        $this->normalizeMessageIdColumn('inbox_calls');
        $this->normalizeMessageIdColumn('inbox_files');
    }

    public function down(): void
    {
        $this->revertMessageIdColumn('inbox_activities');
        $this->revertMessageIdColumn('inbox_notes');
        $this->revertMessageIdColumn('inbox_tasks');
        $this->revertMessageIdColumn('inbox_calls');
        $this->revertMessageIdColumn('inbox_files');
    }

    private function normalizeMessageIdColumn(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'message_id')) {
            return;
        }

        if (Schema::hasColumn($tableName, 'massage_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('massage_id', 'message_id');
            });
            return;
        }

        if (Schema::hasColumn($tableName, 'inbox_message_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('inbox_message_id', 'message_id');
            });
        }
    }

    private function revertMessageIdColumn(string $tableName): void
    {
        if (
            !Schema::hasTable($tableName)
            || !Schema::hasColumn($tableName, 'message_id')
            || Schema::hasColumn($tableName, 'massage_id')
            || Schema::hasColumn($tableName, 'inbox_message_id')
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });
    }
};
