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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('version')->nullable();
            $table->string('locale', 10)->default('en');
            $table->date('effective_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->longText('content_html')->nullable();
            $table->longText('content_text')->nullable();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // optional: add foreign key if created_by relates to users
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
