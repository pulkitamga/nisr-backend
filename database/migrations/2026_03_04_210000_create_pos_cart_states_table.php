<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_cart_states', function (Blueprint $table) {
            $table->id();
            $table->string('cart_id')->unique();
            $table->string('actor_type', 20)->index();
            $table->unsignedBigInteger('actor_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->longText('payload')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cart_states');
    }
};

