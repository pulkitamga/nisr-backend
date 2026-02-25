<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CrmEmployeeChannelAssignmentReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';
        $employees = collect($this->data['employeesForMatrix'] ?? []);
        $monthlyRows = collect($this->data['monthlyRows'] ?? []);
        $summaryPerEmployee = collect(data_get($this->data, 'summary.per_employee', []));
        $grand = data_get($this->data, 'summary.grand', []);

        $matrixHeadings = [translate('month')];
        foreach ($employees as $employee) {
            $name = (string)($employee->name ?? translate('unassigned'));
            $matrixHeadings[] = $name . ' - ' . translate('retail_customers');
            $matrixHeadings[] = $name . ' - ' . translate('wholesale_customers');
            $matrixHeadings[] = $name . ' - ' . translate('total');
        }
        $matrixHeadings[] = translate('retail_total');
        $matrixHeadings[] = translate('wholesale_total');
        $matrixHeadings[] = translate('total_interactions');

        $matrixRows = $monthlyRows->map(function ($row) use ($employees) {
            $line = [(string)data_get($row, 'month_label', '')];

            foreach ($employees as $employee) {
                $employeeId = (int)($employee->id ?? 0);
                $cell = data_get($row, "employees.{$employeeId}", []);
                $line[] = (int)data_get($cell, 'retail_count', 0);
                $line[] = (int)data_get($cell, 'wholesale_count', 0);
                $line[] = (int)data_get($cell, 'total_count', 0);
            }

            $line[] = (int)data_get($row, 'totals.retail_count', 0);
            $line[] = (int)data_get($row, 'totals.wholesale_count', 0);
            $line[] = (int)data_get($row, 'totals.total_count', 0);
            return $line;
        })->values()->all();

        $summaryHeadings = [
            translate('employee'),
            translate('retail_customers'),
            translate('wholesale_customers'),
            translate('total_interactions'),
        ];

        $summaryRows = $summaryPerEmployee->map(function ($row) {
            return [
                (string)data_get($row, 'employee_name', translate('unassigned')),
                (int)data_get($row, 'retail_count', 0),
                (int)data_get($row, 'wholesale_count', 0),
                (int)data_get($row, 'total_count', 0),
            ];
        })->values()->all();

        if (!empty($grand)) {
            $summaryRows[] = [
                translate('grand_total'),
                (int)data_get($grand, 'retail_count', 0),
                (int)data_get($grand, 'wholesale_count', 0),
                (int)data_get($grand, 'total_count', 0),
            ];
        }

        return [
            new InhouseProductSaleSheetExport(
                title: translate('monthly_matrix'),
                headings: $matrixHeadings,
                rows: $matrixRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('employee_summary'),
                headings: $summaryHeadings,
                rows: $summaryRows,
                isRtl: $isRtl
            ),
        ];
    }
}

