<?php

namespace App\Enums\ViewPaths\Admin;

enum SupportTicket
{
    const LIST = [
        URI => 'view',
        VIEW => 'admin-views.support-ticket.view'
    ];
    const WHOLESALE = [
        URI => 'wholesale',
        VIEW => 'admin-views.crm.tickets.wholesale'
    ];
    const RETAIL = [
        URI => 'retail',
        VIEW => 'admin-views.crm.tickets.retail'
    ];
    const SERVICE = [
        URI => 'service',
        VIEW => 'admin-views.crm.tickets.service'
    ];
    const SUPPORT = [
        URI => 'support',
        VIEW => 'admin-views.crm.tickets.support'
    ];
    const COMPLAINT = [
        URI => 'complaint',
        VIEW => 'admin-views.crm.tickets.complaint'
    ];
    const CAREER = [
        URI => 'career',
        VIEW => 'admin-views.crm.tickets.career'
    ];

    const VIEW = [
        URI => 'conversation',
        VIEW => 'admin-views.support-ticket.singleView'
    ];
    const DETAILS = [
        URI => 'single-ticket',
        VIEW => 'admin-views.crm.tickets.partials.support-detail'
    ];

    const EXPORT = [
        URI => '/{type}/export',
        VIEW => ''
    ];
    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];
    const PRIORITY = [
        URI => 'priority',
        VIEW => ''
    ];
    const ESCLATE_RETAIL = [
        URI => 'esclate/retail',
        VIEW => ''
    ];
    const ESCLATE_WHOLESALE = [
        URI => 'esclate/wholesale',
        VIEW => ''
    ];

}
