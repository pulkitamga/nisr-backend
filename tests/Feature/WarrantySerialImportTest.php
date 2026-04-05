<?php

namespace Tests\Feature;

use App\Imports\SerialImport;
use App\Models\Warranty;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarrantySerialImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('warranties');
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->string('serial_number')->unique();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->string('status')->nullable();
            $table->string('warranty_public_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_import_resolves_product_sku_to_product_id(): void
    {
        $productId = \DB::table('products')->insertGetId([
            'name' => 'Battery',
            'code' => 'SKU-100',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $import = new SerialImport();

        $import->model([
            'serial_number' => 'SERIAL-100',
            'product_sku' => 'SKU-100',
            'warranty_months' => 12,
        ]);

        $this->assertSame(0, $import->failed, json_encode($import->errors));
        $warranty = Warranty::query()->where('serial_number', 'SERIAL-100')->firstOrFail();

        $this->assertSame($productId, (int) $warranty->product_id);
        $this->assertSame(1, $import->created);
        $this->assertSame(0, $import->failed);
    }

    public function test_import_records_failure_when_product_sku_is_missing(): void
    {
        $import = new SerialImport();

        $result = $import->model([
            'serial_number' => 'SERIAL-404',
            'product_sku' => 'SKU-404',
            'warranty_months' => 12,
        ]);

        $this->assertNull($result);
        $this->assertSame(0, Warranty::query()->count());
        $this->assertSame(1, $import->failed);
        $this->assertStringContainsString('Product SKU not found', $import->errors[0]['error']);
    }
}
