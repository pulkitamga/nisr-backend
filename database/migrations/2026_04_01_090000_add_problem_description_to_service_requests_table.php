<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_requests') || Schema::hasColumn('service_requests', 'problem_description')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'vin')) {
                $table->text('problem_description')->nullable()->after('vin');
                return;
            }

            $table->text('problem_description')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_requests') || !Schema::hasColumn('service_requests', 'problem_description')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('problem_description');
        });
    }
};
