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
        $displayChannels = collect($this->data['displayChannels'] ?? []);
        $channelLabels = collect($this->data['channelLabels'] ?? []);
        $monthlyRows = collect($this->data['monthlyRows'] ?? []);
        $summaryPerEmployee = collect(data_get($this->data, 'summary.per_employee', []));
        $grand = data_get($this->data, 'summary.grand', []);

        $matrixHeadings = [translate('period')];
        foreach ($employees as $employee) {
            $name = (string)($employee->name ?? translate('Unassigned'));
            foreach ($displayChannels as $channel) {
                $matrixHeadings[] = $name . ' - ' . (string)($channelLabels->get($channel) ?? ucwords(str_replace(['-', '_'], ' ', (string)$channel)));
            }
            $matrixHeadings[] = $name . ' - ' . translate('Total');
        }
        foreach ($displayChannels as $channel) {
            $matrixHeadings[] = (string)($channelLabels->get($channel) ?? ucwords(str_replace(['-', '_'], ' ', (string)$channel)) . ' ' . translate('Total'));
        }
        $matrixHeadings[] = translate('total_interactions');

        $matrixRows = $monthlyRows->map(function ($row) use ($employees, $displayChannels) {
            $line = [(string)data_get($row, 'month_label', '')];

            foreach ($employees as $employee) {
                $employeeId = (int)($employee->id ?? 0);
                $cell = data_get($row, "employees.{$employeeId}", []);
                foreach ($displayChannels as $channel) {
                    $line[] = (int)data_get($cell, "channels.{$channel}", 0);
                }
                $line[] = (int)data_get($cell, 'total_count', 0);
            }

            foreach ($displayChannels as $channel) {
                $line[] = (int)data_get($row, "totals.channels.{$channel}", 0);
            }
            $line[] = (int)data_get($row, 'totals.total_count', 0);
            return $line;
        })->values()->all();

        $summaryHeadings = [translate('Employee')];
        foreach ($displayChannels as $channel) {
            $summaryHeadings[] = (string)($channelLabels->get($channel) ?? ucwords(str_replace(['-', '_'], ' ', (string)$channel)));
        }
        $summaryHeadings[] = translate('total_interactions');

        $summaryRows = $summaryPerEmployee->map(function ($row) use ($displayChannels) {
            $line = [
                (string)data_get($row, 'employee_name', translate('Unassigned')),
            ];

            foreach ($displayChannels as $channel) {
                $line[] = (int)data_get($row, "channels.{$channel}", 0);
            }

            $line[] = (int)data_get($row, 'total_count', 0);
            return $line;
        })->values()->all();

        if (!empty($grand)) {
            $grandLine = [
                translate('grand_total'),
            ];
            foreach ($displayChannels as $channel) {
                $grandLine[] = (int)data_get($grand, "channels.{$channel}", 0);
            }
            $grandLine[] = (int)data_get($grand, 'total_count', 0);
            $summaryRows[] = $grandLine;
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
