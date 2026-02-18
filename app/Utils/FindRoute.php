<?php

use Illuminate\Support\Facades\Auth;

function productRoute(array $params = [])
{
    $customer = Auth::guard('customer')->user();

    if ($customer && $customer->wholesaler_status == 1) {
        return route('wholesale.products', $params);
    }

    return route('products', $params);
}
