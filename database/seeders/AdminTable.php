<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $legacySuperAdminRoleId = DB::table('admin_roles')
            ->where('name', 'Master Admin')
            ->value('id');

        DB::table('admins')->insert([
            'id' => 1,
            'name' => 'Master Admin',
            'phone' => '01759412381',
            'email' => 'admin@admin.com',
            'admin_role_id' => $legacySuperAdminRoleId,
            'image' => 'def.png',
            'password' => bcrypt(12345678),
            'remember_token' =>Str::random(10),
        ]);
    }
}
