@php
    $isRtl = $isRtl ?? (app()->getLocale() === 'ar' || session('direction') === 'rtl');
    $dateRange = ($filters['from'] ?? '-') . ' - ' . ($filters['to'] ?? '-');
    $hasData = isset($pivotData) && count($pivotData) > 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_sales_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        /* Header Styles with Logo - Green like CRM */
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

        /* KPI Metrics - Table layout for mPDF */
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

        .kpi-value.percentage {
            color: #0f766e;
        }

        /* Chart Row - Table layout for mPDF */
        .chart-row {
            width: 100%;
            margin-bottom: 20px;
            display: block;
            overflow: hidden;
        }

        .chart-full {
            width: 100%;
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

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 250px;
        }

        /* Table Styles */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px 12px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            page-break-inside: avoid;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th {
            background: #e5e7eb;
            font-weight: 600;
            padding: 8px 6px;
            text-align: center;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* No Data Message */
        .no-data-message {
            text-align: center;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            margin: 20px 0;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .footer-table td {
            border: none;
            padding: 2px;
        }

        /* RTL Support */
        @if ($isRtl)
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }

            .text-end {
                text-align: left;
            }

            .text-start {
                text-align: right;
            }
        @endif
    </style>
</head>
<body>

    <!-- Modern Header with Logo - Green like CRM -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('crm_sales_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $filters['from'] ?? '-' }} - {{ $filters['to'] ?? '-' }}</p>
            @if(!empty($filters['sale_type']) && $filters['sale_type'] != 'all')
                <p style="font-size:10px; margin-top:3px;">{{ translate('sale_type') }}: {{ $filters['sale_type'] }}</p>
            @endif
            @if(!empty($filters['agent']) && $filters['agent'] != 'all')
                <p style="font-size:10px; margin-top:3px;">{{ translate('agent') }}: {{ $filters['agent'] }}</p>
            @endif
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            @endphp
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px;">
            @elseif(file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    alt="Logo" style="max-width:100px; max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((float)($statistics['total_sales'] ?? 0), 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('retail_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((float)($statistics['retail_sales'] ?? 0), 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('wholesale_sales') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((float)($statistics['wholesale_sales'] ?? 0), 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('top_agent') }}</div>
                    <div class="kpi-value"><strong>{{ $statistics['top_agent'] ?? '-' }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Chart Row: Sales Trend Chart -->
    @if(!empty($chartImage))
        <div class="chart-row">
            <div class="chart-full">
                <div class="chart-col">
                    <div class="chart-title">{{ translate('sales_trend') }} ({{ $dateRange }})</div>
                    <img src="{{ $chartImage }}" class="chart-image" alt="Sales Trend" />
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Statistics Table -->
    <div class="table-container" style="margin-bottom:20px;">
        <div class="table-header">
            <h3>{{ translate('summary_statistics') }}</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('metric') }}</th>
                    <th class="text-end">{{ translate('value') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-start">{{ translate('total_sales') }}</td>
                    <td class="text-end">{{ number_format((float)($statistics['total_sales'] ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('retail_sales') }}</td>
                    <td class="text-end">{{ number_format((float)($statistics['retail_sales'] ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('wholesale_sales') }}</td>
                    <td class="text-end">{{ number_format((float)($statistics['wholesale_sales'] ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('retail_percentage') }}</td>
                    <td class="text-end">{{ number_format((float)($statistics['retail_percentage'] ?? 0), 1) }}%</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('wholesale_percentage') }}</td>
                    <td class="text-end">{{ number_format((float)($statistics['wholesale_percentage'] ?? 0), 1) }}%</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('total_orders') }}</td>
                    <td class="text-end">{{ (int)($statistics['total_orders'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('total_quantity') }}</td>
                    <td class="text-end">{{ (int)($statistics['total_quantity'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="text-start">{{ translate('top_agent') }}</td>
                    <td class="text-end">{{ $statistics['top_agent'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Period Summary Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('period_summary') }} ({{ $dateRange }})</h3>
        </div>
        
        @if($hasData)
            <table>
                <thead>
                    <tr>
                        <th>{{ translate('period') }}</th>
                        <th class="text-end">{{ translate('retail_sales') }}</th>
                        <th class="text-end">{{ translate('wholesale_sales') }}</th>
                        <th class="text-end">{{ translate('total_sales') }}</th>
                        <th class="text-end">{{ translate('retail_orders') }}</th>
                        <th class="text-end">{{ translate('wholesale_orders') }}</th>
                        <th class="text-end">{{ translate('total_orders') }}</th>
                        <th class="text-end">{{ translate('total_quantity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pivotData as $row)
                    <tr>
                        <td class="text-start">{{ data_get($row, 'period', '-') }}</td>
                        <td class="text-end">{{ number_format((float)data_get($row, 'totals.retail_sales', 0), 2) }}</td>
                        <td class="text-end">{{ number_format((float)data_get($row, 'totals.wholesale_sales', 0), 2) }}</td>
                        <td class="text-end">{{ number_format((float)data_get($row, 'totals.total_sales', 0), 2) }}</td>
                        <td class="text-end">{{ (int)data_get($row, 'totals.retail_orders', 0) }}</td>
                        <td class="text-end">{{ (int)data_get($row, 'totals.wholesale_orders', 0) }}</td>
                        <td class="text-end">{{ (int)data_get($row, 'totals.total_orders', 0) }}</td>
                        <td class="text-end">{{ (int)data_get($row, 'totals.total_quantity', 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-data-message">{{ translate('no_data_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <div class="no-data-message">
                {{ translate('no_data_found_for_selected_period') }}
            </div>
        @endif
    </div>
</body>
</html>