<?php

namespace App\Enums\ViewPaths\Admin;

enum Crm
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.crm.index'
    ];
 const SHOW = [
        URI => 'massage-show',
        VIEW => 'admin-views.crm.index-view'
    ];
 const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];
    const VIEW = [
        URI => 'single-ticket',
        VIEW => 'admin-views.complaints.view'
    ];
    const DASHBOARD_INDEX = [
        URI => 'dashboard',
        VIEW => 'admin-views.crm.dashboard.index'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];

    const DEPARTMENT = [
        URI => 'get-departments',
        VIEW => ''
    ];
    const ADD_NEW_MASSAGE = [
        URI => 'add-new-massage',
        VIEW => ''
    ];

    const DEPARTMENT_EMPLOYEE = [
        URI => 'get-department-employee',
        VIEW => ''
    ];
    const CONVERT_INQUIRY = [
        URI => 'inquiry-convertd',
        VIEW => ''
    ];
    const CONVERT_BULK_INQUIRY = [
        URI => 'inquiry-bulk-convert',
        VIEW => ''
    ];
    const TYPE_CHANGE = [
        URI => 'massage-type-change',
        VIEW => ''
    ];
    const MASSAGE_IGNORE = [
        URI => 'massage-ignore',
        VIEW => ''
    ];
    const ASSIGN_OWNER = [
        URI => 'owner-assign',
        VIEW => ''
    ];
    const ASSIGN_EMPLOYEE = [
        URI => 'employee-assign',
        VIEW => ''
    ];
    const SPAM_MASSAGE = [
        URI => 'spam-massage',
        VIEW => ''
    ];
    const MASSAGE_DELETE = [
        URI => 'massages-destroy',
        VIEW => ''
    ];

    const TICKET_DEPARTMENT = [
        URI => 'update-ticket-department',
        VIEW => ''
    ];

    const TICKET_EMPLOYEE = [
        URI => 'update-ticket-employee',
        VIEW => ''
    ];

    const TICKET_FOLLOW_UP = [
        URI => 'update-ticket-follow-up',
        VIEW => ''
    ];

}
