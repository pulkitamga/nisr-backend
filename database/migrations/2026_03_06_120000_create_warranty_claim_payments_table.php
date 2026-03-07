<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranty_claim_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_claim_id')->constrained('warranty_claims')->onDelete('cascade');
            $table->string('payment_channel', 50); // pos, cod, online_link, waive
            $table->string('payment_status', 50)->default('pending'); // pending, paid, waived, pending_cod, failed, rejected
            $table->decimal('amount', 12, 2)->default(0);
            $table->json('charge_ids')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_link')->nullable();
            $table->string('payment_link_token')->nullable()->unique();
            $table->timestamp('payment_link_expires_at')->nullable();
            $table->string('gateway_payment_method')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['warranty_claim_id', 'payment_status']);
            $table->index(['payment_channel', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claim_payments');
    }
};
