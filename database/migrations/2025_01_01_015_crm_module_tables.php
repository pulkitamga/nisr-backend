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
        if ($this->allTablesExist([
            'leads',
            'deals',
            'lead_activity',
            'crm_calls',
            'lead_note',
            'lead_task',
            'lead_call',
            'lead_file',
            'inbox_messages',
            'inbox_activities',
            'inbox_calls',
            'inbox_tasks',
            'inbox_notes',
            'inbox_files',
            'inbox_suggestions',
            'deal_activities',
            'deal_calls',
            'deal_notes',
            'deal_tasks',
            'deal_files',
        ])) {
            return;
        }

        // Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['wholesale', 'retail', 'service']);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->enum('status', ['new', 'working', 'qualified', 'disqualified', 'converted'])->default('new');
            $table->enum('escalation_level', ['none', 'l1', 'l2'])->default('none');
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedBigInteger('escalated_by')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->integer('reopen_count')->default(0);
            $table->timestamp('sla_paused_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Deals
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->enum('related_party_type', ['company', 'contact']);
            $table->unsignedBigInteger('related_party_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->enum('stage', ['join_request', 'register', 'confirmed_order', 'negotiation', 'closed']);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('low');
            $table->decimal('value', 15, 2)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->string('status', 50)->default('register');
            $table->enum('escalation_level', ['none', 'l1', 'l2'])->default('none');
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedBigInteger('escalated_by')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->integer('reopen_count')->default(0);
            $table->timestamp('sla_paused_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->string('quotation_id')->nullable();
            $table->string('quotation_status')->default('draft');
            $table->string('order_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->softDeletes();
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
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->enum('pipeline', ['email', 'form', 'chat', 'social', 'phone']);
            $table->enum('message_type', ['support', 'service', 'career', 'warranty', 'contact'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('related_lead_id')->nullable();
            $table->unsignedBigInteger('related_ticket_id')->nullable();
            $table->unsignedBigInteger('related_warranty_id')->nullable();
            $table->json('details')->nullable();
            $table->enum('status', ['new', 'processing', 'converted', 'ignored', 'spam'])->default('new');
            $table->enum('escalation_level', ['none', 'l1', 'l2'])->default('none');
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedBigInteger('escalated_by')->nullable();
            $table->double('spam_score', 8, 2)->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->longText('attachment')->nullable();
            $table->longText('reply')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->longText('message')->nullable();
            $table->string('convert_type')->nullable();
            $table->string('convert_sub_type')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->integer('reopen_count')->default(0);
            $table->timestamp('sla_paused_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Inbox Activities
        Schema::create('inbox_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('title');
            $table->string('activity_type')->nullable();
            $table->longText('details')->nullable();
            $table->string('subject');
            $table->date('note_date');
            $table->timestamps();
        });

        // Inbox Calls
        Schema::create('inbox_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('title');
            $table->string('from');
            $table->string('to');
            $table->json('guests')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Inbox Tasks
        Schema::create('inbox_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->enum('status', ['pending', 'complete'])->default('pending');
            $table->timestamps();
        });

        // Inbox Notes
        Schema::create('inbox_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->text('note');
            $table->date('noted_at');
            $table->timestamps();
        });

        // Inbox Files
        Schema::create('inbox_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('file');
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
        // Historical catch-up migration: do not drop live module tables on rollback.
    }

    private function allTablesExist(array $tables): bool
    {
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
};
