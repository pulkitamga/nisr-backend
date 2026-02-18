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
        Schema::create('view_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('jti')->unique();
            $table->string('warranty_public_id');
            $table->string('recipient_hash')->nullable();
            $table->string('scope');
            $table->dateTime('issued_at');
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_tokens');
    }
};
