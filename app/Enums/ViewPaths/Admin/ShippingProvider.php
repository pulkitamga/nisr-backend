<?php

namespace App\Enums\ViewPaths\Admin;

enum ShippingProvider
{
    const VIEW = [
        URI => 'shipping-provider',
        VIEW => 'admin-views.business-settings.shipping-provider.index',
    ];

    const UPDATE = [
        URI => 'shipping-provider/update',
        VIEW => '',
    ];
}

