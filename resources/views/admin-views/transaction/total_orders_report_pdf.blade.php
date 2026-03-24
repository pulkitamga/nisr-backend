@php
    $isRtl = session('direction') === 'rtl' || app()->getLocale() === 'ar';
    $dateRange = $data['date_range'] ?? '';
    $hasData = isset($data['orders']) && count($data['orders']) > 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('order_report') }}</title>
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
            text-align: center;
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
            text-align: center;
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

        .chart-trend {
            width: 49%;
            float: left;
            margin-right: 1%;
        }

        .chart-stage {
            width: 49%;
            float: left;
            margin-left: 1%;
        }

        .chart-col {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            background: white;
            height: auto;
            min-height: 250px;
        }

        .chart-title {
            font-size: 8px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 5px;
            margin: 0 0 12px 0;
        }

        .chart-image {
            width: 100%;
            height: 220px;
            object-fit: contain;
        }

        /* Payment Stats */
        .payment-stats {
            margin-top: 15px;
            font-size: 10px;
        }

        .payment-stat-item {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .color-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 8px;
            border-radius: 3px;
        }

        @if ($isRtl)
            .color-box {
                margin-right: 0;
                margin-left: 8px;
            }
        @endif

        /* Table Styles */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px 12px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 14px;
            color: white;
        }

        .table-header .badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 2px 10px;
            font-size: 11px;
            margin-left: 8px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th {
            background: #e5e7eb;
            font-weight: 600;
            padding: 8px 4px;
            text-align: center;
        }

        td {
            padding: 6px 4px;
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

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending,
        .status-payment-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #cce5ff;
            color: #004085;
        }

        .status-processing,
        .status-out-for-delivery {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #28a745;
            color: white;
        }

        .status-canceled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-returned {
            background: #e2d5f0;
            color: #563d7c;
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
            background: transparent;
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

            .chart-trend {
                float: right;
                margin-right: 0;
                margin-left: 1%;
            }

            .chart-stage {
                float: right;
                margin-left: 0;
                margin-right: 1%;
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
            <h2>{{ translate('order_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $dateRange }}</p>
        </div>
        <div class="logo-container">
            @php
                $logoFile = $data['company_web_logo'] ?? null;
                if (is_array($logoFile)) {
                    $logoFile = !empty($logoFile) ? reset($logoFile) : null;
                }
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                $logoSrc = '';
                if (!empty($logoFile) && file_exists(public_path($logoFile))) {
                    $path = public_path($logoFile);
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                    $logoSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                } elseif (file_exists($defaultLogoPath)) {
                    $extension = pathinfo($defaultLogoPath, PATHINFO_EXTENSION);
                    $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                    $logoSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($defaultLogoPath));
                }
            @endphp
            @if ($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_orders') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((int) ($data['order_count']['total_order'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('completed') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((int) ($data['order_count']['delivered_order'] ?? 0)) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('ongoing') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((int) ($data['order_count']['ongoing_order'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('canceled') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((int) ($data['order_count']['canceled_order'] ?? 0)) }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Second Row of KPI if revenue data exists -->
    @if (isset($data['total_order_amount']) || isset($data['avg_order_value']))
        <div class="kpi-container" style="margin-top: -10px;">
            <table class="kpi-table" cellpadding="0" cellspacing="0">
                <tr>
                    @if (isset($data['total_order_amount']))
                        <td>
                            <div class="kpi-label">{{ translate('total_revenue') }}</div>
                            <div class="kpi-value">
                                <strong>{{ number_format((float) ($data['total_order_amount'] ?? 0), 2) }}</strong>
                            </div>
                        </td>
                    @endif
                    @if (isset($data['avg_order_value']))
                        <td>
                            <div class="kpi-label">{{ translate('avg_order_value') }}</div>
                            <div class="kpi-value">
                                <strong>{{ number_format((float) ($data['avg_order_value'] ?? 0), 2) }}</strong></div>
                        </td>
                    @endif
                    @if (isset($data['total_tax']))
                        <td>
                            <div class="kpi-label">{{ translate('total_tax') }}</div>
                            <div class="kpi-value">
                                <strong>{{ number_format((float) ($data['total_tax'] ?? 0), 2) }}</strong></div>
                        </td>
                    @endif
                    @if (isset($data['total_discount']))
                        <td>
                            <div class="kpi-label">{{ translate('total_discount') }}</div>
                            <div class="kpi-value">
                                <strong>{{ number_format((float) ($data['total_discount'] ?? 0), 2) }}</strong></div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    <!-- CHARTS ROW: Two columns side by side -->
    @if (!empty($data['trend_chart']) || !empty($data['stage_chart']))
        <div class="chart-row">
            @if (!empty($data['trend_chart']))
                <div class="chart-trend">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('order_statistics') }} ({{ $dateRange }})</div>
                        <img src="{{ $data['trend_chart'] }}" class="chart-image" alt="Order Statistics">
                    </div>
                </div>
            @endif

            @if (!empty($data['stage_chart']))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('payment_statistics') }} ({{ $dateRange }})</div>
                        <img src="{{ $data['stage_chart'] }}" class="chart-image" alt="Payment Statistics">
                    </div>
                </div>
            @endif
        </div>
    @endif
  
    <!-- Orders Details Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('order_details') }} {{ $dateRange }}</h3>
        </div>

        @if ($hasData)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('order_id') }}</th>
                        <th class="text-end">{{ translate('total_amount') }}</th>
                        <th class="text-end">{{ translate('product_discount') }}</th>
                        <th class="text-end">{{ translate('coupon_discount') }}</th>
                        <th class="text-end">{{ translate('shipping_charge') }}</th>
                        <th class="text-end">{{ translate('vat/tax') }}</th>
                        <th class="text-end">{{ translate('commission') }}</th>
                        <th class="text-end">{{ translate('deliveryman_incentive') }}</th>
                        <th>{{ translate('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['orders'] as $index => $order)
                        @php
                            $status = str_replace('_', '-', $order->order_status ?? '');
                            $statusClass = 'status-' . $status;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="value-ltr"><strong>#{{ $order->id ?? '' }}</strong></td>
                            <td class="text-end value-ltr">{{ number_format((float) ($order->order_amount ?? 0), 2) }}
                            </td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) ($order->details_sum_discount ?? 0), 2) }}</td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) ($order->discount_amount ?? 0), 2) }}</td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) (($order->shipping_cost ?? 0) - ($order->extra_discount_type == 'free_shipping_over_order_amount' ? $order->extra_discount ?? 0 : 0)), 2) }}
                            </td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) ($order->details_sum_tax ?? 0), 2) }}</td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) ($order->admin_commission ?? 0), 2) }}</td>
                            <td class="text-end value-ltr">
                                {{ number_format((float) ($order->deliveryman_charge ?? 0), 2) }}</td>
                            <td><span
                                    class="status-badge {{ $statusClass }}">{{ translate($order->order_status ?? '') }}</span>
                            </td>
                        </tr>
                    @endforeach
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
