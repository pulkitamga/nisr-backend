<?php

namespace Tests\Unit;

use App\Contracts\Repositories\AnalyticScriptRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\SocialMediaRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Admin\Settings\BusinessSettingsController;
use App\Http\Requests\Admin\BusinessSettingRequest;
use App\Models\BusinessSetting;
use App\Services\BusinessSettingService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class BusinessModeDefaultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            'type' => 'pnc_language',
            'value' => json_encode(['en', 'ar']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_get_web_config_defaults_business_mode_to_single_when_missing(): void
    {
        $this->assertSame('single', getWebConfig('business_mode'));
    }

    public function test_update_settings_saves_single_business_mode_when_request_omits_it(): void
    {
        Toastr::shouldReceive('success')->once();

        $businessSettingRepo = $this->mock(BusinessSettingRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('updateOrInsert')
                ->with('business_mode', 'single')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('updateOrInsert')
                ->zeroOrMoreTimes()
                ->andReturn(true);
            $mock->shouldReceive('updateWhere')
                ->zeroOrMoreTimes()
                ->andReturn(true);
            $mock->shouldReceive('getFirstWhere')
                ->andReturn(
                    null,
                    null,
                    null,
                    null,
                    null,
                    new BusinessSetting([
                        'value' => json_encode([
                            ['id' => 1, 'name' => 'english', 'direction' => 'ltr', 'code' => 'en', 'status' => 1, 'default' => true],
                            ['id' => 2, 'name' => 'arabic', 'direction' => 'rtl', 'code' => 'ar', 'status' => 1, 'default' => false],
                        ]),
                    ])
                );
        });

        $controller = new BusinessSettingsController(
            businessSettingRepo: $businessSettingRepo,
            analyticScriptRepo: $this->mock(AnalyticScriptRepositoryInterface::class),
            vendorRepo: $this->mock(VendorRepositoryInterface::class),
            deliveryManRepo: $this->mock(DeliveryManRepositoryInterface::class),
            currencyRepo: $this->mock(CurrencyRepositoryInterface::class),
            socialMediaRepo: $this->mock(SocialMediaRepositoryInterface::class),
            businessSettingService: new BusinessSettingService(),
        );

        $request = BusinessSettingRequest::create('/admin/business-settings', 'POST', [
            'basic_lang' => ['en'],
            'text_lang' => ['en'],
            'footer_lang' => ['en'],
            'company_name' => ['Elnisr'],
            'shop_address' => ['Cairo'],
            'company_copyright_text' => ['Copyright 2026'],
            'footer_description_text' => ['Footer text'],
            'company_email' => 'info@example.com',
            'company_phone' => '16870',
            'language' => 'en',
            'timezone' => 'Africa/Cairo',
            'phone_verification' => 0,
            'email_verification' => 0,
            'decimal_point_settings' => 2,
            'currency_symbol_position' => 'left',
            'currency_symbol_space' => '0',
            'country_code' => 'EG',
            'currency_id' => 1,
            'primary' => '#000000',
            'secondary' => '#ffffff',
            'primary_light' => '#CFDFFB',
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'app_store_download_url' => 'https://example.com/ios',
            'play_store_download_url' => 'https://example.com/android',
            'pagination_limit' => 25,
        ]);

        $response = $controller->updateSettings($request, new BusinessSettingService());

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
