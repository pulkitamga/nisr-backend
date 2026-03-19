<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Allow warranty_timeline_events to be created by customers too
 *
 * ISSUE: user_id foreign key only allows admins, but events can be created by customers
 *
 * SOLUTION: Drop foreign key constraint, add index
 * Events can reference either admins.id or users.id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_timeline_events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Add index for performance without FK constraint
        Schema::table('warranty_timeline_events', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('warranty_timeline_events', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('warranty_timeline_events', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('admins')
                  ->onDelete('set null')
                  ->change();
        });
    }
};
