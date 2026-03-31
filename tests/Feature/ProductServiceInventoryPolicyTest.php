<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductServiceInventoryPolicyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string)($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
        if ($database === '' || $database === ':memory:') {
            $database = basename(getcwd());
        }

        putenv('DB_CONNECTION=mysql');
        putenv("DB_DATABASE={$database}");
        $_SERVER['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_DATABASE'] = $database;
        $_ENV['DB_DATABASE'] = $database;

        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $database,
        ]);
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_service_products_are_created_without_inventory_or_tracking_flags(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Service Policy Admin',
            'phone' => '1000000000',
            'email' => 'service-policy-admin-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);
        $this->actingAs($admin, 'admin');

        $service = new ProductService(app('App\Models\Color'));
        $request = new Request($this->baseServicePayload() + [
            'current_stock' => 9,
            'is_traceable' => '1',
            'is_warranty' => '1',
        ]);

        $data = $service->getAddProductData($request, 'admin');

        $this->assertSame('services', $data['product_type']);
        $this->assertSame(0, $data['current_stock']);
        $this->assertSame(0, $data['is_traceable']);
        $this->assertSame(0, $data['is_warranty']);
        $this->assertSame('[]', $data['variation']);
        $this->assertSame('[]', $data['choice_options']);
        $this->assertSame('[]', $data['attributes']);
        $this->assertSame('<p>Service description from service field</p>', $data['details']);
    }

    public function test_service_products_are_updated_without_inventory_or_tracking_flags(): void
    {
        $service = new ProductService(app('App\Models\Color'));
        $product = new Product([
            'id' => 5001,
            'product_type' => 'services',
            'images' => json_encode([['image_name' => 'existing-image.webp', 'storage' => 'public']]),
            'color_image' => json_encode([]),
            'choice_options' => json_encode([]),
            'variation' => json_encode([]),
            'colors' => json_encode([]),
            'attributes' => json_encode([]),
            'current_stock' => 12,
            'digital_file_ready_storage_type' => null,
            'digital_file_ready' => null,
            'preview_file' => null,
            'thumbnail' => 'thumb.webp',
            'meta_image' => null,
            'shipping_cost' => 0,
            'request_status' => 1,
            'match_makes' => null,
            'match_models' => null,
            'match_years' => null,
        ]);
        $product->exists = true;

        $request = new Request($this->baseServicePayload() + [
            'current_stock' => 14,
            'is_traceable' => '1',
            'is_warranty' => '1',
        ]);

        $data = $service->getUpdateProductData($request, $product, 'admin');

        $this->assertSame('services', $data['product_type']);
        $this->assertSame(0, $data['current_stock']);
        $this->assertSame(0, $data['is_traceable']);
        $this->assertSame(0, $data['is_warranty']);
        $this->assertSame('[]', $data['variation']);
        $this->assertSame('[]', $data['choice_options']);
        $this->assertSame('[]', $data['attributes']);
        $this->assertSame('<p>Service description from service field</p>', $data['details']);
    }

    private function baseServicePayload(): array
    {
        return [
            'lang' => ['en'],
            'name' => ['Service Product'],
            'description' => ['Legacy service description'],
            'service_description' => ['<p>Service description from service field</p>'],
            'code' => 'SRV001',
            'branch_id' => 1,
            'category_id' => 1,
            'sub_category_id' => null,
            'sub_sub_category_id' => null,
            'product_type' => 'services',
            'unit_price' => 150,
            'tax' => 0,
            'tax_type' => 'percent',
            'tax_model' => 'exclude',
            'discount' => 0,
            'discount_type' => 'flat',
            'minimum_order_qty' => 1,
            'video_url' => '',
            'meta_title' => 'Service Meta Title',
            'meta_description' => 'Service Meta Description',
            'existing_thumbnail' => 'existing-thumbnail.webp',
            'existing_images' => ['existing-image.webp'],
        ];
    }
}
