<?php

namespace App\Domain\Stock\DTO;

use App\Domain\Stock\Enums\StockChannel;

class StockValidationContext
{
    public function __construct(
        public StockChannel $channel,
        public ?string $deliveryType = null,
        public ?int $branchId = null,
        public ?int $sellerId = null,
        public bool $isCheckout = false,
        public bool $isWholesale = false,
    ) {}
}

