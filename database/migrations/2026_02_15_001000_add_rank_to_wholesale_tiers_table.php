<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('wholesale_tiers', 'rank')) {
            Schema::table('wholesale_tiers', function (Blueprint $table) {
                $table->unsignedInteger('rank')->default(0)->after('is_active');
            });
        }

        $tiers = DB::table('wholesale_tiers')->orderBy('id')->pluck('id');
        foreach ($tiers as $index => $tierId) {
            DB::table('wholesale_tiers')
                ->where('id', $tierId)
                ->update(['rank' => $index + 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('wholesale_tiers', 'rank')) {
            Schema::table('wholesale_tiers', function (Blueprint $table) {
                $table->dropColumn('rank');
            });
        }
    }
};

