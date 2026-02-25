<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CrmAgentSalesMatrixReportExport implements WithMultipleSheets
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
        $unassignedLabel = translate('unassigned');

        $matrixHeadings = [translate('month')];
        foreach ($employees as $employee) {
            $name = (string)($employee->name ?? $unassignedLabel);
            $matrixHeadings[] = $name . ' - ' . translate('retail_batteries');
            $matrixHeadings[] = $name . ' - ' . translate('retail_customers');
            $matrixHeadings[] = $name . ' - ' . translate('wholesale_batteries');
            $matrixHeadings[] = $name . ' - ' . translate('wholesale_customers');
        }
        $matrixHeadings[] = translate('total_batteries');
        $matrixHeadings[] = translate('total_customers');

        $matrixRows = $monthlyRows->map(function ($row) use ($employees) {
            $line = [(string)data_get($row, 'month_label', '')];

            foreach ($employees as $employee) {
                $employeeId = (int)($employee->id ?? 0);
                $cell = data_get($row, "employees.{$employeeId}", []);
                $line[] = (int)data_get($cell, 'retail_batteries', 0);
                $line[] = (int)data_get($cell, 'retail_customers', 0);
                $line[] = (int)data_get($cell, 'wholesale_batteries', 0);
                $line[] = (int)data_get($cell, 'wholesale_customers', 0);
            }

            $line[] = (int)data_get($row, 'totals.total_batteries', 0);
            $line[] = (int)data_get($row, 'totals.total_customers', 0);
            return $line;
        })->values()->all();

        $summaryHeadings = [
            translate('employee'),
            translate('retail_batteries'),
            translate('retail_customers'),
            translate('wholesale_batteries'),
            translate('wholesale_customers'),
            translate('total_batteries'),
            translate('total_customers'),
        ];

        $summaryRows = $summaryPerEmployee->map(function ($row) {
            return [
                (string)data_get($row, 'employee_name', translate('unassigned')),
                (int)data_get($row, 'retail_batteries', 0),
                (int)data_get($row, 'retail_customers', 0),
                (int)data_get($row, 'wholesale_batteries', 0),
                (int)data_get($row, 'wholesale_customers', 0),
                (int)data_get($row, 'total_batteries', 0),
                (int)data_get($row, 'total_customers', 0),
            ];
        })->values()->all();

        if (!empty($grand)) {
            $summaryRows[] = [
                translate('grand_total'),
                (int)data_get($grand, 'retail_batteries', 0),
                (int)data_get($grand, 'retail_customers', 0),
                (int)data_get($grand, 'wholesale_batteries', 0),
                (int)data_get($grand, 'wholesale_customers', 0),
                (int)data_get($grand, 'total_batteries', 0),
                (int)data_get($grand, 'total_customers', 0),
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
                title: translate('agent_summary'),
                headings: $summaryHeadings,
                rows: $summaryRows,
                isRtl: $isRtl
            ),
        ];
    }
}
