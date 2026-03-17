<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('CRM Analytics Report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 15px;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
        }

        .report-header {
            background: #0f766e;
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
            font-size: 11px;
            opacity: .9;
        }

        .filter-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .filter-item {
            display: flex;
            padding: 5px 0;
        }

        .filter-label {
            font-weight: bold;
            width: 110px;
            color: #495057;
        }

        .filter-value {
            color: #2C3E50;
            font-weight: 600;
        }

        .kpi-container {
            background: #f3f6fb;
            padding: 10px 6px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            table-layout: fixed;
        }

        .kpi-table td {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            height: 55px;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th {
            background: #e5e7eb;
            padding: 8px 4px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #cbd5e1;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

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

        .badge-danger {
            background: #e74c3c;
        }

        .badge-info {
            background: #3498db;
        }

        .badge-primary {
            background: #3498db;
        }

        .text-bold {
            font-weight: 700;
        }

        .text-success {
            color: #27ae60;
        }

        .text-warning {
            color: #f39c12;
        }

        .text-danger {
            color: #e74c3c;
        }

        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
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
        @if (app()->getLocale() == 'ar')
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

    <!-- HEADER -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('CRM Analytics Report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $filters['from'] ?? '-' }} - {{ $filters['to'] ?? '-' }}</p>
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            @endphp
            @if (file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    style="max-width:100px;max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI CARDS - Showing Total Messages and Assigned prominently -->
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_messages') }}</div>
                    <div class="kpi-value text-warning">{{ number_format($summary['total'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('assigned') }}</div>
                    <div class="kpi-value text-info">{{ number_format($summary['assigned'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('pending') }}</div>
                    <div class="kpi-value text-warning">{{ number_format($summary['pending'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('converted') }}</div>
                    <div class="kpi-value text-info">{{ number_format($summary['converted'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('ignored') }}</div>
                    <div class="kpi-value text-danger">{{ number_format($summary['ignored'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('spam') }}</div>
                    <div class="kpi-value">{{ number_format($summary['spam'] ?? 0) }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="full-chart" style="margin-bottom: 20px; page-break-inside: avoid;">
        <div style="margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">
                {{ translate('crm_analytics_overview') }}
            </h2>
        </div>

        @if (!empty($crmChart))
           <img src="{{ $crmChart }}" style="max-width:100%; max-height:300px;">
        @else
            <p style="text-align:center; color:#999;">
                {{ translate('no_chart_available') }}
            </p>
        @endif

    </div>

    <!-- DATA TABLE -->
    <div class="table-container">
        <div class="table-header">
            {{ translate('detailed_breakdown') }}
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('period') }}</th>
                    <th>{{ translate('total') }}</th>
                    <th>{{ translate('assigned') }}</th>
                    <th>{{ translate('pending') }}</th>
                    <th>{{ translate('converted') }}</th>
                    <th>{{ translate('ignored') }}</th>
                    <th>{{ translate('spam') }}</th>
                    <th>{{ translate('assigned_%') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daily_stats ?? [] as $stat)
                    @php
                        $assignedPct = $stat['total'] > 0 ? round(($stat['assigned'] / $stat['total']) * 100, 1) : 0;
                        $badgeClass =
                            $assignedPct >= 70
                                ? 'badge-success'
                                : ($assignedPct >= 40
                                    ? 'badge-warning'
                                    : 'badge-danger');
                    @endphp
                    <tr>
                        <td>{{ $stat['period'] }}</td>
                        <td class="text-bold">{{ number_format($stat['total']) }}</td>
                        <td class="text-success">{{ number_format($stat['assigned']) }}</td>
                        <td class="text-warning">{{ number_format($stat['pending']) }}</td>
                        <td class="text-info">{{ number_format($stat['converted']) }}</td>
                        <td class="text-danger">{{ number_format($stat['ignored']) }}</td>
                        <td>{{ number_format($stat['spam']) }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $assignedPct }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">
                            {{ translate('no_data_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (!empty($daily_stats) && count($daily_stats) > 0)
                @php
                    $totalSum = collect($daily_stats)->sum('total');
                    $assignedSum = collect($daily_stats)->sum('assigned');
                    $pendingSum = collect($daily_stats)->sum('pending');
                    $convertedSum = collect($daily_stats)->sum('converted');
                    $ignoredSum = collect($daily_stats)->sum('ignored');
                    $spamSum = collect($daily_stats)->sum('spam');
                    $totalAssignedPct = $totalSum > 0 ? round(($assignedSum / $totalSum) * 100, 1) : 0;
                @endphp
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td>{{ translate('grand_total') }}</td>
                        <td>{{ number_format($totalSum) }}</td>
                        <td>{{ number_format($assignedSum) }}</td>
                        <td>{{ number_format($pendingSum) }}</td>
                        <td>{{ number_format($convertedSum) }}</td>
                        <td>{{ number_format($ignoredSum) }}</td>
                        <td>{{ number_format($spamSum) }}</td>
                        <td>
                            <span
                                class="badge {{ $totalAssignedPct >= 70 ? 'badge-success' : ($totalAssignedPct >= 40 ? 'badge-warning' : 'badge-danger') }}">
                                {{ $totalAssignedPct }}%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="20%"
                    style="text-align:{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}; color:red;">
                    {{ translate('page') }} {PAGENO}
                </td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}:
                    {{ $exportedAt?->translatedFormat('j F Y, h:i A') ?? now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('crm_analytics_report') }}<br>
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
