<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('branch_stock_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }};
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

        .logo-container .fallback-logo {
            color: white;
            font-weight: bold;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
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

        /* Filter Summary */
        .filter-summary {
            background: #f8fafc;
            border-left: 4px solid #0f766e;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #1e293b;
        }

        .filter-summary strong {
            color: #0f766e;
        }

        /* KPI Container */
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
            height: 70px;
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
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            text-align: center;
        }

        .kpi-value.total-stock {
            color: #3498db;
        }

        .kpi-value.total-in {
            color: #2ecc71;
        }

        .kpi-value.total-out {
            color: #e74c3c;
        }

        /* Chart Container - FIXED */
        .chart-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: white;
            margin-bottom: 20px;
            page-break-inside: avoid;
            width: 100%;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f766e;
        }

        .chart-header h4 {
            margin: 0;
            font-size: 14px;
            color: #0f766e;
            font-weight: 600;
        }

        .badge-soft {
            background: rgba(15, 118, 110, 0.1);
            color: #0f766e;
            border-radius: 999px;
            font-size: 10px;
            padding: 4px 10px;
            font-weight: 600;
        }

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .no-chart {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 8px;
            color: #64748b;
            font-size: 12px;
            border: 1px dashed #cbd5e1;
        }

        .no-chart p {
            margin: 10px 0 0;
        }

        /* Table Container - FIXED to appear right after chart */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            clear: both;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
        }

        .table-header h5 {
            margin: 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .badge-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 999px;
            font-size: 11px;
            padding: 3px 10px;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        th {
            background: #f1f5f9;
            font-weight: 600;
            padding: 10px 6px;
            text-align: center;
            border: 1px solid #cbd5e1;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
        }

        td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .total-row {
            background: #f8fafc !important;
            font-weight: 700;
        }

        .total-row td {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            border-top: 2px solid #0f766e;
            border-bottom: 2px solid #0f766e;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* Stock badges */
        .stock-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }

        .stock-in {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-out {
            background: #fee2e2;
            color: #991b1b;
        }

        .stock-inout-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
        }

        .stock-inout-item {
            display: flex;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }

        /* No Data */
        .no-data-message {
            text-align: center;
            padding: 50px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13px;
            margin: 20px 0;
        }

        .no-data-message h3 {
            color: #374151;
            margin: 0 0 10px 0;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
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
            background: transparent;
        }

        .text-left {
            text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        .text-right {
            text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'left' : 'right' }};
        }

        /* RTL Support */
        @if (session('direction') === 'rtl' || app()->getLocale() === 'ar')
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }

            .filter-summary {
                border-left: none;
                border-right: 4px solid #0f766e;
            }

            .badge-count {
                margin-left: 0;
                margin-right: 10px;
            }

            .stock-inout-item {
                flex-direction: row-reverse;
            }
        @endif
    </style>
</head>

