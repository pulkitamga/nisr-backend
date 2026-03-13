<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Support\AdminPermissionRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminRbacMigrationTest extends TestCase
{
    private static bool $testRoutesRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        $this->prepareSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Artisan::call('permissions:sync-admin');
        $this->registerTestRoutes();
    }

    public function test_non_authorized_admin_cannot_access_crm_inbox_sla_and_role_management_routes(): void
    {
        $limitedRole = Role::query()->create([
            'name' => 'Limited Admin',
            'guard_name' => AdminPermissionRegistry::guard(),
            'status' => true,
        ]);
        $limitedRole->givePermissionTo(['user_section.read']);

        $admin = $this->createAdmin('limited@example.com');
        $admin->assignRole($limitedRole->name);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.crm.index'))->assertForbidden();
        $this->get(route('admin.sla.index'))->assertForbidden();
        $this->get(route('admin.custom-role.create'))->assertForbidden();
    }

    public function test_super_admin_can_bypass_permission_middleware(): void
    {
        Role::findOrCreate(AdminPermissionRegistry::superAdminRole(), AdminPermissionRegistry::guard());

        $superAdmin = $this->createAdmin('super@example.com');
        $superAdmin->assignRole(AdminPermissionRegistry::superAdminRole());

        $this->actingAs($superAdmin, 'admin');
        $this->get(route('rbac.test.protected'))->assertOk()->assertSeeText('ok');
    }

    public function test_last_super_admin_cannot_be_disabled_from_employee_status_endpoint(): void
    {
        Role::findOrCreate(AdminPermissionRegistry::superAdminRole(), AdminPermissionRegistry::guard());

        $superAdmin = $this->createAdmin('only-super@example.com');
        $superAdmin->assignRole(AdminPermissionRegistry::superAdminRole());

        $this->actingAs($superAdmin, 'admin');

        $response = $this->post(route('admin.employee.status'), [
            'id' => $superAdmin->id,
            'status' => 0,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('admins', [
            'id' => $superAdmin->id,
            'status' => 1,
        ]);
    }

    public function test_every_route_permission_exists_in_registry_and_database_after_sync(): void
    {
        $content = file_get_contents(base_path('routes/admin/routes.php'));
        preg_match_all("/permission:([^,'\"\\]\\)\\s]+)/", $content, $matches);
        $usedPermissions = collect($matches[1] ?? [])
            ->flatMap(function (string $permissionExpression): array {
                return array_values(array_filter(
                    array_map('trim', explode('|', $permissionExpression)),
                    static fn(string $permission): bool => $permission !== ''
                ));
            })
            ->unique()
            ->values()
            ->all();
        sort($usedPermissions);

        $registry = AdminPermissionRegistry::all();
        $dbPermissions = DB::table('permissions')
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->pluck('name')
            ->toArray();

        $missingFromRegistry = array_diff($usedPermissions, $registry);
        $missingFromDb = array_diff($usedPermissions, $dbPermissions);

        $this->assertSame([], array_values($missingFromRegistry), 'All route permissions must exist in the registry.');
        $this->assertSame([], array_values($missingFromDb), 'All route permissions must exist in permissions table.');
    }

    public function test_every_admin_route_except_allowlist_has_authorization_middleware(): void
    {
        $allowlist = [
            'admin/logout',
        ];

        $unguarded = [];
        foreach (Route::getRoutes() as $route) {
            $uri = ltrim($route->uri(), '/');
            if (!str_starts_with($uri, 'admin')) {
                continue;
            }

            if (in_array($uri, $allowlist, true)) {
                continue;
            }

            $middlewares = $route->gatherMiddleware();
            $hasAuthorizationMiddleware = collect($middlewares)->contains(
                static fn(string $middleware): bool =>
                    str_contains($middleware, 'PermissionMiddleware:')
                    || str_contains($middleware, 'RoleMiddleware:')
                    || str_contains($middleware, 'RoleOrPermissionMiddleware:')
                    || str_starts_with($middleware, 'can:')
                    || str_contains($middleware, 'Authorize:')
            );

            if (!$hasAuthorizationMiddleware) {
                $unguarded[] = sprintf(
                    '%s [%s]',
                    $uri,
                    implode('|', $route->methods())
                );
            }
        }

        $this->assertSame(
            [],
            $unguarded,
            'Every admin route must include permission/role/can authorization middleware.'
        );
    }

    public function test_roles_migration_maps_legacy_module_access_without_creating_unknown_permissions(): void
    {
        $now = now();
        DB::table('admin_roles')->insert([
            [
                'id' => 1,
                'name' => 'Master Admin',
                'module_access' => json_encode([
                    'crm_section' => ['inbox_list', 'unknown_action'],
                    'unknown_module' => ['read'],
                ]),
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Agent',
                'module_access' => json_encode([
                    'crm_section' => ['inbox_list'],
                ]),
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $master = $this->createAdmin('legacy-master@example.com', 1);
        $agent = $this->createAdmin('legacy-agent@example.com', 2);

        $exitCode = Artisan::call('roles:migrate-module-access');
        $this->assertSame(0, $exitCode);

        $superAdminRole = Role::query()
            ->where('name', AdminPermissionRegistry::superAdminRole())
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->first();
        $this->assertNotNull($superAdminRole);

        $agentRole = Role::query()
            ->where('name', 'Agent')
            ->where('guard_name', AdminPermissionRegistry::guard())
            ->first();
        $this->assertNotNull($agentRole);
        $this->assertTrue($agentRole->hasPermissionTo('crm_section.inbox_list'));

        $this->assertDatabaseMissing('permissions', [
            'name' => 'unknown_module.read',
            'guard_name' => AdminPermissionRegistry::guard(),
        ]);
        $this->assertDatabaseMissing('permissions', [
            'name' => 'crm_section.unknown_action',
            'guard_name' => AdminPermissionRegistry::guard(),
        ]);

        $this->assertTrue($master->fresh()->hasRole(AdminPermissionRegistry::superAdminRole()));
        $this->assertTrue($agent->fresh()->hasRole('Agent'));
    }

    public function test_role_delete_endpoint_deletes_unassigned_role(): void
    {
        Role::findOrCreate(AdminPermissionRegistry::superAdminRole(), AdminPermissionRegistry::guard());

        $superAdmin = $this->createAdmin('delete-super@example.com');
        $superAdmin->assignRole(AdminPermissionRegistry::superAdminRole());
        $this->actingAs($superAdmin, 'admin');

        $deletableRole = Role::query()->create([
            'name' => 'Delete Me',
            'guard_name' => AdminPermissionRegistry::guard(),
            'status' => true,
        ]);

        $response = $this->postJson(route('admin.custom-role.delete'), [
            'id' => $deletableRole->id,
        ]);

        $response->assertOk()->assertJson([
            'success' => 1,
        ]);

        $this->assertDatabaseMissing('roles', [
            'id' => $deletableRole->id,
        ]);
    }

    public function test_role_delete_endpoint_blocks_assigned_role(): void
    {
        Role::findOrCreate(AdminPermissionRegistry::superAdminRole(), AdminPermissionRegistry::guard());

        $superAdmin = $this->createAdmin('delete-assigned-super@example.com');
        $superAdmin->assignRole(AdminPermissionRegistry::superAdminRole());
        $this->actingAs($superAdmin, 'admin');

        $assignedRole = Role::query()->create([
            'name' => 'Assigned Role',
            'guard_name' => AdminPermissionRegistry::guard(),
            'status' => true,
        ]);

        $assignedAdmin = $this->createAdmin('assigned-role-user@example.com');
        $assignedAdmin->assignRole($assignedRole->name);

        $response = $this->postJson(route('admin.custom-role.delete'), [
            'id' => $assignedRole->id,
        ]);

        $response->assertStatus(422)->assertJson([
            'success' => 0,
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $assignedRole->id,
        ]);
    }

    private function registerTestRoutes(): void
    {
        if (self::$testRoutesRegistered) {
            return;
        }

        Route::middleware(['web', 'admin', 'permission:crm_section.inbox_list,admin'])
            ->get('/admin/_rbac-test/protected', static fn() => response('ok'))
            ->name('rbac.test.protected');

        self::$testRoutesRegistered = true;
    }

    private function prepareSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'permissions',
            'roles',
            'storages',
            'admins',
            'admin_roles',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        Schema::create('permissions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', static function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', static function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', static function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('admin_roles', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('module_access')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('admins', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('admin_role_id')->nullable();
            $table->string('image')->nullable();
            $table->longText('identify_image')->nullable();
            $table->string('identify_type')->nullable();
            $table->unsignedBigInteger('identify_number')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('storages', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('data_type');
            $table->unsignedBigInteger('data_id');
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    private function createAdmin(string $email, ?int $legacyRoleId = null): Admin
    {
        return Admin::query()->create([
            'name' => 'Test Admin',
            'phone' => '1000000000',
            'email' => $email,
            'password' => bcrypt('Password@123'),
            'status' => 1,
            'admin_role_id' => $legacyRoleId,
        ]);
    }
}
