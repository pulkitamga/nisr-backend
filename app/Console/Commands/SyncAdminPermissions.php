<?php

namespace App\Console\Commands;

use App\Support\AdminPermissionRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncAdminPermissions extends Command
{
    protected $signature = 'permissions:sync-admin {--prune : Delete admin-guard permissions not present in the registry}';

    protected $description = 'Synchronize admin permissions from config/permissions_admin.php into the permissions table.';

    public function handle(): int
    {
        if (!Schema::hasTable('permissions')) {
            $this->error('permissions table not found. Run migrations first.');
            return self::FAILURE;
        }

        $guard = AdminPermissionRegistry::guard();
        $desired = AdminPermissionRegistry::all();
        $desiredLookup = array_fill_keys($desired, true);

        $existing = Permission::query()
            ->where('guard_name', $guard)
            ->get(['id', 'name']);

        $existingLookup = [];
        foreach ($existing as $permission) {
            $existingLookup[$permission->name] = $permission;
        }

        $created = 0;
        foreach ($desired as $name) {
            if (isset($existingLookup[$name])) {
                continue;
            }

            Permission::query()->create([
                'name' => $name,
                'guard_name' => $guard,
            ]);
            $created++;
            $this->line("Created: {$name}");
        }

        $pruned = 0;
        if ((bool)$this->option('prune')) {
            foreach ($existingLookup as $name => $permission) {
                if (isset($desiredLookup[$name])) {
                    continue;
                }

                $permission->delete();
                $pruned++;
                $this->line("Pruned: {$name}");
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info(sprintf(
            'Admin permission sync complete. desired=%d created=%d pruned=%d',
            count($desired),
            $created,
            $pruned
        ));

        return self::SUCCESS;
    }
}
