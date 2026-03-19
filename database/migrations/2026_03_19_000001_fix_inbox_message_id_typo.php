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
        // Rename the typo column in all inbox-related tables
        Schema::table('inbox_activities', function (Blueprint $table) {
            $table->renameColumn('massage_id', 'message_id');
        });

        Schema::table('inbox_notes', function (Blueprint $table) {
            $table->renameColumn('massage_id', 'message_id');
        });

        Schema::table('inbox_tasks', function (Blueprint $table) {
            $table->renameColumn('massage_id', 'message_id');
        });

        Schema::table('inbox_calls', function (Blueprint $table) {
            $table->renameColumn('massage_id', 'message_id');
        });

        Schema::table('inbox_files', function (Blueprint $table) {
            $table->renameColumn('massage_id', 'message_id');
        });
    }

    public function down(): void
    {
        // Rollback - rename back to typo
        Schema::table('inbox_activities', function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });

        Schema::table('inbox_notes', function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });

        Schema::table('inbox_tasks', function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });

        Schema::table('inbox_calls', function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });

        Schema::table('inbox_files', function (Blueprint $table) {
            $table->renameColumn('message_id', 'massage_id');
        });
    }
};
