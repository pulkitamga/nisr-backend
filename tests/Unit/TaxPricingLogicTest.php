<?php

namespace Tests\Unit;

use App\Utils\CartManager;
use App\Utils\Helpers;
use Tests\TestCase;

class TaxPricingLogicTest extends TestCase
{
    public function test_it_extracts_vat_when_price_includes_tax(): void
    {
        $result = CartManager::getTaxBreakdownFromAmount(
            amount: 800,
            taxRate: 14,
            taxModel: 'include'
        );

        $this->assertEqualsWithDelta(701.7544, $result['net'], 0.01);
        $this->assertEqualsWithDelta(98.2456, $result['vat'], 0.01);
        $this->assertEqualsWithDelta(800, $result['gross'], 0.01);
    }

    public function test_it_adds_vat_when_price_excludes_tax(): void
    {
        $result = CartManager::getTaxBreakdownFromAmount(
            amount: 800,
            taxRate: 14,
            taxModel: 'exclude'
        );

        $this->assertEqualsWithDelta(800, $result['net'], 0.01);
        $this->assertEqualsWithDelta(112, $result['vat'], 0.01);
        $this->assertEqualsWithDelta(912, $result['gross'], 0.01);
    }

    public function test_helpers_tax_calculation_handles_include_and_exclude_models(): void
    {
        $includeTax = Helpers::tax_calculation(
            product: ['tax_model' => 'include'],
            price: 800,
            tax: 14,
            tax_type: 'percent'
        );
        $excludeTax = Helpers::tax_calculation(
            product: ['tax_model' => 'exclude'],
            price: 800,
            tax: 14,
            tax_type: 'percent'
        );

        $this->assertEqualsWithDelta(98.2456, $includeTax, 0.01);
        $this->assertEqualsWithDelta(112, $excludeTax, 0.01);
    }
}
