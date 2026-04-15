<?php

namespace Tests\Unit;

use App\Models\ProductStock;
use App\Models\ProductStockTransaction;
use Tests\TestCase;

class ProductStockTransactionPayloadTest extends TestCase
{
    public function test_it_builds_payload_for_new_stock_transaction_schema(): void
    {
        $stock = new ProductStock();
        $stock->forceFill([
            'id' => 15,
            'product_id' => 42,
        ]);

        $payload = $this->invokeBuildLogPayload(
            ['product_stock_id', 'type', 'quantity', 'reason', 'remarks', 'to_branch_id'],
            $stock,
            'IN',
            5,
            'INITIAL_STOCK',
            'Initial stock added on product creation',
            1
        );

        $this->assertSame([
            'product_stock_id' => 15,
            'type' => 'IN',
            'quantity' => 5,
            'reason' => 'INITIAL_STOCK',
            'remarks' => 'Initial stock added on product creation',
            'to_branch_id' => 1,
        ], $payload);
    }

    public function test_it_builds_payload_for_legacy_stock_transaction_schema(): void
    {
        $stock = new ProductStock();
        $stock->forceFill([
            'id' => 15,
            'product_id' => 42,
        ]);

        $payload = $this->invokeBuildLogPayload(
            ['product_id', 'product_stock_id', 'type', 'quantity', 'branch_id', 'notes', 'created_by'],
            $stock,
            'OUT',
            3,
            'ORDER_PLACED',
            'Order #100 status delivered',
            7
        );

        $this->assertSame(42, $payload['product_id']);
        $this->assertSame(15, $payload['product_stock_id']);
        $this->assertSame('OUT', $payload['type']);
        $this->assertSame(3, $payload['quantity']);
        $this->assertSame(7, $payload['branch_id']);
        $this->assertSame('Order #100 status delivered', $payload['notes']);
        $this->assertArrayHasKey('created_by', $payload);
    }

    private function invokeBuildLogPayload(
        array $availableColumns,
        ProductStock $stock,
        string $type,
        int $qty,
        string $reason,
        string $remarks,
        ?int $branchId
    ): array {
        $cachedColumnsProperty = new \ReflectionProperty(ProductStockTransaction::class, 'cachedTableColumns');
        $cachedColumnsProperty->setAccessible(true);
        $cachedColumnsProperty->setValue(null, $availableColumns);

        $method = new \ReflectionMethod(ProductStockTransaction::class, 'buildLogPayload');
        $method->setAccessible(true);

        $payload = $method->invoke(null, $stock, $type, $qty, $reason, $remarks, $branchId);

        $cachedColumnsProperty->setValue(null, null);

        return $payload;
    }
}
