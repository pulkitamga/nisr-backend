<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\StockReason;
use App\Models\Branch;
use App\Models\ManageBranchProductStock;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductStockTransaction;
use Illuminate\Support\Facades\DB;

class InventoryMutationService
{
    private ?VariantMatcher $variantMatcher = null;

    public function seedInitialPhysicalInventory(Product $product, int $branchId): array
    {
        if ($product->product_type !== 'physical') {
            return ['status' => true, 'message' => ''];
        }

        try {
            $resolvedBranchId = $branchId > 0 ? $branchId : $this->resolvePosBranchId($product);
            $variations = json_decode($product->variation ?? '[]', true);

            if (!is_array($variations) || count($variations) === 0) {
                $this->seedInitialStockRow(
                    product: $product,
                    branchId: $resolvedBranchId,
                    variantType: null,
                    qty: (int)($product->current_stock ?? 0),
                    sku: $product->code,
                    price: (float)($product->unit_price ?? 0)
                );

                return ['status' => true, 'message' => ''];
            }

            foreach ($variations as $variation) {
                $variantType = $this->normalizeVariantType($variation['type'] ?? null);
                if ($variantType === null) {
                    continue;
                }

                $this->seedInitialStockRow(
                    product: $product,
                    branchId: $resolvedBranchId,
                    variantType: $variantType,
                    qty: (int)($variation['qty'] ?? 0),
                    sku: (string)($variation['sku'] ?? $product->code ?? ''),
                    price: (float)($variation['price'] ?? $product->unit_price ?? 0)
                );
            }

            return ['status' => true, 'message' => ''];
        } catch (\Throwable $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function decreaseForPosLine(
        int $productId,
        int $qty,
        mixed $variant = null,
        ?int $branchId = null,
        ?int $sellerId = null,
        ?int $referenceId = null,
        string $context = 'POS',
        ?string $stockReason = null
    ): array {
        if ($qty <= 0) {
            return ['status' => true, 'message' => '', 'branchId' => $branchId];
        }

        try {
            return DB::transaction(function () use ($productId, $qty, $variant, $branchId, $sellerId, $referenceId, $context, $stockReason) {
                $product = Product::query()->lockForUpdate()->find($productId);
                if (!$product) {
                    return ['status' => false, 'message' => "Product not found for ID {$productId}"];
                }

                if ($product->product_type !== 'physical') {
                    return ['status' => true, 'message' => '', 'branchId' => null];
                }

                $resolvedBranchId = $this->resolvePosBranchId(
                    product: $product,
                    branchId: $branchId,
                    sellerId: $sellerId
                );

                $response = $this->mutatePhysicalInventory(
                    product: $product,
                    branchId: $resolvedBranchId,
                    variantType: $this->normalizeVariantType($variant),
                    delta: (0 - $qty),
                    status: 'delivered',
                    orderId: (int)($referenceId ?? 0),
                    context: $context,
                    reasonOverride: $stockReason
                );

                if ($response['status']) {
                    $response['branchId'] = $resolvedBranchId;
                }

                return $response;
            });
        } catch (\Throwable $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function transferBetweenBranches(
        int $productId,
        int $qty,
        int $fromBranchId,
        int $toBranchId,
        mixed $variant = null,
        ?int $referenceId = null,
        string $context = 'Branch Transfer',
        ?string $stockReason = null
    ): array {
        if ($qty <= 0) {
            return ['status' => true, 'message' => ''];
        }

        if ($fromBranchId <= 0 || $toBranchId <= 0) {
            return ['status' => false, 'message' => 'Invalid branch ID for stock transfer'];
        }

        if ($fromBranchId === $toBranchId) {
            return ['status' => false, 'message' => 'Source and destination branch cannot be the same'];
        }

        try {
            return DB::transaction(function () use ($productId, $qty, $fromBranchId, $toBranchId, $variant, $referenceId, $context, $stockReason) {
                $product = Product::query()->lockForUpdate()->find($productId);
                if (!$product) {
                    return ['status' => false, 'message' => "Product not found for ID {$productId}"];
                }

                if ($product->product_type !== 'physical') {
                    return ['status' => false, 'message' => "Product {$productId} is not physical"];
                }

                $variantType = $this->normalizeVariantType($variant);
                $reason = $stockReason ?: StockReason::BRANCH_TRANSFER;
                $reference = (int)($referenceId ?? 0);

                $outResponse = $this->mutatePhysicalInventory(
                    product: $product,
                    branchId: $fromBranchId,
                    variantType: $variantType,
                    delta: (0 - $qty),
                    status: 'delivered',
                    orderId: $reference,
                    context: "{$context} OUT",
                    reasonOverride: $reason
                );
                if (!($outResponse['status'] ?? false)) {
                    return $outResponse;
                }

                $inResponse = $this->mutatePhysicalInventory(
                    product: $product,
                    branchId: $toBranchId,
                    variantType: $variantType,
                    delta: $qty,
                    status: 'returned',
                    orderId: $reference,
                    context: "{$context} IN",
                    reasonOverride: $reason
                );
                if (!($inResponse['status'] ?? false)) {
                    return $inResponse;
                }

                return [
                    'status' => true,
                    'message' => '',
                    'fromBranchId' => $fromBranchId,
                    'toBranchId' => $toBranchId,
                ];
            });
        } catch (\Throwable $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function manualAdjust(
        int $productId,
        int $branchId,
        mixed $variant = null,
        int $delta = 0,
        string $note = '',
        ?string $stockReason = null,
        ?int $referenceId = null,
        string $context = 'Manual Stock Adjust'
    ): array {
        if ($delta === 0) {
            return ['status' => true, 'message' => '', 'branchId' => $branchId];
        }

        try {
            return DB::transaction(function () use ($productId, $branchId, $variant, $delta, $note, $stockReason, $referenceId, $context) {
                $product = Product::query()->lockForUpdate()->find($productId);
                if (!$product) {
                    return ['status' => false, 'message' => "Product not found for ID {$productId}"];
                }

                if ($product->product_type !== 'physical') {
                    return ['status' => false, 'message' => "Manual adjustment is only supported for physical products"];
                }

                $systemBranchId = 1;
                if ($delta > 0) {
                    $resolvedBranchId = $branchId > 0 ? $branchId : $systemBranchId;
                } else {
                    if ($branchId <= 0) {
                        return ['status' => false, 'message' => 'Deduction branch is required for negative manual adjustment'];
                    }
                    $resolvedBranchId = $branchId;
                }

                $status = $delta < 0 ? 'delivered' : 'returned';
                $reason = $stockReason ?: StockReason::MANUAL_ADJUSTMENT;
                $reference = (int)($referenceId ?? $productId);
                $remarksContext = $note !== '' ? "{$context} ({$note})" : $context;

                $response = $this->mutatePhysicalInventory(
                    product: $product,
                    branchId: $resolvedBranchId,
                    variantType: $this->normalizeVariantType($variant),
                    delta: $delta,
                    status: $status,
                    orderId: $reference,
                    context: $remarksContext,
                    reasonOverride: $reason
                );

                if ($response['status']) {
                    $response['branchId'] = $resolvedBranchId;
                }

                return $response;
            });
        } catch (\Throwable $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function decreaseForOrder(Order $order): array
    {
        return $this->applyOrderStatusChange(order: $order, status: 'delivered');
    }

    public function decreaseForOrderById(string|int $orderId): array
    {
        return $this->applyOrderStatusChangeById(orderId: $orderId, status: 'delivered');
    }

    public function increaseForReturnOrCancel(Order $order, string $status = 'returned'): array
    {
        $normalized = strtolower(trim($status));
        if (!in_array($normalized, ['returned', 'failed', 'canceled'], true)) {
            $normalized = 'returned';
        }

        return $this->applyOrderStatusChange(order: $order, status: $normalized);
    }

    public function increaseForReturnOrCancelById(string|int $orderId, string $status = 'returned'): array
    {
        $normalized = strtolower(trim($status));
        if (!in_array($normalized, ['returned', 'failed', 'canceled'], true)) {
            $normalized = 'returned';
        }

        return $this->applyOrderStatusChangeById(orderId: $orderId, status: $normalized);
    }

    public function applyOrderStatusChangeById(string|int $orderId, string $status): array
    {
        $order = Order::query()->find($orderId);
        if (!$order) {
            return ['status' => false, 'message' => 'Order not found'];
        }

        return $this->applyOrderStatusChange($order, $status);
    }

    public function applyOrderStatusChange(Order $order, string $status): array
    {
        $normalizedStatus = strtolower(trim($status));
        $isDeliveredTransition = $normalizedStatus === 'delivered';
        $isReverseTransition = in_array($normalizedStatus, ['returned', 'failed', 'canceled'], true);

        try {
            return DB::transaction(function () use ($order, $normalizedStatus, $isDeliveredTransition, $isReverseTransition) {
                $lockedOrder = Order::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->find($order->id);

                if (!$lockedOrder) {
                    return ['status' => false, 'message' => 'Order not found'];
                }

                $branchId = $this->resolveOrderBranchId($lockedOrder);

                foreach ($lockedOrder->details as $detailSnapshot) {
                    $detail = OrderDetail::query()->lockForUpdate()->find($detailSnapshot->id);
                    if (!$detail) {
                        continue;
                    }

                    if (!$isDeliveredTransition && !$isReverseTransition) {
                        if ($detail->delivery_status !== $normalizedStatus) {
                            $detail->delivery_status = $normalizedStatus;
                            $detail->save();
                        }
                        continue;
                    }

                    if ($isDeliveredTransition && (int)$detail->is_stock_decreased === 1) {
                        $detail->delivery_status = $normalizedStatus;
                        $detail->save();
                        continue;
                    }

                    if ($isReverseTransition && (int)$detail->is_stock_decreased === 0) {
                        $detail->delivery_status = $normalizedStatus;
                        $detail->save();
                        continue;
                    }

                    $qty = max(0, (int)$detail->qty);
                    if ($qty === 0) {
                        $detail->is_stock_decreased = $isDeliveredTransition ? 1 : 0;
                        $detail->delivery_status = $normalizedStatus;
                        $detail->save();
                        continue;
                    }

                    $product = Product::query()->lockForUpdate()->find($detail->product_id);
                    if (!$product) {
                        return ['status' => false, 'message' => "Product not found for order detail {$detail->id}"];
                    }

                    if ($product->product_type !== 'physical') {
                        $detail->is_stock_decreased = $isDeliveredTransition ? 1 : 0;
                        $detail->delivery_status = $normalizedStatus;
                        $detail->save();
                        continue;
                    }

                    $variantType = $this->normalizeVariantType($detail->variant);
                    $delta = $isDeliveredTransition ? -$qty : $qty;

                    $mutationResponse = $this->mutatePhysicalInventory(
                        product: $product,
                        branchId: $branchId,
                        variantType: $variantType,
                        delta: $delta,
                        status: $normalizedStatus,
                        orderId: (int)$lockedOrder->id
                    );

                    if (!$mutationResponse['status']) {
                        return $mutationResponse;
                    }

                    $detail->is_stock_decreased = $isDeliveredTransition ? 1 : 0;
                    $detail->delivery_status = $normalizedStatus;
                    $detail->save();
                }

                return ['status' => true, 'message' => ''];
            });
        } catch (\Throwable $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    private function mutatePhysicalInventory(Product $product, int $branchId, ?string $variantType, int $delta, string $status, int $orderId, string $context = 'Order', ?string $reasonOverride = null): array
    {
        $qty = abs($delta);
        $isDeduction = $delta < 0;

        $branchStock = $this->getBranchStockRow(
            branchId: $branchId,
            productId: (int)$product->id,
            variantType: $variantType,
            createIfMissing: !$isDeduction
        );

        if (!$branchStock) {
            return ['status' => false, 'message' => "Branch stock not found for product {$product->id}"];
        }

        $productStock = $this->getOrCreateProductStockRow($product, $variantType);

        if ($isDeduction) {
            if ((int)$branchStock->current_stock < $qty) {
                return ['status' => false, 'message' => "Branch stock not sufficient for product {$product->id}"];
            }

            if ((int)$productStock->qty < $qty) {
                return ['status' => false, 'message' => "Master stock not sufficient for product {$product->id}"];
            }

            if ((int)$product->current_stock < $qty) {
                return ['status' => false, 'message' => "Product stock not sufficient for product {$product->id}"];
            }

            if (!$this->canUpdateVariationQty($product, $variantType, $delta)) {
                return ['status' => false, 'message' => "Variant stock not sufficient for product {$product->id}"];
            }
        }

        $newBranchStock = (int)$branchStock->current_stock + $delta;
        if ($newBranchStock < 0) {
            return ['status' => false, 'message' => "Negative branch stock blocked for product {$product->id}"];
        }
        $branchStock->current_stock = $newBranchStock;
        $branchStock->save();

        $newProductStock = (int)$productStock->qty + $delta;
        if ($newProductStock < 0) {
            return ['status' => false, 'message' => "Negative product stock ledger blocked for product {$product->id}"];
        }
        $productStock->qty = $newProductStock;
        $productStock->save();

        $newProductCurrentStock = (int)$product->current_stock + $delta;
        if ($newProductCurrentStock < 0) {
            return ['status' => false, 'message' => "Negative product stock blocked for product {$product->id}"];
        }
        $product->current_stock = $newProductCurrentStock;
        if (!$this->applyProductVariationDelta($product, $variantType, $delta)) {
            return ['status' => false, 'message' => "Negative variation stock blocked for product {$product->id}"];
        }
        $product->save();

        $reason = $reasonOverride ?: $this->resolveStockReason($status, $delta);
        $remarks = "{$context} #{$orderId} status {$status}";
        if ($delta < 0) {
            ProductStockTransaction::logStockOut($productStock, $qty, $reason, $remarks, $branchId);
        } else {
            ProductStockTransaction::logStockIn($productStock, $qty, $reason, $remarks, $branchId);
        }

        return ['status' => true, 'message' => ''];
    }

    private function resolveOrderBranchId(Order $order): int
    {
        $transferBranch = (int)($order->transfer_from_branch ?? 0);
        if ($transferBranch > 0) {
            return $transferBranch;
        }

        $pickupBranch = (int)($order->pickup_from_branch ?? 0);
        if ($pickupBranch > 0) {
            return $pickupBranch;
        }

        return 1;
    }

    private function resolvePosBranchId(Product $product, ?int $branchId = null, ?int $sellerId = null): int
    {
        $providedBranchId = (int)($branchId ?? 0);
        if ($providedBranchId > 0) {
            return $providedBranchId;
        }

        $productBranchId = (int)($product->branch_id ?? 0);
        if ($productBranchId > 0) {
            return $productBranchId;
        }

        $sellerId = (int)($sellerId ?? 0);
        if ($sellerId > 0) {
            $sellerBranches = Branch::query()
                ->where('vendor_id', $sellerId)
                ->where('status', 'active')
                ->orderBy('id')
                ->pluck('id');

            if ($sellerBranches->count() > 0) {
                return (int)$sellerBranches->first();
            }
        }

        return 1;
    }

    private function normalizeVariantType(mixed $variant): ?string
    {
        return $this->getVariantMatcher()->canonical($variant);
    }

    private function getBranchStockRow(int $branchId, int $productId, ?string $variantType, bool $createIfMissing = false): ?ManageBranchProductStock
    {
        $stockRows = ManageBranchProductStock::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->get();

        $stock = $stockRows->first(function (ManageBranchProductStock $row) use ($variantType) {
            if (is_null($variantType)) {
                return $this->getVariantMatcher()->isDefault($row->variation_type ?? null)
                    || $this->getVariantMatcher()->isDefault($row->variation_key ?? null)
                    || $this->getVariantMatcher()->isDefault($row->attributes ?? null);
            }

            return $this->getVariantMatcher()->matches($row->variation_type ?? null, $variantType)
                || $this->getVariantMatcher()->matches($row->variation_key ?? null, $variantType)
                || $this->getVariantMatcher()->matches($row->attributes ?? null, $variantType);
        });

        if ($stock || !$createIfMissing) {
            return $stock;
        }

        return ManageBranchProductStock::query()->create(array_merge(
            ManageBranchProductStock::buildInventoryLookup(
                branchId: $branchId,
                productId: $productId,
                variantType: $variantType
            ),
            ManageBranchProductStock::buildInventoryValues(
                currentStock: 0,
                variantType: $variantType
            )
        ));
    }

    private function getOrCreateProductStockRow(Product $product, ?string $variantType): ProductStock
    {
        $productStocks = ProductStock::query()
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->get();

        $productStock = $productStocks->first(function (ProductStock $row) use ($variantType) {
            if (is_null($variantType)) {
                return $this->getVariantMatcher()->isDefault($row->variant ?? null);
            }

            return $this->getVariantMatcher()->matches($row->variant ?? null, $variantType);
        });
        if ($productStock) {
            return $productStock;
        }

        $fallbackQty = $variantType !== null
            ? $this->getVariationQtyFromProduct($product, $variantType)
            : (int)($product->current_stock ?? 0);

        return ProductStock::query()->create([
            'product_id' => $product->id,
            'variant' => $variantType,
            'sku' => null,
            'price' => 0,
            'qty' => max(0, $fallbackQty),
        ]);
    }

    private function getVariationQtyFromProduct(Product $product, string $variantType): int
    {
        $variations = json_decode($product->variation ?? '[]', true);
        if (!is_array($variations)) {
            return 0;
        }

        foreach ($variations as $variation) {
            $type = $variation['type'] ?? null;
            if ($this->getVariantMatcher()->matches($type, $variantType)) {
                return max(0, (int)($variation['qty'] ?? 0));
            }
        }

        return 0;
    }

    private function seedInitialStockRow(
        Product $product,
        int $branchId,
        ?string $variantType,
        int $qty,
        ?string $sku,
        float $price
    ): void {
        $normalizedQty = max(0, $qty);

        $productStock = ProductStock::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'variant' => $variantType,
            ],
            [
                'sku' => $sku !== '' ? $sku : null,
                'price' => $price,
                'qty' => $normalizedQty,
            ]
        );

        ManageBranchProductStock::query()->updateOrCreate(
            ManageBranchProductStock::buildInventoryLookup(
                branchId: $branchId,
                productId: (int)$product->id,
                variantType: $variantType
            ),
            ManageBranchProductStock::buildInventoryValues(
                currentStock: $normalizedQty,
                variantType: $variantType
            )
        );

        if ($normalizedQty > 0) {
            ProductStockTransaction::logStockIn(
                $productStock,
                $normalizedQty,
                StockReason::INITIAL_STOCK,
                'Initial stock added on product creation',
                $branchId
            );
        }
    }

    private function canUpdateVariationQty(Product $product, ?string $variantType, int $delta): bool
    {
        if ($variantType === null) {
            return true;
        }

        $variations = json_decode($product->variation ?? '[]', true);
        if (!is_array($variations) || count($variations) === 0) {
            return true;
        }

        foreach ($variations as $variation) {
            $type = $variation['type'] ?? null;
            if (!$this->getVariantMatcher()->matches($type, $variantType)) {
                continue;
            }

            $newQty = (int)($variation['qty'] ?? 0) + $delta;
            return $newQty >= 0;
        }

        return true;
    }

    private function applyProductVariationDelta(Product $product, ?string $variantType, int $delta): bool
    {
        if ($variantType === null) {
            return true;
        }

        $variations = json_decode($product->variation ?? '[]', true);
        if (!is_array($variations) || count($variations) === 0) {
            return true;
        }

        $updated = false;
        foreach ($variations as &$variation) {
            $type = $variation['type'] ?? null;
            if (!$this->getVariantMatcher()->matches($type, $variantType)) {
                continue;
            }

            $newQty = (int)($variation['qty'] ?? 0) + $delta;
            if ($newQty < 0) {
                return false;
            }

            $variation['qty'] = $newQty;
            $updated = true;
            break;
        }
        unset($variation);

        if ($updated) {
            $product->variation = json_encode($variations);
        }

        return true;
    }

    private function getVariantMatcher(): VariantMatcher
    {
        if (is_null($this->variantMatcher)) {
            $this->variantMatcher = new VariantMatcher();
        }

        return $this->variantMatcher;
    }

    private function resolveStockReason(string $status, int $delta): string
    {
        if ($delta < 0) {
            return StockReason::ORDER_PLACED;
        }

        if ($status === 'returned') {
            return StockReason::RETURN;
        }

        return StockReason::ORDER_CANCELLED;
    }
}
