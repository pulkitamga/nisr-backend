<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        $attributes = [
            'value' => 'single',
            'is_active' => 1,
            'updated_at' => $timestamp,
        ];

        if (!DB::table('business_settings')->where('type', 'business_mode')->exists()) {
            $attributes['created_at'] = $timestamp;
        }

        DB::table('business_settings')->updateOrInsert(
            ['type' => 'business_mode'],
            $attributes
        );

        Cache::forget('business_mode');
        Cache::forget('cache_business_settings_table');
    }

    public function down(): void
    {
        // Intentionally left blank because the prior business_mode value is environment-specific.
    }
};
