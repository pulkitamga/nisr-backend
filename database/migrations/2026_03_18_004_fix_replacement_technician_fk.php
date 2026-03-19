<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Standardize warranty_replacements technician_id foreign key
 *
 * ISSUE: Migration references 'admins' table, model uses 'users' table
 *
 * DECISION: This fix assumes technicians are in the 'users' table
 * Adjust if technicians are actually admins
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop existing foreign key
        Schema::table('warranty_replacements', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
        });

        // Recreate with users table (matching the model)
        Schema::table('warranty_replacements', function (Blueprint $table) {
            $table->foreignId('technician_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->change();
        });
    }

    public function down(): void
    {
        // Revert to admins table
        Schema::table('warranty_replacements', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
        });

        Schema::table('warranty_replacements', function (Blueprint $table) {
            $table->foreignId('technician_id')
                  ->nullable()
                  ->constrained('admins')
                  ->onDelete('set null')
                  ->change();
        });
    }
};
