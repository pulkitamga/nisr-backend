<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_employee_channel_assignment_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        /* HEADER */
        .report-header {
            background: #0f766e;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .header-content {
            float: left;
            width: 70%;
        }

        .logo-container {
            float: right;
            width: 25%;
            text-align: right;
        }

        .logo-container img {
            max-width: 100px;
            max-height: 50px;
            object-fit: contain;
        }

        .header-content h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .header-content p {
            margin: 0;
            font-size: 11px;
            opacity: .9;
        }

        /* KPI */
        .kpi-container {
            background: #f3f6fb;
            padding: 10px 6px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            table-layout: fixed;
        }

        .kpi-table td {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            height: 55px;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        /* CHARTS */
        .full-chart {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            background: white;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .chart-title small {
            font-weight: normal;
            font-size: 10px;
            color: #4b5563;
            margin-{{ $isRtl ?? false ? 'right' : 'left' }}: 5px;
        }

        .chart-image {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
        }

        /* TABLE CONTAINER */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 13px;
        }

        /* MATRIX TABLE */
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        .matrix-table th {
            background: #e5e7eb;
            padding: 8px 4px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #cbd5e1;
        }

        .matrix-table td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .matrix-table thead {
            display: table-header-group;
        }

        .matrix-table tfoot {
            display: table-footer-group;
        }

        .matrix-table tr,
        .matrix-table th,
        .matrix-table td {
            page-break-inside: avoid;
        }

        .matrix-page {
            page-break-before: always;
        }

        .matrix-page-header {
            margin-bottom: 12px;
        }

        .matrix-page-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 8px;
        }

        .matrix-filter-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .matrix-filter-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
        }

        .matrix-filter-label {
            width: 18%;
            font-weight: 600;
            background: #f8fafc;
        }

        .summary-section {
            page-break-before: always;
        }

        .group-header {
            background: #d1d5db !important;
            font-weight: 700;
        }

        .group-separator {
            border-right: 2px solid #334155 !important;
        }

        .total-separator {
            border-left: 2px solid #111827 !important;
        }

        .left-align {
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
            padding-left: 8px;
            font-weight: 600;
        }

        .grand-total-row {
            background: #0f766e !important;
            color: white !important;
            font-weight: 700;
        }

        .grand-total-row td {
            background: #0f766e !important;
            color: white !important;
            border-color: #1e293b;
        }

        .totals-row {
            background: #e6f0f0;
            font-weight: 600;
        }

        /* SUMMARY TABLE */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }

        .summary-label {
            font-weight: 600;
            background: #f3f4f6;
        }

        /* FOOTER */
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            border: none;
            padding: 2px;
        }

        /* RTL Support */
        @if ($isRtl ?? false)
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }

            .group-separator {
                border-right: 0 !important;
                border-left: 2px solid #334155 !important;
            }

            .total-separator {
                border-left: 0 !important;
                border-right: 2px solid #111827 !important;
            }

            .left-align {
                text-align: right;
                padding-right: 8px;
            }

            .chart-title small {
                margin-right: 5px;
                margin-left: 0;
            }
        @endif

        .text-bold {
            font-weight: 700;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('crm_employee_channel_assignment_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $filters['from'] }} - {{ $filters['to'] }}</p>
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            @endphp
            @if (file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    style="max-width:100px;max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_interactions') }}</div>
                    <div class="kpi-value">{{ $summary['grand']['total_count'] }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('active_employees') }}</div>
                    <div class="kpi-value">{{ $summary['active_employees'] }}</div>
                </td>
                @foreach ($counterChannels as $channel)
                    <td>
                        <div class="kpi-label">
                            {{ $channelLabels[$channel] ?? ucwords(str_replace(['-', '_'], ' ', $channel)) }}</div>
                        <div class="kpi-value">{{ $counterTotals[$channel] ?? 0 }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <!-- CHART -->
   @if (!empty($channelChart) && $summary['grand']['total_count'] > 0)
        <div class="full-chart">
            <div class="chart-title">
                {{ translate('assigned_interactions_by_channel') }}
                <small>({{ $filters['from'] }} - {{ $filters['to'] }})</small>
            </div>
            <img src="{{ $channelChart }}" class="chart-image" alt="Channel Assignment Chart">
        </div>
    @endif

    @php
        $employeeChunks = collect(data_get($pdfMatrixLayout, 'employee_chunks', [[]]));
        $employeeSubColumns = data_get($pdfMatrixLayout, 'employee_sub_columns', array_merge($displayChannels, ['total']));
        $periodColumnWidthMm = (float) data_get($pdfMatrixLayout, 'period_column_width_mm', 24);
        $subColumnWidthMm = (float) data_get($pdfMatrixLayout, 'sub_column_width_mm', 16);
        $employeeBlockWidthMm = (float) data_get($pdfMatrixLayout, 'employee_block_width_mm', count($employeeSubColumns) * $subColumnWidthMm);
    @endphp

    <!-- MATRIX TABLE -->
    @foreach ($employeeChunks as $employeeChunk)
        @php
            $employeeChunk = collect($employeeChunk);
            $matrixColumnCount = 1 + (($employeeChunk->count() + 1) * count($employeeSubColumns));
            $matrixTableWidthMm = $periodColumnWidthMm + (($employeeChunk->count() + 1) * $employeeBlockWidthMm);
        @endphp
        <div class="matrix-page">
            <div class="matrix-page-header">
                <div class="matrix-page-title">{{ translate('crm_employee_channel_assignment_report') }}</div>
                <table class="matrix-filter-table">
                    <tr>
                        <td class="matrix-filter-label">{{ translate('department') }}</td>
                        <td>{{ $selectedFilterLabels['department'] ?? translate('all') }}</td>
                        <td class="matrix-filter-label">{{ translate('employee') }}</td>
                        <td>{{ $selectedFilterLabels['employee'] ?? translate('all') }}</td>
                    </tr>
                    <tr>
                        <td class="matrix-filter-label">{{ translate('channel') }}</td>
                        <td>{{ $selectedFilterLabels['channel'] ?? translate('all') }}</td>
                        <td class="matrix-filter-label">{{ translate('date_range') }}</td>
                        <td>{{ $filters['from'] }} - {{ $filters['to'] }}</td>
                    </tr>
                </table>
            </div>

            <div class="table-container">
                <div class="table-header">
                    {{ translate('employee_channel_assignment_matrix') }}
                </div>
                <table class="matrix-table" style="width: {{ number_format($matrixTableWidthMm, 2, '.', '') }}mm;">
                    <thead>
                        <tr>
                            <th rowspan="2" class="left-align"
                                style="width: {{ number_format($periodColumnWidthMm, 2, '.', '') }}mm;">
                                {{ translate('period') }}
                            </th>
                            @foreach ($employeeChunk as $employee)
                                <th class="group-header {{ !$loop->last ? 'group-separator' : '' }}"
                                    colspan="{{ count($employeeSubColumns) }}"
                                    style="width: {{ number_format($employeeBlockWidthMm, 2, '.', '') }}mm;">
                                    {{ $employee->name }}
                                </th>
                            @endforeach
                            <th class="group-header total-separator" colspan="{{ count($employeeSubColumns) }}"
                                style="width: {{ number_format($employeeBlockWidthMm, 2, '.', '') }}mm;">
                                {{ translate('totals') }}
                            </th>
                        </tr>
                        <tr>
                            @foreach ($employeeChunk as $employee)
                                @foreach ($displayChannels as $channel)
                                    <th style="width: {{ number_format($subColumnWidthMm, 2, '.', '') }}mm;">
                                        {{ $channelLabels[$channel] ?? ucwords(str_replace(['-', '_'], ' ', $channel)) }}
                                    </th>
                                @endforeach
                                <th class="{{ !$loop->last ? 'group-separator' : '' }}"
                                    style="width: {{ number_format($subColumnWidthMm, 2, '.', '') }}mm;">
                                    {{ translate('total') }}
                                </th>
                            @endforeach
                            @foreach ($displayChannels as $channel)
                                <th class="{{ $loop->first ? 'total-separator' : '' }}"
                                    style="width: {{ number_format($subColumnWidthMm, 2, '.', '') }}mm;">
                                    {{ $channelLabels[$channel] ?? ucwords(str_replace(['-', '_'], ' ', $channel)) }}
                                </th>
                            @endforeach
                            <th style="width: {{ number_format($subColumnWidthMm, 2, '.', '') }}mm;">
                                {{ translate('total') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyRows as $row)
                            <tr>
                                <td class="left-align">{{ $row->month_label }}</td>
                                @foreach ($employeeChunk as $employee)
                                    @php($cell = $row->employees[$employee->id] ?? null)
                                    @foreach ($displayChannels as $channel)
                                        <td>{{ $cell['channels'][$channel] ?? 0 }}</td>
                                    @endforeach
                                    <td class="{{ !$loop->last ? 'group-separator' : '' }}">
                                        {{ $cell['total_count'] ?? 0 }}
                                    </td>
                                @endforeach
                                @foreach ($displayChannels as $channel)
                                    <td class="{{ $loop->first ? 'total-separator' : '' }} text-bold">
                                        {{ $row->totals['channels'][$channel] ?? 0 }}
                                    </td>
                                @endforeach
                                <td class="text-bold">{{ $row->totals['total_count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $matrixColumnCount }}" style="text-align: center; padding: 20px;">
                                    {{ translate('no_data_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($monthlyRows->isNotEmpty())
                        <tfoot>
                            <tr class="totals-row">
                                <td class="left-align">{{ translate('grand_total') }}</td>
                                @foreach ($employeeChunk as $employee)
                                    @php($employeeTotal = $summary['per_employee']->firstWhere('employee_id', $employee->id))
                                    @foreach ($displayChannels as $channel)
                                        <td>{{ $employeeTotal->channels[$channel] ?? 0 }}</td>
                                    @endforeach
                                    <td class="{{ !$loop->last ? 'group-separator' : '' }}">
                                        {{ $employeeTotal->total_count ?? 0 }}
                                    </td>
                                @endforeach
                                @foreach ($displayChannels as $channel)
                                    <td class="{{ $loop->first ? 'total-separator' : '' }} text-bold">
                                        {{ $summary['grand']['channels'][$channel] ?? 0 }}
                                    </td>
                                @endforeach
                                <td class="text-bold">{{ $summary['grand']['total_count'] }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endforeach

    <!-- EMPLOYEE SUMMARY TABLE -->
    <div class="summary-section">
        <div class="table-container">
        <div class="table-header">
            {{ translate('total_interactions_by_employee') }}
        </div>
        <table class="matrix-table">
            <thead>
                <tr>
                    <th class="left-align">{{ translate('employee') }}</th>
                    @foreach ($displayChannels as $channel)
                        <th>{{ $channelLabels[$channel] ?? ucwords(str_replace(['-', '_'], ' ', $channel)) }}</th>
                    @endforeach
                    <th>{{ translate('total_interactions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary['per_employee'] as $item)
                    <tr>
                        <td class="left-align">{{ $item->employee_name }}</td>
                        @foreach ($displayChannels as $channel)
                            <td>{{ $item->channels[$channel] ?? 0 }}</td>
                        @endforeach
                        <td class="text-bold">{{ $item->total_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($displayChannels) + 2 }}" style="text-align: center; padding: 15px;">
                            {{ translate('no_data_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</body>

</html>
