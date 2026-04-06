@php
    $isRtl = $isRtl ?? app()->getLocale() === 'ar' || session('direction') === 'rtl';
    $dateRange = $fromDate->translatedFormat('M d, Y') . ' - ' . $toDate->translatedFormat('M d, Y');
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
            text-align: center;
        }

        .kpi-value.percentage {
            color: #0f766e;
        }

        .kpi-value.small {
            font-size: 14px;
        }

        .chart-row {
            width: 100%;
            margin-bottom: 20px;
            display: block;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .chart-col {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            background: white;
            height: 200px;
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
            max-height: 160px;
            object-fit: contain;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            margin: 20px 0;
        }

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

        thead th {
            background: #0f766e;
            color: #ffffff;
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

        @if ($isRtl)
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }
        @endif
    </style>
</head>

<body>
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('sla_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $fromDate->translatedFormat('M d, Y') }} -
                {{ $toDate->translatedFormat('M d, Y') }}</p>
        </div>
        <div class="logo-container">
            @php
                $logoSrc = '';
                $logoArray = $companyLogo ?? [];
                if (is_array($logoArray) && isset($logoArray['key'])) {
                    $logoPath = storage_path('app/public/company/' . $logoArray['key']);
                } else {
                    $logoPath = storage_path('app/public/company/' . $logoArray);
                }

                if (isset($logoPath) && file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                    $logoSrc = 'data:image/' . $mime . ';base64,' . $logoData;
                } else {
                    $logoPathPublic = public_path(
                        'storage/company/' . (is_array($logoArray) ? $logoArray['key'] ?? '' : $logoArray),
                    );
                    if (file_exists($logoPathPublic)) {
                        $logoData = base64_encode(file_get_contents($logoPathPublic));
                        $extension = pathinfo($logoPathPublic, PATHINFO_EXTENSION);
                        $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                        $logoSrc = 'data:image/' . $mime . ';base64,' . $logoData;
                    }
                }
            @endphp
            @if ($logoSrc)
                <img src="{{ $logoSrc }}" style="max-width:100px; max-height:50px;">
            @endif
        </div>
    </div>

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
                        <strong>{{ number_format((float) ($kpi['compliance'] ?? 0), 1) }}%</strong>
                    </div>
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
                    <div class="kpi-value small">
                        <strong>
                            {{ ($kpi['avg_breach_hours'] ?? null) !== null ? number_format((float) $kpi['avg_breach_hours'], 1) . 'h' : translate('na') }}
                        </strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table width="100%" style="margin-bottom:20px;">
        <tr>
            <td width="33%" style="padding-right:10px;">
                <div class="chart-col">
                    <div class="chart-title">{{ translate('sla_compliance_breakdown') }} ({{ $dateRange }})</div>
                    @if (!empty($complianceChartImage))
                        <img src="{{ $complianceChartImage }}" class="chart-image">
                    @else
                        <div class="no-data">{{ translate('no_data_available') }}</div>
                    @endif
                </div>
            </td>
            <td width="33%" style="padding-right:10px; padding-left:10px;">
                <div class="chart-col">
                    <div class="chart-title">{{ translate('sla_by_type') }} ({{ $dateRange }})</div>
                    @if (!empty($typeChartImage))
                        <img src="{{ $typeChartImage }}" class="chart-image">
                    @else
                        <div class="no-data">{{ translate('no_data_available') }}</div>
                    @endif
                </div>
            </td>
            <td width="34%" style="padding-left:10px;">
                <div class="chart-col" style="text-align:center;">
                    <div class="chart-title">{{ translate('breached_claims') }}</div>
                    <div style="margin-top:20px;">
                        <h1 style="font-size:40px; color:#dc2626;">{{ number_format((int) ($kpi['breached'] ?? 0)) }}
                        </h1>
                        <p style="font-size:11px; color:#6b7280;">
                            {{ translate('deadline_breaches_in_selected_period') }}</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if (!empty($trendChartImage))
        <div style="margin-top:20px; page-break-inside:avoid;">
            <div class="chart-title">{{ translate('sla_trend') }} ({{ $dateRange }})</div>
            <img src="{{ $trendChartImage }}"
                style="width:100%; max-height:200px; object-fit:contain; border:1px solid #e5e7eb; border-radius:6px; padding:8px;">
        </div>
    @else
        <div class="no-data" style="margin-top:20px;">{{ translate('no_trend_data_available') }}</div>
    @endif

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
                            <td class="value-ltr">
                                {{ \Carbon\Carbon::parse($row->due_date)->translatedFormat('Y-m-d H:i') }}</td>
                            <td class="value-ltr">
                                {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->translatedFormat('Y-m-d H:i') : '-' }}
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
            <div class="no-data">{{ translate('no_data_found_for_selected_period') }}</div>
        @endif
    </div>
</body>

</html>
