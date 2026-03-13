<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('warranty_claim_id')->nullable()->after('id');
            $table->string('status')->nullable()->after('warranty_claim_id');
            $table->json('checklist_items')->nullable()->after('status');
            $table->text('diagnosis')->nullable()->after('checklist_items');
            $table->json('parts_used')->nullable()->after('diagnosis');
            $table->decimal('labor_hours', 8, 2)->nullable()->after('parts_used');

            // foreign key optional (better)
            $table->foreign('warranty_claim_id')->references('id')->on('warranty_claims')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['warranty_claim_id']);
            $table->dropColumn(['warranty_claim_id', 'status', 'checklist_items', 'diagnosis', 'parts_used', 'labor_hours']);
        });
    }
};
