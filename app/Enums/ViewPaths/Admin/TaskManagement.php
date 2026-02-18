<?php

namespace App\Enums\ViewPaths\Admin;

enum TaskManagement
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.task-management.index'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.task-management.add'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.task-management.edit'
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const USER_VIEW = [
        URI => 'users',
        VIEW => 'admin-views.task-management.view-users'
    ];

    const USER_ADD = [
        URI => 'add-users',
        VIEW => 'admin-views.task-management.add-users'
    ];

    const ASSIGN_MANAGER = [
        URI => 'assign-manager',
        VIEW => 'admin-views.branch.assign-manager'
    ];
    const ADD_MANAGER = [
        URI => 'add-manager',
        VIEW => 'admin-views.branch.add-manager'
    ];
    const UPDATE_MANAGER = [
        URI => 'update-manager',
        VIEW => 'admin-views.branch.update-manager'
    ];
    
    const ORDER_LIST_EXPORT = [
        URI => 'order-list-export',
    ];
    const ORDER_DETAILS = [
        URI => 'order-details',
        VIEW => 'admin-views.branch.order-details'
    ];

    const PRODUCT_LIST = [
        URI => 'product-list',
        VIEW => 'admin-views.branch.product-list'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];

    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.branch.view'
    ];

    const VIEW_ORDER = [
        URI => '',
        VIEW => 'admin-views.branch.view.order'
    ];

    const VIEW_PRODUCT = [
        URI => '',
        VIEW => 'admin-views.branch.view.product'
    ];

    const VIEW_REVIEW = [
        URI => '',
        VIEW => 'admin-views.branch.view.review'
    ];

    const VIEW_TRANSACTION = [
        URI => '',
        VIEW => 'admin-views.branch.view.transaction'
    ];

    const VIEW_SETTING = [
        URI => '',
        VIEW => 'admin-views.branch.view.setting'
    ];

    const UPDATE_SETTING = [
        URI => 'update_setting',
        VIEW => ''
    ];
}
