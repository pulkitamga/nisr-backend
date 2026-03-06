<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CrmSalesReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';
        $statistics = $this->data['statistics'] ?? [];
        $pivotData = collect($this->data['pivotData'] ?? []);

        $summaryHeadings = [translate('metric'), translate('value')];
        $summaryRows = [
            [translate('total_sales'), (float)($statistics['total_sales'] ?? 0)],
            [translate('retail_sales'), (float)($statistics['retail_sales'] ?? 0)],
            [translate('wholesale_sales'), (float)($statistics['wholesale_sales'] ?? 0)],
            [translate('total_orders'), (int)($statistics['total_orders'] ?? 0)],
            [translate('total_quantity'), (int)($statistics['total_quantity'] ?? 0)],
            [translate('top_agent'), (string)($statistics['top_agent'] ?? '-')],
        ];

        $periodHeadings = [
            translate('period'),
            translate('retail_sales'),
            translate('wholesale_sales'),
            translate('total_sales'),
            translate('retail_orders'),
            translate('wholesale_orders'),
            translate('total_orders'),
            translate('total_quantity'),
        ];

        $periodRows = $pivotData->map(function ($periodRow) {
            return [
                (string)data_get($periodRow, 'period', '-'),
                (float)data_get($periodRow, 'totals.retail_sales', 0),
                (float)data_get($periodRow, 'totals.wholesale_sales', 0),
                (float)data_get($periodRow, 'totals.total_sales', 0),
                (int)data_get($periodRow, 'totals.retail_orders', 0),
                (int)data_get($periodRow, 'totals.wholesale_orders', 0),
                (int)data_get($periodRow, 'totals.total_orders', 0),
                (int)data_get($periodRow, 'totals.total_quantity', 0),
            ];
        })->values()->all();

        return [
            new InhouseProductSaleSheetExport(
                title: translate('summary'),
                headings: $summaryHeadings,
                rows: $summaryRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('period_summary'),
                headings: $periodHeadings,
                rows: $periodRows,
                isRtl: $isRtl
            ),
        ];
    }
}

