<?php

namespace App\Domain\Stock;

use App\Domain\Stock\DTO\StockValidationContext;
use App\Domain\Stock\DTO\StockValidationResult;
use App\Domain\Stock\Enums\StockValidationMode;
use App\Domain\Stock\Support\VariantMatcher;
use App\Models\ManageBranchProductStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StockAvailabilityService
{
    private VariantMatcher $variantMatcher;
    private StockPolicyResolver $policyResolver;

    public function __construct(
        ?VariantMatcher $variantMatcher = null,
        ?StockPolicyResolver $policyResolver = null
    ) {
        $this->variantMatcher = $variantMatcher ?? new VariantMatcher();
        $this->policyResolver = $policyResolver ?? new StockPolicyResolver();
    }

    public function validate(iterable $carts, StockValidationContext $context): StockValidationResult
    {
        if ((int)getWebConfig(name: 'stock_check') !== 1) {
            return StockValidationResult::success();
        }

        $mode = $this->policyResolver->resolve($context);
        if ($mode === StockValidationMode::NONE) {
            $this->incrementMetricCounter('stock_check_wholesale_bypass_total');
            Log::info('stock_check_bypass', [
                'channel' => $context->channel->value,
                'mode' => $mode->value,
                'delivery_type' => $context->deliveryType,
                'branch_id' => $context->branchId,
            ]);
            return StockValidationResult::success();
        }

        if ($mode === StockValidationMode::BRANCH) {
            $branchId = (int)($context->branchId ?? 0);
            if ($branchId <= 0) {
                $result = StockValidationResult::success();
                $result->addFailure([
                    'code' => 'missing_branch',
                    'message' => 'Branch id is required for branch stock validation',
                ]);
                $this->recordValidationObservability($result, $context, $mode);
                return $result;
            }

            $result = $this->validateBranch($carts, $branchId);
            $this->recordValidationObservability($result, $context, $mode);
            return $result;
        }

        $result = $this->validateGlobal($carts);
        $this->recordValidationObservability($result, $context, $mode);
        return $result;
    }

    public function validateGlobal(iterable $carts): StockValidationResult
    {
        $result = StockValidationResult::success();
        $requiredStockRows = $this->buildRequiredStockRows($carts, $result);
        if ($result->failed()) {
            return $result;
        }

        foreach ($requiredStockRows as $requiredRow) {
            $product = $requiredRow['product'];
            $requiredQty = (int)$requiredRow['qty'];
            $canonicalVariant = $requiredRow['canonical_variant'];
            $variantRaw = $requiredRow['raw_variant'] ?? null;

            $variationRows = $this->variantMatcher->decodeVariationRows($product->variation ?? []);
            $hasMatchingVariation = $this->hasMatchingVariation($variationRows, $canonicalVariant);
            if (!empty($variationRows) && !is_null($canonicalVariant)) {
                $availableQty = $this->resolveProductVariantQty($variationRows, $canonicalVariant);
            } else {
                $availableQty = (int)($product->current_stock ?? 0);
            }

            if ($availableQty < $requiredQty) {
                $result->addFailure([
                    'code' => 'out_of_stock_global',
                    'product_id' => (int)$product->id,
                    'variant' => $canonicalVariant,
                    'variant_raw' => $variantRaw,
                    'variant_normalized' => $canonicalVariant,
                    'variant_mismatch' => !is_null($canonicalVariant) && !empty($variationRows) && !$hasMatchingVariation,
                    'required_qty' => $requiredQty,
                    'available_qty' => $availableQty,
                ]);
            }
        }

        return $result;
    }

    public function validateBranch(iterable $carts, int $branchId): StockValidationResult
    {
        $result = StockValidationResult::success();
        $requiredStockRows = $this->buildRequiredStockRows($carts, $result);
        if ($result->failed()) {
            return $result;
        }

        $productIds = array_values(array_unique(array_map(
            fn($row) => (int)$row['product_id'],
            $requiredStockRows
        )));

        $branchStockRows = ManageBranchProductStock::query()
            ->where('branch_id', $branchId)
            ->whereIn('product_id', $productIds)
            ->get(['product_id', 'variation_type', 'variation_key', 'current_stock'])
            ->groupBy('product_id');

        foreach ($requiredStockRows as $requiredRow) {
            $productId = (int)$requiredRow['product_id'];
            $requiredQty = (int)$requiredRow['qty'];
            $canonicalVariant = $requiredRow['canonical_variant'];
            $variantRaw = $requiredRow['raw_variant'] ?? null;
            $productBranchRows = $branchStockRows->get($productId, collect());

            if ($productBranchRows->isEmpty()) {
                $result->addFailure([
                    'code' => 'missing_branch_stock',
                    'product_id' => $productId,
                    'variant' => $canonicalVariant,
                    'variant_raw' => $variantRaw,
                    'variant_normalized' => $canonicalVariant,
                    'variant_mismatch' => false,
                    'required_qty' => $requiredQty,
                    'available_qty' => 0,
                ]);
                continue;
            }

            $availableQty = (int)$productBranchRows->filter(function ($stockRow) use ($canonicalVariant) {
                if (is_null($canonicalVariant)) {
                    return $this->variantMatcher->isDefault($stockRow->variation_type ?? null)
                        || $this->variantMatcher->isDefault($stockRow->variation_key ?? null);
                }

                return $this->variantMatcher->matches($stockRow->variation_type ?? null, $canonicalVariant)
                    || $this->variantMatcher->matches($stockRow->variation_key ?? null, $canonicalVariant);
            })->sum('current_stock');

            $anyStockOnOtherVariants = (int)$productBranchRows->sum('current_stock') > 0;
            $variantMismatch = !is_null($canonicalVariant) && $availableQty === 0 && $anyStockOnOtherVariants;

            if ($availableQty < $requiredQty) {
                $result->addFailure([
                    'code' => 'out_of_stock_branch',
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'variant' => $canonicalVariant,
                    'variant_raw' => $variantRaw,
                    'variant_normalized' => $canonicalVariant,
                    'variant_mismatch' => $variantMismatch,
                    'required_qty' => $requiredQty,
                    'available_qty' => $availableQty,
                ]);
            }
        }

        return $result;
    }

    private function buildRequiredStockRows(iterable $carts, StockValidationResult $result): array
    {
        $requiredStock = [];

        foreach ($carts as $cart) {
            if ($this->isWholesaleCart($cart)) {
                continue;
            }

            $product = $cart->product ?? null;
            if (!$product) {
                $result->addFailure([
                    'code' => 'missing_product',
                    'cart_id' => (int)($cart->id ?? 0),
                    'message' => 'Product not found for cart row',
                ]);
                continue;
            }

            if (($cart->product_type ?? $product->product_type) !== 'physical') {
                continue;
            }

            $productId = (int)($cart->product_id ?? $product->id);
            $rawVariant = $this->extractRawVariant($cart);
            $canonicalVariant = $this->variantMatcher->canonicalFromProduct(
                $rawVariant,
                $product->variation ?? []
            );

            $key = $productId . '|' . ($canonicalVariant ?? '__default__');
            if (!isset($requiredStock[$key])) {
                $requiredStock[$key] = [
                    'product_id' => $productId,
                    'canonical_variant' => $canonicalVariant,
                    'raw_variant' => $rawVariant,
                    'qty' => 0,
                    'product' => $product,
                ];
            }

            $requiredStock[$key]['qty'] += (int)($cart->quantity ?? 0);
        }

        return $requiredStock;
    }

    private function resolveProductVariantQty(array $variationRows, string $canonicalVariant): int
    {
        foreach ($variationRows as $variationRow) {
            if ($this->variantMatcher->matches($variationRow['type'] ?? null, $canonicalVariant)) {
                return (int)($variationRow['qty'] ?? 0);
            }
        }

        return 0;
    }

    private function hasMatchingVariation(array $variationRows, ?string $canonicalVariant): bool
    {
        if (is_null($canonicalVariant)) {
            return true;
        }

        foreach ($variationRows as $variationRow) {
            if ($this->variantMatcher->matches($variationRow['type'] ?? null, $canonicalVariant)) {
                return true;
            }
        }

        return false;
    }

    private function extractRawVariant(mixed $cart): ?string
    {
        $variant = null;
        if (is_array($cart)) {
            $variant = $cart['variant'] ?? null;
        } elseif (is_object($cart)) {
            $variant = $cart->variant ?? null;
        }

        $raw = trim((string)($variant ?? ''));
        return $raw !== '' ? $raw : null;
    }

    private function isWholesaleCart(mixed $cart): bool
    {
        $cartGroupId = '';
        if (is_array($cart)) {
            $cartGroupId = (string)($cart['cart_group_id'] ?? '');
        } elseif (is_object($cart)) {
            $cartGroupId = (string)($cart->cart_group_id ?? '');
        }

        if ($cartGroupId === '') {
            return false;
        }

        $cartGroupId = strtolower(trim($cartGroupId));
        return str_starts_with($cartGroupId, 'wh-') || str_starts_with($cartGroupId, 'wholesale_');
    }

    private function recordValidationObservability(
        StockValidationResult $result,
        StockValidationContext $context,
        StockValidationMode $mode
    ): void {
        if ($result->passed()) {
            return;
        }

        $channel = $context->channel->value;
        $failures = $result->failures();
        $this->incrementMetricCounter("stock_check_fail_total:{$channel}:{$mode->value}", count($failures));

        $mismatchCount = 0;
        foreach ($failures as $failure) {
            if (!empty($failure['variant_mismatch'])) {
                $mismatchCount++;
            }

            Log::warning('stock_check_failed', [
                'channel' => $channel,
                'mode' => $mode->value,
                'delivery_type' => $context->deliveryType,
                'branch_id' => $context->branchId,
                'product_id' => $failure['product_id'] ?? null,
                'variant_raw' => $failure['variant_raw'] ?? null,
                'variant_normalized' => $failure['variant_normalized'] ?? ($failure['variant'] ?? null),
                'requested_qty' => (int)($failure['required_qty'] ?? 0),
                'available_qty' => (int)($failure['available_qty'] ?? 0),
                'failure_code' => $failure['code'] ?? null,
            ]);
        }

        if ($mismatchCount > 0) {
            $this->incrementMetricCounter('stock_check_variant_mismatch_total', $mismatchCount);
        }
    }

    private function incrementMetricCounter(string $key, int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        try {
            if (!Cache::has($key)) {
                Cache::forever($key, 0);
            }
            Cache::increment($key, $amount);
        } catch (\Throwable $exception) {
            Log::debug('stock_metric_increment_failed', [
                'metric_key' => $key,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
