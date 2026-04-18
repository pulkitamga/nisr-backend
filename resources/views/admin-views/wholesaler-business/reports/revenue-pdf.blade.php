<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('wholesale_revenue_report') }}</title>
    <style>
        /* Your existing styles remain exactly the same */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        /* Header Styles with Logo - EXACT same as CRM insights */
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
            font-size: 18px;
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
        .kpi-wrapper {
            background-color: #f3f6fb;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate !important;
            border-spacing: 10px 0;
            /* Gap between the 4 cards */
            table-layout: fixed;
        }

        .kpi-table td {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px !important;
            /* Rounded corners */
            padding: 12px 10px;
            vertical-align: top;
            height: 55px;
            /* Consistent height */
            text-align: left;
            /* Left aligned */
        }

        .kpi-label {
            color: #6b7280;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .kpi-value {
            color: #111827;
            font-size: 15px;
            font-weight: 900;
            /* Extra bold */
            display: block;
        }

        .row {
            width: 100%;
            margin-bottom: 10px;
            display: block;
            overflow: hidden;
        }

        .col {
            width: 25%;
            float: left;
            box-sizing: border-box;
            padding-right: 10px;
            /* Increased padding for more spacing between cards */
        }

        .col:last-child {
            padding-right: 0;
            /* Remove padding from last column */
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .card-body {
            padding: 14px 16px;
        }

        .kpi-label {
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: .5px;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        /* Chart Row - Table layout for mPDF */
        .chart-row {
            width: 100%;
            margin-bottom: 25px;
            /* Increased bottom margin */
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
            border-radius: 8px;
            /* Slightly increased border radius */
            padding: 15px;
            /* Increased padding */
            background: white;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            /* Increased padding */
            margin: 0 0 15px 0;
            /* Increased bottom margin */
        }

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 170px;
        }

        /* Full Width Chart */
        .full-chart {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            background: white;
        }

        /* Table Styles */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            /* Increased top margin */
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
            /* Increased padding */
        }

        .table-header h3 {
            margin: 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #e5e7eb;
            font-weight: 600;
            padding: 10px 8px;
            /* Increased padding */
            text-align: center;
        }

        td {
            padding: 8px;
            /* Increased padding */
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* Footer - EXACT same as CRM insights */
        .footer {
            margin-top: 25px;
            /* Increased top margin */
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 10px;
            /* Increased padding */
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

            .col {
                padding-right: 0;
                padding-left: 10px;
            }

            .col:last-child {
                padding-left: 0;
            }
        @endif
    </style>
</head>

<body>
    <!-- Modern Header with Logo - EXACT same as CRM insights -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('wholesale_revenue_report') }}</h2>
            <p>{{ translate('report_period') }}:{{ $snapshotFromDisplay }} - {{ $snapshotToDisplay }}</p>
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            @endphp

            @if (!empty($logo))
                <img src="{{ $logo }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px;">
            @elseif(file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    alt="Logo" style="max-width:100px; max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    @if (isset($kpi) && !empty($kpi))
        <div class="kpi-wrapper">
            <table class="kpi-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="kpi-label">{{ translate('total_revenue') }}</div>
                        <div class="kpi-value">
                            <strong>{{ number_format((float) ($kpi['total_revenue'] ?? 0), 2) }}</strong>
                        </div>
                    </td>

                    <td>
                        <div class="kpi-label">{{ translate('paid_revenue') }}</div>
                        <div class="kpi-value">
                            <strong>{{ number_format((float) ($kpi['paid_revenue'] ?? 0), 2) }}</strong>
                        </div>
                    </td>

                    <td>
                        <div class="kpi-label">{{ translate('avg_order_value') }}</div>
                        <div class="kpi-value">
                            <strong>{{ number_format((float) ($kpi['avg_order_value'] ?? 0), 2) }}</strong>
                        </div>
                    </td>

                    <td>
                        <div class="kpi-label">{{ translate('fulfillment_rate') }}</div>
                        <div class="kpi-value">
                            <strong>{{ number_format((float) ($kpi['fulfillment_rate'] ?? 0), 1) }}%</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @php
        $dateRange = $snapshotFromDisplay . ' - ' . $snapshotToDisplay;
    @endphp

    <!-- First Row: Revenue Trend + Delivery Status side by side -->
    @if (!empty($revenueTrendChartImage) || !empty($deliveryStatusChartImage))
        <div class="chart-row">
            @if (!empty($revenueTrendChartImage))
                <div class="chart-trend">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('revenue_trend') }} ({{ $dateRange }})</div>
                        <img src="{{ $revenueTrendChartImage }}" class="chart-image" alt="Revenue Trend" />
                    </div>
                </div>
            @endif

            @if (!empty($deliveryStatusChartImage))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('delivery_status_mix') }} ({{ $dateRange }})</div>
                        <img src="{{ $deliveryStatusChartImage }}" class="chart-image" alt="Delivery Status" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Top Wholesalers Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('top_wholesalers_by_revenue') }} ({{ $dateRange }})</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Wholesaler') }}</th>
                    <th>{{ translate('Company') }}</th>
                    <th>{{ translate('Orders') }}</th>
                    <th>{{ translate('revenue') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topWholesalers ?? [] as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $row->wholeseller?->name ?? '#' . $row->wholesaler_id }}</strong></td>
                        <td>{{ $row->wholeseller?->wholesalerBusiness?->company_name ?? '-' }}</td>
                        <td class="value-ltr">{{ number_format((int) ($row->orders_count ?? 0)) }}</td>
                        <td class="value-ltr">{{ number_format((float) ($row->total_revenue ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 15px;">
                            {{ translate('no_wholesale_orders_found_in_this_period') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
