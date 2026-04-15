<?php

namespace Tests\Unit;

use App\Models\BusinessSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class BusinessSettingLocaleRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->integer('item_index')->nullable();
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            [
                'type' => 'pnc_language',
                'value' => json_encode(['en', 'ar']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'language',
                'value' => json_encode([
                    ['id' => 1, 'name' => 'english', 'direction' => 'ltr', 'code' => 'en', 'status' => 1, 'default' => true],
                    ['id' => 2, 'name' => 'arabic', 'direction' => 'rtl', 'code' => 'ar', 'status' => 1, 'default' => false],
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'footer_description_text',
                'value' => json_encode(['en' => 'English footer text', 'ar' => 'نص الفوتر العربي']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'company_reliability',
                'value' => json_encode([
                    ['item' => 'delivery_info', 'title' => 'Fast Delivery all across the country', 'image' => '', 'status' => '1'],
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $setting = BusinessSetting::query()->where('type', 'company_reliability')->firstOrFail();
        DB::table('translations')->insert([
            'translationable_type' => BusinessSetting::class,
            'translationable_id' => $setting->id,
            'locale' => 'ar',
            'key' => 'title',
            'value' => 'توصيل سريع في جميع أنحاء البلاد',
            'item_index' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_language_wise_business_config_uses_active_locale_when_session_locale_is_missing(): void
    {
        session()->forget(['local', 'locale']);
        app()->setLocale('ar');

        $this->assertSame('نص الفوتر العربي', getWebConfig('footer_description_text'));
    }

    public function test_company_reliability_partial_renders_database_translation_for_active_locale(): void
    {
        session()->forget(['local', 'locale']);
        session()->put('direction', 'rtl');
        app()->setLocale('ar');

        $html = View::file(
            resource_path('themes/default/web-views/partials/_company-reliability.blade.php')
        )->render();

        $this->assertStringContainsString('توصيل سريع في جميع أنحاء البلاد', $html);
        $this->assertStringNotContainsString('Fast Delivery all across the country', $html);
    }
}
