<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to warranty tables
 *
 * Optimizes:
 * - Active warranty queries
 * - Serial number lookups
 * - Customer warranty queries
 * - Branch inventory queries
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
        // Warranties table indexes
        $this->addIndexIfNeeded('warranties', 'idx_warranties_status_end_date', 'status, end_date');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_serial_status', 'serial_number, status');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_final_user_status', 'final_user_id, status');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_branch_status', 'branch_id, status');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_status', 'status');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_serial', 'serial_number');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_product_id', 'product_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_distributor_id', 'distributor_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_retailer_branch_id', 'retailer_branch_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_end_date', 'end_date');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_purchase_date', 'purchase_date');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_warranty_public_id', 'warranty_public_id');

        // Warranty Distributions table indexes
        $this->addIndexIfNeeded('warranty_distribution_histories', 'idx_warranty_dist_warranty_id', 'warranty_id');
        $this->addIndexIfNeeded('warranty_distribution_histories', 'idx_warranty_dist_from_branch', 'from_branch_id');
        $this->addIndexIfNeeded('warranty_distribution_histories', 'idx_warranty_dist_to_branch', 'to_branch_id');
        $this->addIndexIfNeeded('warranty_distribution_histories', 'idx_warranty_dist_created_at', 'created_at');

        // Warranty Replacements table indexes
        $this->addIndexIfNeeded('warranty_replacements', 'idx_warranty_replace_original_id', 'original_warranty_id');
        $this->addIndexIfNeeded('warranty_replacements', 'idx_warranty_replace_serial', 'serial_number');
        $this->addIndexIfNeeded('warranty_replacements', 'idx_warranty_replace_status', 'status');

        // Warranty Timeline Events indexes
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_warranty_id', 'warranty_id');
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_event_type', 'event_type');
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_created_at', 'created_at');

        // Activation Reviews indexes
        $this->addIndexIfNeeded('activation_reviews', 'idx_activation_reviews_warranty_id', 'warranty_id');
        $this->addIndexIfNeeded('activation_reviews', 'idx_activation_reviews_status', 'status');
        $this->addIndexIfNeeded('activation_reviews', 'idx_activation_reviews_created_at', 'created_at');
    }

    public function down(): void
    {
        $indexes = [
            'warranties' => [
                'idx_warranties_status_end_date',
                'idx_warranties_serial_status',
                'idx_warranties_final_user_status',
                'idx_warranties_branch_status',
                'idx_warranties_status',
                'idx_warranties_serial',
                'idx_warranties_product_id',
                'idx_warranties_distributor_id',
                'idx_warranties_retailer_branch_id',
                'idx_warranties_end_date',
                'idx_warranties_purchase_date',
                'idx_warranties_warranty_public_id',
            ],
            'warranty_distribution_histories' => [
                'idx_warranty_dist_warranty_id',
                'idx_warranty_dist_from_branch',
                'idx_warranty_dist_to_branch',
                'idx_warranty_dist_created_at',
            ],
            'warranty_replacements' => [
                'idx_warranty_replace_original_id',
                'idx_warranty_replace_serial',
                'idx_warranty_replace_status',
            ],
            'warranty_timeline_events' => [
                'idx_warranty_timeline_warranty_id',
                'idx_warranty_timeline_event_type',
                'idx_warranty_timeline_created_at',
            ],
            'activation_reviews' => [
                'idx_activation_reviews_warranty_id',
                'idx_activation_reviews_status',
                'idx_activation_reviews_created_at',
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

        // Also drop triggers
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_active_warranties_insert');
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_active_warranties_update');
    }
};
