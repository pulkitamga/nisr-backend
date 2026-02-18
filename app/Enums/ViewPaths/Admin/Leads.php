<?php

namespace App\Enums\ViewPaths\Admin;

enum Leads
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.crm.leads'
    ];

    const VIEW = [
        URI => 'lead-view',
        VIEW => ''
    ];
    const SHOW = [
        URI => 'lead-show',
        VIEW => 'admin-views.crm.lead-view'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];
    const SEARCH_PARTY = [
        URI => 'search-party',
        VIEW => ''
    ];
    const GET_USER = [
        URI => 'search-user-order',
        VIEW => ''
    ];


    const CONVERT_TO_DEAL = [
        URI => 'convert-to-deal',
        VIEW => ''
    ];

    const DESQUALIFY = [
        URI => 'desqualify',
        VIEW => ''
    ];

    const DEPARTMENT = [
        URI => 'get-departments',
        VIEW => ''
    ];

    const ASSIGN_EMPLOYEE = [
        URI => 'employee-assign',
        VIEW => ''
    ];
    const DEPARTMENT_EMPLOYEE = [
        URI => 'get-department-employee',
        VIEW => ''
    ];

    const ASSIGN_OWNER = [
        URI => 'owner-assign',
        VIEW => ''
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];
    const ASSIGN_DEPARTMENT = [
        URI => 'update-department',
        VIEW => ''
    ];
}
