<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_warranty_id')->constrained('warranties')->onDelete('cascade');
            $table->foreignId('new_warranty_id')->constrained('warranties')->onDelete('cascade');
            $table->timestamp('replaced_at')->useCurrent();
            $table->foreignId('technician_id')
                ->nullable() // allow NULL values
                ->constrained('admins')
                ->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_replacements');
    }
};
