<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to warranty tables
 *
 * Optimizes:
 * - Status and date-based queries
 * - Warranty claim lookups
 * - Branch workload queries
 * - RMA tracking
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
        // Warranty Claims table indexes
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_status_created', 'status, created_at');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_warranty_status', 'warranty_id, status');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_branch_status', 'branch_id, status');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_technician_status', 'technician_id, status');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_status', 'status');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_serial', 'serial_number');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_claim_number', 'claim_number');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_rma_number', 'rma_number');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_response_due', 'response_due');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_resolution_due', 'resolution_due');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_submitted_at', 'submitted_at');
        $this->addIndexIfNeeded('warranty_claims', 'idx_warranty_claims_priority', 'priority');

        // Warranties table indexes
        $this->addIndexIfNeeded('warranties', 'idx_warranties_status', 'status');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_serial', 'serial_number');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_product_id', 'product_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_distributor_id', 'distributor_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_retailer_branch_id', 'retailer_branch_id');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_expires_at', 'expires_at');
        $this->addIndexIfNeeded('warranties', 'idx_warranties_purchase_date', 'purchase_date');

        // Warranty Timeline Events indexes
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_claim_id', 'warranty_claim_id');
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_event_type', 'event_type');
        $this->addIndexIfNeeded('warranty_timeline_events', 'idx_warranty_timeline_created_at', 'created_at');

        // Warranty Claim Attachments indexes
        $this->addIndexIfNeeded('warranty_claim_attachments', 'idx_warranty_attachments_claim_id', 'warranty_claim_id');
        $this->addIndexIfNeeded('warranty_claim_attachments', 'idx_warranty_attachments_type', 'attachment_type');

        // Warranty Claim Charges indexes
        $this->addIndexIfNeeded('warranty_claim_charges', 'idx_warranty_charges_claim_id', 'warranty_claim_id');
        $this->addIndexIfNeeded('warranty_claim_charges', 'idx_warranty_charges_type', 'charge_type');

        // Warranty Claim Payments indexes
        $this->addIndexIfNeeded('warranty_claim_payments', 'idx_warranty_payments_claim_id', 'warranty_claim_id');
        $this->addIndexIfNeeded('warranty_claim_payments', 'idx_warranty_payments_status', 'status');
        $this->addIndexIfNeeded('warranty_claim_payments', 'idx_warranty_payments_transaction_id', 'transaction_id');
    }

    public function down(): void
    {
        $indexes = [
            'warranty_claims' => [
                'idx_warranty_claims_status_created',
                'idx_warranty_claims_warranty_status',
                'idx_warranty_claims_branch_status',
                'idx_warranty_claims_technician_status',
                'idx_warranty_claims_status',
                'idx_warranty_claims_serial',
                'idx_warranty_claims_claim_number',
                'idx_warranty_claims_rma_number',
                'idx_warranty_claims_response_due',
                'idx_warranty_claims_resolution_due',
                'idx_warranty_claims_submitted_at',
                'idx_warranty_claims_priority',
            ],
            'warranties' => [
                'idx_warranties_status',
                'idx_warranties_serial',
                'idx_warranties_product_id',
                'idx_warranties_distributor_id',
                'idx_warranties_retailer_branch_id',
                'idx_warranties_expires_at',
                'idx_warranties_purchase_date',
            ],
            'warranty_timeline_events' => [
                'idx_warranty_timeline_claim_id',
                'idx_warranty_timeline_event_type',
                'idx_warranty_timeline_created_at',
            ],
            'warranty_claim_attachments' => [
                'idx_warranty_attachments_claim_id',
                'idx_warranty_attachments_type',
            ],
            'warranty_claim_charges' => [
                'idx_warranty_charges_claim_id',
                'idx_warranty_charges_type',
            ],
            'warranty_claim_payments' => [
                'idx_warranty_payments_claim_id',
                'idx_warranty_payments_status',
                'idx_warranty_payments_transaction_id',
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
