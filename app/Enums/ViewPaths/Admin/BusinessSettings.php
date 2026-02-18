<?php

namespace App\Enums\ViewPaths\Admin;

enum BusinessSettings
{
    const INDEX = [
        URI => '',
        VIEW => 'admin-views.business-settings.website-info',
    ];

    const ANALYTICS_INDEX = [
        URI => 'analytics-index',
        VIEW => 'admin-views.business-settings.analytics.index'
    ];

     const CACHE = [
        URI => 'cache',
        VIEW => 'admin-views.business-settings.cache-view'
    ];

    const ANALYTICS_UPDATE = [
        URI => 'analytics-update',
        VIEW => ''
    ];
    const CLEAR_CACHE = [
        URI => 'cache-clear',
        VIEW => ''
    ];

    const APP_SETTINGS = [
        URI => 'app-settings',
        VIEW => 'admin-views.business-settings.apps-settings'
    ];

    const MAINTENANCE_MODE = [
        URI => 'maintenance-mode',
        VIEW => '',
    ];

    const ORDER_VIEW = [
        URI => 'index',
        VIEW => 'admin-views.business-settings.order-settings.index'
    ];

    const ORDER_UPDATE = [
        URI => 'update-order-settings',
        VIEW => ''
    ];

    const VENDOR_VIEW = [
        URI => '/',
        VIEW => 'admin-views.business-settings.seller-settings'
    ];

    const VENDOR_SETTINGS_UPDATE = [
        URI => 'update-vendor-settings',
        VIEW => ''
    ];

    const DELIVERYMAN_VIEW = [
        URI => '/',
        VIEW => 'admin-views.business-settings.delivery-man-settings.index'
    ];

    const DELIVERYMAN_VIEW_UPDATE = [
        URI => 'delivery-man-settings/update',
        VIEW => ''
    ];

    const ANNOUNCEMENT = [
        URI => 'announcement',
        VIEW => 'admin-views.business-settings.website-announcement'
    ];

    const PRODUCT_SETTINGS = [
        URI => '',
        VIEW => 'admin-views.business-settings.product-settings'
    ];
    const WARRANTY_SETTINGD = [
        URI => '',
        VIEW => 'admin-views.business-settings.warranty-settings'
    ];

}
