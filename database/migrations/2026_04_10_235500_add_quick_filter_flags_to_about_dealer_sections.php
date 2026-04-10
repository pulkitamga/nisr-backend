<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_dealer_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('about_dealer_sections', 'show_partner_type_filter')) {
                $table->boolean('show_partner_type_filter')->default(false)->after('partner_type');
            }

            if (!Schema::hasColumn('about_dealer_sections', 'show_location_filter')) {
                $table->boolean('show_location_filter')->default(false)->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('about_dealer_sections', function (Blueprint $table) {
            if (Schema::hasColumn('about_dealer_sections', 'show_partner_type_filter')) {
                $table->dropColumn('show_partner_type_filter');
            }

            if (Schema::hasColumn('about_dealer_sections', 'show_location_filter')) {
                $table->dropColumn('show_location_filter');
            }
        });
    }
};
