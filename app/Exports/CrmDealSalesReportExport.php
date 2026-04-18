<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CrmDealSalesReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';
        $summary = $this->data['summary'] ?? [];
        $departmentSections = collect($this->data['departmentSections'] ?? []);
        $unassignedLabel = translate('Unassigned');

        $summaryHeadings = [translate('metric'), translate('value')];
        $summaryRows = [
            [translate('won_sales'), (float)($summary['won_sales_total'] ?? 0)],
            [translate('retail_won_sales'), (float)($summary['retail_won_sales'] ?? 0)],
            [translate('wholesale_won_sales'), (float)($summary['wholesale_won_sales'] ?? 0)],
            [translate('won_deals'), (int)($summary['won_count'] ?? 0)],
            [translate('lost_deals'), (int)($summary['lost_count'] ?? 0)],
            [translate('total_deals'), (int)($summary['total_deals'] ?? 0)],
        ];

        $detailsHeadings = [
            translate('Department'),
            translate('Employee'),
            translate('retail_won_sales'),
            translate('wholesale_won_sales'),
            translate('won_sales'),
            translate('won'),
            translate('lost'),
            translate('Total'),
        ];

        $detailsRows = [];
        foreach ($departmentSections as $section) {
            foreach (($section->employees ?? []) as $row) {
                $detailsRows[] = [
                    (string)($section->department_name ?? $unassignedLabel),
                    (string)($row->employee_name ?? $unassignedLabel),
                    (float)($row->retail_won_sales ?? 0),
                    (float)($row->wholesale_won_sales ?? 0),
                    (float)($row->won_sales_total ?? 0),
                    (int)($row->won_count ?? 0),
                    (int)($row->lost_count ?? 0),
                    (int)($row->total_deals ?? 0),
                ];
            }

            $detailsRows[] = [
                (string)($section->department_name ?? $unassignedLabel),
                translate('department_total'),
                (float)data_get($section, 'totals.retail_won_sales', 0),
                (float)data_get($section, 'totals.wholesale_won_sales', 0),
                (float)data_get($section, 'totals.won_sales_total', 0),
                (int)data_get($section, 'totals.won_count', 0),
                (int)data_get($section, 'totals.lost_count', 0),
                (int)data_get($section, 'totals.total_deals', 0),
            ];
        }

        $detailsRows[] = [
            translate('grand_total'),
            '-',
            (float)($summary['retail_won_sales'] ?? 0),
            (float)($summary['wholesale_won_sales'] ?? 0),
            (float)($summary['won_sales_total'] ?? 0),
            (int)($summary['won_count'] ?? 0),
            (int)($summary['lost_count'] ?? 0),
            (int)($summary['total_deals'] ?? 0),
        ];

        return [
            new InhouseProductSaleSheetExport(
                title: translate('summary'),
                headings: $summaryHeadings,
                rows: $summaryRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('Details'),
                headings: $detailsHeadings,
                rows: $detailsRows,
                isRtl: $isRtl
            ),
        ];
    }
}
