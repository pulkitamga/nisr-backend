<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('product_stock_analytics_report') }}</title>
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
<h2>{{ translate('product_stock_analytics_report') }}</h2>

<div class="meta">
    <div>{{ translate('from') }}: {{ $filters['from'] }} | {{ translate('to') }}: {{ $filters['to'] }}</div>
    <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
</div>

<div class="summary">
    <table>
        <thead>
        <tr>
            <th>{{ translate('metric') }}</th>
            <th>{{ translate('value') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr><td>{{ translate('total_current_stock') }}</td><td>{{ number_format((int)$summary['total_current_stock']) }}</td></tr>
        <tr><td>{{ translate('total_stock_in') }}</td><td>{{ number_format((int)$summary['total_stock_in']) }}</td></tr>
        <tr><td>{{ translate('total_stock_out') }}</td><td>{{ number_format((int)$summary['total_stock_out']) }}</td></tr>
        <tr><td>{{ translate('net_stock_movement') }}</td><td>{{ number_format((int)$summary['net_stock_movement']) }}</td></tr>
        <tr><td>{{ translate('products_count') }}</td><td>{{ number_format((int)$summary['products_count']) }}</td></tr>
        <tr><td>{{ translate('branches_count') }}</td><td>{{ number_format((int)$summary['branches_count']) }}</td></tr>
        </tbody>
    </table>
</div>

<h3>{{ translate('stock_by_product') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('current_stock') }}</th>
        <th>{{ translate('stock_in') }}</th>
        <th>{{ translate('stock_out') }}</th>
        <th>{{ translate('net_stock_movement') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($stockByProductRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ number_format((int)$row->current_stock) }}</td>
            <td>{{ number_format((int)$row->stock_in) }}</td>
            <td>{{ number_format((int)$row->stock_out) }}</td>
            <td>{{ number_format((int)$row->net_movement) }}</td>
        </tr>
    @empty
        <tr><td colspan="6">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>

<h3>{{ translate('stock_by_branch') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('current_stock') }}</th>
        <th>{{ translate('products_count') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($stockByBranchRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ number_format((int)$row->current_stock) }}</td>
            <td>{{ number_format((int)$row->products_count) }}</td>
        </tr>
    @empty
        <tr><td colspan="4">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>

<h3>{{ translate('stock_by_branch_and_product') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('current_stock') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($stockByBranchProductRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ number_format((int)$row->current_stock) }}</td>
        </tr>
    @empty
        <tr><td colspan="4">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>

<h3>{{ translate('stock_movement_history') }}</h3>
<table>
    <thead>
    <tr>
        <th>{{ translate('SL') }}</th>
        <th>{{ translate('date') }}</th>
        <th>{{ translate('product') }}</th>
        <th>{{ translate('branch') }}</th>
        <th>{{ translate('type') }}</th>
        <th>{{ translate('quantity') }}</th>
        <th>{{ translate('category') }}</th>
        <th>{{ translate('reference') }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($movementRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i') }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->type === 'IN' ? translate('stock_in') : translate('stock_out') }}</td>
            <td>{{ number_format((int)$row->quantity) }}</td>
            <td>{{ $row->category }}</td>
            <td>{{ $row->reference }}</td>
        </tr>
    @empty
        <tr><td colspan="8">{{ translate('no_data_found') }}</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
