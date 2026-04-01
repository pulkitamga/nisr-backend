<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_tickets') || !Schema::hasColumn('support_tickets', 'status')) {
            return;
        }

        DB::table('support_tickets')
            ->whereNull('status')
            ->update(['status' => 'open']);

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('status', 15)->default('open')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('support_tickets') || !Schema::hasColumn('support_tickets', 'status')) {
            return;
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('status', 15)->default('open')->nullable()->change();
        });
    }
};
