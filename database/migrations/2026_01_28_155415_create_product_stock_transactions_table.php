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
        Schema::create('product_stock_transactions', function (Blueprint $table) {
            $table->id();

            // Which stock this transaction is related to
            $table->foreignId('product_stock_id')->constrained('product_stocks')->cascadeOnDelete();

            // Stock movement type: IN = added, OUT = removed/sold, TRANSFER = branch transfer
            $table->enum('type', ['IN', 'OUT', 'TRANSFER']);

            // Quantity of stock moved
            $table->integer('quantity');

            // Reason for the stock change (e.g., initial stock, manual update, etc.)
            $table->string('reason')->nullable();

            // Branch where stock is coming from (for transfer)
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Branch where stock is going to (for transfer or initial stock)
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Optional notes
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_transactions');
    }
};
