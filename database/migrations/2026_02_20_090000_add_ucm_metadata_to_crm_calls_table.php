<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('crm_calls')) {
            return;
        }

        Schema::table('crm_calls', function (Blueprint $table) {
            try {
                $table->dropForeign(['customer_id']);
            } catch (\Throwable) {
                // Foreign key may not exist in some environments.
            }
        });

        Schema::table('crm_calls', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();

            $table->string('ucm_channel')->nullable();
            $table->string('ucm_peer_channel')->nullable();
            $table->string('ucm_uniqueid')->nullable();
            $table->string('ucm_bridge_id')->nullable();
            $table->string('src_number')->nullable();
            $table->string('dst_number')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->longText('raw_payload')->nullable();

            $table->index('ucm_channel');
            $table->index('ucm_uniqueid');
            $table->index('ucm_bridge_id');
            $table->index('src_number');
            $table->index('dst_number');
        });

        Schema::table('crm_calls', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('crm_calls')) {
            return;
        }

        Schema::table('crm_calls', function (Blueprint $table) {
            try {
                $table->dropForeign(['customer_id']);
            } catch (\Throwable) {
                // Ignore if key does not exist.
            }
        });

        $fallbackCustomerId = Schema::hasTable('users') ? DB::table('users')->value('id') : null;
        if ($fallbackCustomerId) {
            DB::table('crm_calls')
                ->whereNull('customer_id')
                ->update(['customer_id' => $fallbackCustomerId]);
        }

        Schema::table('crm_calls', function (Blueprint $table) {
            foreach (['ucm_channel', 'ucm_uniqueid', 'ucm_bridge_id', 'src_number', 'dst_number'] as $indexColumn) {
                try {
                    $table->dropIndex([$indexColumn]);
                } catch (\Throwable) {
                    // Ignore missing index.
                }
            }

            $table->dropColumn([
                'ucm_channel',
                'ucm_peer_channel',
                'ucm_uniqueid',
                'ucm_bridge_id',
                'src_number',
                'dst_number',
                'started_at',
                'answered_at',
                'ended_at',
                'raw_payload',
            ]);
        });

        Schema::table('crm_calls', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });

        Schema::table('crm_calls', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
