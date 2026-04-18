<?php

namespace Tests\Unit;

use App\Utils\Helpers;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CurrencyLoadFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        session()->flush();

        Schema::dropIfExists('translations');
        Schema::dropIfExists('currencies');
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

        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->string('code')->nullable();
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->boolean('status')->default(true);
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
        Cache::flush();
        session()->flush();

        Schema::dropIfExists('translations');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_helpers_currency_load_skips_when_default_currency_is_not_configured(): void
    {
        Helpers::currency_load();

        $this->assertNull(session('system_default_currency_info'));
        $this->assertNull(session('currency_code'));
        $this->assertNull(session('currency_symbol'));
        $this->assertNull(session('currency_exchange_rate'));
    }

    public function test_global_load_currency_skips_when_configured_currency_row_is_missing(): void
    {
        DB::table('business_settings')->insert([
            'type' => 'system_default_currency',
            'value' => '999',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        loadCurrency();

        $this->assertNull(session('system_default_currency_info'));
        $this->assertNull(session('currency_code'));
        $this->assertNull(session('currency_symbol'));
        $this->assertNull(session('currency_exchange_rate'));
    }

    public function test_get_currency_code_returns_empty_string_when_default_currency_row_is_missing(): void
    {
        DB::table('business_settings')->insert([
            'type' => 'system_default_currency',
            'value' => '999',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('', getCurrencyCode());
    }
}
