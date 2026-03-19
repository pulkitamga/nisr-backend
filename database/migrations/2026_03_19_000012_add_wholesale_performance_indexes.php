<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to wholesale tables
 *
 * Optimizes:
 * - Wholesale product queries
 * - Purchase order lookups
 * - Price range queries
 * - Tier lookups
 * - Wholesaler business queries
 */
return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function addIndexIfNeeded(string $table, string $index, string $columns): void
    {
        if (!$this->indexExists($table, $index)) {
            try {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$columns})");
            } catch (\Exception $e) {
                // Skip if table or column doesn't exist
            }
        }
    }

    public function up(): void
    {
        // wholesale_products table indexes
        $this->addIndexIfNeeded('wholesale_products', 'idx_wholesale_products_product_status', 'product_id, status');
        $this->addIndexIfNeeded('wholesale_products', 'idx_wholesale_products_category_status', 'category_id, status');
        $this->addIndexIfNeeded('wholesale_products', 'idx_wholesale_products_subcategory_status', 'sub_category_id, status');
        $this->addIndexIfNeeded('wholesale_products', 'idx_wholesale_products_status', 'status');
        $this->addIndexIfNeeded('wholesale_products', 'idx_wholesale_products_variation_type', 'variation_type');

        // wholesale_purchase_orders table indexes
        $this->addIndexIfNeeded('wholesale_purchase_orders', 'idx_wholesale_orders_wholeseller_status', 'wholeseller_id, status');
        $this->addIndexIfNeeded('wholesale_purchase_orders', 'idx_wholesale_orders_status_created', 'status, created_at');
        $this->addIndexIfNeeded('wholesale_purchase_orders', 'idx_wholesale_orders_status', 'status');
        $this->addIndexIfNeeded('wholesale_purchase_orders', 'idx_wholesale_orders_wholeseller', 'wholeseller_id');
        $this->addIndexIfNeeded('wholesale_purchase_orders', 'idx_wholesale_orders_created_at', 'created_at');

        // wholesale_purchase_order_items table indexes
        $this->addIndexIfNeeded('wholesale_purchase_order_items', 'idx_wholesale_order_items_order_id', 'order_id');
        $this->addIndexIfNeeded('wholesale_purchase_order_items', 'idx_wholesale_order_items_product_id', 'product_id');

        // wholesale_price_ranges table indexes
        $this->addIndexIfNeeded('wholesale_price_ranges', 'idx_wholesale_price_ranges_wholesale_tier', 'wholesale_id, tier');
        $this->addIndexIfNeeded('wholesale_price_ranges', 'idx_wholesale_price_ranges_tier_active', 'tier, is_active');
        $this->addIndexIfNeeded('wholesale_price_ranges', 'idx_wholesale_price_ranges_wholesale', 'wholesale_id');
        $this->addIndexIfNeeded('wholesale_price_ranges', 'idx_wholesale_price_ranges_tier', 'tier');

        // wholesale_tiers table indexes
        $this->addIndexIfNeeded('wholesale_tiers', 'idx_wholesale_tiers_active_rank', 'is_active, rank');
        $this->addIndexIfNeeded('wholesale_tiers', 'idx_wholesale_tiers_is_active', 'is_active');
        $this->addIndexIfNeeded('wholesale_tiers', 'idx_wholesale_tiers_rank', 'rank');

        // wholesaler_businesses table indexes
        $this->addIndexIfNeeded('wholesaler_businesses', 'idx_wholesaler_businesses_wholesaler', 'wholesaler_id');
        $this->addIndexIfNeeded('wholesaler_businesses', 'idx_wholesaler_businesses_deleted_at', 'deleted_at');

        // wholesale_contacts table indexes
        $this->addIndexIfNeeded('wholesale_contacts', 'idx_wholesale_contacts_company', 'company_id');

        // wholesale_quotations table indexes
        $this->addIndexIfNeeded('wholesale_quotations', 'idx_wholesale_quotations_wholeseller_status', 'wholeseller_id, status');
        $this->addIndexIfNeeded('wholesale_quotations', 'idx_wholesale_quotations_status', 'status');
        $this->addIndexIfNeeded('wholesale_quotations', 'idx_wholesale_quotations_created_at', 'created_at');

        // wholesale_order_delivery table indexes
        $this->addIndexIfNeeded('wholesale_order_delivery', 'idx_wholesale_delivery_order_id', 'order_id');
        $this->addIndexIfNeeded('wholesale_order_delivery', 'idx_wholesale_delivery_status', 'status');

        // wholesale_order_payments table indexes
        $this->addIndexIfNeeded('wholesale_order_payments', 'idx_wholesale_payments_order_id', 'order_id');
        $this->addIndexIfNeeded('wholesale_order_payments', 'idx_wholesale_payments_status', 'status');
    }

    public function down(): void
    {
        $indexes = [
            'wholesale_products' => [
                'idx_wholesale_products_product_status',
                'idx_wholesale_products_category_status',
                'idx_wholesale_products_subcategory_status',
                'idx_wholesale_products_status',
                'idx_wholesale_products_variation_type',
            ],
            'wholesale_purchase_orders' => [
                'idx_wholesale_orders_wholeseller_status',
                'idx_wholesale_orders_status_created',
                'idx_wholesale_orders_status',
                'idx_wholesale_orders_wholeseller',
                'idx_wholesale_orders_created_at',
            ],
            'wholesale_purchase_order_items' => [
                'idx_wholesale_order_items_order_id',
                'idx_wholesale_order_items_product_id',
            ],
            'wholesale_price_ranges' => [
                'idx_wholesale_price_ranges_wholesale_tier',
                'idx_wholesale_price_ranges_tier_active',
                'idx_wholesale_price_ranges_wholesale',
                'idx_wholesale_price_ranges_tier',
            ],
            'wholesale_tiers' => [
                'idx_wholesale_tiers_active_rank',
                'idx_wholesale_tiers_is_active',
                'idx_wholesale_tiers_rank',
            ],
            'wholesaler_businesses' => [
                'idx_wholesaler_businesses_wholesaler',
                'idx_wholesaler_businesses_deleted_at',
            ],
            'wholesale_contacts' => [
                'idx_wholesale_contacts_company',
            ],
            'wholesale_quotations' => [
                'idx_wholesale_quotations_wholeseller_status',
                'idx_wholesale_quotations_status',
                'idx_wholesale_quotations_created_at',
            ],
            'wholesale_order_delivery' => [
                'idx_wholesale_delivery_order_id',
                'idx_wholesale_delivery_status',
            ],
            'wholesale_order_payments' => [
                'idx_wholesale_payments_order_id',
                'idx_wholesale_payments_status',
            ],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                try {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$index}`");
                } catch (\Exception $e) {
                    // Skip if index doesn't exist
                }
            }
        }
    }
};
