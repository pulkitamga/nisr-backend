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

        /* Header Styles */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .report-header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .report-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 11px;
        }

        /* KPI Metrics */
        .kpi-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .metric-box {
            flex: 1 1 calc(16.666% - 8px);
            background: #f3f4f6;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .metric-box h4 {
            margin: 0 0 5px 0;
            font-size: 10px;
            color: #4b5563;
            text-transform: uppercase;
        }

        .metric-box span {
            font-weight: bold;
            font-size: 16px;
            color: #0f766e;
        }

        /* Chart Row - 2 columns */
        .chart-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        .chart-trend {
            display: table-cell;
            width: 70%;
            padding-right: 10px;
        }

        .chart-stage {
            display: table-cell;
            width: 30%;
        }

        .chart-col {
            /* flex: 1; */
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
            object-fit: contain;
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

            .metric-box {
                text-align: center;
            }
        @endif
    </style>
</head>

<body>

    <!-- Modern Header -->
    <div class="report-header">
        <h2>{{ translate('crm_insights_report') }}</h2>
        <p>{{ translate('report_period') }}: {{ $snapshotFrom->format('M d, Y') }} - {{ $snapshotTo->format('M d, Y') }}
        </p>
    </div>

    <!-- First Row: Trend + Stage Charts side by side with dynamic titles -->
    @if (!empty($trendChart) || !empty($stageChart))
        <div class="chart-row">
            @if (!empty($trendChart))
                <div class="chart-trend">
                    @php
                        $trendTitle = match ($filters['date_type'] ?? 'this_year') {
                            'today' => translate('crm_trend') . ' (' . $snapshotFrom->format('j F Y') . ')',
                            'this_week' => translate('crm_trend') .
                                ' (' .
                                $snapshotFrom->format('M d') .
                                ' - ' .
                                $snapshotTo->format('M d, Y') .
                                ')',
                            'this_month' => translate('crm_trend') . ' (' . $snapshotFrom->format('F Y') . ')',
                            'this_year' => translate('crm_trend') . ' (' . $snapshotFrom->format('Y') . ')',
                            'custom_date' => translate('crm_trend') .
                                ': ' .
                                $snapshotFrom->format('j F Y') .
                                ' - ' .
                                $snapshotTo->format('j F Y'),
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
                            'today' => translate('deal_stage_mix') . ' (' . $snapshotFrom->format('j F Y') . ')',
                            'this_week' => translate('deal_stage_mix') .
                                ' (' .
                                $snapshotFrom->format('M d') .
                                ' - ' .
                                $snapshotTo->format('M d, Y') .
                                ')',
                            'this_month' => translate('deal_stage_mix') . ' (' . $snapshotFrom->format('F Y') . ')',
                            'this_year' => translate('deal_stage_mix') . ' (' . $snapshotFrom->format('Y') . ')',
                            'custom_date' => translate('deal_stage_mix') .
                                ': ' .
                                $snapshotFrom->format('j F Y') .
                                ' - ' .
                                $snapshotTo->format('j F Y'),
                            default => translate('deal_stage_mix') . ' (' . translate('last_12_months') . ')',
                        };
                    @endphp
                    <div class="chart-title">{{ $stageTitle }}</div>
                    <img src="{{ $stageChart }}" class="chart-image" alt="Deal Stage Mix" />
                </div>
            @endif
        </div>
    @endif

    <!-- Message Status Chart with dynamic title -->
    @if (!empty($statusChart))
        <div class="full-chart">
            @php
                $statusTitle = match ($filters['date_type'] ?? 'this_year') {
                    'today' => translate('message_status_distribution') . ' (' . $snapshotFrom->format('j F Y') . ')',
                    'this_week' => translate('message_status_distribution') .
                        ' (' .
                        $snapshotFrom->format('M d') .
                        ' - ' .
                        $snapshotTo->format('M d, Y') .
                        ')',
                    'this_month' => translate('message_status_distribution') .
                        ' (' .
                        $snapshotFrom->format('F Y') .
                        ')',
                    'this_year' => translate('message_status_distribution') . ' (' . $snapshotFrom->format('Y') . ')',
                    'custom_date' => translate('message_status_distribution') .
                        ': ' .
                        $snapshotFrom->format('j F Y') .
                        ' - ' .
                        $snapshotTo->format('j F Y'),
                    default => translate('message_status_distribution') . ' (' . translate('last_12_months') . ')',
                };
            @endphp
            <div class="chart-title">{{ $statusTitle }}</div>
            <img src="{{ $statusChart }}" style="width:100%; max-height:200px; object-fit:contain;"
                alt="Message Status" />
        </div>
    @endif

    <!-- Top Owners Table with dynamic date -->
    <div class="table-container">
        <div class="table-header">
            @php
                $tableDatePart = match ($filters['date_type'] ?? 'this_year') {
                    'today' => '(' . $snapshotFrom->format('j F Y') . ')',
                    'this_week' => '(' . $snapshotFrom->format('M d') . ' - ' . $snapshotTo->format('M d, Y') . ')',
                    'this_month' => '(' . $snapshotFrom->format('F Y') . ')',
                    'this_year' => '(' . $snapshotFrom->format('Y') . ')',
                    'custom_date' => '(' . $snapshotFrom->format('j F Y') . ' - ' . $snapshotTo->format('j F Y') . ')',
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
                @forelse ($topOwners as $owner)
                    @php
                        $avg = (int) $owner->deals_count > 0 ? $owner->total_value / (int) $owner->deals_count : 0;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $owner->owner_name }}</strong></td>
                        <td class="value-ltr">{{ number_format((int) $owner->deals_count) }}</td>
                        <td class="value-ltr">{{ number_format((float) $owner->total_value, 2) }}</td>
                        <td class="value-ltr">{{ number_format($avg, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 15px;">
                            {{ translate('no_owner_activity_in_this_period') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ translate('generated_on') }}: {{ now()->format('j F Y, h:i A') }} | {{ translate('crm_insights_report') }}
    </div>

</body>

</html>
