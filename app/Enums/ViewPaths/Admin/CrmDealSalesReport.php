<?php

namespace App\Enums\ViewPaths\Admin;

enum CrmDealSalesReport
{
    const VIEW = [
        URI => 'crm-sales-performance',
        VIEW => 'admin-views.report.crm-deal-sales-report',
    ];

    const EXPORT_EXCEL = [
        URI => 'crm-sales-performance-export-excel',
        VIEW => '',
    ];

    const EXPORT_PDF = [
        URI => 'crm-sales-performance-export-pdf',
        VIEW => 'admin-views.report.crm-deal-sales-report-pdf',
    ];
}
