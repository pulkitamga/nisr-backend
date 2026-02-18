<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_distribution_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained('warranties')->onDelete('cascade');
            $table->unsignedBigInteger('from_distributor_id')->nullable();
            $table->unsignedBigInteger('to_distributor_id')->nullable();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamp('timestamp')->useCurrent();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_distribution_histories');
    }
};