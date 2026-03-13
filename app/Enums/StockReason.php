<?php

namespace App\Enums;

class StockReason
{
    const INITIAL_STOCK     = 'INITIAL_STOCK';
    const MANUAL_ADJUSTMENT = 'MANUAL_ADJUSTMENT';
    const ORDER_PLACED      = 'ORDER_PLACED';
    const ORDER_CANCELLED   = 'ORDER_CANCELLED';
    const RETURN            = 'RETURN';
    const BRANCH_TRANSFER   = 'BRANCH_TRANSFER';
    const DAMAGE            = 'DAMAGE';
     const WHOLESALE_DELIVERY = 'WHOLESALE_DELIVERY';

}
