<?php



namespace Database\Seeders;

use App\Models\Admin;
use App\Support\AdminPermissionRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Master Admin',
                'phone' => '01759412381',
                'email' => 'admin@admin.com',
                'image' => 'def.png',
                'password' => bcrypt(12345678),
                'remember_token' => Str::random(10),
                'status' => 1,
            ]
        );

        if (Schema::hasTable('roles')) {
            \Spatie\Permission\Models\Role::query()->firstOrCreate([
                'name' => AdminPermissionRegistry::superAdminRole(),
                'guard_name' => config('permissions_admin.guard', 'admin'),
            ]);
            $admin->syncRoles([AdminPermissionRegistry::superAdminRole()]);
        }
    }
}
