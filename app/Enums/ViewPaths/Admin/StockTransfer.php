<?php

namespace App\Enums\ViewPaths\Admin;

enum StockTransfer
{
    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.stock-transfer.add-new'
    ];

    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.stock-transfer.list'
    ];
    const DOWNLOAD_ERROR_CSV = [
        URI => 'error-csv/{filename}',
        VIEW => ''
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.stock-transfer.edit',
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.stock-transfer.view',
        ROUTE => 'admin-views.stock-transfer.view'
    ];

    const SEARCH = [
        URI => 'search-products',
        VIEW => 'admin-views.stock-transfer.partials._search-product'
    ];

    const QUICK_VIEW = [
        URI => 'quick-view',
        VIEW => 'admin-views.pos.partials._quick-view'
    ];

    const PRODUCT_STOCK = [
        URI => 'get-branches-product-stock',
        VIEW => ''
    ];


    const UPDATE_STOCK_REQUEST = [
        URI => 'stock-request-update',
        VIEW => '',
    ];
    
    const TRANSFER_REQUEST_LIST = [
        URI => 'transfer-request-list',
        VIEW => 'admin-views.stock-transfer.transfer-request-list'
    ];

    const ADD_TRANSFER_REQUEST = [
        URI => 'add-transfer-request',
        VIEW => 'admin-views.product.add-transfer-request'
    ];

    const TRANSFER_SEARCH = [
        URI => 'search-transfer-products',
        VIEW => 'admin-views.product.partials._search-product'
    ];

    const TRANSFER_QUICK_VIEW = [
        URI => 'quick-view',
        VIEW => 'admin-views.pos.partials._quick-view'
    ];

    const SAVE_TRANSFER_REQUEST = [
        URI => 'save-transfer-request',
        VIEW => ''
    ];
}
