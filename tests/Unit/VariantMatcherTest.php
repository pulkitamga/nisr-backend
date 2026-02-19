<?php

namespace Tests\Unit;

use App\Domain\Stock\Support\VariantMatcher;
use PHPUnit\Framework\TestCase;

class VariantMatcherTest extends TestCase
{
    public function test_matches_simple_values_case_insensitive(): void
    {
        $matcher = new VariantMatcher();

        $this->assertTrue($matcher->matches('Left', 'left'));
        $this->assertTrue($matcher->matches('LEFT', 'left'));
    }

    public function test_matches_token_with_key_value_variant(): void
    {
        $matcher = new VariantMatcher();

        $this->assertTrue($matcher->matches('Left', 'l/r:left'));
        $this->assertTrue($matcher->matches('Left', 'color:Yellow | l/r:left'));
    }

    public function test_canonical_from_product_uses_matching_product_row(): void
    {
        $matcher = new VariantMatcher();
        $rows = [
            ['type' => 'color:Yellow | l/r:left', 'qty' => 3],
            ['type' => 'color:Yellow | l/r:right', 'qty' => 4],
        ];

        $canonical = $matcher->canonicalFromProduct('Left', $rows);

        $this->assertSame('color:yellow|l/r:left', $canonical);
    }

    public function test_default_variants_are_detected(): void
    {
        $matcher = new VariantMatcher();

        $this->assertTrue($matcher->isDefault(null));
        $this->assertTrue($matcher->isDefault('No Variation'));
        $this->assertFalse($matcher->isDefault('Left'));
    }
}

