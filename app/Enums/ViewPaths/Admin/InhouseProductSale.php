<?php

namespace App\Enums\ViewPaths\Admin;

enum InhouseProductSale
{

    const VIEW = [
        URI => 'inhouse-product-sale',
        VIEW => 'admin-views.report.inhouse-product-sale'
    ];

    const EXPORT_EXCEL = [
        URI => 'inhouse-product-sale-export-excel',
        VIEW => ''
    ];

    const EXPORT_PDF = [
        URI => 'inhouse-product-sale-export-pdf',
        VIEW => 'admin-views.report.inhouse-product-sale-pdf'
    ];

}
