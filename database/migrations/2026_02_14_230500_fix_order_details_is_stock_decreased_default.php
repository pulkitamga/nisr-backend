<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `order_details` MODIFY `is_stock_decreased` TINYINT(1) NOT NULL DEFAULT 0");

        DB::table('order_details')
            ->where(function ($query) {
                $query->whereNull('delivery_status')
                    ->orWhere('delivery_status', '!=', 'delivered');
            })
            ->update(['is_stock_decreased' => 0]);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `order_details` MODIFY `is_stock_decreased` TINYINT(1) NOT NULL DEFAULT 1");
    }
};
