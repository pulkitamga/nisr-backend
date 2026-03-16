<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_insights_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        /* Header Styles with Logo */
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
        .kpi-wrapper {
            background: #f3f6fb;
            padding: 7px;
            border-radius: 10px;
            margin-bottom: 20px;
            width: 100%;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px !important;
            padding: 5px 5px;
            vertical-align: middle;
        }

        .kpi-label {
            color: #5f6672;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 0 0 6px 0;
            text-align: center;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-align: left;
        }

        /* .kpi-value.percentage {
            color: #0f766e;
        } */

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

        /* Insights Section */
        .insights-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            background: #f9fafb;
        }

        .insights-box ol {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }

        .insights-box li {
            margin-bottom: 5px;
            color: #1f2937;
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
            .insights-box ol {
                padding-right: 20px;
                padding-left: 0;
            }

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

        .row {
            width: 100%;
            margin-bottom: 20px;
            display: block;
            overflow: hidden;
        }

        .col {
            width: 16.2%;
            float: left;
            margin-right: 0.5%;
        }

        .col:last-child {
            margin-right: 0;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0;
            height: auto;
        }

        .card-body {
            padding: 12px 15px;
            text-align: left;
        }

        .kpi-container {
            background-color: #f3f6fb;
            padding: 10px 5px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            /* Required for radius */
            border-spacing: 10px 0;
            /* Space between cards */
            table-layout: fixed;
        }

        .kpi-table td {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            /* mPDF needs this specific property and value to render radius on cells */
            border-radius: 12px !important;
            padding: 12px 10px;
            vertical-align: top;
            height: 55px;
            text-align: left;
        }

        /* .kpi-label {
            color: #6b7280;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
            display: block;
        } */

        /* .kpi-value {
           
            font-size: 16px;
            font-weight: 900;
            color: #000000;
            display: block;
        } */

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

        /* Clear fix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>

    <!-- Modern Header with Logo - Fixed for mPDF -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('crm_insights_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $snapshotFrom->translatedFormat('M d, Y') }} - {{ $snapshotTo->translatedFormat('M d, Y') }}</p>
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

    <!-- KPI Metrics Cards - Fixed with proper number formatting and black values -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('messages') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['message_count'] ?? 0)) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('leads') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['lead_count'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('deals') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['deal_count'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('pipeline_value') }}</div>
                    <div class="kpi-value" style="font-size: 13px;">
                        <strong>{{ number_format((float) ($kpi['total_deal_value'] ?? 0), 2) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('lead_to_deal') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((float) ($kpi['lead_to_deal_rate'] ?? 0), 1) }}%</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('win_rate') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format((float) ($kpi['deal_win_rate'] ?? 0), 1) }}%</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- First Row: Trend + Stage Charts side by side -->
    @if (!empty($trendChart) || !empty($stageChart))
        <div class="chart-row">
            @if (!empty($trendChart))
                <div class="chart-trend">
                    @php
                        $trendTitle = match ($filters['date_type'] ?? 'this_year') {
                            'today' => translate('crm_trend') . ' (' . $snapshotFrom->translatedFormat('j F Y') . ')',
                            'this_week' => translate('crm_trend') .
                                ' (' .
                                $snapshotFrom->translatedFormat('M d') .
                                ' - ' .
                                $snapshotTo->translatedFormat('M d, Y') .
                                ')',
                            'this_month' => translate('crm_trend') . ' (' . $snapshotFrom->translatedFormat('F Y') . ')',
                            'this_year' => translate('crm_trend') . ' (' . $snapshotFrom->translatedFormat('Y') . ')',
                            'custom_date' => translate('crm_trend') .
                                ': ' .
                                $snapshotFrom->translatedFormat('j F Y') .
                                ' - ' .
                                $snapshotTo->translatedFormat('j F Y'),
                            default => translate('crm_trend') . ' (' . translate('last_12_months') . ')',
                        };
                    @endphp
                    <div class="chart-title">{{ $trendTitle }}</div>
                    <img src="{{ $trendChart }}" class="chart-image" alt="CRM Trend" />
                </div>
            @endif

            @if (!empty($stageChart))
                <div class="chart-stage">
                    @php
                        $stageTitle = match ($filters['date_type'] ?? 'this_year') {
                            'today' => translate('deal_stage_mix') . ' (' . $snapshotFrom->translatedFormat('j F Y') . ')',
                            'this_week' => translate('deal_stage_mix') .
                                ' (' .
                                $snapshotFrom->translatedFormat('M d') .
                                ' - ' .
                                $snapshotTo->translatedFormat('M d, Y') .
                                ')',
                            'this_month' => translate('deal_stage_mix') . ' (' . $snapshotFrom->translatedFormat('F Y') . ')',
                            'this_year' => translate('deal_stage_mix') . ' (' . $snapshotFrom->translatedFormat('Y') . ')',
                            'custom_date' => translate('deal_stage_mix') .
                                ': ' .
                                $snapshotFrom->translatedFormat('j F Y') .
                                ' - ' .
                                $snapshotTo->translatedFormat('j F Y'),
                            default => translate('deal_stage_mix') . ' (' . translate('last_12_months') . ')',
                        };
                    @endphp
                    <div class="chart-title">{{ $stageTitle }}</div>
                    <img src="{{ $stageChart }}" class="chart-image" alt="Deal Stage Mix" />
                </div>
            @endif
        </div>
    @endif

    <!-- Message Status Chart (if available) -->
    @if (!empty($statusChart))
        <div class="full-chart">
            @php
                $statusTitle = match ($filters['date_type'] ?? 'this_year') {
                    'today' => translate('message_status_distribution') . ' (' . $snapshotFrom->translatedFormat('j F Y') . ')',
                    'this_week' => translate('message_status_distribution') .
                        ' (' .
                        $snapshotFrom->translatedFormat('M d') .
                        ' - ' .
                        $snapshotTo->translatedFormat('M d, Y') .
                        ')',
                    'this_month' => translate('message_status_distribution') .
                        ' (' .
                        $snapshotFrom->translatedFormat('F Y') .
                        ')',
                    'this_year' => translate('message_status_distribution') . ' (' . $snapshotFrom->translatedFormat('Y') . ')',
                    'custom_date' => translate('message_status_distribution') .
                        ': ' .
                        $snapshotFrom->translatedFormat('j F Y') .
                        ' - ' .
                        $snapshotTo->translatedFormat('j F Y'),
                    default => translate('message_status_distribution') . ' (' . translate('last_12_months') . ')',
                };
            @endphp
            <div class="chart-title">{{ $statusTitle }}</div>
            <img src="{{ $statusChart }}" style="width:100%; max-height:200px; object-fit:contain;"
                alt="Message Status" />
        </div>
    @endif

    <!-- Top Owners Table -->
    <div class="table-container">
        <div class="table-header">
            @php
                $tableDatePart = match ($filters['date_type'] ?? 'this_year') {
                    'today' => '(' . $snapshotFrom->translatedFormat('j F Y') . ')',
                    'this_week' => '(' . $snapshotFrom->translatedFormat('M d') . ' - ' . $snapshotTo->translatedFormat('M d, Y') . ')',
                    'this_month' => '(' . $snapshotFrom->translatedFormat('F Y') . ')',
                    'this_year' => '(' . $snapshotFrom->translatedFormat('Y') . ')',
                    'custom_date' => '(' . $snapshotFrom->translatedFormat('j F Y') . ' - ' . $snapshotTo->translatedFormat('j F Y') . ')',
                    default => '(' . translate('last_12_months') . ')',
                };
            @endphp
            <h3>{{ translate('top_deal_owners_by_value') }} {{ $tableDatePart }}</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('owner') }}</th>
                    <th>{{ translate('deals') }}</th>
                    <th>{{ translate('total_value') }}</th>
                    <th>{{ translate('avg_value') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topOwners ?? [] as $owner)
                    @php
                        $avg =
                            (int) ($owner->deals_count ?? 0) > 0
                                ? ($owner->total_value ?? 0) / (int) ($owner->deals_count ?? 1)
                                : 0;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $owner->owner_name ?? translate('unknown') }}</strong></td>
                        <td class="value-ltr">{{ number_format((int) ($owner->deals_count ?? 0)) }}</td>
                        <td class="value-ltr">{{ number_format((float) ($owner->total_value ?? 0), 2) }}</td>
                        <td class="value-ltr">{{ number_format($avg, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 15px;">
                            {{ translate('no_owner_activity_in_this_period') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} | {{ translate('crm_insights_report') }}
    </div>

</body>

</html>