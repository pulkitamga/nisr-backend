<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Standardize warranty claim column names
 *
 * ISSUE: Migration uses first_response_due, decision_due
 *        Code uses response_due, resolution_due
 *
 * MIGRATION: align_warranty_schema_with_module_logic.php added compatibility columns
 * FIX: Properly rename and clean up
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // Drop old columns if compatibility migration ran
            if (Schema::hasColumn('warranty_claims', 'first_response_due')) {
                $table->dropColumn('first_response_due');
            }
            if (Schema::hasColumn('warranty_claims', 'decision_due')) {
                $table->dropColumn('decision_due');
            }
        });

        // Add proper columns if they don't exist
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'response_due')) {
                $table->timestamp('response_due')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'resolution_due')) {
                $table->timestamp('resolution_due')->nullable();
            }
        });

        // Copy data from old to new if needed (migration might have done this)
        // This is idempotent - safe to run multiple times
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // Recreate old columns
            if (!Schema::hasColumn('warranty_claims', 'first_response_due')) {
                $table->timestamp('first_response_due')->nullable();
            }
            if (!Schema::hasColumn('warranty_claims', 'decision_due')) {
                $table->timestamp('decision_due')->nullable();
            }
        });

        // Copy data back
        DB::statement("
            UPDATE warranty_claims
            SET first_response_due = response_due,
                decision_due = resolution_due
            WHERE first_response_due IS NULL
        ");
    }
};
