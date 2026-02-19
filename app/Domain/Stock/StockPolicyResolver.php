<?php

namespace App\Domain\Stock;

use App\Domain\Stock\DTO\StockValidationContext;
use App\Domain\Stock\Enums\StockChannel;
use App\Domain\Stock\Enums\StockValidationMode;

class StockPolicyResolver
{
    public function resolve(StockValidationContext $context): StockValidationMode
    {
        if ($context->isWholesale || $context->channel === StockChannel::WHOLESALE) {
            return StockValidationMode::NONE;
        }

        if (in_array($context->channel, [StockChannel::POS_ADMIN, StockChannel::POS_VENDOR], true)) {
            return StockValidationMode::BRANCH;
        }

        if ($context->channel === StockChannel::RETAIL) {
            return $context->deliveryType === 'pickup'
                ? StockValidationMode::BRANCH
                : StockValidationMode::GLOBAL;
        }

        return StockValidationMode::GLOBAL;
    }
}

