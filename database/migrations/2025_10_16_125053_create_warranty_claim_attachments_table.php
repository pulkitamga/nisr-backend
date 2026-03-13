<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_claim_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_claim_id')->constrained('warranty_claims')->onDelete('cascade');
            $table->string('file_path');
            $table->string('type')->default('document');  // photo, receipt, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_claim_attachments');
    }
};