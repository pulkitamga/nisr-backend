<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_tickets') || Schema::hasColumn('support_tickets', 'service_id')) {
            return;
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('request_type');
            $table->index('service_id', 'support_tickets_service_id_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('support_tickets') || !Schema::hasColumn('support_tickets', 'service_id')) {
            return;
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            try {
                $table->dropIndex('support_tickets_service_id_idx');
            } catch (\Throwable) {
                // The index may not exist in drifted environments.
            }

            $table->dropColumn('service_id');
        });
    }
};
