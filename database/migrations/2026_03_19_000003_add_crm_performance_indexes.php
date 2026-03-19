<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes to CRM tables
 *
 * Optimizes:
 * - Lead assignment queries by status and owner
 * - Deal pipeline queries by stage
 * - Activity timeline queries
 * - Inbox message queries by status and owner
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfTableColumnsExist('leads', ['status', 'owner_id'], 'idx_leads_status_owner');
        $this->addIndexIfTableColumnsExist('leads', ['status', 'employee_id'], 'idx_leads_status_employee');
        $this->addIndexIfTableColumnsExist('leads', ['department_id', 'status'], 'idx_leads_department_status');
        $this->addIndexIfTableColumnsExist('leads', ['company_id'], 'idx_leads_company');
        $this->addIndexIfTableColumnsExist('leads', ['contact_id'], 'idx_leads_contact');
        $this->addIndexIfTableColumnsExist('leads', ['utm_source'], 'idx_leads_utm_source');
        $this->addIndexIfTableColumnsExist('leads', ['utm_campaign'], 'idx_leads_utm_campaign');

        $this->addIndexIfTableColumnsExist('deals', ['stage', 'created_at'], 'idx_deals_stage_created');
        $this->addIndexIfTableColumnsExist('deals', ['stage', 'owner_id'], 'idx_deals_stage_owner');
        $this->addIndexIfTableColumnsExist('deals', ['status'], 'idx_deals_status');
        $this->addIndexIfTableColumnsExist('deals', ['lead_id'], 'idx_deals_lead');
        $this->addIndexIfTableColumnsExist('deals', ['priority', 'status'], 'idx_deals_priority_status');

        $this->addIndexIfTableColumnsExist('inbox_messages', ['status', 'owner_id'], 'idx_inbox_status_owner');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['department_id', 'status'], 'idx_inbox_department_status');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['employee_id', 'status'], 'idx_inbox_employee_status');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['message_type'], 'idx_inbox_message_type');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['pipeline'], 'idx_inbox_pipeline');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['related_lead_id'], 'idx_inbox_related_lead');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['related_ticket_id'], 'idx_inbox_related_ticket');
        $this->addIndexIfTableColumnsExist('inbox_messages', ['related_warranty_id'], 'idx_inbox_related_warranty');

        $this->addIndexIfTableColumnsExist('lead_activity', ['lead_id', 'created_at'], 'idx_lead_activity_timeline');
        $this->addIndexIfTableColumnsExist('deal_activities', ['deal_id', 'created_at'], 'idx_deal_activities_timeline');
        $this->addIndexIfTableColumnsExist('lead_call', ['lead_id', 'created_at'], 'idx_lead_calls_timeline');
        $this->addIndexIfTableColumnsExist('deal_calls', ['deal_id', 'created_at'], 'idx_deal_calls_timeline');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('leads', 'idx_leads_status_owner');
        $this->dropIndexIfExists('leads', 'idx_leads_status_employee');
        $this->dropIndexIfExists('leads', 'idx_leads_department_status');
        $this->dropIndexIfExists('leads', 'idx_leads_company');
        $this->dropIndexIfExists('leads', 'idx_leads_contact');
        $this->dropIndexIfExists('leads', 'idx_leads_utm_source');
        $this->dropIndexIfExists('leads', 'idx_leads_utm_campaign');

        $this->dropIndexIfExists('deals', 'idx_deals_stage_created');
        $this->dropIndexIfExists('deals', 'idx_deals_stage_owner');
        $this->dropIndexIfExists('deals', 'idx_deals_status');
        $this->dropIndexIfExists('deals', 'idx_deals_lead');
        $this->dropIndexIfExists('deals', 'idx_deals_priority_status');

        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_status_owner');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_department_status');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_employee_status');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_message_type');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_pipeline');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_related_lead');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_related_ticket');
        $this->dropIndexIfExists('inbox_messages', 'idx_inbox_related_warranty');

        $this->dropIndexIfExists('lead_activity', 'idx_lead_activity_timeline');
        $this->dropIndexIfExists('deal_activities', 'idx_deal_activities_timeline');
        $this->dropIndexIfExists('lead_call', 'idx_lead_calls_timeline');
        $this->dropIndexIfExists('deal_calls', 'idx_deal_calls_timeline');
    }

    private function addIndexIfTableColumnsExist(string $tableName, array $columns, string $indexName): void
    {
        if (
            !Schema::hasTable($tableName)
            || !$this->tableHasColumns($tableName, $columns)
            || $this->indexExists($tableName, $indexName)
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || !$this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function tableHasColumns(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $indexes = DB::select("SHOW INDEX FROM `{$tableName}`");

        foreach ($indexes as $index) {
            if (($index->Key_name ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
