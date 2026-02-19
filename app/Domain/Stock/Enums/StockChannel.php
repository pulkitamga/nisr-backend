<?php

namespace App\Domain\Stock\Enums;

enum StockChannel: string
{
    case RETAIL = 'retail';
    case WHOLESALE = 'wholesale';
    case POS_ADMIN = 'pos_admin';
    case POS_VENDOR = 'pos_vendor';
}

