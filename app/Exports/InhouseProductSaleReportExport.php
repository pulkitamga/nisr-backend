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
            [translate('Wholesale'), (int)($this->data['summary']['wholesale_qty'] ?? 0), (float)($this->data['summary']['wholesale_amount'] ?? 0)],
            [translate('Total'), (int)($this->data['summary']['total_qty'] ?? 0), (float)($this->data['summary']['total_amount'] ?? 0)],
        ];

        $commonHeadings = [
            translate('SL'),
            translate('Product'),
            translate('Branch'),
            translate('QTY'),
            translate('Orders'),
            translate('Sales'),
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

        $stateRows = collect($this->data['retailStateRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->location_name ?? '',
                (int)($row->total_qty ?? 0),
                (int)($row->total_orders ?? 0),
                (float)($row->total_amount ?? 0),
            ];
        })->all();

        $cityRows = collect($this->data['retailCityRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->location_name ?? '',
                (int)($row->total_qty ?? 0),
                (int)($row->total_orders ?? 0),
                (float)($row->total_amount ?? 0),
            ];
        })->all();

        $areaRows = collect($this->data['retailAreaRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row->location_name ?? '',
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
                title: translate('Wholesale'),
                headings: $commonHeadings,
                rows: $wholesaleRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('State'),
                headings: [translate('SL'), translate('State'), translate('QTY'), translate('Orders'), translate('Sales')],
                rows: $stateRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('City'),
                headings: [translate('SL'), translate('City'), translate('QTY'), translate('Orders'), translate('Sales')],
                rows: $cityRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('Area'),
                headings: [translate('SL'), translate('Area'), translate('QTY'), translate('Orders'), translate('Sales')],
                rows: $areaRows,
                isRtl: $isRtl
            ),
        ];
    }
}
