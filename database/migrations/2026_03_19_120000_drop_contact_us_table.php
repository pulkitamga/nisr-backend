<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('contact_us');
    }

    public function down(): void
    {
        Schema::create('contact_us', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('location')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });
    }
};
