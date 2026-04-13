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

    public function test_add_request_rejects_invalid_video_url_when_present(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validPayload([
            'video_url' => 'not-a-valid-url',
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('please_enter_a_valid_video_url') . '!',
            $validator->errors()->first('video_url')
        );
    }

    public function test_update_request_rejects_invalid_video_url_when_present(): void
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
            'video_url' => 'still-not-a-valid-url',
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('please_enter_a_valid_video_url') . '!',
            $validator->errors()->first('video_url')
        );
    }

    public function test_add_request_normalizes_numeric_service_id_before_validation(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validServicePayload([
            'service_id' => 12345,
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('12345', $request->input('service_id'));
    }

    public function test_update_request_normalizes_numeric_service_id_before_validation(): void
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
            'service_id' => 67890,
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('67890', $request->input('service_id'));
    }

    public function test_add_request_normalizes_nested_array_service_id_before_validation(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validServicePayload([
            'service_id' => [['SRV-ARRAY-101']],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('SRV-ARRAY-101', $request->input('service_id'));
    }

    public function test_update_request_normalizes_nested_array_service_id_before_validation(): void
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
            'service_id' => [['SRV-ARRAY-202']],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('SRV-ARRAY-202', $request->input('service_id'));
    }

    public function test_add_request_accepts_nested_array_service_id_without_normalized_validation_data(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validServicePayload([
            'service_id' => [['SRV-RAW-303']],
        ]));

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        foreach ($request->after() as $afterValidationHook) {
            $validator->after($afterValidationHook);
        }

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    public function test_add_request_normalizes_all_service_scalar_fields_before_validation(): void
    {
        $request = ProductAddRequest::create('/admin/products/store', 'POST', $this->validServicePayload([
            'service_id' => [['SRV-SCALAR-401']],
            'base_price_inshop' => [[20]],
            'base_price_mobile' => [[27.5]],
            'parts_cost' => [[0]],
            'included_km_mobile' => [[20]],
            'travel_fee_per_km' => [[1]],
            'labor_hours' => [[0.5]],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('SRV-SCALAR-401', $request->input('service_id'));
        $this->assertSame('20', $request->input('base_price_inshop'));
        $this->assertSame('27.5', $request->input('base_price_mobile'));
        $this->assertSame('0', $request->input('parts_cost'));
        $this->assertSame('20', $request->input('included_km_mobile'));
        $this->assertSame('1', $request->input('travel_fee_per_km'));
        $this->assertSame('0.5', $request->input('labor_hours'));
    }

    public function test_update_request_normalizes_all_service_scalar_fields_before_validation(): void
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
            'service_id' => [['SRV-SCALAR-402']],
            'base_price_inshop' => [[20]],
            'base_price_mobile' => [[27.5]],
            'parts_cost' => [[0]],
            'included_km_mobile' => [[20]],
            'travel_fee_per_km' => [[1]],
            'labor_hours' => [[0.5]],
        ]));

        $validator = $this->validateFormRequest($request);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame('SRV-SCALAR-402', $request->input('service_id'));
        $this->assertSame('20', $request->input('base_price_inshop'));
        $this->assertSame('27.5', $request->input('base_price_mobile'));
        $this->assertSame('0', $request->input('parts_cost'));
        $this->assertSame('20', $request->input('included_km_mobile'));
        $this->assertSame('1', $request->input('travel_fee_per_km'));
        $this->assertSame('0.5', $request->input('labor_hours'));
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

        if (method_exists($request, 'prepareForValidation')) {
            $reflection = new \ReflectionMethod($request, 'prepareForValidation');
            $reflection->setAccessible(true);
            $reflection->invoke($request);
        }

        $validationData = method_exists($request, 'validationData')
            ? $request->validationData()
            : $request->all();

        $validator = Validator::make($validationData, $request->rules(), $request->messages());

        foreach ($request->after() as $afterValidationHook) {
            $validator->after($afterValidationHook);
        }

        $validator->fails();

        return $validator;
    }
}
