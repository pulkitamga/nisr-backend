<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_sales_performance_report') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        /* HEADER */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
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
            opacity: 0.9;
            font-size: 11px;
        }

        /* Clear float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI */
        .kpi-container {
            background-color: #f3f6fb;
            padding: 10px 5px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            table-layout: fixed;
        }

        .kpi-table td {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px !important;
            padding: 12px 10px;
            vertical-align: top;
            height: 55px;
            text-align: left;
        }

        .kpi-label {
            color: #5f6672;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 0 0 8px 0;
            text-align: center;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-align: left;
        }

        /* CHARTS */
        .chart-row {
            width: 100%;
            margin: 0 0 6px 0;
            display: block;
            overflow: hidden;
        }

        .chart-trend {
            width: 68%;
            float: left;
            margin-right: 2%;
        }

        .chart-stage {
            width: 30%;
            float: left;
        }

        .chart-col {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            background: white;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 5px;
            margin: 0 0 12px 0;
        }

        .chart-title small {
            font-weight: normal;
            font-size: 10px;
            color: #4b5563;
            margin-{{ $isRtl ?? false ? 'right' : 'left' }}: 5px;
        }

        .chart-image {
            width: 100%;
            max-height: 170px;
        }

        /* FULL CHART */
        .full-chart {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            background: white;
            page-break-inside: avoid;
        }

        /* TABLE */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px 12px;
        }

        .table-header strong {
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        th {
            background: #e5e7eb;
            padding: 8px 6px;
            font-weight: 600;
            text-align: center;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
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

            .chart-trend {
                float: right;
                margin-right: 0;
                margin-left: 2%;
            }

            .chart-stage {
                float: right;
            }

            .chart-title small {
                margin-right: 5px;
                margin-left: 0;
            }
        @endif
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('crm_sales_performance_report') }}</h2>
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
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('won_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($summary['won_sales_total'], 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('retail_won_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($summary['retail_won_sales'], 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('wholesale_won_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($summary['wholesale_won_sales'], 2) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('won_deals') }}</div>
                    <div class="kpi-value"><strong>{{ $summary['won_count'] }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('lost_deals') }}</div>
                    <div class="kpi-value"><strong>{{ $summary['lost_count'] }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('total_deals') }}</div>
                    <div class="kpi-value"><strong>{{ $summary['total_deals'] }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FIRST ROW: Two Charts Side by Side -->
    @if (!empty($employeeChart) || !empty($statusChart))
        <div class="chart-row">
            @if (!empty($employeeChart))
                <div class="chart-trend" style="margin-right:20px;">
                    <div class="chart-col">
                        <div class="chart-title">
                            {{ translate('won_vs_lost_by_employee') }}
                            <small>({{ $filters['from'] }} - {{ $filters['to'] }})</small>
                        </div>
                        <img src="{{ $employeeChart }}" class="chart-image">
                    </div>
                </div>
            @endif

            @if (!empty($statusChart))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">
                            {{ translate('overall_deal_status_split') }}
                            <small>({{ $filters['from'] }} - {{ $filters['to'] }})</small>
                        </div>
                        <img src="{{ $statusChart }}" class="chart-image">
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- SECOND ROW: Full Width Chart -->
    @if (!empty($retailWholesaleChart))
        <div class="full-chart">
            <div class="chart-title">
                {{ translate('won_sales_retail_vs_wholesale_by_employee') }}
                <small>({{ $filters['from'] }} - {{ $filters['to'] }})</small>
            </div>
            <img src="{{ $retailWholesaleChart }}" style="width:100%; max-height:140px;">
        </div>
    @endif

    <!-- TABLE -->
    <div class="table-container">
        <div class="table-header">
            <strong>{{ translate('department_and_employee_won_lost_summary') }}</strong>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('department') }}</th>
                    <th>{{ translate('employee') }}</th>
                    <th>{{ translate('retail_won_sales') }}</th>
                    <th>{{ translate('wholesale_won_sales') }}</th>
                    <th>{{ translate('won_sales') }}</th>
                    <th>{{ translate('won') }}</th>
                    <th>{{ translate('lost') }}</th>
                    <th>{{ translate('total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departmentSections as $section)
                    @foreach ($section->employees as $row)
                        <tr>
                            <td>{{ $section->department_name }}</td>
                            <td><strong>{{ $row->employee_name }}</strong></td>
                            <td class="value-ltr">{{ number_format($row->retail_won_sales, 2) }}</td>
                            <td class="value-ltr">{{ number_format($row->wholesale_won_sales, 2) }}</td>
                            <td class="value-ltr">{{ number_format($row->won_sales_total, 2) }}</td>
                            <td>{{ $row->won_count }}</td>
                            <td>{{ $row->lost_count }}</td>
                            <td>{{ $row->total_deals }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#e6f0f0;font-weight:bold">
                        <td colspan="2">{{ $section->department_name }} {{ translate('total') }}</td>
                        <td>{{ number_format($section->totals['retail_won_sales'], 2) }}</td>
                        <td>{{ number_format($section->totals['wholesale_won_sales'], 2) }}</td>
                        <td>{{ number_format($section->totals['won_sales_total'], 2) }}</td>
                        <td>{{ $section->totals['won_count'] }}</td>
                        <td>{{ $section->totals['lost_count'] }}</td>
                        <td>{{ $section->totals['total_deals'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#0f766e;color:white;font-weight:bold">
                    <td colspan="2">{{ translate('grand_total') }}</td>
                    <td>{{ number_format($summary['retail_won_sales'], 2) }}</td>
                    <td>{{ number_format($summary['wholesale_won_sales'], 2) }}</td>
                    <td>{{ number_format($summary['won_sales_total'], 2) }}</td>
                    <td>{{ $summary['won_count'] }}</td>
                    <td>{{ $summary['lost_count'] }}</td>
                    <td>{{ $summary['total_deals'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>


</body>

</html>
