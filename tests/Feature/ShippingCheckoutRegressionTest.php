<?php

namespace Tests\Feature;

use App\Http\Controllers\Customer\SystemController;
use App\Repositories\ShippingTypeRepository;
use App\Models\ShippingType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class ShippingCheckoutRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'cache.default' => 'array',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('shipping_types');
        Schema::dropIfExists('states');
        Schema::dropIfExists('translations');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('shipping_types', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('shipping_type')->nullable();
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('states');
        Schema::dropIfExists('shipping_types');
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_shipping_type_update_clears_shipping_and_delivery_restriction_caches(): void
    {
        $shippingTypeId = DB::table('shipping_types')->insertGetId([
            'seller_id' => 0,
            'shipping_type' => 'order_wise',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::put(CACHE_FOR_IN_HOUSE_SHIPPING_TYPE, 'stale-shipping-type');
        Cache::put(CACHE_DELIVERY_RESTRICTION_SETUP, ['single_country_mode' => true]);

        $repository = new ShippingTypeRepository(new ShippingType());
        $updated = $repository->update((string)$shippingTypeId, ['shipping_type' => 'product_wise']);

        $this->assertTrue($updated);
        $this->assertFalse(Cache::has(CACHE_FOR_IN_HOUSE_SHIPPING_TYPE));
        $this->assertFalse(Cache::has(CACHE_DELIVERY_RESTRICTION_SETUP));
        $this->assertSame(
            'product_wise',
            DB::table('shipping_types')->where('id', $shippingTypeId)->value('shipping_type')
        );
    }

    public function test_billing_normalization_clears_hidden_location_fields_and_auto_selects_single_country(): void
    {
        $this->setBusinessSetting('delivery_country_restriction', 0);
        $this->setBusinessSetting('delivery_state_restriction', 0);
        $this->setBusinessSetting('delivery_city_restriction', 0);
        $this->setBusinessSetting('delivery_area_restriction', 0);

        DB::table('states')->insert([
            'name' => 'Alexandria',
            'country' => 'EG',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new SystemController();
        $method = new ReflectionMethod(SystemController::class, 'normalizeBillingCheckoutData');
        $method->setAccessible(true);

        $normalized = $method->invoke($controller, [
            'billing_country' => null,
            'billing_state' => 'Alexandria',
            'billing_state_id' => 10,
            'billing_city' => 'Montaza',
            'billing_city_id' => 20,
            'billing_area' => 'Sidi Bishr',
            'billing_area_id' => 30,
        ]);

        $this->assertSame('EG', $normalized['billing_country']);
        $this->assertNull($normalized['billing_state']);
        $this->assertNull($normalized['billing_state_id']);
        $this->assertNull($normalized['billing_city']);
        $this->assertNull($normalized['billing_city_id']);
        $this->assertNull($normalized['billing_area']);
        $this->assertNull($normalized['billing_area_id']);
    }

    private function setBusinessSetting(string $type, mixed $value): void
    {
        DB::table('business_settings')->updateOrInsert(
            ['type' => $type],
            [
                'value' => is_scalar($value) ? (string)$value : json_encode($value),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
