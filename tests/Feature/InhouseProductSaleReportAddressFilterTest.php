<?php

namespace Tests\Feature;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Http\Controllers\Admin\InhouseProductSaleController;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InhouseProductSaleReportAddressFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-31 10:00:00');
        $this->createTables();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_retail_address_filters_limit_order_rows_and_location_breakdown(): void
    {
        DB::table('branches')->insert([
            'id' => 1,
            'branch_name' => 'Main Branch',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'id' => 11,
            'name' => 'Air Conditioner',
            'added_by' => 'admin',
            'product_type' => 'physical',
            'category_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipping_addresses')->insert([
            [
                'id' => 1,
                'state' => 'Cairo',
                'city' => 'Cairo',
                'area' => 'Nasr City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'state' => 'Giza',
                'city' => 'Giza',
                'area' => 'Dokki',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('orders')->insert([
            [
                'id' => 101,
                'seller_is' => 'admin',
                'order_status' => 'delivered',
                'order_type' => 'ONLINE',
                'shipping_address' => '1',
                'transfer_from_branch' => 1,
                'pickup_from_branch' => null,
                'created_at' => '2026-03-10 10:00:00',
                'updated_at' => '2026-03-10 10:00:00',
            ],
            [
                'id' => 102,
                'seller_is' => 'admin',
                'order_status' => 'delivered',
                'order_type' => 'ONLINE',
                'shipping_address' => '2',
                'transfer_from_branch' => 1,
                'pickup_from_branch' => null,
                'created_at' => '2026-03-12 10:00:00',
                'updated_at' => '2026-03-12 10:00:00',
            ],
        ]);

        DB::table('order_details')->insert([
            [
                'order_id' => 101,
                'product_id' => 11,
                'qty' => 2,
                'price' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => 102,
                'product_id' => 11,
                'qty' => 1,
                'price' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('wholesale_confirmorder_item')->insert([
            'confirmed_order_id' => 901,
            'product_id' => 11,
            'product_variation_type' => '',
            'product_quantity' => 1,
            'final_price' => 70,
            'base_price' => 70,
        ]);

        DB::table('wholesale_order_delivery')->insert([
            'confirmed_order_id' => 901,
            'product_id' => 11,
            'product_variation_type' => '',
            'quantity_sent' => 1,
            'branch_id' => 1,
            'delivery_date' => '2026-03-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new InhouseProductSaleController($this->createMock(CategoryRepositoryInterface::class));
        $branchMap = collect([1 => 'Main Branch']);
        $fromDate = Carbon::parse('2026-03-01 00:00:00');
        $toDate = Carbon::parse('2026-03-31 23:59:59');

        $orderRows = $this->invokePrivateMethod($controller, 'getOrderChannelRows', [
            'ONLINE',
            $fromDate,
            $toDate,
            'all',
            [],
            [],
            $branchMap,
            ['Cairo'],
            [],
            [],
        ]);

        $locationRows = $this->invokePrivateMethod($controller, 'getRetailLocationRows', [
            'state',
            $fromDate,
            $toDate,
            'all',
            [],
            [],
            ['Cairo'],
            [],
            [],
        ]);

        $trend = $this->invokePrivateMethod($controller, 'getDateTrend', [
            $fromDate,
            $toDate,
            'all',
            [],
            [],
            ['Cairo'],
            [],
            [],
            false,
        ]);

        $this->assertCount(1, $orderRows);
        $this->assertSame('Air Conditioner', $orderRows->first()->product_name);
        $this->assertSame(100.0, $orderRows->first()->total_amount);
        $this->assertSame('Main Branch', $orderRows->first()->branch_name);

        $this->assertCount(1, $locationRows);
        $this->assertSame('Cairo', $locationRows->first()->location_name);
        $this->assertSame(100.0, $locationRows->first()->total_amount);
        $this->assertSame(2, $locationRows->first()->total_qty);

        $this->assertEquals(0.0, array_sum($trend['wholesale']));
    }

    private function createTables(): void
    {
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table): void {
                $table->id();
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id');
                $table->string('locale')->nullable();
                $table->string('key')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('delivery_man_id')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedTinyInteger('rating')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('storages')) {
            Schema::create('storages', function (Blueprint $table): void {
                $table->id();
                $table->string('data_type')->nullable();
                $table->unsignedBigInteger('data_id')->nullable();
                $table->string('key')->nullable();
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->string('name');
                $table->string('added_by');
                $table->string('product_type');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->string('branch_name');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('shipping_addresses')) {
            Schema::create('shipping_addresses', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->string('state')->nullable();
                $table->string('city')->nullable();
                $table->string('area')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->string('seller_is')->nullable();
                $table->string('order_status')->nullable();
                $table->string('order_type')->nullable();
                $table->string('shipping_address')->nullable();
                $table->unsignedBigInteger('transfer_from_branch')->nullable();
                $table->unsignedBigInteger('pickup_from_branch')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_details')) {
            Schema::create('order_details', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('qty')->default(0);
                $table->decimal('price', 24, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wholesale_confirmorder_item')) {
            Schema::create('wholesale_confirmorder_item', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('confirmed_order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_variation_type')->nullable();
                $table->integer('product_quantity')->nullable();
                $table->decimal('final_price', 24, 2)->nullable();
                $table->decimal('base_price', 24, 2)->nullable();
            });
        }

        if (!Schema::hasTable('wholesale_order_delivery')) {
            Schema::create('wholesale_order_delivery', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('confirmed_order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_variation_type')->nullable();
                $table->integer('quantity_sent')->default(0);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->date('delivery_date')->nullable();
                $table->timestamps();
            });
        }
    }

    private function invokePrivateMethod(object $target, string $method, array $arguments): mixed
    {
        $reflection = new \ReflectionMethod($target, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($target, $arguments);
    }
}
