<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_sales_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            text-align: {{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'right' : 'left' }};
        }
        h2 {
            margin: 0 0 8px;
        }
        .meta {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
        }
        th {
            background: #f3f4f6;
        }
        .left {
            text-align: {{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'right' : 'left' }};
        }
    </style>
</head>
<body>
<h2>{{ translate('crm_sales_report') }}</h2>

<div class="meta">
    <div>{{ translate('from') }}: {{ $filters['from'] ?? '-' }} | {{ translate('to') }}: {{ $filters['to'] ?? '-' }}</div>
    <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
</div>

<table>
    <thead>
    <tr>
        <th>{{ translate('metric') }}</th>
        <th>{{ translate('value') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr><td class="left">{{ translate('total_sales') }}</td><td>{{ number_format((float)($statistics['total_sales'] ?? 0), 2) }}</td></tr>
    <tr><td class="left">{{ translate('retail_sales') }}</td><td>{{ number_format((float)($statistics['retail_sales'] ?? 0), 2) }}</td></tr>
    <tr><td class="left">{{ translate('wholesale_sales') }}</td><td>{{ number_format((float)($statistics['wholesale_sales'] ?? 0), 2) }}</td></tr>
    <tr><td class="left">{{ translate('total_orders') }}</td><td>{{ (int)($statistics['total_orders'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('total_quantity') }}</td><td>{{ (int)($statistics['total_quantity'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('top_agent') }}</td><td>{{ $statistics['top_agent'] ?? '-' }}</td></tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <th>{{ translate('period') }}</th>
        <th>{{ translate('retail_sales') }}</th>
        <th>{{ translate('wholesale_sales') }}</th>
        <th>{{ translate('total_sales') }}</th>
        <th>{{ translate('retail_orders') }}</th>
        <th>{{ translate('wholesale_orders') }}</th>
        <th>{{ translate('total_orders') }}</th>
        <th>{{ translate('total_quantity') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($pivotData as $row)
        <tr>
            <td class="left">{{ data_get($row, 'period', '-') }}</td>
            <td>{{ number_format((float)data_get($row, 'totals.retail_sales', 0), 2) }}</td>
            <td>{{ number_format((float)data_get($row, 'totals.wholesale_sales', 0), 2) }}</td>
            <td>{{ number_format((float)data_get($row, 'totals.total_sales', 0), 2) }}</td>
            <td>{{ (int)data_get($row, 'totals.retail_orders', 0) }}</td>
            <td>{{ (int)data_get($row, 'totals.wholesale_orders', 0) }}</td>
            <td>{{ (int)data_get($row, 'totals.total_orders', 0) }}</td>
            <td>{{ (int)data_get($row, 'totals.total_quantity', 0) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="8">{{ translate('no_data_found') }}</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>

