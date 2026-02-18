<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'manage_branch_product_stock';
    private string $uniqueIndexName = 'mbps_branch_product_variation_unique';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        DB::transaction(function () {
            $hasVariationType = Schema::hasColumn($this->table, 'variation_type');
            $hasVariationKey = Schema::hasColumn($this->table, 'variation_key');
            $hasCurrentStock = Schema::hasColumn($this->table, 'current_stock');

            if (!$hasVariationType || !$hasVariationKey || !$hasCurrentStock) {
                return;
            }

            DB::table($this->table)
                ->whereIn('variation_type', ['', 'No Variation', 'no variation', 'Default', 'default'])
                ->update(['variation_type' => null]);

            DB::table($this->table)
                ->whereIn('variation_key', ['', 'No Variation', 'no variation', 'Default', 'default'])
                ->update(['variation_key' => null]);

            $duplicates = DB::table($this->table)
                ->selectRaw('MIN(id) AS keep_id')
                ->selectRaw('branch_id, product_id')
                ->selectRaw("COALESCE(variation_type, '') AS variation_type_norm")
                ->selectRaw("COALESCE(variation_key, '') AS variation_key_norm")
                ->selectRaw('SUM(current_stock) AS merged_stock')
                ->selectRaw('COUNT(*) AS row_count')
                ->groupBy('branch_id', 'product_id')
                ->groupByRaw("COALESCE(variation_type, '')")
                ->groupByRaw("COALESCE(variation_key, '')")
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                DB::table($this->table)
                    ->where('id', $duplicate->keep_id)
                    ->update([
                        'variation_type' => $duplicate->variation_type_norm !== '' ? $duplicate->variation_type_norm : null,
                        'variation_key' => $duplicate->variation_key_norm !== '' ? $duplicate->variation_key_norm : null,
                        'current_stock' => max(0, (int)$duplicate->merged_stock),
                    ]);

                DB::table($this->table)
                    ->where('branch_id', $duplicate->branch_id)
                    ->where('product_id', $duplicate->product_id)
                    ->whereRaw("COALESCE(variation_type, '') = ?", [$duplicate->variation_type_norm])
                    ->whereRaw("COALESCE(variation_key, '') = ?", [$duplicate->variation_key_norm])
                    ->where('id', '!=', $duplicate->keep_id)
                    ->delete();
            }
        });

        if (!$this->indexExists($this->uniqueIndexName)) {
            $indexName = $this->uniqueIndexName;
            Schema::table($this->table, function (Blueprint $table) use ($indexName) {
                $table->unique(
                    ['branch_id', 'product_id', 'variation_type', 'variation_key'],
                    $indexName
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        if ($this->indexExists($this->uniqueIndexName)) {
            $indexName = $this->uniqueIndexName;
            Schema::table($this->table, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();
        $result = DB::select(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$databaseName, $this->table, $indexName]
        );

        return !empty($result);
    }
};
