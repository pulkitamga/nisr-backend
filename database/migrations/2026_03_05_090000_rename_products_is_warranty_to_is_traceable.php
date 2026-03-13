<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'is_warranty') || Schema::hasColumn('products', 'is_traceable')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `products` CHANGE `is_warranty` `is_traceable` TINYINT(1) NULL DEFAULT 0');
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('is_warranty', 'is_traceable');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'is_traceable') || Schema::hasColumn('products', 'is_warranty')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `products` CHANGE `is_traceable` `is_warranty` TINYINT(1) NULL DEFAULT 0');
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('is_traceable', 'is_warranty');
        });
    }
};
