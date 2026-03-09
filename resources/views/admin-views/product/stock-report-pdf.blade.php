<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('stock_report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h2 { margin: 0 0 10px 0; }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 4px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary-grid th, .summary-grid td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th, .history-table td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        .history-table th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>{{ translate('stock_report') }}</h2>

    <div class="meta">
        <div><strong>{{ translate('product') }}:</strong> {{ $product->name }}</div>
        <div><strong>{{ translate('variation') }}:</strong> {{ $variation ?? 'Default' }}</div>
        <div><strong>{{ translate('current_stock') }}:</strong> {{ $currentStock }}</div>
    </div>

    <table class="summary-grid">
        <thead>
            <tr>
                <th>{{ translate('stock_in') }}</th>
                <th>{{ translate('stock_out') }}</th>
                @if($includeInternalTransfer)
                    <th>{{ translate('internal_branch_transfer') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ translate('initial_stock') }}: +{{ $summary['stock_in']['initial_stock'] }}<br>
                    {{ translate('manual_adjust_add') }}: +{{ $summary['stock_in']['manual_adjust_add'] }}<br>
                    {{ translate('returns') }}: +{{ $summary['stock_in']['returns'] }}
                </td>
                <td>
                    {{ translate('sales_pos') }}: -{{ $summary['stock_out']['sales_pos'] }}<br>
                    {{ translate('sales_online') }}: -{{ $summary['stock_out']['sales_online'] }}<br>
                    {{ translate('sales_wholesale_transfer') }}: -{{ $summary['stock_out']['sales_wholesale_transfer'] }}<br>
                    {{ translate('manual_adjust_negative') }}: -{{ $summary['stock_out']['manual_adjust_negative'] }}
                </td>
                @if($includeInternalTransfer)
                    <td>
                        {{ translate('stock_in') }}: +{{ $summary['internal_transfer']['in'] }}<br>
                        {{ translate('stock_out') }}: -{{ $summary['internal_transfer']['out'] }}
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    <table class="history-table">
        <thead>
            <tr>
                <th>{{ translate('date') }}</th>
                <th>{{ translate('type') }}</th>
                <th>{{ translate('quantity') }}</th>
                <th>{{ translate('category') }}</th>
                <th>{{ translate('reference') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historyRows as $history)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($history['date'])->format('Y-m-d H:i') }}</td>
                    <td>{{ strtoupper((string)$history['type']) === 'IN' ? 'Stock In' : 'Stock Out' }}</td>
                    <td>{{ strtoupper((string)$history['type']) === 'IN' ? '+' : '-' }}{{ (int)$history['quantity'] }}</td>
                    <td>{{ $history['category'] }}</td>
                    <td>
                        {{ str_replace('_', ' ', (string)$history['reason']) }}<br>
                        {{ $history['remarks'] ?? '' }}
                        @if ($history['from_branch'] || $history['to_branch'])
                            <br>{{ $history['from_branch'] ? ('From: ' . $history['from_branch']) : '' }}{{ $history['to_branch'] ? (' | To: ' . $history['to_branch']) : '' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">{{ translate('no_stock_history_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
