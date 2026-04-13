<?php

namespace Tests\Unit;

use App\Support\WholesaleLinePrice;
use PHPUnit\Framework\TestCase;

class WholesaleLinePriceTest extends TestCase
{
    public function test_it_treats_percent_tax_as_percentage_when_stored_final_matches_base_total(): void
    {
        $pricing = WholesaleLinePrice::fromValues(
            basePrice: 970,
            quantity: 1,
            tax: '14',
            storedFinalPrice: 970
        );

        $this->assertSame('percent', $pricing['tax_mode']);
        $this->assertEqualsWithDelta(135.80, $pricing['tax_amount'], 0.01);
        $this->assertEqualsWithDelta(1105.80, $pricing['final_price'], 0.01);
        $this->assertSame('14.00%', $pricing['display_tax']);
    }

    public function test_it_treats_large_raw_tax_values_as_fixed_amounts(): void
    {
        $pricing = WholesaleLinePrice::fromValues(
            basePrice: 970,
            quantity: 1,
            tax: '116.67',
            storedFinalPrice: 2059.67
        );

        $this->assertSame('amount', $pricing['tax_mode']);
        $this->assertEqualsWithDelta(116.67, $pricing['tax_amount'], 0.01);
        $this->assertEqualsWithDelta(1086.67, $pricing['final_price'], 0.01);
        $this->assertSame('116.67', $pricing['display_tax']);
    }
}
