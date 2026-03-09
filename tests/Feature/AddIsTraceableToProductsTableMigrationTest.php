<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddIsTraceableToProductsTableMigrationTest extends TestCase
{
    public function test_migration_adds_is_traceable_column_when_missing(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        Schema::dropIfExists('products');
        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_warranty')->default(0);
            $table->timestamps();
        });

        /** @var object $migration */
        $migration = include base_path('database/migrations/2026_03_08_160000_add_is_traceable_to_products_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('products', 'is_traceable'));
    }
}
