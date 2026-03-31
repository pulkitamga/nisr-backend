<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Http\Requests\ProductAddRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductEnglishValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Cache::put('pnc_language', ['en', 'ar']);
        Cache::put('product_brand', false);
    }

    protected function tearDown(): void
    {
        Cache::forget('pnc_language');
        Cache::forget('product_brand');
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_add_request_rejects_blank_english_name_and_description(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validPayload([
            'name' => ['', 'بطارية'],
            'description' => ['<p><br></p>', '<p>وصف عربي</p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required') . '!',
            $validator->errors()->first('name.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('description.0')
        );
    }

    public function test_update_request_rejects_blank_english_name_and_description(): void
    {
        $productRepository = $this->mock(ProductRepositoryInterface::class);
        $product = new class extends Model {
            protected $table = 'products';
            public $timestamps = false;
            protected $guarded = [];
        };
        $product->forceFill([
            'id' => 8,
            'images' => '["product.webp"]',
            'color_image' => null,
        ]);
        $product->setRelation('digitalVariation', collect());

        $productRepository->shouldReceive('getFirstWhere')
            ->andReturn($product);

        $request = new ProductUpdateRequest($productRepository);
        $request->initialize($this->validPayload([
            'name' => ['', 'بطارية'],
            'description' => ['<p><br></p>', '<p>وصف عربي</p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required') . '!',
            $validator->errors()->first('name.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('description.0')
        );
    }

    public function test_add_request_rejects_blank_english_name_and_description_when_english_is_second(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validPayload([
            'lang' => ['ar', 'en'],
            'name' => ['بطارية', ''],
            'description' => ['<p>وصف عربي</p>', '<p><br></p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required') . '!',
            $validator->errors()->first('name.1')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('description.1')
        );
    }

    public function test_update_request_rejects_blank_english_name_and_description_when_english_is_second(): void
    {
        $productRepository = $this->mock(ProductRepositoryInterface::class);
        $product = new class extends Model {
            protected $table = 'products';
            public $timestamps = false;
            protected $guarded = [];
        };
        $product->forceFill([
            'id' => 8,
            'images' => '["product.webp"]',
            'color_image' => null,
        ]);
        $product->setRelation('digitalVariation', collect());

        $productRepository->shouldReceive('getFirstWhere')
            ->andReturn($product);

        $request = new ProductUpdateRequest($productRepository);
        $request->initialize($this->validPayload([
            'lang' => ['ar', 'en'],
            'name' => ['بطارية', ''],
            'description' => ['<p>وصف عربي</p>', '<p><br></p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required') . '!',
            $validator->errors()->first('name.1')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('description.1')
        );
    }

    public function test_add_request_uses_service_fields_for_service_products(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validServicePayload([
            'name' => ['', ''],
            'description' => ['<p><br></p>', '<p><br></p>'],
            'service_tittle' => ['', 'عنوان الخدمة'],
            'parts_included' => ['', 'القطع المشمولة'],
            'service_description' => ['<p><br></p>', '<p>وصف الخدمة</p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->errors()->has('name.0'));
        $this->assertFalse($validator->errors()->has('description.0'));
        $this->assertSame(
            translate('The_title_in_english_is_required') . '!',
            $validator->errors()->first('service_tittle.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('parts_included.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('service_description.0')
        );
    }

    public function test_update_request_uses_service_fields_for_service_products(): void
    {
        $productRepository = $this->mock(ProductRepositoryInterface::class);
        $product = new class extends Model {
            protected $table = 'products';
            public $timestamps = false;
            protected $guarded = [];
        };
        $product->forceFill([
            'id' => 8,
            'images' => '["product.webp"]',
            'color_image' => null,
        ]);
        $product->setRelation('digitalVariation', collect());

        $productRepository->shouldReceive('getFirstWhere')
            ->andReturn($product);

        $request = new ProductUpdateRequest($productRepository);
        $request->initialize($this->validServicePayload([
            'name' => ['', ''],
            'description' => ['<p><br></p>', '<p><br></p>'],
            'service_tittle' => ['', 'عنوان الخدمة'],
            'parts_included' => ['', 'القطع المشمولة'],
            'service_description' => ['<p><br></p>', '<p>وصف الخدمة</p>'],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->errors()->has('name.0'));
        $this->assertFalse($validator->errors()->has('description.0'));
        $this->assertSame(
            translate('The_title_in_english_is_required') . '!',
            $validator->errors()->first('service_tittle.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('parts_included.0')
        );
        $this->assertSame(
            translate('The_description_in_english_is_required') . '!',
            $validator->errors()->first('service_description.0')
        );
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'lang' => ['en', 'ar'],
            'name' => ['Battery', 'بطارية'],
            'description' => ['<p>English description</p>', '<p>وصف عربي</p>'],
            'product_type' => 'physical',
            'branch_id' => 1,
            'category_id' => 1,
            'unit' => 'pc',
            'tax' => 0,
            'tax_model' => 'include',
            'unit_price' => 100,
            'discount' => 0,
            'discount_type' => 'flat',
            'shipping_cost' => 0,
            'code' => 'ABC123',
            'minimum_order_qty' => 1,
            'current_stock' => 5,
            'existing_thumbnail' => 'thumb.webp',
            'existing_images' => ['product.webp'],
        ], $overrides);
    }

    private function validServicePayload(array $overrides = []): array
    {
        return array_merge($this->validPayload([
            'product_type' => 'services',
            'service_tittle' => ['Synthetic Oil Change', 'تغيير زيت صناعي'],
            'parts_included' => ['Oil and filter', 'الزيت والفلتر'],
            'service_description' => ['<p>Service description</p>', '<p>وصف الخدمة</p>'],
            'service_id' => 'SRV-OIL-SYN',
            'base_price_inshop' => 20,
            'base_price_mobile' => 27.5,
            'parts_cost' => 0,
            'included_km_mobile' => 20,
            'travel_fee_per_km' => 1,
            'labor_hours' => 0.5,
        ]), $overrides);
    }

    private function validateFormRequest(ProductAddRequest|ProductUpdateRequest $request): \Illuminate\Validation\Validator
    {
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        foreach ($request->after() as $afterValidationHook) {
            $validator->after($afterValidationHook);
        }

        $validator->fails();

        return $validator;
    }
}
