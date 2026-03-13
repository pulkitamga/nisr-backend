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
        Schema::create('warranty_claim_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_claim_id')->constrained('warranty_claims')->onDelete('cascade');
            $table->string('charge_type'); // e.g. 'inspection_fee', 'repair_fee'
            $table->decimal('amount', 12, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claim_charges');
    }
};
