<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes to delivery tables
 *
 * Optimizes:
 * - Finding available delivery men
 * - Delivery man's active orders
 * - Wallet and transaction queries
 * - Notification queries
 */
return new class extends Migration
{
    public function up(): void
    {
        // Delivery Men table indexes
        Schema::table('delivery_men', function (Blueprint $table) {
            // Composite index for finding available delivery men
            $table->index(['is_active', 'is_online'], 'idx_delivery_men_available');

            // Index for seller relationship
            $table->index('seller_id', 'idx_delivery_men_seller');

            // Index for auth token
            $table->index('auth_token', 'idx_delivery_men_auth_token');

            // Index for FCM token
            $table->index('fcm_token', 'idx_delivery_men_fcm_token');
        });

        // Delivery Histories indexes
        Schema::table('delivery_histories', function (Blueprint $table) {
            // Composite index for delivery man's active orders
            $table->index(['deliveryman_id', 'time'], 'idx_delivery_histories_man_time');

            // Index for order lookups
            $table->index('order_id', 'idx_delivery_histories_order');

            // Index for delivery status/location queries
            $table->index(['deliveryman_id', 'time'], 'idx_delivery_histories_tracking');
        });

        // Delivery Man Transactions indexes
        Schema::table('delivery_man_transactions', function (Blueprint $table) {
            // Composite index for delivery man's transaction history
            $table->index(['delivery_man_id', 'created_at'], 'idx_transactions_man_date');

            // Index for user lookups
            $table->index(['user_id', 'user_type'], 'idx_transactions_user');

            // Index for transaction ID lookups
            $table->index('transaction_id', 'idx_transactions_id');

            // Index for transaction type
            $table->index('transaction_type', 'idx_transactions_type');
        });

        // Delivery Man Wallets indexes
        Schema::table('deliveryman_wallets', function (Blueprint $table) {
            // Unique constraint already exists on delivery_man_id
            // Add index for balance queries
            $table->index('delivery_man_id', 'idx_wallets_delivery_man');
        });

        // Delivery Man Notifications indexes
        Schema::table('deliveryman_notifications', function (Blueprint $table) {
            // Composite index for unread notifications
            $table->index(['delivery_man_id', 'order_id'], 'idx_notifications_man_order');

            // Index for order-related notifications
            $table->index('order_id', 'idx_notifications_order');
        });

        // Delivery Areas indexes
        Schema::table('delivery_areas', function (Blueprint $table) {
            // Index for area name searches
            $table->index('area', 'idx_delivery_areas_name');
        });

        // Shipping Methods indexes
        Schema::table('shipping_methods', function (Blueprint $table) {
            // Index for active shipping methods
            $table->index(['status', 'creator_type'], 'idx_shipping_methods_status');

            // Index for creator lookups
            $table->index(['creator_id', 'creator_type'], 'idx_shipping_methods_creator');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_men_available');
            $table->dropIndex('idx_delivery_men_seller');
            $table->dropIndex('idx_delivery_men_auth_token');
            $table->dropIndex('idx_delivery_men_fcm_token');
        });

        Schema::table('delivery_histories', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_histories_man_time');
            $table->dropIndex('idx_delivery_histories_order');
            $table->dropIndex('idx_delivery_histories_tracking');
        });

        Schema::table('delivery_man_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_man_date');
            $table->dropIndex('idx_transactions_user');
            $table->dropIndex('idx_transactions_id');
            $table->dropIndex('idx_transactions_type');
        });

        Schema::table('deliveryman_wallets', function (Blueprint $table) {
            $table->dropIndex('idx_wallets_delivery_man');
        });

        Schema::table('deliveryman_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_man_order');
            $table->dropIndex('idx_notifications_order');
        });

        Schema::table('delivery_areas', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_areas_name');
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropIndex('idx_shipping_methods_status');
            $table->dropIndex('idx_shipping_methods_creator');
        });
    }
};
