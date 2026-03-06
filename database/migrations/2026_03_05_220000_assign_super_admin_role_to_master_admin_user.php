<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $guard = config('permissions_admin.guard', 'admin');

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => $guard,
        ]);

        // Spatie-only targeting for the seeded default admin account.
        $admins = Admin::query()
            ->where('email', 'admin@admin.com')
            ->get();

        foreach ($admins as $admin) {
            if (!$admin->hasRole('Super Admin')) {
                $admin->assignRole($superAdminRole);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $guard = config('permissions_admin.guard', 'admin');
        $admins = Admin::query()
            ->whereHas('roles', function ($query) use ($guard) {
                $query->where('roles.name', 'Super Admin')
                    ->where('roles.guard_name', $guard);
            })
            ->where('email', 'admin@admin.com')
            ->get();

        foreach ($admins as $admin) {
            $admin->removeRole('Super Admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
