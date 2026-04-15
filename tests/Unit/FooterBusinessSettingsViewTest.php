<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FooterBusinessSettingsViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Schema::dropIfExists('flash_deals');
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

        Schema::create('flash_deals', function (Blueprint $table): void {
            $table->id();
            $table->string('deal_type')->nullable();
            $table->integer('status')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
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
                'type' => 'company_phone',
                'value' => '16870',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'company_email',
                'value' => 'info@example.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'company_copyright_text',
                'value' => json_encode(['en' => 'Copyright 2026', 'ar' => 'حقوق الملكية']),
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
        ]);

        session()->put('local', 'en');
        session()->put('locale', 'en');
        session()->put('direction', 'ltr');
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Schema::dropIfExists('flash_deals');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_footer_description_does_not_fall_back_to_about_page_content(): void
    {
        $html = $this->renderFooter([
            'footer_description' => '',
            'about' => (object) ['value' => '<p>About page text should not render in the footer.</p>'],
        ]);

        $this->assertStringNotContainsString('About page text should not render in the footer.', $html);
    }

    public function test_footer_description_is_rendered_from_business_settings_with_auto_direction(): void
    {
        $html = $this->renderFooter();

        $this->assertStringContainsString('English footer text', $html);
        $this->assertStringContainsString('<p class="nisr-ft-tagline" dir="auto">', $html);
    }

    public function test_short_footer_description_is_still_rendered_when_present(): void
    {
        DB::table('business_settings')
            ->where('type', 'footer_description_text')
            ->update([
                'value' => json_encode(['en' => 'Short text', 'ar' => 'نص قصير']),
                'updated_at' => now(),
            ]);
        Cache::flush();

        $html = $this->renderFooter();

        $this->assertStringContainsString('Short text', $html);
    }

    public function test_footer_description_uses_active_locale_instead_of_stale_preloaded_web_config_value(): void
    {
        session()->put('local', 'ar');
        session()->put('locale', 'ar');
        session()->put('direction', 'rtl');
        app()->setLocale('ar');
        Cache::flush();

        $html = $this->renderFooter([
            'footer_description' => 'English stale text',
        ]);

        $this->assertStringContainsString('نص الفوتر العربي', $html);
        $this->assertStringNotContainsString('English stale text', $html);
    }

    private function renderFooter(array $overrides = []): string
    {
        $webConfig = array_merge([
            'business_pages' => collect(),
            'footer_logo' => null,
            'company_name' => 'Elnisr',
            'footer_description' => '',
            'ios' => ['status' => 0, 'link' => ''],
            'android' => ['status' => 0, 'link' => ''],
            'about' => (object) ['value' => ''],
            'social_media' => null,
            'cookie_setting' => null,
            'phone' => '16870',
        ], $overrides);

        return View::file(
            resource_path('themes/default/layouts/front-end/partials/_footer.blade.php'),
            ['web_config' => $webConfig]
        )->render();
    }
}
