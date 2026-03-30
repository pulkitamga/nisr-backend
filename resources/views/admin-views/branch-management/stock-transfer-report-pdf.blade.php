<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('stock_transfer_report') }}</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
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

        .header-content h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .header-content p {
            margin: 0;
            font-size: 11px;
            opacity: .9;
        }

        .logo-container {
            float: right;
            width: 25%;
            text-align: right;
        }

        .logo-container img {
            width: 90px;
            height: 40px;
        }

        .fallback-logo {
            color: white;
            font-weight: bold;
            font-size: 14px;
            background: rgba(255, 255, 255, .2);
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI */

        .kpi-container {
            background: #f3f6fb;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .kpi-table td {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 10px;
        }

        .kpi-label {
            color: #5f6672;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
            text-align: center;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }

        /* CHART */
        .chart-header {
            page-break-after: avoid;
        }

        .chart-container {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            background: white;
            margin-bottom: 24px;
            text-align: center;
        }

        .chart-header h4 {
            margin: 0 0 15px;
            font-size: 16px;
        }

        .chart-image {
            width: 100%;
            max-height: 270px;
        }

        /* TABLE */

        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h5 {
            margin: 0;
            font-size: 15px;
        }

        .badge-soft-dark {
            background: #e9ecef;
            border-radius: 999px;
            font-size: 11px;
            padding: 4px 8px;
            margin-left: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #f1f5f9;
            font-weight: 600;
            padding: 10px 6px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }

        td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .left {
            text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        /* FOOTER */

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        .footer table {
            width: 100%;
        }

        .footer td {
            border: none;
            background: none;
            padding: 5px;
        }

        .page-info {
            text-align: left;
            color: red;
        }

        .center-info {
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- HEADER -->

    <div class="report-header clearfix">

        <div class="header-content">
            <h2>{{ translate('stock_transfer_report') }}</h2>
            <p>{{ translate('report_period') }}:
                <span dir="{{ get_direction() }}">
                    {{ $filters['from'] ?? '-' }} - {{ $filters['to'] ?? '-' }}
                </span>
            </p>
        </div>

        <div class="logo-container">

            @php
                // Try to get logo from business settings first
                $logoUrl = null;

                // Check if business_settings table exists and has logo
                if (\Illuminate\Support\Facades\Schema::hasTable('business_settings')) {
                    $businessLogo = \Illuminate\Support\Facades\DB::table('business_settings')
                        ->where('type', 'company_logo')
                        ->first();

                    if ($businessLogo && !empty($businessLogo->value)) {
                        $logoPath = public_path('storage/' . $businessLogo->value);
                        if (file_exists($logoPath)) {
                            $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $logoUrl =
                                'data:image/' . $extension . ';base64,' . base64_encode(file_get_contents($logoPath));
                        }
                    }
                }

                // If no logo found, try default path
                if (!$logoUrl) {
                    $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                    if (file_exists($defaultLogoPath)) {
                        $logoUrl = 'data:image/webp;base64,' . base64_encode(file_get_contents($defaultLogoPath));
                    }
                }
            @endphp

            @if ($logoUrl)
                <img src="{{ $logoUrl }}" width="100" height="45">
            @else
                <span class="fallback-logo">{{ config('app.name') }}</span>
            @endif

        </div>

    </div>

    <!-- KPI -->

    <div class="kpi-container">
        <table class="kpi-table">

            <tr>

                <td>
                    <div class="kpi-label">{{ translate('total_transfers') }}</div>
                    <div class="kpi-value">
                        {{ (int) ($statistics['total_transfers'] ?? 0) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('pending') }}</div>
                    <div class="kpi-value">
                        {{ (int) ($statistics['pending_transfers'] ?? 0) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('approved') }}</div>
                    <div class="kpi-value">
                        {{ (int) ($statistics['approved_transfers'] ?? 0) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('rejected') }}</div>
                    <div class="kpi-value">
                        {{ (int) ($statistics['rejected_transfers'] ?? 0) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('total_quantity') }}</div>
                    <div class="kpi-value">
                        {{ (int) ($statistics['total_quantity'] ?? 0) }}
                    </div>
                </td>

            </tr>

        </table>
    </div>

    <!-- CHART -->

    <div class="chart-container">

        <div class="chart-header">
            <h4 style="color:#0f766e; border-bottom:2px solid #0f766e; padding-bottom:6px;">
                {{ translate('stock_transfer_chart') }}
                <span style="font-size:10px; color:#6b7280;">
                    ({{ $filters['from'] ?? '-' }} - {{ $filters['to'] ?? '-' }})
                </span>
            </h4>
        </div>

        @if (!empty($chartData['labels']) && !empty($chartData['datasets']))
            @php
                $chartConfig = [
                    'type' => 'line',
                    'data' => $chartData,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                                'min' => 0,
                            ],
                        ],
                    ],
                ];

                $encodedConfig = urlencode(json_encode($chartConfig));
                $chartUrl = 'https://quickchart.io/chart?c=' . $encodedConfig . '&width=800&height=270';
            @endphp

            <img src="{{ $chartUrl }}" class="chart-image">
        @else
            <div style="padding:40px;background:#f8f9fa;border-radius:8px">
                {{ translate('chart_data_not_available') }}
            </div>
        @endif

    </div>
    <!-- TABLE -->

    <div class="table-container">

        <div class="table-header">

            <h5 style="display:flex; justify-content:space-between; align-items:center;">

                <span>
                    {{ translate('stock_transfer_details') }}
                    <small style="font-weight:normal; color:#ffffff; margin-left:10px;">
                        {{ $filters['from'] ?? '-' }} - {{ $filters['to'] ?? '-' }})
                    </small>
                </span>

                <span>
                    {{ count($transfers) }}
                </span>

            </h5>

        </div>

        <table>

            <thead>
                <tr>
                    <th>{{ translate('SL') }}</th>
                    <th>{{ translate('date') }}</th>
                    <th>{{ translate('from_branch') }}</th>
                    <th>{{ translate('to_branch') }}</th>
                    <th>{{ translate('items') }}</th>
                    <th>{{ translate('status') }}</th>
                </tr>
            </thead>

            <tbody>

                @forelse($transfers as $index=>$transfer)
                    @php
                        $quantity = collect($transfer->products ?? [])->sum('quantity');

                        $status = collect($transfer->products ?? [])
                            ->pluck('status')
                            ->filter()
                            ->map(fn($v) => translate(strtolower((string) $v)))
                            ->unique()
                            ->implode(', ');
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            {{ $transfer->transfer_date ? \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') : '-' }}
                        </td>

                        <td class="left">
                            {{ data_get($transfer, 'fromBranch.branch_name', data_get($transfer, 'from_branch.branch_name', '-')) }}
                        </td>

                        <td class="left">
                            {{ data_get($transfer, 'toBranch.branch_name', data_get($transfer, 'to_branch.branch_name', '-')) }}
                        </td>

                        <td>{{ (int) $quantity }}</td>

                        <td>
                            @if ($status)
                                <span
                                    style="
            background:#e0f2fe;
            color:#0369a1;
            padding:3px 8px;
            border-radius:10px;
            font-size:9px;
        ">
                                    {{ $status }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">{{ translate('no_data_found') }}</td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>
</body>

</html>
