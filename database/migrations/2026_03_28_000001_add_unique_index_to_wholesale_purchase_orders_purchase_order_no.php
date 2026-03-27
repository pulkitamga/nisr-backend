<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $table = 'wholesale_purchase_orders';
    private string $index = 'uq_wholesale_purchase_orders_purchase_order_no';

    private function indexExists(): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$this->table}` WHERE Key_name = ?",
            [$this->index]
        );

        return count($result) > 0;
    }

    private function hasDuplicatePurchaseOrderNumbers(): bool
    {
        return DB::table($this->table)
            ->select('purchase_order_no')
            ->whereNotNull('purchase_order_no')
            ->where('purchase_order_no', '!=', '')
            ->groupBy('purchase_order_no')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    public function up(): void
    {
        if ($this->indexExists()) {
            return;
        }

        if ($this->hasDuplicatePurchaseOrderNumbers()) {
            throw new \RuntimeException(
                'Cannot add unique index to wholesale_purchase_orders.purchase_order_no because duplicate non-empty values exist.'
            );
        }

        DB::statement(
            "ALTER TABLE `{$this->table}` ADD UNIQUE INDEX `{$this->index}` (`purchase_order_no`)"
        );
    }

    public function down(): void
    {
        if (!$this->indexExists()) {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$this->table}` DROP INDEX `{$this->index}`"
        );
    }
};
