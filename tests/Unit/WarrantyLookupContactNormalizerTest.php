<?php

namespace Tests\Unit;

use App\Support\WarrantyLookupContactNormalizer;
use Tests\TestCase;

class WarrantyLookupContactNormalizerTest extends TestCase
{
    public function test_it_normalizes_egyptian_phone_variants_to_a_single_e164_shape(): void
    {
        $expected = '+201002010173';

        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('01002010173'));
        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('+201002010173'));
        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('201002010173'));
        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('+2001002010173'));
        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('00201002010173'));
        $this->assertSame($expected, WarrantyLookupContactNormalizer::normalize('1002010173'));
    }

    public function test_it_preserves_emails_as_lowercase(): void
    {
        $this->assertSame(
            'customer@example.com',
            WarrantyLookupContactNormalizer::normalize(' Customer@Example.com ')
        );
    }
}
