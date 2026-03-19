<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Add missing 'pending_review' status to warranties table
 *
 * ISSUE: API controller sets status to 'pending_review' but enum doesn't include it
 * FILE: app/Http/Controllers/RestAPI/v1/WarrantyActivationApiController.php:409
 *
 * IMPACT: Database errors when flagging warranties for review
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL doesn't support ALTER ENUM directly, need to recreate
        // Using raw SQL for MySQL compatibility
        DB::statement("
            ALTER TABLE warranties
            MODIFY COLUMN status ENUM(
                'preactivated',
                'active',
                'cancelled',
                'replaced',
                'expired',
                'pending_review'
            ) DEFAULT 'preactivated'
        ");
    }

    public function down(): void
    {
        // First, ensure no records use pending_review
        $count = DB::table('warranties')
            ->where('status', 'pending_review')
            ->count();

        if ($count > 0) {
            // Convert pending_review to preactivated for rollback
            DB::table('warranties')
                ->where('status', 'pending_review')
                ->update(['status' => 'preactivated']);
        }

        DB::statement("
            ALTER TABLE warranties
            MODIFY COLUMN status ENUM(
                'preactivated',
                'active',
                'cancelled',
                'replaced',
                'expired'
            ) DEFAULT 'preactivated'
        ");
    }
};
