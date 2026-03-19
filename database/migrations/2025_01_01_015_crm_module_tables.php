<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM: Customer Relationship Management tables
 *
 * Creates tables for leads, deals, activities, calls,
 * notes, tasks, files, and CRM inbox features.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->enum('source', ['website', 'referral', 'social_media', 'email', 'phone', 'event', 'other'])->default('website');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->decimal('estimated_value', 12, 2)->default(0);
            $table->foreignId('currency_id')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to'], 'idx_leads_status_assigned');
            $table->index('company_id', 'idx_leads_company');
            $table->index('contact_id', 'idx_leads_contact');
            $table->index('email', 'idx_leads_email');
        });

        // Deals
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('deal_name');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignId('currency_id')->nullable();
            $table->enum('status', ['prospect', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('prospect');
            $table->string('pipeline_stage')->default('lead');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->date('expected_close_date')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->integer('probability')->default(0);
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable();
            $table->unsignedBigInteger('related_party_id')->nullable();
            $table->string('related_party_type')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_stage', 'expected_close_date'], 'idx_deals_pipeline_date');
            $table->index('status', 'idx_deals_status');
            $table->index(['related_party_id', 'related_party_type'], 'idx_deals_related_party');
        });

        // Lead Activities (calls, emails, meetings, notes)
        Schema::create('lead_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('cascade');
            $table->enum('activity_type', ['call', 'email', 'meeting', 'note', 'task'])->default('note');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('activity_date')->nullable();
            $table->string('outcome')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['lead_id', 'created_at'], 'idx_lead_activities_timeline');
            $table->index('activity_date', 'idx_lead_activities_date');
        });

        // CRM Calls
        Schema::create('crm_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->timestamp('call_date')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])->default('scheduled');
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['lead_id', 'call_date'], 'idx_crm_calls_timeline');
        });

        // Lead Notes
        Schema::create('lead_note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
            $table->text('note');
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Lead Tasks
        Schema::create('lead_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Lead Calls (separate from crm_calls - legacy)
        Schema::create('lead_call', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->timestamp('call_date')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])->default('scheduled');
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['lead_id', 'call_date'], 'idx_lead_calls_timeline');
        });

        // Lead Files
        Schema::create('lead_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Inbox Messages (CRM communication hub)
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['unread', 'read', 'archived'])->default('unread');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Inbox Activities
        Schema::create('inbox_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_message_id')->nullable()->constrained('inbox_messages')->onDelete('cascade');
            $table->string('activity_type');
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Inbox Calls
        Schema::create('inbox_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->timestamp('call_date')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['scheduled', 'completed', 'missed'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Inbox Tasks
        Schema::create('inbox_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Inbox Notes
        Schema::create('inbox_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('note');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Inbox Files
        Schema::create('inbox_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Inbox Suggestions (AI/automated suggestions)
        Schema::create('inbox_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('suggestion_type');
            $table->text('suggestion')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamps();
        });

        // Deal Activities (deal-specific activity tracking)
        Schema::create('deal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->enum('activity_type', ['call', 'email', 'meeting', 'note', 'task'])->default('note');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('activity_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['deal_id', 'created_at'], 'idx_deal_activities_timeline');
        });

        // Deal Calls
        Schema::create('deal_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->timestamp('call_date')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])->default('scheduled');
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Deal Notes
        Schema::create('deal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->text('note');
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Deal Tasks
        Schema::create('deal_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Deal Files
        Schema::create('deal_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_files');
        Schema::dropIfExists('deal_tasks');
        Schema::dropIfExists('deal_notes');
        Schema::dropIfExists('deal_calls');
        Schema::dropIfExists('deal_activities');
        Schema::dropIfExists('inbox_suggestions');
        Schema::dropIfExists('inbox_files');
        Schema::dropIfExists('inbox_notes');
        Schema::dropIfExists('inbox_tasks');
        Schema::dropIfExists('inbox_calls');
        Schema::dropIfExists('inbox_activities');
        Schema::dropIfExists('inbox_messages');
        Schema::dropIfExists('lead_file');
        Schema::dropIfExists('lead_call');
        Schema::dropIfExists('lead_task');
        Schema::dropIfExists('lead_note');
        Schema::dropIfExists('crm_calls');
        Schema::dropIfExists('lead_activity');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('leads');
    }
};
