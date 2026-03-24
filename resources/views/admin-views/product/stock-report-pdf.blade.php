@php($isRtl = session('direction') === 'rtl' || app()->getLocale() === 'ar')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('stock_report') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        /* HEADER */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
        }

        .header-meta {
            font-size: 11px;
            margin-top: 5px;
        }

        /* KPI */
        .kpi-container {
            background: #f3f6fb;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-spacing: 10px 0;
        }

        .kpi-table td {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .kpi-label {
            font-size: 10px;
            color: #6b7280;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: bold;
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
            font-size: 13px;
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
<div class="report-header">
    <div class="header-title">{{ translate('stock_report') }}</div>
    <div class="header-meta">
        {{ translate('product') }}: {{ $product->name }} |
        {{ translate('variation') }}: {{ $variation ?? translate('Default') }} |
        {{ translate('current_stock') }}: {{ $currentStock }}
    </div>
    
</div>

<!-- KPI -->
<div class="kpi-container">
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('stock_in') }}</div>
                <div class="kpi-value">
                    {{ (int)$summary['stock_in']['initial_stock']
                     + (int)$summary['stock_in']['manual_adjust_add']
                     + (int)$summary['stock_in']['returns'] }}
                </div>
            </td>

            <td>
                <div class="kpi-label">{{ translate('stock_out') }}</div>
                <div class="kpi-value">
                    {{ (int)$summary['stock_out']['sales_pos']
                     + (int)$summary['stock_out']['sales_online']
                     + (int)$summary['stock_out']['sales_wholesale_transfer']
                     + (int)$summary['stock_out']['manual_adjust_negative'] }}
                </div>
            </td>

            @if($includeInternalTransfer)
            <td>
                <div class="kpi-label">{{ translate('internal_transfer') }}</div>
                <div class="kpi-value">
                    +{{ (int)$summary['internal_transfer']['in'] }}
                    / -{{ (int)$summary['internal_transfer']['out'] }}
                </div>
            </td>
            @endif
        </tr>
    </table>
</div>

<!-- SUMMARY DETAILS -->
<div class="table-container">
    <div class="table-header">{{ translate('stock_summary_details') }}</div>
    <table>
        <tr>
            <td>
                <strong>{{ translate('stock_in') }}</strong><br>
                {{ translate('initial_stock') }}: +{{ $summary['stock_in']['initial_stock'] }}<br>
                {{ translate('manual_adjust_add') }}: +{{ $summary['stock_in']['manual_adjust_add'] }}<br>
                {{ translate('returns') }}: +{{ $summary['stock_in']['returns'] }}
            </td>
            <td>
                <strong>{{ translate('stock_out') }}</strong><br>
                {{ translate('sales_pos') }}: -{{ $summary['stock_out']['sales_pos'] }}<br>
                {{ translate('sales_online') }}: -{{ $summary['stock_out']['sales_online'] }}<br>
                {{ translate('sales_wholesale_transfer') }}: -{{ $summary['stock_out']['sales_wholesale_transfer'] }}<br>
                {{ translate('manual_adjust_negative') }}: -{{ $summary['stock_out']['manual_adjust_negative'] }}
            </td>

            @if($includeInternalTransfer)
            <td>
                <strong>{{ translate('internal_transfer') }}</strong><br>
                {{ translate('stock_in') }}: +{{ $summary['internal_transfer']['in'] }}<br>
                {{ translate('stock_out') }}: -{{ $summary['internal_transfer']['out'] }}
            </td>
            @endif
        </tr>
    </table>
</div>

<!-- HISTORY TABLE -->
<div class="table-container">
    <div class="table-header">{{ translate('stock_movement_history') }}</div>

    <table>
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
                <td>{{ strtoupper((string)$history['type']) === 'IN' ? translate('Stock In') : translate('Stock Out') }}</td>
                <td>{{ strtoupper((string)$history['type']) === 'IN' ? '+' : '-' }}{{ (int)$history['quantity'] }}</td>
                <td>{{ $history['category'] }}</td>
                <td>
                    {{ $history['reason_label'] ?? str_replace('_', ' ', (string)$history['reason']) }}<br>
                    {{ $history['remarks_label'] ?? ($history['remarks'] ?? '') }}

                    @if ($history['from_branch'] || $history['to_branch'])
                        <br>
                        {{ $history['from_branch'] ? (translate('from') . ': ' . $history['from_branch']) : '' }}
                        {{ $history['to_branch'] ? (' | ' . translate('to') . ': ' . $history['to_branch']) : '' }}
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
</div>
</body>
</html>