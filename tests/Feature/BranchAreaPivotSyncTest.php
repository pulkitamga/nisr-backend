<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchAreaPivotSyncTest extends TestCase
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
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();
    }

    public function test_branch_service_syncs_area_relations_from_request_arrays(): void
    {
        DB::table('shipping_method_areas')->insert([
            ['id' => 1, 'area' => 'Cairo'],
            ['id' => 2, 'area' => 'Alexandria'],
        ]);

        DB::table('delivery_areas')->insert([
            ['id' => 7, 'area' => 'North'],
            ['id' => 9, 'area' => 'South'],
        ]);

        $branch = Branch::query()->create([
            'branch_name' => 'Pivot Branch',
        ]);

        $service = new BranchService();
        $service->syncAreaRelations($branch, (object) [
            'shipping_methods_area' => ['1', '2', '2', '0'],
            'delivery_restriction' => ['7', '9'],
        ]);

        $branch->load('shippingAreas', 'deliveryRestrictions');

        $this->assertSame(['Cairo', 'Alexandria'], $branch->shippingAreas->pluck('area')->all());
        $this->assertSame(['North', 'South'], $branch->deliveryRestrictions->pluck('area')->all());
        $this->assertSame('Cairo, Alexandria', $branch->getShippingMethodsAreas());
        $this->assertSame('North, South', $branch->getDeliveryRestriction());
        $this->assertSame(2, DB::table('branch_shipping_method_areas')->count());
        $this->assertSame(2, DB::table('branch_delivery_restrictions')->count());
    }

    public function test_branch_service_detaches_missing_area_relations_on_update(): void
    {
        DB::table('shipping_method_areas')->insert([
            ['id' => 3, 'area' => 'Giza'],
            ['id' => 4, 'area' => 'Mansoura'],
        ]);

        DB::table('delivery_areas')->insert([
            ['id' => 11, 'area' => 'East'],
            ['id' => 12, 'area' => 'West'],
        ]);

        $branch = Branch::query()->create([
            'branch_name' => 'Detach Branch',
        ]);

        $service = new BranchService();
        $service->syncAreaRelations($branch, (object) [
            'shipping_methods_area' => ['3', '4'],
            'delivery_restriction' => ['11', '12'],
        ]);

        $service->syncAreaRelations($branch, (object) [
            'shipping_methods_area' => ['4'],
            'delivery_restriction' => ['12'],
        ]);

        $branch->load('shippingAreas', 'deliveryRestrictions');

        $this->assertSame(['Mansoura'], $branch->shippingAreas->pluck('area')->all());
        $this->assertSame(['West'], $branch->deliveryRestrictions->pluck('area')->all());
    }

    private function createTestSchema(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipping_method_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('area')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('area')->nullable();
            $table->timestamps();
        });

        Schema::create('branch_shipping_method_areas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('shipping_method_area_id');
            $table->timestamps();
        });

        Schema::create('branch_delivery_restrictions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('delivery_area_id');
            $table->timestamps();
        });
    }
}
