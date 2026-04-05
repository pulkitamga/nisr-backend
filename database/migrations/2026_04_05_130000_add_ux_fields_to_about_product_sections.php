<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_product_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('about_product_sections', 'card_label')) {
                $table->string('card_label')->nullable()->after('description');
            }

            if (!Schema::hasColumn('about_product_sections', 'card_note')) {
                $table->string('card_note')->nullable()->after('card_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('about_product_sections', function (Blueprint $table) {
            if (Schema::hasColumn('about_product_sections', 'card_note')) {
                $table->dropColumn('card_note');
            }

            if (Schema::hasColumn('about_product_sections', 'card_label')) {
                $table->dropColumn('card_label');
            }
        });
    }
};
