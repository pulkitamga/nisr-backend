<?php

namespace App\Enums\ViewPaths\Admin;

enum  Deals
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.crm.deals.wholesaler'
    ];
    const RETAILER = [
        URI => 'list',
        VIEW => 'admin-views.crm.deals.retailer'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.crm.deals.wholesaler-view'
    ];
    const RETAIL_VIEW = [
        URI => 'view',
        VIEW => 'admin-views.crm.deals.retail-view'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];
    const LINK_ORDER = [
        URI => 'link-order',
        VIEW => ''
    ];
    const GET_USER_DATA = [
        URI => 'get-user-data',
        VIEW => ''
    ];
    const REQUEST_QUOTATION = [
        URI => 'request-quotation',
        VIEW => ''
    ];

 
 const EXPORT = [
        URI => 'export',
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

  
    const CONVERT_INQUIRY = [
        URI => 'inquiry-convert',
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
 
    const SPAM_MASSAGE = [
        URI => 'spam-massage',
        VIEW => ''
    ];
    const MASSAGE_DELETE = [
        URI => 'massages-destroy',
        VIEW => ''
    ];


    const TICKET_FOLLOW_UP = [
        URI => 'update-ticket-follow-up',
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

    const ASSIGN_DEPARTMENT = [
        URI => 'update-department',
        VIEW => ''
    ];
}
