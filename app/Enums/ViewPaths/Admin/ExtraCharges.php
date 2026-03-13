<?php

namespace App\Enums\ViewPaths\Admin;

enum ExtraCharges
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.extra-charges.index'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.extra-charges.add-new-branch'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.extra-charges.edit_branch'
    ];

    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.extra-charges.view'
    ];

    const VIEW_ORDER = [
        URI => '',
        VIEW => 'admin-views.extra-charges.view.order'
    ];

    const VIEW_PRODUCT = [
        URI => '',
        VIEW => 'admin-views.extra-charges.view.product'
    ];

    const VIEW_REVIEW = [
        URI => '',
        VIEW => 'admin-views.extra-charges.view.review'
    ];

    const VIEW_TRANSACTION = [
        URI => '',
        VIEW => 'admin-views.extra-charges.view.transaction'
    ];

    const VIEW_SETTING = [
        URI => '',
        VIEW => 'admin-views.extra-charges.view.setting'
    ];

    const UPDATE_SETTING = [
        URI => 'update_setting',
        VIEW => ''
    ];

    const BRANCH_STOCK_LIST = [
        URI => 'branch-stock-list',
        VIEW => 'admin-views.extra-charges.branch-stock-list'
    ];
 



}
