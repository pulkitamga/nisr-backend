<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\WholeSaleProductsService;
use InvalidArgumentException;
use Tests\TestCase;

class WholeSaleProductsServiceTest extends TestCase
{
    public function test_build_validated_price_ranges_uses_server_side_variation_price(): void
    {
        $product = new Product();
        $product->unit_price = 100;
        $product->variation = json_encode([
            ['type' => 'Left', 'price' => 175],
        ]);

        $service = new WholeSaleProductsService();

        $ranges = $service->buildValidatedPriceRanges(
            product: $product,
            variationType: 'Left',
            variationKey: 'variant:Left',
            payload: [
                'tier' => ['gold'],
                'min_qty' => [5],
                'max_qty' => [10],
                'discount' => [10],
                'unit_price' => [0],
                'final_price' => [0],
            ]
        );

        $this->assertCount(1, $ranges);
        $this->assertSame('gold', $ranges[0]['tier']);
        $this->assertSame(5, $ranges[0]['min_qty']);
        $this->assertSame(10, $ranges[0]['max_qty']);
        $this->assertSame(157.5, $ranges[0]['price_per_piece']);
    }

    public function test_build_validated_price_ranges_rejects_overlapping_ranges(): void
    {
        $product = new Product();
        $product->unit_price = 100;
        $product->variation = json_encode([]);

        $service = new WholeSaleProductsService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(translate('wholesale_price_ranges_cannot_overlap'));

        $service->buildValidatedPriceRanges(
            product: $product,
            variationType: null,
            variationKey: null,
            payload: [
                'tier' => ['gold', 'silver'],
                'min_qty' => [5, 10],
                'max_qty' => [10, 20],
                'discount' => [0, 5],
            ]
        );
    }
}
