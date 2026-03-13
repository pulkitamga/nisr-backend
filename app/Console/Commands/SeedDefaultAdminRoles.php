<?php

namespace App\Console\Commands;

use App\Support\AdminPermissionRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedDefaultAdminRoles extends Command
{
    protected $signature = 'roles:seed-default-admin
                            {--only=* : Seed only specific role names}
                            {--dry-run : Preview roles and permissions without writing}
                            {--no-sync : Skip running permissions:sync-admin before seeding}';

    protected $description = 'Create/update default admin roles and sync their permissions from config/admin_default_roles.php';

    public function handle(): int
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            $this->error('roles/permissions tables not found. Run migrations first.');
            return self::FAILURE;
        }

        $dryRun = (bool)$this->option('dry-run');
        if (!$this->option('no-sync')) {
            if ($dryRun) {
                $this->line('Dry-run: would execute permissions:sync-admin');
            } else {
                $exitCode = Artisan::call('permissions:sync-admin');
                $this->line(trim((string)Artisan::output()));
                if ($exitCode !== 0) {
                    $this->error('permissions:sync-admin failed.');
                    return self::FAILURE;
                }
            }
        }

        $guard = AdminPermissionRegistry::guard();
        $registryModules = AdminPermissionRegistry::modules();
        $registryPermissions = AdminPermissionRegistry::all();
        $registryLookup = array_fill_keys($registryPermissions, true);

        $blueprints = config('admin_default_roles.roles', []);
        if (!is_array($blueprints) || count($blueprints) === 0) {
            $this->warn('No default roles defined in config/admin_default_roles.php');
            return self::SUCCESS;
        }

        $onlyRoles = array_map(static fn(string $name) => strtolower(trim($name)), (array)$this->option('only'));
        $onlyRoles = array_values(array_filter($onlyRoles, static fn(string $name) => $name !== ''));

        $permissionLookup = Permission::query()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->flip()
            ->all();

        $seeded = 0;
        foreach ($blueprints as $blueprint) {
            if (!is_array($blueprint)) {
                continue;
            }

            $roleName = trim((string)($blueprint['name'] ?? ''));
            if ($roleName === '') {
                continue;
            }

            if (count($onlyRoles) > 0 && !in_array(strtolower($roleName), $onlyRoles, true)) {
                continue;
            }

            $permissions = $this->resolvePermissionsForRole(
                roleName: $roleName,
                blueprint: $blueprint,
                registryModules: $registryModules,
                registryLookup: $registryLookup
            );

            $missingFromDb = array_values(array_filter($permissions, static fn(string $permission) => !isset($permissionLookup[$permission])));
            if (count($missingFromDb) > 0) {
                $this->warn("Role {$roleName}: skipping permissions missing in DB => " . implode(', ', $missingFromDb));
                $permissions = array_values(array_diff($permissions, $missingFromDb));
            }

            if ($dryRun) {
                $this->line("Dry-run: {$roleName} => " . count($permissions) . ' permissions');
                continue;
            }

            $role = Role::query()->firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                $this->statusPayload(true)
            );

            if ($this->roleHasStatusColumn()) {
                $role->status = true;
                $role->save();
            }

            $role->syncPermissions($permissions);

            $seeded++;
            $this->line("Seeded role: {$roleName} ({$role->permissions()->count()} permissions)");
        }

        if (!$dryRun) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $this->info("Default admin role seed complete. processed={$seeded} dry_run=" . ($dryRun ? 'yes' : 'no'));
        return self::SUCCESS;
    }

    private function resolvePermissionsForRole(
        string $roleName,
        array $blueprint,
        array $registryModules,
        array $registryLookup
    ): array {
        $superAdminRole = AdminPermissionRegistry::superAdminRole();
        if (strtolower($roleName) === strtolower($superAdminRole)) {
            return AdminPermissionRegistry::all();
        }

        $permissions = [];
        $moduleSpec = $blueprint['modules'] ?? [];
        if ($moduleSpec === '*') {
            foreach ($registryModules as $module => $actions) {
                foreach ((array)$actions as $action) {
                    $permissions[] = "{$module}.{$action}";
                }
            }
        } elseif (is_array($moduleSpec)) {
            foreach ($moduleSpec as $module => $actionsSpec) {
                if (!isset($registryModules[$module])) {
                    $this->warn("Role {$roleName}: unknown module '{$module}' ignored.");
                    continue;
                }

                $actions = $actionsSpec === '*'
                    ? $registryModules[$module]
                    : (is_array($actionsSpec) ? $actionsSpec : []);

                foreach ($actions as $action) {
                    $permission = "{$module}.{$action}";
                    if (!isset($registryLookup[$permission])) {
                        $this->warn("Role {$roleName}: unknown action '{$module}.{$action}' ignored.");
                        continue;
                    }
                    $permissions[] = $permission;
                }
            }
        }

        $explicit = $blueprint['permissions'] ?? [];
        if ($explicit === '*') {
            $permissions = array_merge($permissions, array_keys($registryLookup));
        } elseif (is_array($explicit)) {
            foreach ($explicit as $permission) {
                if ($permission === '*') {
                    $permissions = array_merge($permissions, array_keys($registryLookup));
                    continue;
                }
                if (!is_string($permission) || $permission === '') {
                    continue;
                }
                if (!isset($registryLookup[$permission])) {
                    $this->warn("Role {$roleName}: unknown explicit permission '{$permission}' ignored.");
                    continue;
                }
                $permissions[] = $permission;
            }
        }

        $permissions = array_values(array_unique($permissions));
        sort($permissions);

        return $permissions;
    }

    private function roleHasStatusColumn(): bool
    {
        static $hasStatus = null;
        if ($hasStatus !== null) {
            return $hasStatus;
        }

        $hasStatus = Schema::hasColumn('roles', 'status');
        return $hasStatus;
    }

    private function statusPayload(bool $status): array
    {
        if ($this->roleHasStatusColumn()) {
            return ['status' => $status];
        }

        return [];
    }
}
