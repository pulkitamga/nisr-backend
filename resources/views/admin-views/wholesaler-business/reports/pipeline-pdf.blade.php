<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('wholesale_pipeline_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        /* Header Styles with Logo - Teal like CRM */
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

        /* KPI Container - Exactly like CRM */
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

        .kpi-value.small {
            font-size: 14px;
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

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 170px;
        }

        /* Full Width Chart */
        .full-chart {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            background: white;
        }

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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
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

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
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
        @endif
    </style>
</head>

<body>

    <!-- Modern Header with Logo - Teal like CRM -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('wholesale_pipeline_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $snapshotFrom->format('M d, Y') }} -
                {{ $snapshotTo->format('M d, Y') }}</p>
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

    @php
        $dateRange = $snapshotFrom->format('M d, Y') . ' - ' . $snapshotTo->format('M d, Y');
    @endphp

    <!-- KPI Metrics Cards - ALL IN ONE SINGLE ROW with 7 cards -->
    @if (isset($kpi) && !empty($kpi))
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('purchase_orders') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['purchase_count'] ?? 0)) }}</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('quotations') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['quotation_count'] ?? 0)) }}</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('confirmed_orders') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['confirmed_count'] ?? 0)) }}</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('end_to_end') }}</div>
                    <div class="kpi-value percentage"><strong>{{ number_format((float) ($kpi['end_to_end_rate'] ?? 0), 1) }}%</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('po_to_quote') }}</div>
                    <div class="kpi-value percentage"><strong>{{ number_format((float) ($kpi['purchase_to_quotation_rate'] ?? 0), 1) }}%</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('quote_to_confirmed') }}</div>
                    <div class="kpi-value percentage"><strong>{{ number_format((float) ($kpi['quotation_to_confirmed_rate'] ?? 0), 1) }}%</strong></div>
                </td>
                <td style="width: 14.28%;">
                    <div class="kpi-label">{{ translate('cycle_time') }}</div>
                    <div class="kpi-value small">
                        <strong>
                            @php
                                $poToQuote = $kpi['avg_po_to_quote_hours'] !== null ? number_format((float) $kpi['avg_po_to_quote_hours'], 1) . 'h' : 'N/A';
                                $quoteToConfirm = $kpi['avg_quote_to_confirm_hours'] !== null ? number_format((float) $kpi['avg_quote_to_confirm_hours'], 1) . 'h' : 'N/A';
                            @endphp
                            {{ $poToQuote }} / {{ $quoteToConfirm }}
                        </strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Charts Row: Stage Trend + Stage Snapshot -->
    @if (!empty($pipelineTrendChartImage) || !empty($stageSnapshotChartImage))
        <div class="chart-row">
            @if (!empty($pipelineTrendChartImage))
                <div class="chart-trend">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('stage_trend') }} ({{ $dateRange }})</div>
                        <img src="{{ $pipelineTrendChartImage }}" class="chart-image" alt="Pipeline Trend" />
                    </div>
                </div>
            @endif

            @if (!empty($stageSnapshotChartImage))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('stage_snapshot') }} ({{ $dateRange }})</div>
                        <img src="{{ $stageSnapshotChartImage }}" class="chart-image" alt="Stage Snapshot" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Charts Row: Top Products + Tier Mix -->
    @if (!empty($topProductsChartImage) || !empty($tierMixChartImage))
        <div class="chart-row">
            @if (!empty($topProductsChartImage))
                <div class="chart-trend">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('top_product_volume') }} ({{ $dateRange }})</div>
                        <img src="{{ $topProductsChartImage }}" class="chart-image" alt="Top Products" />
                    </div>
                </div>
            @endif

            @if (!empty($tierMixChartImage))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('wholesaler_tier_mix') }} ({{ $dateRange }})</div>
                        <img src="{{ $tierMixChartImage }}" class="chart-image" alt="Tier Mix" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Tier Revenue Breakdown Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('tier_revenue_breakdown') }} ({{ $dateRange }})</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('tier') }}</th>
                    <th>{{ translate('orders') }}</th>
                    <th>{{ translate('revenue') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tierRevenue ?? [] as $row)
                    <tr>
                        <td><strong>{{ $row->tier_name }}</strong></td>
                        <td class="value-ltr">{{ number_format((int) ($row->orders_count ?? 0)) }}</td>
                        <td class="value-ltr">{{ number_format((float) ($row->total_revenue ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 15px;">
                            {{ translate('no_tier_revenue_data_in_this_period') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>