<?php

namespace App\Enums\ViewPaths\Admin;

enum Crm
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.crm.index'
    ];
    const SHOW = [
        URI => 'message-show',
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
    const ADD_NEW_MESSAGE = [
        URI => 'add-new-message',
        VIEW => ''
    ];
    const ADD_NEW_MASSAGE = self::ADD_NEW_MESSAGE; // Legacy typo alias.

    const DEPARTMENT_EMPLOYEE = [
        URI => 'get-department-employee',
        VIEW => ''
    ];
    const CONVERT_INQUIRY = [
        URI => 'inquiry-convert',
        VIEW => ''
    ];
    const CONVERT_BULK_INQUIRY = [
        URI => 'inquiry-bulk-convert',
        VIEW => ''
    ];
    const TYPE_CHANGE = [
        URI => 'message-type-change',
        VIEW => ''
    ];
    const MESSAGE_IGNORE = [
        URI => 'message-ignore',
        VIEW => ''
    ];
    const MASSAGE_IGNORE = self::MESSAGE_IGNORE; // Legacy typo alias.
    const ASSIGN_OWNER = [
        URI => 'owner-assign',
        VIEW => ''
    ];
    const ASSIGN_EMPLOYEE = [
        URI => 'employee-assign',
        VIEW => ''
    ];
    const SPAM_MESSAGE = [
        URI => 'spam-message',
        VIEW => ''
    ];
    const SPAM_MASSAGE = self::SPAM_MESSAGE; // Legacy typo alias.
    const MESSAGE_DELETE = [
        URI => 'messages-destroy',
        VIEW => ''
    ];
    const MASSAGE_DELETE = self::MESSAGE_DELETE; // Legacy typo alias.

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
