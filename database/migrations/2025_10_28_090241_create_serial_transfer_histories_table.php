<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_transfer_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            // ✅ Corrected table name to plural
            $table->foreignId('wholesale_delivery_id')
                ->nullable()
                ->constrained('wholesale_order_delivery')
                ->onDelete('cascade');

            $table->string('serial_number');
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->enum('transfer_type', ['branch_to_branch', 'branch_to_wholesale']);
            $table->timestamp('transferred_at');
            $table->timestamps();

            // ✅ Give shorter custom names to avoid MySQL 64-char limit
            $table->unique(['serial_number', 'stock_transfer_id'], 'uniq_serial_stock');
            $table->unique(['serial_number', 'wholesale_delivery_id'], 'uniq_serial_delivery');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_transfer_histories');
    }
};
