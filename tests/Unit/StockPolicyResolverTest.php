<?php

namespace Tests\Unit;

use App\Domain\Stock\DTO\StockValidationContext;
use App\Domain\Stock\Enums\StockChannel;
use App\Domain\Stock\Enums\StockValidationMode;
use App\Domain\Stock\StockPolicyResolver;
use PHPUnit\Framework\TestCase;

class StockPolicyResolverTest extends TestCase
{
    public function test_retail_delivery_uses_global_validation(): void
    {
        $resolver = new StockPolicyResolver();
        $context = new StockValidationContext(
            channel: StockChannel::RETAIL,
            deliveryType: 'delivery'
        );

        $this->assertSame(StockValidationMode::GLOBAL, $resolver->resolve($context));
    }

    public function test_retail_pickup_uses_branch_validation(): void
    {
        $resolver = new StockPolicyResolver();
        $context = new StockValidationContext(
            channel: StockChannel::RETAIL,
            deliveryType: 'pickup'
        );

        $this->assertSame(StockValidationMode::BRANCH, $resolver->resolve($context));
    }

    public function test_wholesale_is_bypassed(): void
    {
        $resolver = new StockPolicyResolver();
        $context = new StockValidationContext(
            channel: StockChannel::WHOLESALE
        );

        $this->assertSame(StockValidationMode::NONE, $resolver->resolve($context));
    }

    public function test_wholesale_flag_bypasses_even_if_channel_is_retail(): void
    {
        $resolver = new StockPolicyResolver();
        $context = new StockValidationContext(
            channel: StockChannel::RETAIL,
            deliveryType: 'pickup',
            isWholesale: true
        );

        $this->assertSame(StockValidationMode::NONE, $resolver->resolve($context));
    }

    public function test_pos_channels_use_branch_validation(): void
    {
        $resolver = new StockPolicyResolver();

        $adminContext = new StockValidationContext(
            channel: StockChannel::POS_ADMIN
        );
        $vendorContext = new StockValidationContext(
            channel: StockChannel::POS_VENDOR
        );

        $this->assertSame(StockValidationMode::BRANCH, $resolver->resolve($adminContext));
        $this->assertSame(StockValidationMode::BRANCH, $resolver->resolve($vendorContext));
    }
}

