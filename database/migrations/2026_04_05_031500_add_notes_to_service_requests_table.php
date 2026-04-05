<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_requests') || Schema::hasColumn('service_requests', 'notes')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'problem_description')) {
                $table->text('notes')->nullable()->after('problem_description');
                return;
            }

            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_requests') || !Schema::hasColumn('service_requests', 'notes')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
