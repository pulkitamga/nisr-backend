@php
    $isRtl = $isRtl ?? app()->getLocale() === 'ar' || session('direction') === 'rtl';
    $dateRange = $fromDate->format('M d, Y') . ' - ' . $toDate->format('M d, Y');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('claims_report') }}</title>
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

        .kpi-value.percentage {
            color: #0f766e;
        }

        /* CHART ROW - EXACT same as working pipeline report */
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
            height: 300px !important;
            object-fit: contain;
        }

        /* TABLE */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
            page-break-inside: avoid !important;
            break-inside: avoid !important
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


        .chart-container {
            margin: 20px 0;
        }

        .chart-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            height: 150px;
            gap: 8px;
            margin-top: 10px;
        }

        .bar-wrapper {
            flex: 1;
            text-align: center;
        }

        .bar {
            background-color: #0177CD;
            border-radius: 4px 4px 0 0;
            width: 100%;
            min-height: 2px;
        }

        .bar-label {
            margin-top: 5px;
            font-size: 9px;
        }

        .bar-value {
            font-size: 8px;
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

        /* FOOTER - EXACT same as working pipeline report */
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
            <h2>{{ translate('claims_report') }}</h2>
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

    <!-- KPI CARDS - 4 in one row -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_claims') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($kpi['total_claims'] ?? 0) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('claim_rate') }}</div>
                    <div class="kpi-value percentage"><strong>{{ number_format($kpi['claim_rate'] ?? 0, 1) }}%</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('open_claims') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($kpi['open_claims'] ?? 0) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('resolved_claims') }}</div>
                    <div class="kpi-value"><strong>{{ number_format($kpi['resolved_claims'] ?? 0) }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- CHARTS ROW - Using proper chart-row structure -->
    @if (!empty($trendChartImage) || !empty($statusChartImage))
        <div class="chart-row">
            @if (!empty($trendChartImage))
                <div class="chart-trend">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('claims_volume_trend') }} ({{ $dateRange }})</div>
                        <img src="{{ $trendChartImage }}" class="chart-image" alt="Claims Trend" />
                    </div>
                </div>
            @endif

            @if (!empty($statusChartImage))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('claim_status_mix') }} ({{ $dateRange }})</div>
                        <img src="{{ $statusChartImage }}" class="chart-image" alt="Claim Status" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- CLAIMS TABLE -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('claims_list') }} ({{ $dateRange }})</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('claim_number') }}</th>
                    <th>{{ translate('serial') }}</th>
                    <th>{{ translate('product') }}</th>
                    <th>{{ translate('customer') }}</th>
                    <th>{{ translate('status') }}</th>
                    <th>{{ translate('submitted_at') }}</th>
                    <th>{{ translate('sla_due') }}</th>
                    <th>{{ translate('branch') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($claimsForPdf as $claim)
                    @php
                        $customerName = trim(
                            ($claim->warranty?->user?->f_name ?? '') . ' ' . ($claim->warranty?->user?->l_name ?? ''),
                        );
                        if ($customerName === '') {
                            $customerName = $claim->warranty?->activated_by_name ?? '-';
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $claim->claim_number }}</strong></td>
                        <td class="value-ltr">{{ $claim->serial_number }}</td>
                        <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                        <td>{{ $customerName }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $claim->status)) }}</td>
                        <td class="value-ltr">
                            {{ optional($claim->submitted_at ?? $claim->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="value-ltr">{{ optional($claim->resolution_due)->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>{{ $claim->branch?->branch_name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 15px;">
                            {{ translate('no_data_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
