<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('product_stock_analytics_report') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
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
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI */
        .kpi-container {
            background-color: #f3f6fb;
            padding: 10px 5px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-spacing: 10px 0;
        }

        .kpi-table td {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        .kpi-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* TABLE */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 15px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #e5e7eb;
            padding: 6px;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
            font-size: 9px;
            color: #6b7280;
        }

    </style>
</head>

<body>

<!-- HEADER -->
<div class="report-header clearfix">
    <div class="header-content">
        <h2>{{ translate('product_stock_analytics_report') }}</h2>
        <p>
            {{ translate('from') }}: {{ $filters['from'] }} -
            {{ translate('to') }}: {{ $filters['to'] }}
        </p>
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

<!-- KPI -->
<div class="kpi-container">
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_current_stock') }}</div>
                <div class="kpi-value">{{ number_format((int)$summary['total_current_stock']) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('stock_in') }}</div>
                <div class="kpi-value">{{ number_format((int)$summary['total_stock_in']) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('stock_out') }}</div>
                <div class="kpi-value">{{ number_format((int)$summary['total_stock_out']) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('net_movement') }}</div>
                <div class="kpi-value">{{ number_format((int)$summary['net_stock_movement']) }}</div>
            </td>
        </tr>
    </table>
</div>
@if (!empty($chartImages))
    <div style="margin-top:20px;">

        <!-- ROW 1 -->
        <table width="100%" style="margin-bottom:20px;">
            <tr>
                @if (!empty($chartImages['movement']))
                    <td style="width:50%; text-align:center;">
                        <h4>{{ translate('stock_movement_trend') }}</h4>
                        <img src="{{ $chartImages['movement'] }}" style="width:100%; max-height:250px;">
                    </td>
                @endif

                @if (!empty($chartImages['branch']))
                    <td style="width:50%; text-align:center;">
                        <h4>{{ translate('stock_by_branch_chart') }}</h4>
                        <img src="{{ $chartImages['branch'] }}" style="width:100%; max-height:250px;">
                    </td>
                @endif
            </tr>
        </table>

        <!-- ROW 2 -->
        <table width="100%" style="margin-bottom:20px;">
            <tr>
                @if (!empty($chartImages['product']))
                    <td style="width:50%; text-align:center;">
                        <h4>{{ translate('top_products_chart') }}</h4>
                        <img src="{{ $chartImages['product'] }}" style="width:100%; max-height:250px;">
                    </td>
                @endif

                @if (!empty($chartImages['branchProduct']))
                    <td style="width:50%; text-align:center;">
                        <h4>{{ translate('branch_product_chart') }}</h4>
                        <img src="{{ $chartImages['branchProduct'] }}" style="width:100%; max-height:250px;">
                    </td>
                @endif
            </tr>
        </table>

    </div>
@endif
<!-- PRODUCT TABLE -->
<div class="table-container">
    <div class="table-header">
        <h3>{{ translate('stock_by_product') }}</h3>
    </div>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>{{ translate('product') }}</th>
            <th>{{ translate('current_stock') }}</th>
            <th>{{ translate('stock_in') }}</th>
            <th>{{ translate('stock_out') }}</th>
            <th>{{ translate('net') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($stockByProductRows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->product_name }}</td>
                <td>{{ $row->current_stock }}</td>
                <td>{{ $row->stock_in }}</td>
                <td>{{ $row->stock_out }}</td>
                <td>{{ $row->net_movement }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- BRANCH TABLE -->
<div class="table-container">
    <div class="table-header">
        <h3>{{ translate('stock_by_branch') }}</h3>
    </div>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>{{ translate('branch') }}</th>
            <th>{{ translate('current_stock') }}</th>
            <th>{{ translate('products') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($stockByBranchRows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->branch_name }}</td>
                <td>{{ $row->current_stock }}</td>
                <td>{{ $row->products_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- MOVEMENT TABLE -->
<div class="table-container">
    <div class="table-header">
        <h3>{{ translate('stock_movement_history') }}</h3>
    </div>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>{{ translate('date') }}</th>
            <th>{{ translate('product') }}</th>
            <th>{{ translate('branch') }}</th>
            <th>{{ translate('type') }}</th>
            <th>{{ translate('qty') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($movementRows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                <td>{{ $row->product_name }}</td>
                <td>{{ $row->branch_name }}</td>
                <td>{{ $row->type }}</td>
                <td>{{ $row->quantity }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- FOOTER -->
<div class="footer">
    <table width="100%">
        <tr>
            <td width="20%">Page {PAGENO}</td>
            <td width="60%" style="text-align:center;">
                Generated on: {{ now()->format('Y-m-d H:i') }} <br>
                {{ config('app.name') }}
            </td>
            <td width="20%"></td>
        </tr>
    </table>
</div>

</body>
</html>