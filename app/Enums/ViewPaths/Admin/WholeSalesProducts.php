<?php

namespace App\Enums\ViewPaths\Admin;

enum WholeSalesProducts
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.wholesale-products.list'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.wholesale-products.add'
    ];
    const GET_VARIATION_PRICE = [
        URI => 'get-variations',
        VIEW => ''
    ];

    const PRODUCT_VIEW = [
        URI => 'view',
        VIEW => 'admin-views.wholesale-products.product-view'
    ];    
    const UPDATE_VIEW = [
        URI => 'edit',
        VIEW => 'admin-views.wholesale-products.update-view'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => ''
    ];
    const EXPORT_EXCEL = [
        URI => 'export-excel',
        VIEW => ''
    ];

    const BUSINESS_REQUEST = [
        URI => 'business-request',
        VIEW => ''
    ];


    const PRODUCT_DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const PRODUCT_TOGGLE = [
        URI => 'toggle-status',
        VIEW => ''
    ];
}
