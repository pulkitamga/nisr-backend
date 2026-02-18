<?php

namespace App\Enums\ViewPaths\Admin;

enum Product
{
    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.product.add-new'
    ];
    const MAKE_ADD = [
        URI => 'make-add',
        VIEW => ''
    ];
    const MAKE_DELETE = [
        URI => 'make-delete',
        VIEW => ''
    ];

    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.product.list'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.product.edit',
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.product.view',
        ROUTE => 'admin.products.view'
    ];
    const SKU_COMBINATION = [
        URI => 'sku-combination',
        VIEW => 'admin-views.product.partials._sku-combinations'
    ];

    const SKU_EDIT_COMBINATION = [
        URI => 'sku-combination',
        VIEW => 'admin-views.product.partials._edit-product-variation-pricing-sku'
    ];

    const DIGITAL_VARIATION_COMBINATION = [
        URI => 'digital-variation-combination',
        VIEW => 'admin-views.product.partials._digital-variation-combination'
    ];

    const DIGITAL_VARIATION_FILE_DELETE = [
        URI => 'digital-variation-file-delete',
        VIEW => ''
    ];

   const FEATURE_PRODUCT_STATUS =[
    URI => 'featured-status',
    VIEW => ''

   ];
    const PRODUCT_CMS_STATUS = [
        URI => 'product-cms-status',
        VIEW => ''
    ];
    const PRODUCT_SHOWCASE_STATUS = [
        URI => 'product-showcase-status',
        VIEW => ''
    ];

    const UPDATE_STATUS = [
        URI => 'status-update',
        VIEW => ''
    ];

    const GET_CATEGORIES = [
        URI => 'get-categories',
        VIEW => ''
    ];
    const GET_SUB_CATEGORIES = [
        URI => 'get-sub-categories',
        VIEW => ''
    ];
    const GET_ATTRIBUTES = [
        URI => 'get-attributes-for-product',
        VIEW => ''
    ];
    const GET_UNIT_PRICE = [
        URI => 'get-unit-price',
        VIEW => ''
    ];

    const BARCODE_VIEW = [
        URI => 'barcode',
        VIEW => 'admin-views.product.barcode'
    ];

    const BARCODE_GENERATE = [
        URI => 'barcode',
        VIEW => 'admin-views.product.barcode'
    ];

    const EXPORT_EXCEL = [
        URI => 'export-excel',
        VIEW => ''
    ];

    const STOCK_LIMIT = [
        URI => 'stock-limit-list',
        VIEW => 'admin-views.product.stock-limit-list'
    ];
    const STOCK_LIMIT_PRODUCTS = [
        URI => 'stock-limit-products',
        VIEW => 'admin-views.product.stock-limit-products'
    ];

    const STOCK_LIMIT_STATUS = [
        URI => 'stock-limit-status',
        VIEW => ''
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const EXPORT_RESTOCK = [
        URI => 'export-restock',
        VIEW => ''
    ];

    const RESTOCK_DELETE = [
        URI => 'restock-delete',
        VIEW => ''
    ];

    const DELETE_IMAGE = [
        URI => 'delete-image',
        VIEW => ''
    ];

    const GET_VARIATIONS = [
        URI => 'get-variations',
        VIEW => 'admin-views.product.partials._update-stock'
    ];

    const STOCK_REPORT = [
        URI => 'stock-report',
        VIEW => 'admin-views.product.partials._stock-report',
    ];

    const UPDATE_QUANTITY = [
        URI => 'update-quantity',
        VIEW => ''
    ];

    const BULK_IMPORT = [
        URI => 'bulk-import',
        VIEW => 'admin-views.product.bulk-import'
    ];

    const REQUEST_RESTOCK_LIST = [
        URI => 'request-restock-list',
        VIEW => 'admin-views.product.request-restock-list'
    ];

    const UPDATED_PRODUCT_LIST = [
        URI => 'updated-product-list',
        VIEW => 'admin-views.product.updated-product-list'
    ];

    const UPDATED_SHIPPING = [
        URI => 'updated-shipping',
        VIEW => ''
    ];

    const DENY = [
        URI => 'deny',
        VIEW => ''
    ];

    const APPROVE_STATUS = [
        URI => 'approve-status',
        VIEW => ''
    ];

    const SEARCH = [
        URI => 'search',
        VIEW => 'admin-views.partials._search-product'

    ];

    const MULTIPLE_PRODUCT_DETAILS = [
        URI => 'multiple-product-details',
        VIEW => 'admin-views.partials._select-product'

    ];

    const PRODUCT_GALLERY = [
        URI => 'product-gallery',
        VIEW => 'admin-views.product.product-gallery'
    ];
    const PRODUCT_MAKE = [
        URI => 'product-make',
        VIEW => 'admin-views.product.product-make'
    ];
    const PRODUCT_MAKE_MODEL = [
        URI => 'models',
        VIEW => ''
    ];

    const DELETE_PREVIEW_FILE = [
        URI => 'delete-preview-file',
        VIEW => '',
    ];

    const GET_PRODUCTS = [
        URI => 'get-products',
        VIEW => ''
    ];
    const DOWNLOAD_CSV = [
        URI => 'download-csv',
        VIEW => ''
    ];
}
