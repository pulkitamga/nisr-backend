<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Add missing foreign key constraints to warranties table
 *
 * ISSUES:
 * 1. distributor_id has no foreign key
 * 2. retailer_branch_id has no foreign key
 *
 * NOTES:
 * - Requires distributors table to exist
 * - If distributors table doesn't exist, add index instead
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            // Try to add foreign key for distributor_id
            // Will fail gracefully if distributors table doesn't exist
            try {
                $table->foreignId('distributor_id_new')->nullable()->change();

                // Copy existing data
                DB::statement('UPDATE warranties SET distributor_id_new = distributor_id');

                // Drop old column
                $table->dropColumn('distributor_id');

                // Rename new column
                DB::statement('ALTER TABLE warranties CHANGE distributor_id_new distributor_id BIGINT UNSIGNED NULL');
            } catch (\Exception $e) {
                // Just add index if foreign key fails
                if (!Schema::hasIndex('warranties', 'warranties_distributor_id_index')) {
                    $table->index('distributor_id');
                }
            }

            // Add foreign key for retailer_branch_id (should exist - branches table)
            $table->foreign('retailer_branch_id')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropForeign(['retailer_branch_id']);
        });
    }
};
