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

        .chart-row {
            width: 100%;
            margin-bottom: 20px;
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
            margin-bottom: 10px;
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
        }

        /* TABLE */

        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #e5e7eb;
            padding: 7px;
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

    <div class="report-header">
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
        <table class="kpi-table">
            <tr>

                <td>
                    <div class="kpi-label">{{ translate('won_sales') }}</div>
                    <div class="kpi-value">{{ number_format($summary['won_sales_total'], 2) }}</div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('retail_won_sales') }}</div>
                    <div class="kpi-value">{{ number_format($summary['retail_won_sales'], 2) }}</div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('wholesale_won_sales') }}</div>
                    <div class="kpi-value">{{ number_format($summary['wholesale_won_sales'], 2) }}</div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('won_deals') }}</div>
                    <div class="kpi-value">{{ $summary['won_count'] }}</div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('lost_deals') }}</div>
                    <div class="kpi-value">{{ $summary['lost_count'] }}</div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('total_deals') }}</div>
                    <div class="kpi-value">{{ $summary['total_deals'] }}</div>
                </td>

            </tr>
        </table>
    </div>

    @if (!empty($employeeChart) || !empty($statusChart))
        <div style="width:100%; margin:10px 0;">

            @if (!empty($employeeChart))
                <div style="display:inline-block; width:68%; vertical-align:top; margin-right:2%;">

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
                <div style="display:inline-block; width:30%; vertical-align:top;">

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


    <!-- FULL CHART - Third Graph (Full Width) -->

    @if (!empty($retailWholesaleChart))
        <div class="full-chart">

            <div class="chart-title">
                {{ translate('won_sales_retail_vs_wholesale_by_employee') }}
                <small>({{ $filters['from'] }} - {{ $filters['to'] }})</small>
            </div>

            <img src="{{ $retailWholesaleChart }}" style="width:100%;max-height:200px;">

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

                        <td colspan="2">
                            {{ $section->department_name }} {{ translate('total') }}
                        </td>

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


    <!-- FOOTER -->

    <div style="
border-top:1px dashed #d1d5db;
margin-top:20px;
padding-top:8px;
font-size:9px;
color:#6b7280;
">

        <table width="100%">
            <tr>

                <td width="20%" style="text-align:left; color:red;">
                    Page {PAGENO}
                </td>

                <td width="60%" style="text-align:center;">
                    Generated on: {{ now()->translatedFormat('j F Y, h:i A') }} | CRM insights report<br>
                    Generated by: <span style="color:red;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color:red;">{{ config('app.name') }}</span>
                </td>

                <td width="20%"></td>

            </tr>
        </table>

    </div>

</body>

</html>
