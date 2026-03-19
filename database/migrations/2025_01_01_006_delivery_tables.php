<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DELIVERY: Delivery management tables
 *
 * Creates tables for delivery men, delivery history,
 * and delivery-related operations.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->allTablesExist([
            'delivery_men',
            'deliveryman_wallets',
            'delivery_man_transactions',
            'delivery_histories',
            'deliveryman_notifications',
            'shipping_methods',
            'shipping_types',
            'shipping_method_areas',
            'category_shipping_costs',
        ])) {
            return;
        }

        // Delivery Men
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('f_name');
            $table->string('l_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('identity_type')->nullable();
            $table->text('identity_images')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('zone_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('fcm_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(true);
            $table->integer('earning')->default(0);
            $table->integer('order_count')->default(0);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
        });

        // Delivery Man Wallets
        Schema::create('deliveryman_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->decimal('balance', 24, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Delivery Man Transactions
        Schema::create('delivery_man_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->decimal('amount', 24, 2);
            $table->string('transaction_type'); // credit, debit
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // Delivery Histories
        Schema::create('delivery_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('delivery_man_id')->nullable()->constrained('delivery_men')->onDelete('set null');
            $table->string('delivery_status');
            $table->timestamp('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Deliveryman Notifications
        Schema::create('deliveryman_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });

        // Shipping Methods
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('cost', 24, 2)->default(0);
            $table->integer('duration')->nullable();
            $table->string('duration_type')->nullable(); // day, hour
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Shipping Types
        Schema::create('shipping_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Shipping Method Areas (pivot)
        Schema::create('shipping_method_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->onDelete('cascade');
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->decimal('cost', 24, 2)->default(0);
            $table->timestamps();
        });

        // Category Shipping Costs
        Schema::create('category_shipping_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->decimal('cost', 24, 2)->default(0);
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
