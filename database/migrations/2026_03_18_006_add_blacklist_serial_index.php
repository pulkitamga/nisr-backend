<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Add unique index to blacklist serial_number
 *
 * ISSUE: No index on serial_number for lookups
 *        A serial should only be blacklisted once
 *
 * IMPACT: Blacklist checks scan entire table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blacklists', function (Blueprint $table) {
            // Add unique index if not exists
            $table->unique('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('blacklists', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
        });
    }
};
