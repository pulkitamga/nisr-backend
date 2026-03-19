<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to service tables
 *
 * Optimizes:
 * - Service lookups by SKU and product
 * - Service requests by customer and status
 * - Common queries in service workflows
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
        // Services table indexes
        $this->addIndexIfNeeded('services', 'idx_services_service_id', 'service_id');
        $this->addIndexIfNeeded('services', 'idx_services_product_id', 'product_id');
        $this->addIndexIfNeeded('services', 'idx_services_title', 'title');
        $this->addIndexIfNeeded('services', 'idx_services_call_center_flag', 'call_center_flag');
        $this->addIndexIfNeeded('services', 'idx_services_created_at', 'created_at');

        // Service Requests table indexes
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_service_id', 'service_id');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_customer_id', 'customer_id');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_deleted_at', 'deleted_at');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_created_at', 'created_at');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_state', 'state');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_city', 'city');
        $this->addIndexIfNeeded('service_requests', 'idx_service_requests_area', 'area');

        // CMS Services table indexes (if exists)
        $this->addIndexIfNeeded('cms_services', 'idx_cms_services_created_at', 'created_at');
    }

    public function down(): void
    {
        $indexes = [
            'services' => [
                'idx_services_service_id', 'idx_services_product_id', 'idx_services_title',
                'idx_services_call_center_flag', 'idx_services_created_at',
            ],
            'service_requests' => [
                'idx_service_requests_service_id', 'idx_service_requests_customer_id',
                'idx_service_requests_deleted_at', 'idx_service_requests_created_at',
                'idx_service_requests_state', 'idx_service_requests_city', 'idx_service_requests_area',
            ],
            'cms_services' => ['idx_cms_services_created_at'],
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
