<?php

namespace Tests\Unit;

use App\Models\ManageBranchProductStock;
use Tests\TestCase;

class ManageBranchProductStockPayloadTest extends TestCase
{
    public function test_it_builds_lookup_and_values_for_current_branch_stock_schema(): void
    {
        $cachedColumnsProperty = new \ReflectionProperty(ManageBranchProductStock::class, 'cachedTableColumns');
        $cachedColumnsProperty->setAccessible(true);
        $cachedColumnsProperty->setValue(null, ['branch_id', 'product_id', 'variation_type', 'variation_key', 'current_stock']);

        $lookup = ManageBranchProductStock::buildInventoryLookup(1, 9, 'Left');
        $values = ManageBranchProductStock::buildInventoryValues(7, 'Left');

        $cachedColumnsProperty->setValue(null, null);

        $this->assertSame([
            'branch_id' => 1,
            'product_id' => 9,
            'variation_type' => 'Left',
            'variation_key' => 'Left',
        ], $lookup);

        $this->assertSame([
            'current_stock' => 7,
            'variation_type' => 'Left',
            'variation_key' => 'Left',
        ], $values);
    }

    public function test_it_builds_lookup_and_values_for_legacy_branch_stock_schema(): void
    {
        $cachedColumnsProperty = new \ReflectionProperty(ManageBranchProductStock::class, 'cachedTableColumns');
        $cachedColumnsProperty->setAccessible(true);
        $cachedColumnsProperty->setValue(null, ['branch_id', 'product_id', 'attributes', 'current_stock']);

        $lookup = ManageBranchProductStock::buildInventoryLookup(2, 11, null);
        $values = ManageBranchProductStock::buildInventoryValues(0, 'Right');

        $cachedColumnsProperty->setValue(null, null);

        $this->assertSame([
            'branch_id' => 2,
            'product_id' => 11,
            'attributes' => null,
        ], $lookup);

        $this->assertSame([
            'current_stock' => 0,
            'attributes' => 'Right',
        ], $values);
    }
}
