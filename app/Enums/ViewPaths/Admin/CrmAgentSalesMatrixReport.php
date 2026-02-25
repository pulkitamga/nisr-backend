<?php

namespace App\Enums\ViewPaths\Admin;

enum CrmAgentSalesMatrixReport
{
    const VIEW = [
        URI => 'crm-agent-sales-matrix',
        VIEW => 'admin-views.report.crm-agent-sales-matrix-report',
    ];

    const EXPORT_EXCEL = [
        URI => 'crm-agent-sales-matrix-export-excel',
        VIEW => '',
    ];

    const EXPORT_PDF = [
        URI => 'crm-agent-sales-matrix-export-pdf',
        VIEW => 'admin-views.report.crm-agent-sales-matrix-report-pdf',
    ];
}
