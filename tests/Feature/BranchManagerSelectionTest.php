<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\DeliveryAreaRepositoryInterface;
use App\Contracts\Repositories\ShippingMethodAreaRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Admin\Branch\BranchController;
use App\Models\Admin;
use App\Models\Branch;
use App\Services\BranchService;
use App\Support\AdminPermissionRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchManagerSelectionTest extends TestCase
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
            'permission.teams' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();
    }

    public function test_current_assigned_manager_is_kept_in_edit_form_options(): void
    {
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';

        $currentManager = Admin::query()->create([
            'name' => 'Assigned Manager',
            'status' => 0,
        ]);

        $availableManager = Admin::query()->create([
            'name' => 'Available Manager',
            'status' => 1,
        ]);

        DB::table(config('permission.table_names.roles', 'roles'))->insert([
            'id' => 1,
            'name' => AdminPermissionRegistry::branchManagerRole(),
            'guard_name' => AdminPermissionRegistry::guard(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))->insert([
            $rolePivotKey => 1,
            'model_type' => Admin::class,
            $modelMorphKey => $availableManager->id,
        ]);

        $branch = Branch::query()->create([
            'branch_name' => 'Alexandria',
            'manager_id' => $currentManager->id,
        ]);

        $controller = new BranchController(
            $this->createMock(VendorRepositoryInterface::class),
            $this->createMock(BranchRepositoryInterface::class),
            new BranchService(),
            $this->createMock(DeliveryAreaRepositoryInterface::class),
            $this->createMock(ShippingMethodAreaRepositoryInterface::class),
            $this->createMock(TranslationRepositoryInterface::class),
        );

        $managerIds = $controller->getActiveBranchManagers($branch)->pluck('id')->all();

        $this->assertSame([$currentManager->id, $availableManager->id], $managerIds);
    }

    private function createTestSchema(): void
    {
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        Schema::create(config('permission.table_names.roles', 'roles'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create(config('permission.table_names.model_has_roles', 'model_has_roles'), function (Blueprint $table) use ($rolePivotKey, $modelMorphKey): void {
            $table->unsignedBigInteger($rolePivotKey);
            $table->string('model_type');
            $table->unsignedBigInteger($modelMorphKey);
        });
    }
}
