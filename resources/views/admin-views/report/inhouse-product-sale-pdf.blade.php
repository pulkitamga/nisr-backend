<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<title>{{ translate('inhouse_product_sale_report') }}</title>

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #000;
    }

    h2 { margin-bottom: 10px; }
    h3 { margin: 15px 0 8px; }

    .meta {
        margin-bottom: 10px;
        font-size: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 9px;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    th {
        background: #f2f2f2;
    }

    .charts-section {
        width: 100%;
        margin-bottom: 20px;
    }

    .chart-box {
        width: 48%;
        float: left;
        margin-bottom: 15px;
    }

    .chart-box-right {
        float: right;
    }

    .chart-card {
        border: 1px solid #ccc;
        padding: 8px;
    }

    .chart-title {
        text-align: center;
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .chart-image {
        width: 100%;
        height: 140px;
    }

    .clearfix {
        clear: both;
    }

    .footer {
        margin-top: 20px;
        font-size: 8px;
        text-align: center;
    }
</style>
</head>

<body>

<h2>{{ translate('inhouse_product_sale_report') }}</h2>

<div class="meta">
    <div><strong>{{ translate('from') }}:</strong> {{ $filters['from'] }}</div>
    <div><strong>{{ translate('to') }}:</strong> {{ $filters['to'] }}</div>
    <div><strong>{{ translate('exported_at') }}:</strong> {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
</div>

{{-- ================== CHARTS ================== --}}

<div class="charts-section">

    {{-- Row 1 --}}
    <div class="chart-box">
        <div class="chart-card">
            <div class="chart-title">{{ translate('sales_by_date') }}</div>
            <img class="chart-image" src="{{ $chartImages['trend'] }}">
        </div>
    </div>

    <div class="chart-box chart-box-right">
        <div class="chart-card">
            <div class="chart-title">{{ translate('channel_mix') }}</div>
            <img class="chart-image" src="{{ $chartImages['channel'] }}">
        </div>
    </div>

    <div class="clearfix"></div>

    {{-- Row 2 --}}
    <div class="chart-box">
        <div class="chart-card">
            <div class="chart-title">{{ translate('branch_and_sales_type') }}</div>
            <img class="chart-image" src="{{ $chartImages['branch_type'] }}">
        </div>
    </div>

    <div class="chart-box chart-box-right">
        <div class="chart-card">
            <div class="chart-title">{{ translate('sales_type_and_product') }}</div>
            <img class="chart-image" src="{{ $chartImages['product_type'] }}">
        </div>
    </div>

    <div class="clearfix"></div>

    {{-- Row 3 --}}
    <div style="width:100%; margin-top:10px;">
        <div class="chart-card">
            <div class="chart-title">{{ translate('branch_and_product') }}</div>
            <img class="chart-image" src="{{ $chartImages['branch_product'] }}" style="height:160px;">
        </div>
    </div>

</div>

{{-- ================== SUMMARY ================== --}}

<h3>{{ translate('sales_summary') }}</h3>

<table>
    <thead>
        <tr>
            <th>{{ translate('sales_type') }}</th>
            <th>{{ translate('total_qty') }}</th>
            <th>{{ translate('total_sales') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ translate('pos') }}</td>
            <td>{{ $summary['pos_qty'] }}</td>
            <td>{{ number_format($summary['pos_amount'], 2) }}</td>
        </tr>
        <tr>
            <td>{{ translate('online') }}</td>
            <td>{{ $summary['online_qty'] }}</td>
            <td>{{ number_format($summary['online_amount'], 2) }}</td>
        </tr>
        <tr>
            <td>{{ translate('wholesale') }}</td>
            <td>{{ $summary['wholesale_qty'] }}</td>
            <td>{{ number_format($summary['wholesale_amount'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>{{ translate('total') }}</strong></td>
            <td><strong>{{ $summary['total_qty'] }}</strong></td>
            <td><strong>{{ number_format($summary['total_amount'], 2) }}</strong></td>
        </tr>
    </tbody>
</table>

{{-- ================== DATA TABLES ================== --}}

@php
$sections = [
    'pos' => $posRows,
    'online' => $onlineRows,
    'wholesale' => $wholesaleRows,
];
@endphp

@foreach($sections as $type => $rows)

<h3>{{ translate($type) }}</h3>

<table>
<thead>
<tr>
<th>{{ translate('sl') }}</th>
<th>{{ translate('period') }}</th>
<th>{{ translate('product') }}</th>
<th>{{ translate('branch') }}</th>
<th>{{ translate('qty') }}</th>
<th>{{ translate('orders') }}</th>
<th>{{ translate('sales') }}</th>
</tr>
</thead>
<tbody>
@forelse($rows as $index => $row)
<tr>
<td>{{ $index + 1 }}</td>
<td>{{ $row->period_label ?? '' }}</td>
<td>{{ $row->product_name }}</td>
<td>{{ $row->branch_name }}</td>
<td>{{ $row->total_qty }}</td>
<td>{{ $row->total_orders }}</td>
<td>{{ number_format($row->total_amount, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="7">{{ translate('no_data_found') }}</td>
</tr>
@endforelse
</tbody>
</table>

@endforeach

<div class="footer">
    {{ translate('generated_by') }}: {{ config('app.name') }}
</div>

</body>
</html>