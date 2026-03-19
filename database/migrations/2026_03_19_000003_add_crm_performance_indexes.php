<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // Leads table indexes
        Schema::table('leads', function (Blueprint $table) {
            // Composite index for owner's leads by status
            $table->index(['status', 'owner_id'], 'idx_leads_status_owner');

            // Composite index for employee's leads by status
            $table->index(['status', 'employee_id'], 'idx_leads_status_employee');

            // Index for department queries
            $table->index(['department_id', 'status'], 'idx_leads_department_status');

            // Index for company/contact queries
            $table->index('company_id', 'idx_leads_company');
            $table->index('contact_id', 'idx_leads_contact');

            // Index for UTM tracking
            $table->index('utm_source', 'idx_leads_utm_source');
            $table->index('utm_campaign', 'idx_leads_utm_campaign');
        });

        // Deals table indexes
        Schema::table('deals', function (Blueprint $table) {
            // Composite index for pipeline forecasting
            $table->index(['stage', 'created_at'], 'idx_deals_stage_created');

            // Composite index for owner's deals by stage
            $table->index(['stage', 'owner_id'], 'idx_deals_stage_owner');

            // Index for status queries
            $table->index('status', 'idx_deals_status');

            // Index for lead relationship
            $table->index('lead_id', 'idx_deals_lead');

            // Index for priority queries
            $table->index(['priority', 'status'], 'idx_deals_priority_status');
        });

        // Inbox messages indexes
        Schema::table('inbox_messages', function (Blueprint $table) {
            // Composite index for owner's messages by status
            $table->index(['status', 'owner_id'], 'idx_inbox_status_owner');

            // Composite index for department's messages
            $table->index(['department_id', 'status'], 'idx_inbox_department_status');

            // Composite index for employee's messages
            $table->index(['employee_id', 'status'], 'idx_inbox_employee_status');

            // Index for message type
            $table->index('message_type', 'idx_inbox_message_type');

            // Index for pipeline/source
            $table->index('pipeline', 'idx_inbox_pipeline');

            // Index for related entities
            $table->index('related_lead_id', 'idx_inbox_related_lead');
            $table->index('related_ticket_id', 'idx_inbox_related_ticket');
            $table->index('related_warranty_id', 'idx_inbox_related_warranty');
        });

        // Lead activity indexes (if table exists)
        if (Schema::hasTable('lead_activity')) {
            Schema::table('lead_activity', function (Blueprint $table) {
                // Composite index for timeline queries
                $table->index(['lead_id', 'created_at'], 'idx_lead_activity_timeline');
            });
        }

        // Deal activities indexes
        Schema::table('deal_activities', function (Blueprint $table) {
            // Composite index for timeline queries
            $table->index(['deal_id', 'created_at'], 'idx_deal_activities_timeline');
        });

        // Lead calls indexes (if table exists)
        if (Schema::hasTable('lead_call')) {
            Schema::table('lead_call', function (Blueprint $table) {
                $table->index(['lead_id', 'created_at'], 'idx_lead_calls_timeline');
            });
        }

        // Deal calls indexes (if table exists)
        if (Schema::hasTable('deal_calls')) {
            Schema::table('deal_calls', function (Blueprint $table) {
                $table->index(['deal_id', 'created_at'], 'idx_deal_calls_timeline');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('idx_leads_status_owner');
            $table->dropIndex('idx_leads_status_employee');
            $table->dropIndex('idx_leads_department_status');
            $table->dropIndex('idx_leads_company');
            $table->dropIndex('idx_leads_contact');
            $table->dropIndex('idx_leads_utm_source');
            $table->dropIndex('idx_leads_utm_campaign');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('idx_deals_stage_created');
            $table->dropIndex('idx_deals_stage_owner');
            $table->dropIndex('idx_deals_status');
            $table->dropIndex('idx_deals_lead');
            $table->dropIndex('idx_deals_priority_status');
        });

        Schema::table('inbox_messages', function (Blueprint $table) {
            $table->dropIndex('idx_inbox_status_owner');
            $table->dropIndex('idx_inbox_department_status');
            $table->dropIndex('idx_inbox_employee_status');
            $table->dropIndex('idx_inbox_message_type');
            $table->dropIndex('idx_inbox_pipeline');
            $table->dropIndex('idx_inbox_related_lead');
            $table->dropIndex('idx_inbox_related_ticket');
            $table->dropIndex('idx_inbox_related_warranty');
        });

        if (Schema::hasTable('lead_activity')) {
            Schema::table('lead_activity', function (Blueprint $table) {
                $table->dropIndex('idx_lead_activity_timeline');
            });
        }

        Schema::table('deal_activities', function (Blueprint $table) {
            $table->dropIndex('idx_deal_activities_timeline');
        });

        if (Schema::hasTable('lead_call')) {
            Schema::table('lead_call', function (Blueprint $table) {
                $table->dropIndex('idx_lead_calls_timeline');
            });
        }

        if (Schema::hasTable('deal_calls')) {
            Schema::table('deal_calls', function (Blueprint $table) {
                $table->dropIndex('idx_deal_calls_timeline');
            });
        }
    }
};
