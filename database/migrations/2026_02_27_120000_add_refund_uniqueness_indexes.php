<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const REFUND_REQUEST_UNIQUE = 'refund_requests_order_details_id_unique';
    private const REFUND_TRANSACTION_UNIQUE = 'refund_transactions_refund_id_unique';

    public function up(): void
    {
        $this->guardNoDuplicateValues(table: 'refund_requests', column: 'order_details_id', ignoreNulls: false);
        $this->guardNoDuplicateValues(table: 'refund_transactions', column: 'refund_id', ignoreNulls: true);

        if (!$this->indexExists(table: 'refund_requests', indexName: self::REFUND_REQUEST_UNIQUE)) {
            Schema::table('refund_requests', function (Blueprint $table) {
                $table->unique('order_details_id', self::REFUND_REQUEST_UNIQUE);
            });
        }

        if (!$this->indexExists(table: 'refund_transactions', indexName: self::REFUND_TRANSACTION_UNIQUE)) {
            Schema::table('refund_transactions', function (Blueprint $table) {
                $table->unique('refund_id', self::REFUND_TRANSACTION_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists(table: 'refund_transactions', indexName: self::REFUND_TRANSACTION_UNIQUE)) {
            Schema::table('refund_transactions', function (Blueprint $table) {
                $table->dropUnique(self::REFUND_TRANSACTION_UNIQUE);
            });
        }

        if ($this->indexExists(table: 'refund_requests', indexName: self::REFUND_REQUEST_UNIQUE)) {
            Schema::table('refund_requests', function (Blueprint $table) {
                $table->dropUnique(self::REFUND_REQUEST_UNIQUE);
            });
        }
    }

    private function guardNoDuplicateValues(string $table, string $column, bool $ignoreNulls): void
    {
        $query = DB::table($table)->select($column, DB::raw('COUNT(*) as duplicate_count'));
        if ($ignoreNulls) {
            $query->whereNotNull($column);
        }

        $duplicate = $query->groupBy($column)->having('duplicate_count', '>', 1)->first();
        if ($duplicate) {
            throw new RuntimeException("Cannot add unique index on {$table}.{$column}; duplicate values exist.");
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};

