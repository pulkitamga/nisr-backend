<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add soft deletes to CRM core tables
 * Allows leads and deals to be soft-deleted for audit trail and recovery
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'deleted_at')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('deals') && !Schema::hasColumn('deals', 'deleted_at')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'deleted_at')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('deals') && Schema::hasColumn('deals', 'deleted_at')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
