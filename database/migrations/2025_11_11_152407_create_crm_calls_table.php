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
        Schema::create('crm_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique()->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->timestamp('call_date');
            $table->integer('call_duration')->default(0);
            $table->text('call_notes')->nullable();
            $table->string('direction'); // inbound/outbound
            $table->string('status')->default('ringing'); // ringing, ongoing, completed
            $table->timestamps();

            // Foreign key constraints defined after column creation
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('agent_id')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_calls');
    }
};