<body>

    <!-- Modern Header with Logo -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('branch_stock_report') }}</h2>
            <p>
                <span dir="ltr">{{ $startDateFormatted ?? '' }} - {{ $endDateFormatted ?? '' }}</span>
            </p>
        </div>
        <div class="logo-container">
            @php
                $logoUrl = null;
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
                if (!$logoUrl) {
                    $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                    if (file_exists($defaultLogoPath)) {
                        $logoUrl = 'data:image/webp;base64,' . base64_encode(file_get_contents($defaultLogoPath));
                    }
                }
            @endphp
            @if (!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px;">
            @else
                <span class="fallback-logo">{{ config('app.name') }}</span>
            @endif
        </div>
    </div>

    <!-- Filter Summary -->
    <div class="filter-summary">
        <strong>{{ translate('filters_applied') }}:</strong>
        <span dir="ltr">
            @if (!empty($startDateFormatted) && !empty($endDateFormatted))
                {{ $startDateFormatted }} - {{ $endDateFormatted }}
            @else
                {{ $dateRange ?? 'All Time' }}
            @endif
        </span>
        @if (isset($product))
            | {{ translate('product') }}: <strong>{{ $product->name }}</strong>
        @endif
        @if (!empty($filters['variation_type']))
            | {{ translate('variation') }}: <strong>{{ $filters['variation_type'] }}</strong>
        @endif
        @if (!empty($filters['branch_id']))
            @php
                $branch = \App\Models\Branch::find($filters['branch_id']);
            @endphp
            | {{ translate('branch') }}:
            <strong>{{ $branch ? $branch->branch_name : translate('selected_branch') }}</strong>
        @endif
    </div>

    <!-- KPI Cards -->
    @if (!empty($branches) && count($branches) > 0)
        @php
            $totalStock = $totalStats['current_stock'] ?? collect($branches)->sum('current_stock');
            $totalIn = $totalStats['total_in'] ?? collect($branches)->sum('total_in');
            $totalOut = $totalStats['total_out'] ?? collect($branches)->sum('total_out');
        @endphp

        <div class="kpi-wrapper">
            <table class="kpi-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="kpi-label">{{ translate('total_stock_quantity') }}</div>
                        <div class="kpi-value total-stock">
                            <strong>{{ number_format($totalStock) }}</strong>
                        </div>
                    </td>
                    <td>
                        <div class="kpi-label">{{ translate('total_in') }}</div>
                        <div class="kpi-value total-in">
                            <strong>{{ number_format($totalIn) }}</strong>
                        </div>
                    </td>
                    <td>
                        <div class="kpi-label">{{ translate('total_out') }}</div>
                        <div class="kpi-value total-out">
                            <strong>{{ number_format($totalOut) }}</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Chart Section - FIXED: Better handling of chart image -->
        @if ($hasChart && !empty($chartImage))
            <div class="chart-container">
                <div class="chart-header">
                    <h4>{{ translate('branch_stock_chart') }}({{ $startDateFormatted ?? '' }} -
                        {{ $endDateFormatted ?? '' }})</h4>

                </div>
                <img src="{{ $chartImage }}" class="chart-image" alt="{{ translate('branch_stock_chart') }}" />
            </div>
        @else
            <div class="chart-container">
                <div class="chart-header">
                    <h4>{{ translate('branch_stock_chart') }}</h4>
                    <span class="badge-soft">{{ $startDateFormatted ?? '' }} - {{ $endDateFormatted ?? '' }}</span>
                </div>
                <div class="no-chart">
                    <strong>{{ translate('chart_data_not_available') }}</strong>
                    <p>{{ translate('no_stock_data_for_selected_filters') }}</p>
                </div>
            </div>
        @endif

        <!-- Stock Details Table - Now appears immediately after chart -->
        <div class="table-container">
            <div class="table-header">
                <h5>
                    {{ translate('branch_stock_details') }} ({{ $startDateFormatted ?? '' }} -
                    {{ $endDateFormatted ?? '' }})
                </h5>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('branch_name') }}</th>
                        <th>{{ translate('product_name') }}</th>
                        @if (!empty($filters['variation_type']))
                            <th>{{ translate('variation') }}</th>
                        @endif
                        <th>{{ translate('current_stock') }}</th>
                        <th>{{ translate('stock_in_out') }}</th>
                        <th>{{ translate('last_updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalStockCalc = 0; @endphp
                    @foreach ($branches as $index => $branch)
                        @php
                            $totalStockCalc += $branch['current_stock'] ?? 0;
                            $lastUpdated = isset($branch['last_updated'])
                                ? \Carbon\Carbon::parse($branch['last_updated'])->format('M d, Y H:i')
                                : translate('na_symbol');
                            $stockIn = $branch['total_in'] ?? 0;
                            $stockOut = $branch['total_out'] ?? 0;

                            // Determine product name to display
                            $productName = translate('all_products');
                            if (isset($product) && $product) {
                                $productName = $product->name;
                            } elseif (isset($branch['product_name']) && $branch['product_name']) {
                                $productName = $branch['product_name'];
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $branch['branch_name'] ?? translate('na_symbol') }}</strong></td>
                            <td>{{ $productName }}</td>
                            @if (!empty($filters['variation_type']))
                                <td>{{ $filters['variation_type'] }}</td>
                            @endif
                            <td class="value-ltr"><strong>{{ number_format($branch['current_stock'] ?? 0) }}</strong>
                            </td>
                            <td>
                                <div class="stock-inout-container">
                                    <div class="stock-inout-item">
                                        <span class="stock-badge stock-in">⬆️ IN</span>
                                        <span class="value-ltr">{{ number_format($stockIn) }}</span>
                                    </div>
                                    <div class="stock-inout-item">
                                        <span class="stock-badge stock-out">⬇️ OUT</span>
                                        <span class="value-ltr">{{ number_format($stockOut) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="value-ltr">{{ $lastUpdated }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="{{ !empty($filters['variation_type']) ? 4 : 3 }}" class="text-right">
                            <strong>{{ translate('total_stock') }}:</strong>
                        </td>
                        <td><strong>{{ number_format($totalStockCalc) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="no-data-message">
            <h3>{{ translate('no_stock_data_found') }}</h3>
            <p>{{ translate('no_stock_records_match_the_selected_filters') }}</p>
        </div>
    @endif

</body>

</html>
