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
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->boolean('tamper_detected')->default(false)->after('repair_or_replace');
            $table->string('replacement_mode')->nullable()->after('tamper_detected'); // store values like 'full', 'partial', 'paid_replacement', etc.
            $table->string('tracking_number')->nullable()->after('replacement_mode');
            $table->timestamp('dispatched_at')->nullable()->after('tracking_number');
            $table->timestamp('qc_passed_at')->nullable()->after('dispatched_at');
            $table->timestamp('resolved_at')->nullable()->after('qc_passed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropColumn([
                'tamper_detected',
                'replacement_mode',
                'tracking_number',
                'dispatched_at',
                'qc_passed_at',
                'resolved_at',
            ]);
        });
    }
};
