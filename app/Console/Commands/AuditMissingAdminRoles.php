<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Support\AdminPermissionRegistry;
use Illuminate\Console\Command;

class AuditMissingAdminRoles extends Command
{
    protected $signature = 'roles:audit-missing-admin-roles
                            {--include-inactive : Include inactive admins (status != 1)}';

    protected $description = 'Report admins missing Spatie roles for the admin guard before RBAC cutover.';

    public function handle(): int
    {
        $guard = AdminPermissionRegistry::guard();
        $query = Admin::query()->select(['id', 'name', 'email', 'status', 'department_id', 'created_at']);

        if (!$this->option('include-inactive')) {
            $query->active();
        }

        $admins = $query->orderBy('id')->get();
        $missingRoles = $admins->filter(function (Admin $admin) use ($guard) {
            return !$admin->roles()->where('guard_name', $guard)->exists();
        })->values();

        $this->info('Admin role audit complete.');
        $this->line('Guard: ' . $guard);
        $this->line('Checked admins: ' . $admins->count());
        $this->line('Missing Spatie roles: ' . $missingRoles->count());

        if ($missingRoles->isEmpty()) {
            $this->info('No admins are missing Spatie roles.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Email', 'Status', 'Department ID', 'Created At'],
            $missingRoles->map(function (Admin $admin) {
                return [
                    $admin->id,
                    $admin->name,
                    $admin->email,
                    (int)$admin->status === 1 ? 'active' : 'inactive',
                    $admin->department_id,
                    optional($admin->created_at)?->toDateTimeString(),
                ];
            })->all()
        );

        $this->newLine();
        $this->warn('Assign roles before cutover. Example: php artisan roles:migrate-module-access');

        return self::FAILURE;
    }
}

