<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'is_supervisor')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->boolean('is_supervisor')->default(false)->after('department_id');
            });

            if (Schema::hasTable('departments')) {
                DB::table('admins')
                    ->whereIn('id', function ($query) {
                        $query->select('head_id')
                            ->from('departments')
                            ->whereNotNull('head_id');
                    })
                    ->update(['is_supervisor' => 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'is_supervisor')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('is_supervisor');
            });
        }
    }
};
