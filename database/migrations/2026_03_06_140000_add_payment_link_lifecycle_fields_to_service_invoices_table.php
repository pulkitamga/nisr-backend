<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_invoices')) {
            return;
        }

        Schema::table('service_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('service_invoices', 'payment_link_expires_at')) {
                $table->timestamp('payment_link_expires_at')->nullable()->after('payment_link');
            }
            if (!Schema::hasColumn('service_invoices', 'gateway_payment_method')) {
                $table->string('gateway_payment_method')->nullable()->after('payment_link_expires_at');
            }
            if (!Schema::hasColumn('service_invoices', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('gateway_payment_method');
            }
            if (!Schema::hasColumn('service_invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('gateway_transaction_id');
            }
        });

        try {
            Schema::table('service_invoices', function (Blueprint $table) {
                $table->index(['payment_status', 'payment_link_expires_at'], 'service_invoices_status_expires_at_idx');
            });
        } catch (\Throwable) {
            // Index may already exist in some environments.
        }
    }

    public function down(): void
    {
        // Intentionally no destructive rollback to avoid dropping production columns accidentally.
    }
};
