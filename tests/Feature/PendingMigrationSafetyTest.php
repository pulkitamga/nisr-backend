<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PendingMigrationSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    public function test_career_baseline_migration_no_ops_when_tables_already_exist(): void
    {
        foreach ([
            'career_jobs',
            'career_sections',
            'career_benefits',
            'career_cards',
            'career_applies',
            'career_interviews',
            'career_activities',
            'career_offers',
            'career_rejections',
            'career_talent_pool',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
            });
        }

        $this->runMigration('database/migrations/2025_01_01_011_career_module_tables.php');

        $this->assertTrue(Schema::hasTable('career_jobs'));
        $this->assertTrue(Schema::hasTable('career_talent_pool'));
    }

    public function test_blacklist_index_migration_skips_when_unique_index_already_exists(): void
    {
        Schema::create('blacklists', function (Blueprint $table): void {
            $table->id();
            $table->string('serial_number')->nullable()->unique();
        });

        $this->runMigration('database/migrations/2026_03_18_006_add_blacklist_serial_index.php');

        $this->assertTrue($this->indexExists('blacklists', 'blacklists_serial_number_unique'));
    }

    public function test_warranty_follow_up_migrations_no_op_when_foreign_keys_already_exist(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('retailer_branch_id')->nullable();
            $table->foreign('retailer_branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('warranty_replacements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->foreign('technician_id')->references('id')->on('admins')->nullOnDelete();
        });

        Schema::create('warranty_timeline_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('admins')->nullOnDelete();
        });

        $this->runMigration('database/migrations/2026_03_18_003_fix_warranty_foreign_keys.php');
        $this->runMigration('database/migrations/2026_03_18_004_fix_replacement_technician_fk.php');
        $this->runMigration('database/migrations/2026_03_18_005_fix_timeline_user_fk.php');

        $this->assertSame(['retailer_branch_id'], $this->foreignKeyColumns('warranties'));
        $this->assertSame(['technician_id'], $this->foreignKeyColumns('warranty_replacements'));
        $this->assertSame(['user_id'], $this->foreignKeyColumns('warranty_timeline_events'));
    }

    public function test_service_job_catch_up_migration_no_ops_when_tables_already_exist(): void
    {
        foreach ([
            'service_jobs',
            'service_job_items',
            'service_job_activities',
            'service_estimates',
            'service_invoices',
            'service_change_orders',
            'service_cancellations',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
            });
        }

        $this->runMigration('database/migrations/2026_03_19_000007_create_service_job_tables.php');

        $this->assertTrue(Schema::hasTable('service_jobs'));
        $this->assertTrue(Schema::hasTable('service_cancellations'));
    }

    private function runMigration(string $relativePath): void
    {
        $migration = require base_path($relativePath);
        $migration->up();
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $indexes = DB::select("PRAGMA index_list('{$tableName}')");

        foreach ($indexes as $index) {
            if (($index->name ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }

    private function foreignKeyColumns(string $tableName): array
    {
        return collect(DB::select("PRAGMA foreign_key_list('{$tableName}')"))
            ->pluck('from')
            ->values()
            ->all();
    }
}
