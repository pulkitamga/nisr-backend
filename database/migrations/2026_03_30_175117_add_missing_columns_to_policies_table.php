<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            if (!Schema::hasColumn('policies', 'locale')) {
                $table->string('locale', 10)->default('en')->after('version');
            }
            if (!Schema::hasColumn('policies', 'status')) {
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('locale');
            }
            if (!Schema::hasColumn('policies', 'content_html')) {
                $table->longText('content_html')->nullable()->after('status');
            }
            if (!Schema::hasColumn('policies', 'content_text')) {
                $table->longText('content_text')->nullable()->after('content_html');
            }
            if (!Schema::hasColumn('policies', 'slug')) {
                $table->string('slug')->unique()->after('content_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $columns = ['slug', 'content_text', 'content_html', 'status', 'locale'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('policies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
