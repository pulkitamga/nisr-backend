<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = array_map(fn($fk) => $fk->getName(), $sm->listTableForeignKeys('policies'));

            foreach ($foreignKeys as $foreignKey) {
                if (str_contains($foreignKey, 'created_by')) {
                    $table->dropForeign($foreignKey);
                }
            }

            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
