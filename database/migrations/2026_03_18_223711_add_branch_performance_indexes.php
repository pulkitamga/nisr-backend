<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds performance indexes to branches and related pivot tables.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Composite indexes for common query patterns
            // Only create indexes for columns that actually exist in the table

            // Index for active branches by country
            $table->index(['status', 'branch_country'], 'branches_status_country_index');

            // Index for manager's branches
            $table->index(['manager_id', 'status'], 'branches_manager_status_index');

            // Index for vendor/seller branches
            $table->index(['vendor_id', 'status'], 'branches_vendor_status_index');

            // Index for branch listing with status and location
            $table->index(['status', 'branch_state'], 'branches_status_location_index');
        });

        // Add indexes for pivot tables (these are newly created tables with standard structure)
        if (Schema::hasTable('branch_shipping_method_areas')) {
            Schema::table('branch_shipping_method_areas', function (Blueprint $table) {
                $table->index(['branch_id', 'shipping_method_area_id'], 'branch_shipping_area_lookup');
            });
        }

        if (Schema::hasTable('branch_delivery_restrictions')) {
            Schema::table('branch_delivery_restrictions', function (Blueprint $table) {
                $table->index(['branch_id', 'delivery_area_id'], 'branch_delivery_restriction_lookup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('branches_status_country_index');
            $table->dropIndex('branches_manager_status_index');
            $table->dropIndex('branches_vendor_status_index');
            $table->dropIndex('branches_status_location_index');
        });

        if (Schema::hasTable('branch_shipping_method_areas')) {
            Schema::table('branch_shipping_method_areas', function (Blueprint $table) {
                $table->dropIndex('branch_shipping_area_lookup');
            });
        }

        if (Schema::hasTable('branch_delivery_restrictions')) {
            Schema::table('branch_delivery_restrictions', function (Blueprint $table) {
                $table->dropIndex('branch_delivery_restriction_lookup');
            });
        }
    }
};
