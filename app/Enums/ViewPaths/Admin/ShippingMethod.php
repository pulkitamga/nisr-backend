<?php

namespace App\Enums\ViewPaths\Admin;

enum ShippingMethod
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.shipping-method.index',
        ROUTE =>'admin.business-settings.shipping-method.index'
    ];

    const AREA = [
        URI => 'add-area',
        VIEW => '',
        ROUTE =>'admin.business-settings.shipping-method.index'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.shipping-method.update-view'
    ];

    const AREA_UPDATE = [
        URI => 'area-update',
        VIEW => 'admin-views.shipping-method.update-area-view'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];

    const UPDATE_AREA_STATUS = [
        URI => 'update-area-status',
        VIEW => ''
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const AREA_DELETE = [
        URI => 'delete-area',
        VIEW => ''
    ];

    const UPDATE_SHIPPING_RESPONSIBILITY = [
        URI => 'update-shipping-responsibility',
        VIEW => ''
    ];

}
