<?php

namespace App\Enums\ViewPaths\Admin;

enum Complaint
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.complaints.index'
    ];

    const VIEW = [
        URI => 'single-ticket',
        VIEW => 'admin-views.complaints.view'
    ];
    const DASHBOARD = [
        URI => 'dashboard',
        VIEW => 'admin-views.complaints.view'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];

    const DEPARTMENT = [
        URI => 'get-departments',
        VIEW => ''
    ];

    const DEPARTMENT_EMPLOYEE = [
        URI => 'get-department-employee',
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
    const SUPPORT_FOLLOW_UP = [
        URI => 'update-support-follow-up',
        VIEW => ''
    ];

     const COMPLAIN_FOLLOW_UP = [
        URI => 'update-complain-follow-up',
        VIEW => ''
    ];
     const WHOLESALE_FOLLOW_UP = [
        URI => 'update-wholesale-follow-up',
        VIEW => ''
    ];

}
