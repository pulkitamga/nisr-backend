<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BusinessPolicyConfigTest extends TestCase
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

    public function test_json_policy_status_takes_precedence_over_stale_is_active_flag(): void
    {
        DB::table('business_settings')->insert([
            'type' => 'shipping-policy',
            'value' => json_encode([
                'status' => 0,
                'content' => '<p>Shipping policy</p>',
            ]),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policy = getBusinessPolicyConfig('shipping-policy');

        $this->assertSame(0, $policy['status']);
        $this->assertSame('<p>Shipping policy</p>', $policy['content']);
    }

    public function test_string_policy_uses_is_active_when_json_status_is_not_available(): void
    {
        DB::table('business_settings')->insert([
            'type' => 'service_policy',
            'value' => '<p>Service policy</p>',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policy = getBusinessPolicyConfig('service_policy', false);

        $this->assertSame(1, $policy['status']);
        $this->assertSame('<p>Service policy</p>', $policy['content']);
    }
}
