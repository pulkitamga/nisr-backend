<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get company name from business settings for the shop name
        $companySetting = DB::table('business_settings')->where('type', 'company_name')->first();
        $companyName = $companySetting ? json_decode($companySetting->value, true) : ['en' => 'Inhouse Shop'];
        $shopName = $companyName['en'] ?? 'Inhouse Shop';

        // Create in-house seller record (id=0) so scopeActive can find it
        if (DB::table('sellers')->where('id', 0)->doesntExist()) {
            DB::statement('SET SESSION sql_mode="NO_AUTO_VALUE_ON_ZERO"');
            DB::table('sellers')->insert([
                'id' => 0,
                'f_name' => 'Inhouse',
                'l_name' => 'Shop',
                'phone' => '',
                'email' => '',
                'image' => '',
                'password' => '',
                'bank_name' => null,
                'branch' => null,
                'account_no' => null,
                'holder_name' => null,
                'status' => 'approved',
                'sales_commission_percentage' => 0,
                'gst' => null,
                'pos_status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create in-house shop record (id=0) so it appears in dropdowns
        if (DB::table('shops')->where('id', 0)->doesntExist()) {
            DB::statement('SET SESSION sql_mode="NO_AUTO_VALUE_ON_ZERO"');
            DB::table('shops')->insert([
                'id' => 0,
                'seller_id' => 0,
                'name' => $shopName,
                'slug' => 'inhouse-shop',
                'address' => '',
                'contact' => '',
                'image' => '',
                'banner' => '',
                'bottom_banner' => '',
                'offer_banner' => '',
                'temporary_close' => 0,
                'vacation_status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('shops')->where('id', 0)->delete();
        DB::table('sellers')->where('id', 0)->delete();
    }
};
