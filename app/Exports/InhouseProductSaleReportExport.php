<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InhouseProductSaleReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';

        $summaryRows = [
            [translate('POS'), (int)($this->data['summary']['pos_qty'] ?? 0), (float)($this->data['summary']['pos_amount'] ?? 0)],
            [translate('online'), (int)($this->data['summary']['online_qty'] ?? 0), (float)($this->data['summary']['online_amount'] ?? 0)],
            [translate('wholesale'), (int)($this->data['summary']['wholesale_qty'] ?? 0), (float)($this->data['summary']['wholesale_amount'] ?? 0)],
            [translate('total'), (int)($this->data['summary']['total_qty'] ?? 0), (float)($this->data['summary']['total_amount'] ?? 0)],
        ];

        $commonHeadings = [
            translate('SL'),
            translate('product'),
            translate('branch'),
            translate('qty'),
            translate('orders'),
            translate('sales'),
        ];

        $posRows = collect($this->data['posRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->product_name ?? '',
                $row->branch_name ?? '',
                (int)($row->total_qty ?? 0),
                (int)($row->total_orders ?? 0),
                (float)($row->total_amount ?? 0),
            ];
        })->all();

        $onlineRows = collect($this->data['onlineRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->product_name ?? '',
                $row->branch_name ?? '',
                (int)($row->total_qty ?? 0),
                (int)($row->total_orders ?? 0),
                (float)($row->total_amount ?? 0),
            ];
        })->all();

        $wholesaleRows = collect($this->data['wholesaleRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->product_name ?? '',
                $row->branch_name ?? '',
                (int)($row->total_qty ?? 0),
                (int)($row->total_orders ?? 0),
                (float)($row->total_amount ?? 0),
            ];
        })->all();

        return [
            new InhouseProductSaleSheetExport(
                title: translate('summary'),
                headings: [translate('sales_type'), translate('total_qty'), translate('total_sales')],
                rows: $summaryRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('POS'),
                headings: $commonHeadings,
                rows: $posRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('online'),
                headings: $commonHeadings,
                rows: $onlineRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('wholesale'),
                headings: $commonHeadings,
                rows: $wholesaleRows,
                isRtl: $isRtl
            ),
        ];
    }
}
