<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to products tables
 *
 * Optimizes:
 * - Category/brand product filtering
 * - New products and featured products queries
 * - Stock and inventory queries
 * - Reviews and wishlist lookups
 * - Deal and discount queries
 */
return new class extends Migration
{
    /**
     * Helper to check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper to add index if not exists
     */
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
        // Products table indexes
        $this->addIndexIfNeeded('products', 'idx_products_category_status', 'category_id, status');
        $this->addIndexIfNeeded('products', 'idx_products_brand_status', 'brand_id, status');
        $this->addIndexIfNeeded('products', 'idx_products_status_date', 'status, created_at');
        $this->addIndexIfNeeded('products', 'idx_products_user', 'user_id');
        $this->addIndexIfNeeded('products', 'idx_products_subcategory_status', 'sub_category_id, status');
        $this->addIndexIfNeeded('products', 'idx_products_sub_subcategory_status', 'sub_sub_category_id, status');
        $this->addIndexIfNeeded('products', 'idx_products_slug', 'slug');
        $this->addIndexIfNeeded('products', 'idx_products_featured', 'featured_status, status');
        $this->addIndexIfNeeded('products', 'idx_products_branch_status', 'branch_id, status');
        $this->addIndexIfNeeded('products', 'idx_products_published', 'published, status');
        $this->addIndexIfNeeded('products', 'idx_products_type', 'product_type');
        $this->addIndexIfNeeded('products', 'idx_products_code', 'code');
        $this->addIndexIfNeeded('products', 'idx_products_showcase', 'showcase_product');
        $this->addIndexIfNeeded('products', 'idx_products_request_status', 'request_status');
        $this->addIndexIfNeeded('products', 'idx_products_branch_id', 'branch_id');
        $this->addIndexIfNeeded('products', 'idx_products_published', 'published');
        $this->addIndexIfNeeded('products', 'idx_products_status', 'status');

        // Categories table indexes (actual columns: parent_id, position, home_status, priority)
        $this->addIndexIfNeeded('categories', 'idx_categories_parent', 'parent_id');
        $this->addIndexIfNeeded('categories', 'idx_categories_position', 'position');
        $this->addIndexIfNeeded('categories', 'idx_categories_home_status', 'home_status');
        $this->addIndexIfNeeded('categories', 'idx_categories_priority', 'priority');
        $this->addIndexIfNeeded('categories', 'idx_categories_slug', 'slug');

        // Brands table indexes (actual columns: status)
        $this->addIndexIfNeeded('brands', 'idx_brands_status', 'status');

        // Attributes table indexes
        $this->addIndexIfNeeded('attributes', 'idx_attributes_category', 'category_id');
        $this->addIndexIfNeeded('attributes', 'idx_attributes_position', 'position');

        // Product Stocks indexes
        $this->addIndexIfNeeded('product_stocks', 'idx_product_stocks_product', 'product_id');
        $this->addIndexIfNeeded('product_stocks', 'idx_product_stocks_sku', 'sku');
        $this->addIndexIfNeeded('product_stocks', 'idx_product_stocks_product_qty', 'product_id, qty');

        // Product Stock Transactions (table may not exist in actual DB)
        $this->addIndexIfNeeded('product_stock_transactions', 'idx_stock_transactions_product', 'product_id');

        // Tags table indexes (actual columns: tag, not slug)
        $this->addIndexIfNeeded('tags', 'idx_tags_tag', 'tag');
        $this->addIndexIfNeeded('tags', 'idx_tags_visit_count', 'visit_count');

        // Product Tag (pivot) indexes
        $this->addIndexIfNeeded('product_tag', 'idx_product_tag_tag', 'tag_id');
        $this->addIndexIfNeeded('product_tag', 'idx_product_tag_product', 'product_id');

        // Product SEO indexes
        $this->addIndexIfNeeded('product_seos', 'idx_product_seos_product', 'product_id');

        // Product Compares indexes
        $this->addIndexIfNeeded('product_compares', 'idx_compares_user_product', 'user_id, product_id');
        $this->addIndexIfNeeded('product_compares', 'idx_compares_guest_product', 'guest_id, product_id');
        $this->addIndexIfNeeded('product_compares', 'idx_compares_product', 'product_id');

        // Wishlists indexes (actual columns: customer_id, not user_id)
        $this->addIndexIfNeeded('wishlists', 'idx_wishlists_customer_product', 'customer_id, product_id');
        $this->addIndexIfNeeded('wishlists', 'idx_wishlists_product', 'product_id');

        // Reviews indexes (actual columns: customer_id, not user_id; status is int)
        $this->addIndexIfNeeded('reviews', 'idx_reviews_product_status', 'product_id, status');
        $this->addIndexIfNeeded('reviews', 'idx_reviews_customer_product', 'customer_id, product_id');
        $this->addIndexIfNeeded('reviews', 'idx_reviews_rating', 'rating');
        $this->addIndexIfNeeded('reviews', 'idx_reviews_status', 'status');
        $this->addIndexIfNeeded('reviews', 'idx_reviews_product', 'product_id');

        // Review Replies indexes
        $this->addIndexIfNeeded('review_replies', 'idx_review_replies_review', 'review_id');

        // Digital Product Variations indexes
        $this->addIndexIfNeeded('digital_product_variations', 'idx_digital_variations_product', 'product_id');
        $this->addIndexIfNeeded('digital_product_variations', 'idx_digital_variations_key', 'variant_key');

        // Digital Product OTP Verifications indexes
        $this->addIndexIfNeeded('digital_product_otp_verifications', 'idx_digital_otp_product_user', 'product_id, user_id, is_verified');
        $this->addIndexIfNeeded('digital_product_otp_verifications', 'idx_digital_otp_expires', 'expires_at');

        // Most Demanded Products indexes
        $this->addIndexIfNeeded('most_demandeds', 'idx_most_demanded_product_status', 'product_id, status');
        $this->addIndexIfNeeded('most_demandeds', 'idx_most_demanded_status_priority', 'status, priority');

        // Deal of the Day indexes
        $this->addIndexIfNeeded('deal_of_the_days', 'idx_deal_day_product_status', 'product_id, status');
        $this->addIndexIfNeeded('deal_of_the_days', 'idx_deal_day_dates_status', 'start_date, end_date, status');

        // Feature Deals indexes
        $this->addIndexIfNeeded('feature_deals', 'idx_feature_deals_product_status', 'product_id, status');
        $this->addIndexIfNeeded('feature_deals', 'idx_feature_deals_dates_status', 'start_date, end_date, status');

        // Flash Deals indexes
        $this->addIndexIfNeeded('flash_deals', 'idx_flash_deals_dates_status', 'start_date, end_date, status');
        $this->addIndexIfNeeded('flash_deals', 'idx_flash_deals_slug', 'slug');

        // Flash Deal Products indexes
        $this->addIndexIfNeeded('flash_deal_products', 'idx_flash_deal_products_deal_product', 'flash_deal_id, product_id');
        $this->addIndexIfNeeded('flash_deal_products', 'idx_flash_deal_products_product', 'product_id');

        // Restock Products indexes
        $this->addIndexIfNeeded('restock_products', 'idx_restock_products_product', 'product_id');
        $this->addIndexIfNeeded('restock_products', 'idx_restock_products_date', 'expected_date');

        // Restock Product Customers indexes
        $this->addIndexIfNeeded('restock_product_customers', 'idx_restock_customers_restock', 'restock_product_id');
        $this->addIndexIfNeeded('restock_product_customers', 'idx_restock_customers_restock_user', 'restock_product_id, user_id');
        $this->addIndexIfNeeded('restock_product_customers', 'idx_restock_customers_restock_guest', 'restock_product_id, guest_id');
    }

    public function down(): void
    {
        $indexes = [
            'products' => [
                'idx_products_category_status', 'idx_products_brand_status', 'idx_products_status_date',
                'idx_products_user', 'idx_products_subcategory_status', 'idx_products_sub_subcategory_status',
                'idx_products_slug', 'idx_products_featured', 'idx_products_branch_status',
                'idx_products_published', 'idx_products_type', 'idx_products_code',
                'idx_products_showcase', 'idx_products_request_status', 'idx_products_branch_id', 'idx_products_status',
            ],
            'categories' => [
                'idx_categories_parent', 'idx_categories_position', 'idx_categories_home_status',
                'idx_categories_priority', 'idx_categories_slug',
            ],
            'brands' => ['idx_brands_status'],
            'attributes' => ['idx_attributes_category', 'idx_attributes_position'],
            'product_stocks' => ['idx_product_stocks_product', 'idx_product_stocks_sku', 'idx_product_stocks_product_qty'],
            'product_stock_transactions' => ['idx_stock_transactions_product'],
            'tags' => ['idx_tags_tag', 'idx_tags_visit_count'],
            'product_tag' => ['idx_product_tag_tag', 'idx_product_tag_product'],
            'product_seos' => ['idx_product_seos_product'],
            'product_compares' => ['idx_compares_user_product', 'idx_compares_guest_product', 'idx_compares_product'],
            'wishlists' => ['idx_wishlists_customer_product', 'idx_wishlists_product'],
            'reviews' => ['idx_reviews_product_status', 'idx_reviews_customer_product', 'idx_reviews_rating', 'idx_reviews_status', 'idx_reviews_product'],
            'review_replies' => ['idx_review_replies_review'],
            'digital_product_variations' => ['idx_digital_variations_product', 'idx_digital_variations_key'],
            'digital_product_otp_verifications' => ['idx_digital_otp_product_user', 'idx_digital_otp_expires'],
            'most_demandeds' => ['idx_most_demanded_product_status', 'idx_most_demanded_status_priority'],
            'deal_of_the_days' => ['idx_deal_day_product_status', 'idx_deal_day_dates_status'],
            'feature_deals' => ['idx_feature_deals_product_status', 'idx_feature_deals_dates_status'],
            'flash_deals' => ['idx_flash_deals_dates_status', 'idx_flash_deals_slug'],
            'flash_deal_products' => ['idx_flash_deal_products_deal_product', 'idx_flash_deal_products_product'],
            'restock_products' => ['idx_restock_products_product', 'idx_restock_products_date'],
            'restock_product_customers' => ['idx_restock_customers_restock', 'idx_restock_customers_restock_user', 'idx_restock_customers_restock_guest'],
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
