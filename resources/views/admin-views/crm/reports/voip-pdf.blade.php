<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('voip_insights_report') }}</title>
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
            border-spacing: 8px 0;
            table-layout: fixed;
        }

        .kpi-table td {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px !important;
            padding: 12px 8px;
            vertical-align: top;
            height: 60px;
        }

        .kpi-label {
            color: #5f6672;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 0 0 6px 0;
            text-align: center;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-align: center;
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

        .chart-status {
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
            border: 1px solid #cbd5e1;
        }

        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
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
        }

        .footer-table td {
            border: none;
            padding: 2px;
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

            .chart-status {
                float: right;
            }
        @endif

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .badge-success {
            background: #2ecc71;
        }

        .badge-warning {
            background: #f39c12;
        }

        .badge-info {
            background: #3498db;
        }

        .text-success {
            color: #27ae60;
        }

        .text-warning {
            color: #f39c12;
        }

        .text-info {
            color: #3498db;
        }
    </style>
</head>

<body>

    <!-- Modern Header with Logo -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('voip_insights_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $snapshotFrom->translatedFormat('M d, Y') }} -
                {{ $snapshotTo->translatedFormat('M d, Y') }}</p>
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
    <div class="kpi-wrapper">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                @php
                    $cardStyles = [
                        'total_calls' => [
                            'label' => translate('total_calls'),
                            'value' => number_format((int) ($kpi['total_calls'] ?? 0)),
                            'color' => '#2563eb',
                        ],
                        'completed_calls' => [
                            'label' => translate('completed'),
                            'value' => number_format((int) ($kpi['completed_calls'] ?? 0)),
                            'color' => '#14b8a6',
                        ],
                        'answer_rate' => [
                            'label' => translate('answer_rate'),
                            'value' => number_format((float) ($kpi['answer_rate'] ?? 0), 1) . '%',
                            'color' => '#f59e0b',
                        ],
                        'avg_duration' => [
                            'label' => translate('avg_duration'),
                            'value' => number_format(((float) ($kpi['avg_duration_seconds'] ?? 0)) / 60, 1) . 'm',
                            'color' => '#0f172a',
                        ],
                        'unique_contacts' => [
                            'label' => translate('unique_contacts'),
                            'value' => number_format((int) ($kpi['unique_contacts'] ?? 0)),
                            'color' => '#14b8a6',
                        ],
                        'active_agents' => [
                            'label' => translate('active_agents'),
                            'value' => number_format((int) ($kpi['active_agents'] ?? 0)),
                            'color' => '#2563eb',
                        ],
                    ];
                @endphp

                @foreach ($cardStyles as $key => $card)
                    <td>
                        <div class="kpi-label">{{ $card['label'] }}</div>
                        <div class="kpi-value" style="color: {{ $card['color'] }};">
                            <strong>{{ $card['value'] }}</strong>
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Charts Section -->
    @if (!empty($trendChart) || !empty($statusChart))
        <div class="chart-row">
            @if (!empty($trendChart))
                <div class="chart-trend">
                    <div class="chart-col">
                        @php
                            $trendTitle = match ($filters['date_type'] ?? 'this_year') {
                                'today' => translate('call_trend') . ' (' . $snapshotFrom->translatedFormat('j F Y') . ')',
                                'this_week' => translate('call_trend') .
                                    ' (' .
                                    $snapshotFrom->translatedFormat('M d') .
                                    ' - ' .
                                    $snapshotTo->translatedFormat('M d, Y') .
                                    ')',
                                'this_month' => translate('call_trend') . ' (' . $snapshotFrom->translatedFormat('F Y') . ')',
                                'this_year' => translate('call_trend') . ' (' . $snapshotFrom->translatedFormat('Y') . ')',
                                'custom_date' => translate('call_trend') .
                                    ': ' .
                                    $snapshotFrom->translatedFormat('j F Y') .
                                    ' - ' .
                                    $snapshotTo->translatedFormat('j F Y'),
                                default => translate('call_trend') . ' (' . translate('last_12_months') . ')',
                            };
                        @endphp
                        <div class="chart-title">{{ $trendTitle }}</div>
                        <img src="{{ $trendChart }}" class="chart-image" alt="Call Trend" />
                    </div>
                </div>
            @endif

            @if (!empty($statusChart))
                <div class="chart-status">
                    <div class="chart-col">
                        @php
                            $statusTitle = match ($filters['date_type'] ?? 'this_year') {
                                'today' => translate('call_status_mix') . ' (' . $snapshotFrom->translatedFormat('j F Y') . ')',
                                'this_week' => translate('call_status_mix') .
                                    ' (' .
                                    $snapshotFrom->translatedFormat('M d') .
                                    ' - ' .
                                    $snapshotTo->translatedFormat('M d, Y') .
                                    ')',
                                'this_month' => translate('call_status_mix') . ' (' . $snapshotFrom->translatedFormat('F Y') . ')',
                                'this_year' => translate('call_status_mix') . ' (' . $snapshotFrom->translatedFormat('Y') . ')',
                                'custom_date' => translate('call_status_mix') .
                                    ': ' .
                                    $snapshotFrom->translatedFormat('j F Y') .
                                    ' - ' .
                                    $snapshotTo->translatedFormat('j F Y'),
                                default => translate('call_status_mix') . ' (' . translate('last_12_months') . ')',
                            };
                        @endphp
                        <div class="chart-title">{{ $statusTitle }}</div>
                        <img src="{{ $statusChart }}" class="chart-image" alt="Call Status Mix" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Direction Chart (if available) -->
    @if (!empty($directionChart))
        <div class="full-chart">
            @php
                $directionTitle = translate('inbound_vs_outbound') . ' - ' . $snapshotFrom->translatedFormat('M Y');
            @endphp
            <div class="chart-title">{{ $directionTitle }}</div>
            <img src="{{ $directionChart }}" style="width:100%; max-height:200px; object-fit:contain;"
                alt="Direction Split" />
        </div>
    @endif

    <!-- Top Agents Table -->
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
            <h3>{{ translate('top_agents_by_call_volume') }} {{ $tableDatePart }}</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('agent') }}</th>
                    <th>{{ translate('calls') }}</th>
                    <th>{{ translate('total_duration') }} (min)</th>
                    <th>{{ translate('avg_duration') }} (min)</th>
                    <th>{{ translate('inbound') }}</th>
                    <th>{{ translate('outbound') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topAgents ?? [] as $agent)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $agent->agent_name }}</strong></td>
                        <td class="value-ltr"><span class="badge badge-info">{{ number_format((int) ($agent->calls_count ?? 0)) }}</span></td>
                        <td class="value-ltr">{{ number_format(((float) ($agent->total_duration ?? 0)) / 60, 1) }}</td>
                        <td class="value-ltr">{{ number_format(((float) ($agent->avg_duration ?? 0)) / 60, 1) }}</td>
                        <td class="value-ltr text-success">{{ number_format((int) ($agent->inbound_calls ?? 0)) }}</td>
                        <td class="value-ltr text-warning">{{ number_format((int) ($agent->outbound_calls ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 15px;">
                            {{ translate('no_agent_call_data_in_this_period') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (!empty($topAgents) && count($topAgents) > 0)
                @php
                    $totalCalls = collect($topAgents)->sum('calls_count');
                    $totalDuration = collect($topAgents)->sum('total_duration');
                    $totalInbound = collect($topAgents)->sum('inbound_calls');
                    $totalOutbound = collect($topAgents)->sum('outbound_calls');
                @endphp
                <tfoot>
                    <tr style="background: #f1c40f; font-weight: bold;">
                        <td colspan="2">{{ translate('totals') }}</td>
                        <td>{{ number_format((int) $totalCalls) }}</td>
                        <td>{{ number_format(((float) $totalDuration) / 60, 1) }}</td>
                        <td>-</td>
                        <td>{{ number_format((int) $totalInbound) }}</td>
                        <td>{{ number_format((int) $totalOutbound) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Insights Section -->
    @if (!empty($insights) && count($insights) > 0)
        <div class="insights-box">
            <strong style="font-size: 12px;">{{ translate('key_insights') }}</strong>
            <ol>
                @foreach ($insights as $insight)
                    <li>{{ $insight }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="20%" style="text-align: {{ $isRtl ?? false ? 'right' : 'left' }}; color:#6b7280;">
                    {{ translate('page') }} {PAGENO}
                </td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('voip_insights_report') }}<br>
                    {{ translate('generated_by') }}: <span
                        style="color:red;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color:red;">{{ config('app.name') }}</span>
                </td>
                <td width="20%"></td>
            </tr>
        </table>
    </div>

</body>

</html>