<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SERVICE: Service module tables
 *
 * Creates tables for service management, service jobs,
 * estimates, invoices, and service requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->json('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->decimal('base_price', 24, 2)->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('status')->default(true);
            $table->integer('duration')->nullable(); // in minutes
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
        });

        // Service Requests
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->decimal('estimated_cost', 24, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Service Jobs
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('job_number')->unique();
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('description')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('technician_notes')->nullable();
            $table->decimal('labor_cost', 24, 2)->default(0);
            $table->decimal('parts_cost', 24, 2)->default(0);
            $table->decimal('total_cost', 24, 2)->default(0);
            $table->text('completion_notes')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });

        // Service Job Items
        Schema::create('service_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('item_type'); // labor, part, material
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 24, 2)->default(0);
            $table->decimal('total_price', 24, 2)->default(0);
            $table->timestamps();
        });

        // Service Job Activities
        Schema::create('service_job_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('activity_type'); // status_change, note, update
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Service Estimates
        Schema::create('service_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->onDelete('set null');
            $table->foreignId('service_job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->string('estimate_number')->unique();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 24, 2)->default(0);
            $table->decimal('tax', 24, 2)->default(0);
            $table->decimal('total', 24, 2)->default(0);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        // Service Invoices
        Schema::create('service_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('service_job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
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
        });

        // Service Change Orders
        Schema::create('service_change_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('change_order_number')->unique();
            $table->text('reason')->nullable();
            $table->decimal('original_amount', 24, 2)->default(0);
            $table->decimal('additional_amount', 24, 2)->default(0);
            $table->decimal('new_total', 24, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Service Cancellations
        Schema::create('service_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->enum('cancelled_by', ['customer', 'admin', 'system'])->default('customer');
            $table->foreignId('cancelled_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->decimal('refund_amount', 24, 2)->default(0);
            $table->boolean('refund_processed')->default(false);
            $table->timestamp('cancelled_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_cancellations');
        Schema::dropIfExists('service_change_orders');
        Schema::dropIfExists('service_invoices');
        Schema::dropIfExists('service_estimates');
        Schema::dropIfExists('service_job_activities');
        Schema::dropIfExists('service_job_items');
        Schema::dropIfExists('service_jobs');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('services');
    }
};
