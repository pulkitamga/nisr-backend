<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductTranslationLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('translations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('added_by')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('product_type')->nullable();
            $table->integer('status')->nullable();
            $table->integer('request_status')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->integer('rating')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_product_uses_active_locale_translation_on_web_requests(): void
    {
        Product::query()->create([
            'name' => 'Battery Service',
            'slug' => 'battery-service',
            'product_type' => 'services',
            'status' => 1,
            'request_status' => 1,
            'details' => 'English details',
        ]);

        \DB::table('translations')->insert([
            [
                'translationable_type' => Product::class,
                'translationable_id' => 1,
                'locale' => 'en',
                'key' => 'name',
                'value' => 'Full Service',
            ],
            [
                'translationable_type' => Product::class,
                'translationable_id' => 1,
                'locale' => 'en',
                'key' => 'description',
                'value' => 'English service details',
            ],
            [
                'translationable_type' => Product::class,
                'translationable_id' => 1,
                'locale' => 'ar',
                'key' => 'name',
                'value' => 'صيانة شاملة',
            ],
            [
                'translationable_type' => Product::class,
                'translationable_id' => 1,
                'locale' => 'ar',
                'key' => 'description',
                'value' => 'تفاصيل عربية',
            ],
        ]);

        $product = Product::query()->firstOrFail();

        App::setLocale('ar');
        $this->assertSame('صيانة شاملة', $product->name);
        $this->assertSame('تفاصيل عربية', $product->details);

        App::setLocale('en');
        $this->assertSame('Full Service', $product->name);
        $this->assertSame('English service details', $product->details);
    }
}
