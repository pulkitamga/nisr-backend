@php
    $isRtl = $isRtl ?? app()->getLocale() === 'ar' || session('direction') === 'rtl';
    $dateRange = $fromDate->format('M d, Y') . ' - ' . $toDate->format('M d, Y');
    $hasData = isset($slaRowsForPdf) && count($slaRowsForPdf) > 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('sla_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
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

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI CONTAINER */
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

<<<<<<< ahmed5
=======
        .kpi-value.percentage {
            color: #0f766e;
        }

        .kpi-value.small {
            font-size: 14px;
        }

        /* CHART ROW */
        .chart-row {
            width: 100%;
            margin-bottom: 20px;
            display: block;
            overflow: hidden;
            page-break-inside: avoid;
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

        /* TABLE */
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

>>>>>>> local
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

        /* NO DATA MESSAGE */
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

        /* FOOTER */
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

    <!-- HEADER -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('sla_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $fromDate->format('M d, Y') }} - {{ $toDate->format('M d, Y') }}</p>
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

<<<<<<< ahmed5
    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_deadlines') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['total_deadlines'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('sla_compliance') }}</div>
                <div class="kpi-value">{{ number_format((float)($kpi['compliance'] ?? 0), 1) }}%</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('on_time') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['on_time'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('breached') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['breached'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('avg_breach_hours') }}</div>
                <div class="kpi-value">
                    {{ ($kpi['avg_breach_hours'] ?? null) !== null ? number_format((float)$kpi['avg_breach_hours'], 1) . 'h' : translate('na') }}
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
=======
    <!-- KPI CARDS - 5 in one row -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 20%;">
                    <div class="kpi-label">{{ translate('total_deadlines') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['total_deadlines'] ?? 0)) }}</strong>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-label">{{ translate('sla_compliance') }}</div>
                    <div class="kpi-value percentage">
                        <strong>{{ number_format((float) ($kpi['compliance'] ?? 0), 1) }}%</strong></div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-label">{{ translate('on_time') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['on_time'] ?? 0)) }}</strong></div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-label">{{ translate('breached') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int) ($kpi['breached'] ?? 0)) }}</strong></div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-label">{{ translate('avg_breach_hours') }}</div>
                    <div class="kpi-value small"><strong>
                            {{ ($kpi['avg_breach_hours'] ?? null) !== null ? number_format((float) $kpi['avg_breach_hours'], 1) . 'h' : translate('na') }}
                        </strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- CHARTS ROW - Compliance and Type side by side -->
    @if (!empty($complianceChartImage) || !empty($typeChartImage))
       <div class="chart-row">

    <div class="chart-trend">
        <div class="chart-col">
            <div class="chart-title">{{ translate('sla_compliance_breakdown') }} ({{ $dateRange }})</div>

            @if(!empty($complianceChartImage))
                <img src="{{ $complianceChartImage }}" class="chart-image">
            @else
                <div style="height:160px;text-align:center;padding-top:60px;color:#9ca3af;">
                    {{ translate('no_data_available') }}
                </div>
            @endif

        </div>
    </div>

    <div class="chart-stage">
        <div class="chart-col">
            <div class="chart-title">{{ translate('sla_by_type') }} ({{ $dateRange }})</div>

            @if(!empty($typeChartImage))
                <img src="{{ $typeChartImage }}" class="chart-image">
            @else
                <div style="height:160px;text-align:center;padding-top:60px;color:#9ca3af;">
                    {{ translate('no_data_available') }}
                </div>
            @endif

        </div>
    </div>

</div>
    @endif

    <!-- TREND CHART - Full width -->
    @if (!empty($trendChartImage))
        <div class="full-chart"
            style="border:1px solid #e5e7eb; border-radius:6px; padding:12px; margin-bottom:20px; background:white; page-break-inside:avoid;">
            <div class="chart-title">{{ translate('sla_trend') }} ({{ $dateRange }})</div>
            <img src="{{ $trendChartImage }}" style="width:100%; max-height:200px; object-fit:contain;"
                alt="SLA Trend" />
        </div>
    @endif

    <!-- SLA DETAILS TABLE -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('sla_details') }} ({{ $dateRange }})</h3>
        </div>

        @if ($hasData)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('sla_type') }}</th>
                        <th>{{ translate('claim_number') }}</th>
                        <th>{{ translate('serial_no') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('due_date') }}</th>
                        <th>{{ translate('completed_at') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('claim_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slaRowsForPdf as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->sla_type_key === 'response' ? translate('first_response_sla') : translate('resolution_sla') }}
                            </td>
                            <td class="value-ltr"><strong>{{ $row->claim_number }}</strong></td>
                            <td class="value-ltr">{{ $row->serial_number }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td class="value-ltr">{{ \Carbon\Carbon::parse($row->due_date)->format('Y-m-d H:i') }}</td>
                            <td class="value-ltr">
                                {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td>
                                <span style="color:{{ (int) $row->is_within_sla === 1 ? '#16a34a' : '#dc2626' }};">
                                    <strong>{{ (int) $row->is_within_sla === 1 ? translate('on_time') : translate('breached') }}</strong>
                                </span>
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', $row->status)) }}</td>
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

    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-table">
>>>>>>> local
            <tr>
                <td width="20%" style="text-align:{{ $isRtl ? 'right' : 'left' }};">
                    Page {PAGENO}
                </td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('sla_report') }}<br>
                    {{ translate('generated_by') }}: <span
                        style="color:#0f766e;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color:#0f766e;">{{ config('app.name') }}</span>
                </td>
                <td width="20%"></td>
            </tr>
<<<<<<< ahmed5
        </thead>
        <tbody>
            @forelse($slaRowsForPdf as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->sla_type_key === 'response' ? translate('first_response_sla') : translate('resolution_sla') }}</td>
                    <td>{{ $row->claim_number }}</td>
                    <td>{{ $row->serial_number }}</td>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->due_date)->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td>{{ (int)$row->is_within_sla === 1 ? translate('on_time') : translate('breached') }}</td>
                    <td>{{ translate($row->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
=======
        </table>
    </div>

</body>

>>>>>>> local
</html>
