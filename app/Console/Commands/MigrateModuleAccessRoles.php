<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminPermissionRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MigrateModuleAccessRoles extends Command
{
    protected $signature = 'roles:migrate-module-access {--skip-admin-sync : Skip assigning migrated Spatie roles to admins by admin_role_id}';

    protected $description = 'Migrate legacy admin_roles.module_access JSON to Spatie roles/permissions for admin guard.';

    public function handle(): int
    {
        if (!Schema::hasTable('admin_roles')) {
            $this->error('admin_roles table not found.');
            return self::FAILURE;
        }
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            $this->error('roles/permissions tables not found. Run migrations and permissions:sync-admin first.');
            return self::FAILURE;
        }

        $guard = AdminPermissionRegistry::guard();
        $permissionLookup = Permission::query()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->flip()
            ->all();

        $allRegistryPermissions = AdminPermissionRegistry::all();
        $superAdminRoleName = AdminPermissionRegistry::superAdminRole();
        $superAdminRole = Role::query()->firstOrCreate(
            ['name' => $superAdminRoleName, 'guard_name' => $guard],
            $this->statusPayload(true)
        );
        if ($this->roleHasStatusColumn()) {
            $superAdminRole->status = true;
            $superAdminRole->save();
        }
        $superAdminRole->syncPermissions($allRegistryPermissions);

        $legacyRoles = AdminRole::query()->orderBy('id')->get();
        $migrated = 0;
        $unknownPairs = 0;

        foreach ($legacyRoles as $legacyRole) {
            $roleName = trim((string)$legacyRole->name);
            if ($roleName === '') {
                $this->warn("Skipping admin_roles.id={$legacyRole->id} due to empty name.");
                continue;
            }

            $role = Role::query()->firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                $this->statusPayload((bool)$legacyRole->status)
            );

            $isSuperAdminRole = trim(strtolower($roleName)) === trim(strtolower($superAdminRoleName));

            if ($this->roleHasStatusColumn()) {
                if ($isSuperAdminRole) {
                    $role->status = true;
                    $role->save();
                } else {
                    $role->status = (bool)$legacyRole->status;
                    $role->save();
                }
            }

            $mappedPermissions = [];
            $decoded = json_decode((string)$legacyRole->module_access, true);

            if (is_array($decoded)) {
                foreach ($decoded as $moduleKey => $actions) {
                    if (is_int($moduleKey)) {
                        $permission = AdminPermissionRegistry::fromModuleAction((string)$actions, null);
                        if ($permission !== null && isset($permissionLookup[$permission])) {
                            $mappedPermissions[$permission] = true;
                        } else {
                            $unknownPairs++;
                            $this->warn("Unknown permission mapping skipped: role={$roleName} module={$actions} action=access");
                        }
                        continue;
                    }

                    $module = (string)$moduleKey;
                    $actionsList = $this->normalizeActions($actions);
                    if (count($actionsList) === 0) {
                        $actionsList = ['access'];
                    }

                    foreach ($actionsList as $action) {
                        $permission = AdminPermissionRegistry::fromModuleAction($module, $action);
                        if ($permission !== null && isset($permissionLookup[$permission])) {
                            $mappedPermissions[$permission] = true;
                        } else {
                            $unknownPairs++;
                            $this->warn("Unknown permission mapping skipped: role={$roleName} module={$module} action={$action}");
                        }
                    }
                }
            }

            if ($isSuperAdminRole) {
                $role->syncPermissions($allRegistryPermissions);
            } else {
                $role->syncPermissions(array_keys($mappedPermissions));
            }
            $migrated++;
            $this->line("Migrated role: {$roleName} (permissions=" . count($mappedPermissions) . ")");

            if (in_array(strtolower($roleName), ['super admin'], true)) {
                $this->assignRoleToAdminsByLegacyRoleId((int)$legacyRole->id, $superAdminRoleName);
            }
        }

        if (!$this->option('skip-admin-sync')) {
            foreach ($legacyRoles as $legacyRole) {
                $roleName = trim((string)$legacyRole->name);
                if ($roleName === '') {
                    continue;
                }
                $count = $this->assignRoleToAdminsByLegacyRoleId((int)$legacyRole->id, $roleName);
                $this->line("Assigned {$roleName} to {$count} admins (admin_role_id={$legacyRole->id}).");
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Role migration complete. migrated={$migrated} unknown_mappings={$unknownPairs}");
        $this->info("Super Admin role enforced: {$superAdminRoleName}");

        return self::SUCCESS;
    }

    private function normalizeActions(mixed $actions): array
    {
        if (is_string($actions)) {
            return [$actions];
        }

        if (!is_array($actions)) {
            return [];
        }

        // Handle both list style ["read","create"] and old keyed style ["read" => "on"].
        $list = [];
        foreach ($actions as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $list[] = $value;
                continue;
            }

            if (is_string($key)) {
                if ($value === true || $value === 1 || $value === '1' || $value === 'on' || $value === 'true' || is_string($value)) {
                    $list[] = $key;
                }
            }
        }

        return array_values(array_unique($list));
    }

    private function assignRoleToAdminsByLegacyRoleId(int $legacyRoleId, string $roleName): int
    {
        if (!Schema::hasTable('admins')) {
            return 0;
        }

        $admins = Admin::query()->where('admin_role_id', $legacyRoleId)->get();
        foreach ($admins as $admin) {
            $admin->assignRole($roleName);
        }

        return $admins->count();
    }

    private function roleHasStatusColumn(): bool
    {
        return Schema::hasColumn('roles', 'status');
    }

    private function statusPayload(bool $status): array
    {
        if ($this->roleHasStatusColumn()) {
            return ['status' => $status];
        }

        return [];
    }
}
