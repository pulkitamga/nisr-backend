<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->nullable()->constrained('warranties')->onDelete('cascade');
            $table->foreignId('warranty_claim_id')->nullable()->constrained('warranty_claims')->onDelete('cascade');
            $table->string('event_type');  // activated, claimed, rma_issued, etc.
            $table->text('description');
            $table->timestamp('timestamp')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_timeline_events');
    }
};