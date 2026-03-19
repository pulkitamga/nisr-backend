<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmInboxMigrationChainTest extends TestCase
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

    public function test_fresh_crm_migration_chain_creates_current_inbox_contract(): void
    {
        $this->createCrmMigrationPrerequisiteTables();

        $this->runMigration('database/migrations/2025_01_01_015_crm_module_tables.php');
        $this->runMigration('database/migrations/2026_03_19_000001_fix_inbox_message_id_typo.php');
        $this->runMigration('database/migrations/2026_03_19_000002_add_soft_deletes_to_crm_tables.php');
        $this->runMigration('database/migrations/2026_03_19_000003_add_crm_performance_indexes.php');

        $this->assertTrue(Schema::hasColumns('inbox_messages', [
            'subject',
            'body',
            'contact_id',
            'pipeline',
            'message_type',
            'related_lead_id',
            'owner_id',
            'department_id',
            'employee_id',
            'message',
            'deleted_at',
        ]));
        $this->assertTrue(Schema::hasColumns('inbox_activities', ['message_id', 'employee_id', 'title', 'subject', 'note_date']));
        $this->assertTrue(Schema::hasColumns('inbox_tasks', ['message_id', 'employee_id', 'department_id', 'name', 'due_date']));
        $this->assertTrue(Schema::hasColumns('inbox_calls', ['message_id', 'employee_id', 'department_id', 'title', 'from', 'to']));
        $this->assertTrue(Schema::hasColumns('inbox_notes', ['message_id', 'employee_id', 'note', 'noted_at']));
        $this->assertTrue(Schema::hasColumns('inbox_files', ['message_id', 'employee_id', 'file']));
        $this->assertTrue(Schema::hasColumn('leads', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('deals', 'deleted_at'));
        $this->assertFalse(Schema::hasColumn('inbox_activities', 'massage_id'));
        $this->assertFalse(Schema::hasColumn('inbox_activities', 'inbox_message_id'));
        $this->assertTrue($this->indexExists('inbox_messages', 'idx_inbox_status_owner'));
        $this->assertTrue($this->indexExists('deal_activities', 'idx_deal_activities_timeline'));
    }

    public function test_typo_migration_renames_legacy_inbox_message_columns(): void
    {
        Schema::create('inbox_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('massage_id');
            $table->timestamps();
        });

        Schema::create('inbox_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('inbox_message_id');
            $table->timestamps();
        });

        Schema::create('inbox_tasks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('massage_id');
            $table->timestamps();
        });

        Schema::create('inbox_calls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('inbox_message_id');
            $table->timestamps();
        });

        Schema::create('inbox_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('massage_id');
            $table->timestamps();
        });

        $this->runMigration('database/migrations/2026_03_19_000001_fix_inbox_message_id_typo.php');

        $this->assertTrue(Schema::hasColumn('inbox_activities', 'message_id'));
        $this->assertTrue(Schema::hasColumn('inbox_notes', 'message_id'));
        $this->assertTrue(Schema::hasColumn('inbox_tasks', 'message_id'));
        $this->assertTrue(Schema::hasColumn('inbox_calls', 'message_id'));
        $this->assertTrue(Schema::hasColumn('inbox_files', 'message_id'));
        $this->assertFalse(Schema::hasColumn('inbox_activities', 'massage_id'));
        $this->assertFalse(Schema::hasColumn('inbox_notes', 'inbox_message_id'));
    }

    private function createCrmMigrationPrerequisiteTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
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
}
