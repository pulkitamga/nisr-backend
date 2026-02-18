<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('inhouse_product_sale_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};
        }
        h2, h3 { margin: 0 0 8px; }
        .meta { margin-bottom: 10px; }
        .summary { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: center; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
<h2>{{ translate('inhouse_product_sale_report') }}</h2>
<div class="meta">
    <div>{{ translate('from') }}: {{ $filters['from'] }} | {{ translate('to') }}: {{ $filters['to'] }}</div>
    <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
</div>

<div class="summary">
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
            <td>{{ translate('POS') }}</td>
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
</div>

<h3>{{ translate('POS') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('qty') }}</th>
        <th>{{ translate('orders') }}</th>
        <th>{{ translate('sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($posRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="6">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>

<h3>{{ translate('online') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('qty') }}</th>
        <th>{{ translate('orders') }}</th>
        <th>{{ translate('sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($onlineRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="6">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>

<h3>{{ translate('wholesale') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('qty') }}</th>
        <th>{{ translate('orders') }}</th>
        <th>{{ translate('sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($wholesaleRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="6">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
