<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicle_years')) {
            return;
        }

        Schema::create('vehicle_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 4)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('vehicle_years')) {
            return;
        }

        Schema::dropIfExists('vehicle_years');
    }
};
