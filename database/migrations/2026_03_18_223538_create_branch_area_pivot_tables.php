<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create pivot table for branches and shipping method areas
        Schema::create('branch_shipping_method_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('shipping_method_area_id')->constrained('shipping_method_areas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['branch_id', 'shipping_method_area_id'], 'branch_shipping_area_unique');
        });

        // Create pivot table for branches and delivery restrictions
        Schema::create('branch_delivery_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('delivery_area_id')->constrained('delivery_areas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['branch_id', 'delivery_area_id'], 'branch_delivery_restriction_unique');
        });

        // Migrate existing data from comma-separated columns to pivot tables
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_delivery_restrictions');
        Schema::dropIfExists('branch_shipping_method_areas');
    }

    /**
     * Migrate existing comma-separated data to pivot tables.
     */
    protected function migrateExistingData(): void
    {
        // Migrate shipping method areas
        $branches = DB::table('branches')
            ->select('id', 'shipping_methods_area')
            ->whereNotNull('shipping_methods_area')
            ->where('shipping_methods_area', '!=', '')
            ->get();

        foreach ($branches as $branch) {
            $areaIds = array_filter(array_map('trim', explode(',', $branch->shipping_methods_area)));
            foreach ($areaIds as $areaId) {
                if (is_numeric($areaId) && $areaId > 0) {
                    DB::table('branch_shipping_method_areas')->insert([
                        'branch_id' => $branch->id,
                        'shipping_method_area_id' => (int) $areaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Migrate delivery restrictions
        $branches = DB::table('branches')
            ->select('id', 'delivery_restriction')
            ->whereNotNull('delivery_restriction')
            ->where('delivery_restriction', '!=', '')
            ->get();

        foreach ($branches as $branch) {
            $areaIds = array_filter(array_map('trim', explode(',', $branch->delivery_restriction)));
            foreach ($areaIds as $areaId) {
                if (is_numeric($areaId) && $areaId > 0) {
                    DB::table('branch_delivery_restrictions')->insert([
                        'branch_id' => $branch->id,
                        'delivery_area_id' => (int) $areaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
