<?php

namespace App\Console\Commands;

use App\Models\ManageBranchProductStock;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InventoryReconcileCommand extends Command
{
    protected $signature = 'inventory:reconcile
                            {--dry-run : Detect drift without changing data}
                            {--fix : Sync compatibility mirrors from product_stocks}
                            {--self-heal-ledger : Rebuild product_stocks from branch stock and sync compatibility mirrors}
                            {--approve-self-heal= : Required phrase for self-heal mode}
                            {--all-products : Allow self-heal mode across all products}
                            {--product_id=* : Limit reconcile to one or more product IDs}';

    protected $description = 'Reconcile inventory drift between product_stocks, branch stock, and product mirrors';

    private const DEFAULT_VARIANT_KEY = '__default__';
    private const SELF_HEAL_APPROVAL_PHRASE = 'HEAL_INVENTORY_NOW';

    public function handle(): int
    {
        $fixMode = (bool)$this->option('fix');
        $selfHealLedger = (bool)$this->option('self-heal-ledger');
        $dryRunFlag = (bool)$this->option('dry-run');
        $allowAllProducts = (bool)$this->option('all-products');
        $approvalPhrase = trim((string)$this->option('approve-self-heal'));
        $productIds = collect((array)$this->option('product_id'))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values()
            ->all();

        if (($fixMode || $selfHealLedger) && $dryRunFlag) {
            $this->error('`--dry-run` cannot be combined with `--fix` or `--self-heal-ledger`.');
            return self::FAILURE;
        }

        if ($selfHealLedger) {
            if ($approvalPhrase !== self::SELF_HEAL_APPROVAL_PHRASE) {
                $this->error('Self-heal is blocked. Pass `--approve-self-heal=' . self::SELF_HEAL_APPROVAL_PHRASE . '`.');
                return self::FAILURE;
            }

            if (empty($productIds) && !$allowAllProducts) {
                $this->error('Self-heal requires `--product_id=*` or explicit `--all-products`.');
                return self::FAILURE;
            }
        }

        $dryRun = !$fixMode && !$selfHealLedger;

        if ($selfHealLedger) {
            $this->warn('Running inventory reconciliation in SELF-HEAL mode (product_stocks <- branch stock, mirrors synced).');
        } elseif ($dryRun) {
            $this->info('Running inventory reconciliation in DRY-RUN mode.');
        } else {
            $this->info('Running inventory reconciliation in FIX mode (mirror fields only).');
        }

        $query = Product::query()
            ->select(['id', 'name', 'current_stock', 'variation'])
            ->orderBy('id');

        if (!empty($productIds)) {
            $query->whereIn('id', $productIds);
        }

        $totalProducts = 0;
        $driftedProducts = 0;
        $fixedProducts = 0;
        $driftLines = [];

        foreach ($query->cursor() as $product) {
            $totalProducts++;

            $productStocks = ProductStock::query()
                ->where('product_id', $product->id)
                ->get(['variant', 'qty']);

            $branchStocks = ManageBranchProductStock::query()
                ->where('product_id', $product->id)
                ->get(['branch_id', 'variation_type', 'variation_key', 'current_stock']);

            $productStockTotal = (int)$productStocks->sum('qty');
            $branchStockTotal = (int)$branchStocks->sum('current_stock');

            $productStockMap = $this->buildProductStockMap($productStocks->toArray());
            $branchMapPayload = $this->buildBranchStockMap($branchStocks->toArray());
            $branchStockMap = $branchMapPayload['qty_map'];
            $branchDuplicateCount = $branchMapPayload['duplicate_count'];

            $variationRows = $this->decodeProductVariationRows($product->variation);
            $variationMap = $this->buildVariationMap($variationRows);

            $issues = [];
            if ((int)$product->current_stock !== $productStockTotal) {
                $issues[] = "products.current_stock={$product->current_stock}, product_stocks.sum={$productStockTotal}";
            }

            if ($productStockTotal !== $branchStockTotal) {
                $issues[] = "product_stocks.sum={$productStockTotal}, branch_stock.sum={$branchStockTotal}";
            }

            if ($branchDuplicateCount > 0) {
                $issues[] = "duplicate branch stock keys detected={$branchDuplicateCount}";
            }

            $variantKeys = collect(array_merge(
                array_keys($productStockMap),
                array_keys($branchStockMap),
                array_keys($variationMap)
            ))->unique()->values()->all();

            foreach ($variantKeys as $variantKey) {
                $psQty = (int)($productStockMap[$variantKey] ?? 0);
                $bsQty = (int)($branchStockMap[$variantKey] ?? 0);

                if ($psQty !== $bsQty) {
                    $issues[] = "variant[{$variantKey}] product_stocks={$psQty}, branch_stock={$bsQty}";
                }

                if (array_key_exists($variantKey, $variationMap) && (int)$variationMap[$variantKey] !== $psQty) {
                    $issues[] = "variant[{$variantKey}] product_json=" . (int)$variationMap[$variantKey] . ", product_stocks={$psQty}";
                }
            }

            if (empty($issues)) {
                continue;
            }

            $driftedProducts++;
            $driftLines[] = "Product {$product->id} ({$product->name}): " . implode(' | ', $issues);

            if (!$dryRun) {
                if ($selfHealLedger) {
                    $didFix = $this->applyLedgerAndMirrorFix(
                        productId: (int)$product->id
                    );
                } else {
                    $didFix = $this->applyMirrorFix(
                        productId: (int)$product->id,
                        productStockTotal: $productStockTotal,
                        productStockMap: $productStockMap
                    );
                }

                if ($didFix) {
                    $fixedProducts++;
                }
            }
        }

        $this->line('---');
        $this->info("Products scanned: {$totalProducts}");
        $this->info("Products with drift: {$driftedProducts}");
        if (!$dryRun) {
            $label = $selfHealLedger ? 'Products healed (ledger + mirrors)' : 'Products fixed (mirror fields)';
            $this->info("{$label}: {$fixedProducts}");
        }

        if (!empty($driftLines)) {
            $this->line('---');
            $this->warn('Drift details:');
            foreach ($driftLines as $line) {
                $this->line("- {$line}");
            }
        } else {
            $this->info('No drift detected.');
        }

        return self::SUCCESS;
    }

    private function applyMirrorFix(int $productId, int $productStockTotal, array $productStockMap): bool
    {
        return DB::transaction(function () use ($productId, $productStockTotal, $productStockMap) {
            $product = Product::query()->lockForUpdate()->find($productId);
            if (!$product) {
                return false;
            }

            return $this->syncProductMirrorsFromMap(
                product: $product,
                productStockTotal: $productStockTotal,
                productStockMap: $productStockMap
            );
        });
    }

    private function applyLedgerAndMirrorFix(int $productId): bool
    {
        return DB::transaction(function () use ($productId) {
            $product = Product::query()->lockForUpdate()->find($productId);
            if (!$product) {
                return false;
            }

            $branchRows = ManageBranchProductStock::query()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->get();

            $dedupePayload = $this->deduplicateBranchRows($branchRows);
            $branchStockMap = $dedupePayload['qty_map'];
            $didBranchChange = $dedupePayload['did_change'];

            $stockRows = ProductStock::query()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->get();

            $rowsByKey = [];
            foreach ($stockRows as $stockRow) {
                $variantKey = $this->normalizeVariantKey($stockRow->variant);
                if (!array_key_exists($variantKey, $rowsByKey)) {
                    $rowsByKey[$variantKey] = [];
                }
                $rowsByKey[$variantKey][] = $stockRow;
            }

            $targetVariantKeys = collect(array_merge(
                array_keys($rowsByKey),
                array_keys($branchStockMap)
            ))->unique()->values()->all();

            $productStockMap = [];
            $didLedgerChange = false;

            foreach ($targetVariantKeys as $variantKey) {
                $targetQty = max(0, (int)($branchStockMap[$variantKey] ?? 0));
                $productStockMap[$variantKey] = $targetQty;

                $variantRows = $rowsByKey[$variantKey] ?? [];
                if (empty($variantRows)) {
                    if ($targetQty <= 0) {
                        continue;
                    }

                    ProductStock::query()->create([
                        'product_id' => $productId,
                        'variant' => $variantKey === self::DEFAULT_VARIANT_KEY ? null : $variantKey,
                        'sku' => null,
                        'price' => 0,
                        'qty' => $targetQty,
                    ]);
                    $didLedgerChange = true;
                    continue;
                }

                $primary = array_shift($variantRows);
                if ((int)$primary->qty !== $targetQty) {
                    $primary->qty = $targetQty;
                    $primary->save();
                    $didLedgerChange = true;
                }

                foreach ($variantRows as $duplicateRow) {
                    if ((int)$duplicateRow->qty !== 0) {
                        $duplicateRow->qty = 0;
                        $duplicateRow->save();
                        $didLedgerChange = true;
                    }
                }
            }

            $total = (int)array_sum($productStockMap);
            $didMirrorSync = $this->syncProductMirrorsFromMap(
                product: $product,
                productStockTotal: $total,
                productStockMap: $productStockMap
            );

            return $didBranchChange || $didLedgerChange || $didMirrorSync;
        });
    }

    private function syncProductMirrorsFromMap(Product $product, int $productStockTotal, array $productStockMap): bool
    {
            $didChange = false;
            if ((int)$product->current_stock !== $productStockTotal) {
                $product->current_stock = max(0, $productStockTotal);
                $didChange = true;
            }

            $variationRows = $this->decodeProductVariationRows($product->variation);
            if (!empty($variationRows)) {
                foreach ($variationRows as $index => $variationRow) {
                    $variantType = trim((string)($variationRow['type'] ?? ''));
                    if ($variantType === '') {
                        continue;
                    }

                    $targetQty = (int)($productStockMap[$variantType] ?? 0);
                    $existingQty = (int)($variationRow['qty'] ?? 0);
                    if ($existingQty !== $targetQty) {
                        $variationRows[$index]['qty'] = $targetQty;
                        $didChange = true;
                    }
                }

                if ($didChange) {
                    $product->variation = json_encode($variationRows);
                }
            }

            if ($didChange) {
                $product->save();
            }

            return $didChange;
    }

    private function buildProductStockMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $variantKey = $this->normalizeVariantKey($row['variant'] ?? null);
            $map[$variantKey] = (int)($map[$variantKey] ?? 0) + (int)($row['qty'] ?? 0);
        }
        return $map;
    }

    private function buildBranchStockMap(array $rows): array
    {
        $qtyMap = [];
        $countMap = [];

        foreach ($rows as $row) {
            $branchId = (int)($row['branch_id'] ?? 0);
            $variantKey = $this->normalizeBranchVariantKey(
                $row['variation_type'] ?? null,
                $row['variation_key'] ?? null
            );
            $qtyMap[$variantKey] = (int)($qtyMap[$variantKey] ?? 0) + (int)($row['current_stock'] ?? 0);
            $duplicateKey = $branchId . '|' . $variantKey;
            $countMap[$duplicateKey] = (int)($countMap[$duplicateKey] ?? 0) + 1;
        }

        $duplicateCount = collect($countMap)
            ->filter(fn($count) => $count > 1)
            ->count();

        return [
            'qty_map' => $qtyMap,
            'duplicate_count' => $duplicateCount,
        ];
    }

    private function deduplicateBranchRows($branchRows): array
    {
        $groups = [];
        foreach ($branchRows as $row) {
            $variantKey = $this->normalizeBranchVariantKey($row->variation_type, $row->variation_key);
            $groupKey = ((int)$row->branch_id) . '|' . $variantKey;

            if (!array_key_exists($groupKey, $groups)) {
                $groups[$groupKey] = [
                    'branch_id' => (int)$row->branch_id,
                    'variant_key' => $variantKey,
                    'rows' => [],
                ];
            }

            $groups[$groupKey]['rows'][] = $row;
        }

        $didChange = false;
        foreach ($groups as $group) {
            $rows = $group['rows'];
            if (count($rows) <= 0) {
                continue;
            }

            usort($rows, fn($a, $b) => (int)$a->id <=> (int)$b->id);
            $primary = $rows[0];
            $totalQty = collect($rows)->sum(fn($row) => (int)($row->current_stock ?? 0));

            $targetType = $group['variant_key'] === self::DEFAULT_VARIANT_KEY ? null : $group['variant_key'];
            $targetKey = $group['variant_key'] === self::DEFAULT_VARIANT_KEY ? null : $group['variant_key'];

            if (
                (int)$primary->current_stock !== (int)$totalQty ||
                (($primary->variation_type ?? null) !== $targetType) ||
                (($primary->variation_key ?? null) !== $targetKey)
            ) {
                $primary->current_stock = max(0, (int)$totalQty);
                $primary->variation_type = $targetType;
                $primary->variation_key = $targetKey;
                $primary->save();
                $didChange = true;
            }

            for ($idx = 1; $idx < count($rows); $idx++) {
                $rows[$idx]->delete();
                $didChange = true;
            }
        }

        $freshRows = ManageBranchProductStock::query()
            ->where('product_id', $branchRows->first()->product_id ?? 0)
            ->get(['branch_id', 'variation_type', 'variation_key', 'current_stock'])
            ->toArray();

        $branchMapPayload = $this->buildBranchStockMap($freshRows);

        return [
            'did_change' => $didChange,
            'qty_map' => $branchMapPayload['qty_map'],
        ];
    }

    private function buildVariationMap(array $variationRows): array
    {
        $map = [];
        foreach ($variationRows as $row) {
            $variantType = trim((string)($row['type'] ?? ''));
            if ($variantType === '') {
                continue;
            }
            $map[$variantType] = (int)($row['qty'] ?? 0);
        }
        return $map;
    }

    private function decodeProductVariationRows(mixed $variation): array
    {
        if (is_array($variation)) {
            return $variation;
        }

        if (is_string($variation) && $variation !== '') {
            $decoded = json_decode($variation, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function normalizeVariantKey(mixed $variant): string
    {
        $normalized = trim((string)($variant ?? ''));
        if ($normalized === '' || strtolower($normalized) === 'null' || strtolower($normalized) === 'no variation') {
            return self::DEFAULT_VARIANT_KEY;
        }
        return $normalized;
    }

    private function normalizeBranchVariantKey(mixed $variationType, mixed $variationKey): string
    {
        $type = trim((string)($variationType ?? ''));
        $key = trim((string)($variationKey ?? ''));

        if ($type !== '' && strtolower($type) !== 'no variation') {
            return $type;
        }

        if ($key !== '' && strtolower($key) !== 'no variation') {
            return $key;
        }

        return self::DEFAULT_VARIANT_KEY;
    }
}
