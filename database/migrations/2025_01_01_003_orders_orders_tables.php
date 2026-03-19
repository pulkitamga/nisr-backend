<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ORDERS: Order management tables
 *
 * Creates all tables related to orders, order details,
 * payments, shipping, refunds, and order tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->allTablesExist([
            'orders',
            'order_details',
            'order_status_histories',
            'order_transactions',
            'order_expected_delivery_histories',
            'order_delivery_verifications',
            'shipping_addresses',
            'billing_addresses',
            'coupons',
            'offline_payment_methods',
            'offline_payments',
            'refund_requests',
            'refund_statuses',
            'refund_transactions',
            'carts',
            'cart_shippings',
            'wallet_transactions',
            'customer_wallets',
            'customer_wallet_histories',
            'loyalty_point_transactions',
            'paytabs_invoices',
            'add_fund_bonus_categories',
        ])) {
            return;
        }

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->foreignId('seller_id')->nullable();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->onDelete('set null');
            $table->string('seller_name')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_is')->default('admin');
            $table->string('order_type')->default('default'); // default, pos, wholesale
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'returned', 'failed', 'canceled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->foreignId('offline_payment_id')->nullable()->constrained('offline_payments')->onDelete('set null');
            $table->string('transaction_ref')->nullable();
            $table->decimal('total_tax_amount', 24, 2)->default(0);
            $table->decimal('total_discount', 24, 2)->default(0);
            $table->decimal('total_shipping_cost', 24, 2)->default(0);
            $table->decimal('total_additional_discount', 24, 2)->default(0);
            $table->decimal('subtotal', 24, 2)->default(0);
            $table->decimal('total_coupon_discount', 24, 2)->default(0);
            $table->decimal('extra_discount', 24, 2)->default(0);
            $table->decimal('extra_discount_type')->default(0);
            $table->decimal('total_order_amount', 24, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->foreignId('coupon_id')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->foreignId('shipping_address')->nullable()->constrained('shipping_addresses')->onDelete('set null');
            $table->foreignId('billing_address')->nullable()->constrained('billing_addresses')->onDelete('set null');
            $table->string('billing_address_data')->nullable();
            $table->string('shipping_address_data')->nullable();
            $table->foreignId('delivery_man_id')->nullable()->constrained('delivery_men')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('order_note')->nullable();
            $table->boolean('is_guest_checkout')->default(false);
            $table->boolean('is_shipping_with_in_product_price')->default(false);
            $table->integer('free_shipping_progress')->default(0);
            $table->foreignId('currency_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // Order Details
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');
            $table->foreignId('seller_id')->nullable();
            $table->string('seller_name')->nullable();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->onDelete('set null');
            $table->string('product_name')->nullable();
            $table->string('product_thumbnail')->nullable();
            $table->string('variant')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 24, 2)->default(0);
            $table->decimal('tax', 24, 2)->default(0);
            $table->decimal('discount', 24, 2)->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('total_price', 24, 2)->default(0);
            $table->string('delivery_status')->default('pending');
            $table->timestamp('delivery_date')->nullable();
            $table->boolean('is_stock_decreased')->default(false);
            $table->string('shipping_type')->nullable();
            $table->foreignId('delivery_man_id')->nullable()->constrained('delivery_men')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->enum('shipping_method', ['standard', 'express'])->default('standard');
            $table->decimal('shipping_cost', 24, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->boolean('is_refunded')->default(false);
            $table->text('refund_reason')->nullable();
            $table->foreignId('refund_request_id')->nullable()->constrained('refund_requests')->onDelete('set null');
            $table->timestamps();
        });

        // Order Status History
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('changed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Order Transactions
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 24, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->text('payment_response')->nullable();
            $table->timestamps();
        });

        // Order Expected Delivery History
        Schema::create('order_expected_delivery_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('order_detail_id')->nullable()->constrained('order_details')->onDelete('cascade');
            $table->timestamp('expected_delivery_date');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Order Delivery Verification
        Schema::create('order_delivery_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('delivery_man_id')->nullable()->constrained('delivery_men')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('verification_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Shipping Addresses
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('contact_person_name');
            $table->string('phone');
            $table->foreignId('country_id')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->string('zip')->nullable();
            $table->text('address');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_billing')->default(false);
            $table->timestamps();
        });

        // Billing Addresses
        Schema::create('billing_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('contact_person_name');
            $table->string('phone');
            $table->foreignId('country_id')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->string('zip')->nullable();
            $table->text('address');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('discount_type', ['percent', 'flat'])->default('flat');
            $table->decimal('discount', 24, 2)->default(0);
            $table->decimal('min_purchase', 24, 2)->default(0);
            $table->decimal('max_discount', 24, 2)->nullable();
            $table->date('start_date');
            $table->date('expire_date');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->integer('limit')->nullable();
            $table->integer('used')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_free_shipping')->default(false);
            $table->string('coupon_type')->default('default');
            $table->integer('min_order_quantity')->default(0);
            $table->integer('max_discount_usage')->default(1);
            $table->timestamps();
        });

        // Offline Payment Methods
        Schema::create('offline_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Offline Payments
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('method_id')->nullable()->constrained('offline_payment_methods')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('customer_note')->nullable();
            $table->string('admin_note')->nullable();
            $table->string('payment_proof')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Refund Requests
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 24, 2);
            $table->text('reason')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        // Refund Statuses
        Schema::create('refund_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->onDelete('cascade');
            $table->string('status');
            $table->foreignId('changed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Refund Transactions
        Schema::create('refund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('amount', 24, 2);
            $table->string('transaction_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamps();
        });

        // Carts
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');
            $table->string('variant')->nullable();
            $table->integer('quantity')->default(1);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
        });

        // Cart Shipping
        Schema::create('cart_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('shipping_method')->nullable();
            $table->decimal('shipping_cost', 24, 2)->default(0);
            $table->timestamps();
        });

        // Wallet Transactions
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 24, 2);
            $table->string('transaction_type'); // credit, debit
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // Customer Wallets
        Schema::create('customer_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('balance', 24, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Customer Wallet Histories
        Schema::create('customer_wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('customer_wallets')->onDelete('cascade');
            $table->decimal('amount', 24, 2);
            $table->string('transaction_type'); // credit, debit
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // Loyalty Point Transactions
        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->string('transaction_type'); // earn, redeem
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // Paytabs Invoices
        Schema::create('paytabs_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('tran_ref')->nullable();
            $table->string('payment_url')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamps();
        });

        // Add Fund Bonus Categories
        Schema::create('add_fund_bonus_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('min_add_amount', 24, 2)->default(0);
            $table->decimal('max_bonus_amount', 24, 2)->default(0);
            $table->decimal('bonus_percentage', 5, 2)->default(0);
            $table->integer('bonus_type')->default(0); // 0=percentage, 1=fixed
            $table->boolean('status')->default(true);
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
