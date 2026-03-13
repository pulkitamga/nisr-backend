<?php

namespace App\Enums\ViewPaths\Admin;

enum WholeSaler
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.wholesaler-business.index'
    ];
    const WHOLESALE_QUOTATIONS = [
        URI => 'quotation-sent',
        VIEW => 'admin-views.wholesaler-business.wholesale-orders'
    ];
    const LIST_REQUEST = [
        URI => 'request',
        VIEW => 'admin-views.wholesaler-business.wholesaler-request'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.wholesale-products.add'
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
    const CHECK_ORDER_NO = [
        URI => 'check-order-no',
        VIEW => ''
    ];
    const ORDER_ASSIGN = [
        URI => 'assign-number',
        VIEW => ''
    ];
    const ORDER_CHECK = [
        URI => 'check-purchase-no',
        VIEW => ''
    ];
    const WHOLESALER_CONTACT = [
        URI => 'wholsaler-contect',
        VIEW => ''
    ];
    const WHOLESALER_CONTACT_DELETE = [
        URI => 'wholsaler-contect.softDelete',
        VIEW => ''
    ];


    const EXPORT_WHOLESALER_QUOT = [
        URI => 'export-quot',
        VIEW => ''
    ];
    const WHOLESALER_CONTACT_UPDATE = [
        URI => 'wholsaler-contect.update',
        VIEW => ''
    ];

    const WHOLESALER_STATUS = [
        URI => 'wholesaler-status',
        VIEW => ''
    ];
    const CONFIRMED_ORDER_NO = [
        URI => 'confirm-order-no',
        VIEW => ''
    ];
    const CONFIRMED_INVOICE_NO = [
        URI => 'confirm-invoice-no',
        VIEW => ''
    ];
    const CONFIREM_ORDER_DELETE = [
        URI => 'confirm-order-delete',
        VIEW => ''
    ];
    const CHECK_CONFIRMED_ORDER_NO = [
        URI => 'confirm-order-check',
        VIEW => ''
    ];
    const WHOLESALER_VIEW = [
        URI => 'wholesaler.profile',
        VIEW => 'admin-views.wholesaler-business.wholesaler-profile'
    ];
    const ORDER_BY_TYPE = [
        URI => 'profile',
        VIEW => 'admin-views.wholesaler-business.wholesaler-profile'
    ];
    const STORE_QUOTATION = [
        URI => 'store-quotation',
        VIEW => 'admin-views.wholesaler-business.wholesaler-profile'
    ];
    const PURCHASE_ORDER_VIEW = [
        URI => 'purchase-order',
        VIEW => 'admin-views.wholesaler-business.purchase-request-view'
    ];
    const CREATE_QUOTATION = [
        URI => 'create-quotation',
        VIEW => 'admin-views.wholesaler-business.create-quotation'
    ];
    const WHOLESALER_EDIT = [
        URI => 'wholesaler.profile.edit',
        VIEW => 'admin-views.wholesaler-business.wholesaler-edit'
    ];
    const MOQ_TOGGLE = [
        URI => 'toggle-moq-override',
        VIEW => ''
    ];
    const MOQ_TOGGLE_OWERRIDE = [
        URI => 'toggle-moq',
        VIEW => ''
    ];
    const BRANCH_PRODUCT_STORE = [
        URI => 'branch-product-show',
        VIEW => ''
    ];
    const BRANCH_LIST = [
        URI => 'branch-list',
        VIEW => ''
    ];
    const CONFIRMED_ORDER_DELIVERY_STORE = [
        URI => 'delivery.store',
        VIEW => ''
    ];

    const ORDER_REQUEST = [
        URI => 'order.request',
        VIEW => 'admin-views.wholesaler-business.order-request'
    ];
    const CONFIRMED_ORDERS = [
        URI => 'confirm-orders',
        VIEW => 'admin-views.wholesaler-business.confirmed-orders'
    ];
    const PAYMENT_VIEW = [
        URI => 'payment',
        VIEW => 'admin-views.wholesaler-business.payment-view'
    ];
    const DELIVERY_VIEW = [
        URI => 'delivery',
        VIEW => 'admin-views.wholesaler-business.delivery-view'
    ];
    const DOWNLOAD_CSV = [
        URI => 'download-csv',
        VIEW => ''
    ];
    const REQUEST_VIEW = [
        URI => 'purchase-request',
        VIEW => 'admin-views.wholesaler-business.order-view'
    ];
    const REQUEST_DELETE = [
        URI => 'order.delete',
        VIEW => ''
    ];
    const PAYMENT_STORE = [
        URI => 'payment.store',
        VIEW => ''
    ];
    const QUOTATION_DELETE = [
        URI => 'order/quotation-delete',
        VIEW => ''
    ];
    const ORDER_APPROVE = [
        URI => 'order.approve',
        VIEW => ''
    ];
    const WHOLESALER_UPDATE = [
        URI => 'wholesaler.update',
        VIEW => ''
    ];
    const TIER_DELETE = [
        URI => 'wholesaler.tier.delete',
        VIEW => ''
    ];
    const TIER_UPDATE = [
        URI => 'wholesaler.tier.update',
        VIEW => ''
    ];
    const TIER_STATUS_UPDATE = [
        URI => 'wholesaler.tier.status',
        VIEW => ''
    ];
    const TIER_ADD = [
        URI => 'wholesaler.tier.add',
        VIEW => ''
    ];
    const INVOICE_UPDATE = [
        URI => 'invoice.update',
        VIEW => ''
    ];
    const TIER_VIEW = [
        URI => 'tier',
        VIEW => 'admin-views.wholesaler-business.tier-view'
    ];
    const INVOICE_VIEW = [
        URI => 'orders.invoice',
        VIEW => 'admin-views.wholesaler-business.quotation-view'
    ];
    const INVOICE_EDIT = [
        URI => 'quotation.edit',
        VIEW => 'admin-views.wholesaler-business.edit-quotation'
    ];
    const CONFIRM_TRACKING_PAGE = [
        URI => 'confirm-orders',
        VIEW => 'admin-views.wholesaler-business.confirm-tracking'
    ];

    const DASHBOARD = [
        URI => '',
        VIEW => 'admin-views.wholesaler-business.dashboard'
    ];

    const EARNING_STATISTICS = [
        URI => 'earning-statistics',
        VIEW => 'admin-views.system.partials.earning-statistics'
    ];
    const ORDER_STATISTICS = [
        URI => 'order-statistics',
        VIEW => 'admin-views.system.partials.order-statistics'
    ];
    const ORDER_STATUS = [
        URI => 'order-status',
        VIEW => ''
    ];

    const REAL_TIME_ACTIVITIES = [
        URI => 'real-time-activities',
        VIEW => ''
    ];

    const EXPORT_WHOLESALER_REQ = [
        URI => 'export-req',
        VIEW => ''
    ];
    const EXPORT_WHOLESALER = [
        URI => 'export-wholesaler',
        VIEW => ''
    ];
    const EXPORT_WHOLESALER_CONFIRM = [
        URI => 'export-confirm',
        VIEW => ''
    ];
    const EXPORT_WHOLESALER_PURCHASE = [
        URI => 'export-purchase',
        VIEW => ''
    ];
    const CONFIREM_ORDER_INVOICE = [
        URI => 'complete-invoice',
        VIEW => 'admin-views.wholesaler-business.invoice-view'
    ];

    const ORDER_HISTORY = [
        URI => 'ajax-activity-history',
        VIEW => 'admin-views.wholesaler-business._order-status-history'
    ];
}
