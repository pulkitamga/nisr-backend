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
        Schema::table('warranties', function (Blueprint $table) {
            $table->string('warranty_public_id')->nullable()->unique()->after('id');
            $table->string('policy_version')->nullable()->after('warranty_public_id');
            $table->boolean('consent_checked')->default(false)->after('policy_version');
            $table->dateTime('consent_timestamp')->nullable()->after('consent_checked');
            $table->string('consent_ip')->nullable()->after('consent_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_public_id',
                'policy_version',
                'consent_checked',
                'consent_timestamp',
                'consent_ip',
            ]);
        });
    }
};
