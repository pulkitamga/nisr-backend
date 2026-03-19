<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create service job tables for the service workflow system
 *
 * Creates the missing service job tables while preserving existing
 * services and service_requests tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->allTablesExist([
            'service_jobs',
            'service_job_items',
            'service_job_activities',
            'service_estimates',
            'service_invoices',
            'service_change_orders',
            'service_cancellations',
        ])) {
            return;
        }

        // Service Jobs
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('status')->default('assigned');
            $table->string('service_mode')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('odometer_start')->nullable();
            $table->integer('odometer_end')->nullable();
            $table->string('gps_location')->nullable();
            $table->text('remarks')->nullable();
            $table->json('attachments')->nullable();
            $table->string('priority')->default('normal');
            $table->integer('sla_hours')->default(24);
            $table->string('service_sku')->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->text('customer_signature')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ticket_id', 'status'], 'idx_service_jobs_ticket_status');
            $table->index(['technician_id', 'status'], 'idx_service_jobs_technician_status');
            $table->index(['status', 'scheduled_at'], 'idx_service_jobs_status_scheduled');
            $table->index('service_sku', 'idx_service_jobs_sku');
        });

        // Service Job Items
        Schema::create('service_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('item_type'); // labor, part, material
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 24, 2)->default(0);
            $table->decimal('total', 24, 2)->default(0);
            $table->timestamps();

            $table->index('job_id', 'idx_service_job_items_job');
            $table->index('item_type', 'idx_service_job_items_type');
        });

        // Service Job Activities
        Schema::create('service_job_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('activity_type'); // status_change, note, update, complete_job, start_job, qa_confirmation, etc.
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['job_id', 'created_at'], 'idx_service_job_activities_job_date');
            $table->index('activity_type', 'idx_service_job_activities_type');
        });

        // Service Estimates
        Schema::create('service_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->string('estimate_number')->unique();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->decimal('subtotal', 24, 2)->default(0);
            $table->decimal('extra_charge', 24, 2)->default(0);
            $table->decimal('labor_charge', 24, 2)->default(0);
            $table->decimal('tax', 24, 2)->default(0);
            $table->decimal('total', 24, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'status'], 'idx_service_estimates_ticket_status');
            $table->index('job_id', 'idx_service_estimates_job');
            $table->index('status', 'idx_service_estimates_status');
        });

        // Service Invoices
        Schema::create('service_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('set null');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 24, 2)->default(0);
            $table->decimal('tax', 24, 2)->default(0);
            $table->decimal('discount', 24, 2)->default(0);
            $table->decimal('total', 24, 2)->default(0);
            $table->decimal('paid_amount', 24, 2)->default(0);
            $table->string('payment_link')->nullable();
            $table->string('payment_link_token')->nullable();
            $table->timestamp('payment_link_expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'status'], 'idx_service_invoices_ticket_status');
            $table->index('job_id', 'idx_service_invoices_job');
            $table->index('customer_id', 'idx_service_invoices_customer');
            $table->index('status', 'idx_service_invoices_status');
            $table->index('payment_link_token', 'idx_service_invoices_payment_token');
        });

        // Service Change Orders
        Schema::create('service_change_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('change_order_number')->unique();
            $table->text('reason')->nullable();
            $table->decimal('original_amount', 24, 2)->default(0);
            $table->decimal('additional_amount', 24, 2)->default(0);
            $table->decimal('additional_charges', 24, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'status'], 'idx_service_change_orders_ticket_status');
            $table->index('job_id', 'idx_service_change_orders_job');
            $table->index('status', 'idx_service_change_orders_status');
        });

        // Service Cancellations
        Schema::create('service_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->enum('cancelled_by', ['customer', 'admin', 'system'])->default('customer');
            $table->foreignId('cancelled_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->decimal('refund_amount', 24, 2)->default(0);
            $table->boolean('refund_processed')->default(false);
            $table->timestamp('cancelled_at')->useCurrent();
            $table->timestamps();

            $table->index('ticket_id', 'idx_service_cancellations_ticket');
            $table->index('job_id', 'idx_service_cancellations_job');
        });
    }

    public function down(): void
    {
        // Historical catch-up migration: do not drop live service workflow tables on rollback.
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
