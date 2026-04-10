<?php

use App\Models\CmsService;
use App\Models\Translation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cms_products', 'selected_item_ids')) {
            Schema::table('cms_products', function (Blueprint $table) {
                $table->text('selected_item_ids')->nullable()->after('button_text');
            });
        }

        if (!Schema::hasColumn('cms_services', 'selected_item_ids')) {
            Schema::table('cms_services', function (Blueprint $table) {
                $table->text('selected_item_ids')->nullable()->after('button_text');
            });
        }

        $featuredServices = CmsService::query()->firstOrCreate(
            ['type' => 'featured_services'],
            [
                'heading' => 'Featured services',
                'description' => 'Highlight the service offers that deserve priority visibility in the catalogue.',
                'button_text' => null,
                'button_link' => '#service-catalogue',
                'selected_item_ids' => null,
                'is_active' => 1,
            ]
        );

        Translation::query()->updateOrCreate(
            [
                'translationable_type' => CmsService::class,
                'translationable_id' => $featuredServices->id,
                'locale' => 'ar',
                'key' => 'heading',
            ],
            [
                'value' => 'الخدمات المميزة',
            ]
        );

        Translation::query()->updateOrCreate(
            [
                'translationable_type' => CmsService::class,
                'translationable_id' => $featuredServices->id,
                'locale' => 'ar',
                'key' => 'description',
            ],
            [
                'value' => 'سلّط الضوء على عروض الخدمات التي تستحق أولوية الظهور داخل الكتالوج.',
            ]
        );
    }

    public function down(): void
    {
        $featuredServices = CmsService::query()->where('type', 'featured_services')->first();

        if ($featuredServices) {
            Translation::query()
                ->where('translationable_type', CmsService::class)
                ->where('translationable_id', $featuredServices->id)
                ->whereIn('key', ['heading', 'description'])
                ->where('locale', 'ar')
                ->delete();

            $featuredServices->delete();
        }

        if (Schema::hasColumn('cms_products', 'selected_item_ids')) {
            Schema::table('cms_products', function (Blueprint $table) {
                $table->dropColumn('selected_item_ids');
            });
        }

        if (Schema::hasColumn('cms_services', 'selected_item_ids')) {
            Schema::table('cms_services', function (Blueprint $table) {
                $table->dropColumn('selected_item_ids');
            });
        }
    }
};
