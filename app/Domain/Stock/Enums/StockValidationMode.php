<?php

namespace App\Domain\Stock\Enums;

enum StockValidationMode: string
{
    case NONE = 'none';
    case GLOBAL = 'global';
    case BRANCH = 'branch';
}

