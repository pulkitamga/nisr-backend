<?php

namespace App\Enums\ViewPaths\Admin;

enum CrmEmployeeChannelAssignmentReport
{
    const VIEW = [
        URI => 'crm-employee-channel-assignment',
        VIEW => 'admin-views.report.crm-employee-channel-assignment-report',
    ];

    const EXPORT_EXCEL = [
        URI => 'crm-employee-channel-assignment-export-excel',
        VIEW => '',
    ];

    const EXPORT_PDF = [
        URI => 'crm-employee-channel-assignment-export-pdf',
        VIEW => 'admin-views.report.crm-employee-channel-assignment-report-pdf',
    ];
}

