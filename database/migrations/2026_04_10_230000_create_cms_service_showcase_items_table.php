<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_service_showcase_items')) {
            return;
        }

        Schema::create('cms_service_showcase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_service_id')->constrained('cms_services')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->string('card_type')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('primary_button_text')->nullable();
            $table->string('primary_button_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_service_showcase_items');
    }
};
