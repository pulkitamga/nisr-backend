<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add performance indexes to support tables
 *
 * Optimizes:
 * - Department ticket queries
 * - Agent workload queries
 * - Priority and urgent ticket queries
 * - Customer support history
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
        // Support Tickets table indexes
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_status_department', 'status, department_id');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_status_employee', 'status, employee_id');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_priority_created', 'priority, created_at');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_customer_id', 'customer_id');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_department_id', 'department_id');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_employee_id', 'employee_id');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_status', 'status');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_priority', 'priority');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_request_type', 'request_type');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_created_at', 'created_at');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_updated_at', 'updated_at');
        $this->addIndexIfNeeded('support_tickets', 'idx_support_tickets_follow_up_date', 'follow_up_date');

        // Support Ticket Conversations indexes
        $this->addIndexIfNeeded('support_ticket_convs', 'idx_support_ticket_convs_ticket_id', 'support_ticket_id');
        $this->addIndexIfNeeded('support_ticket_convs', 'idx_support_ticket_convs_customer_id', 'customer_id');
        $this->addIndexIfNeeded('support_ticket_convs', 'idx_support_ticket_convs_created_at', 'created_at');

        // Support Ticket Department Employee indexes
        $this->addIndexIfNeeded('support_ticket_department_employee', 'idx_support_dept_emp_department_id', 'department_id');
        $this->addIndexIfNeeded('support_ticket_department_employee', 'idx_support_dept_emp_employee_id', 'employee_id');

        // Support Ticket Status Master indexes
        $this->addIndexIfNeeded('support_ticket_status_master', 'idx_support_status_master_type', 'type');
        $this->addIndexIfNeeded('support_ticket_status_master', 'idx_support_status_master_status', 'status');

        // Support Tickets Notifications indexes
        $this->addIndexIfNeeded('support_tickets_notification', 'idx_support_notifications_ticket_id', 'ticket_id');
        $this->addIndexIfNeeded('support_tickets_notification', 'idx_support_notifications_employee_id', 'employee_id');
        $this->addIndexIfNeeded('support_tickets_notification', 'idx_support_notifications_is_read', 'is_read');
        $this->addIndexIfNeeded('support_tickets_notification', 'idx_support_notifications_created_at', 'created_at');
    }

    public function down(): void
    {
        $indexes = [
            'support_tickets' => [
                'idx_support_tickets_status_department',
                'idx_support_tickets_status_employee',
                'idx_support_tickets_priority_created',
                'idx_support_tickets_customer_id',
                'idx_support_tickets_department_id',
                'idx_support_tickets_employee_id',
                'idx_support_tickets_status',
                'idx_support_tickets_priority',
                'idx_support_tickets_request_type',
                'idx_support_tickets_created_at',
                'idx_support_tickets_updated_at',
                'idx_support_tickets_follow_up_date',
            ],
            'support_ticket_convs' => [
                'idx_support_ticket_convs_ticket_id',
                'idx_support_ticket_convs_customer_id',
                'idx_support_ticket_convs_created_at',
            ],
            'support_ticket_department_employee' => [
                'idx_support_dept_emp_department_id',
                'idx_support_dept_emp_employee_id',
            ],
            'support_ticket_status_master' => [
                'idx_support_status_master_type',
                'idx_support_status_master_status',
            ],
            'support_tickets_notification' => [
                'idx_support_notifications_ticket_id',
                'idx_support_notifications_employee_id',
                'idx_support_notifications_is_read',
                'idx_support_notifications_created_at',
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
