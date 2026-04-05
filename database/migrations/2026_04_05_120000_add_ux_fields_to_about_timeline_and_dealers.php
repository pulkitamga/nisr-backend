<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_timeline_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('about_timeline_sections', 'label')) {
                $table->string('label')->nullable()->after('year');
            }
        });

        Schema::table('about_dealer_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('about_dealer_sections', 'partner_type')) {
                $table->string('partner_type')->nullable()->after('dealer_name');
            }

            if (!Schema::hasColumn('about_dealer_sections', 'coverage_area')) {
                $table->string('coverage_area')->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('about_timeline_sections', function (Blueprint $table) {
            if (Schema::hasColumn('about_timeline_sections', 'label')) {
                $table->dropColumn('label');
            }
        });

        Schema::table('about_dealer_sections', function (Blueprint $table) {
            if (Schema::hasColumn('about_dealer_sections', 'partner_type')) {
                $table->dropColumn('partner_type');
            }

            if (Schema::hasColumn('about_dealer_sections', 'coverage_area')) {
                $table->dropColumn('coverage_area');
            }
        });
    }
};
