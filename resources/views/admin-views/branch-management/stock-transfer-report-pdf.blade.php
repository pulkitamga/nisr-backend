<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('stock_transfer_report') }}</title>
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
<h2>{{ translate('stock_transfer_report') }}</h2>

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
    <tr><td class="left">{{ translate('total_transfers') }}</td><td>{{ (int)($statistics['total_transfers'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('pending') }}</td><td>{{ (int)($statistics['pending_transfers'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('approved') }}</td><td>{{ (int)($statistics['approved_transfers'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('rejected') }}</td><td>{{ (int)($statistics['rejected_transfers'] ?? 0) }}</td></tr>
    <tr><td class="left">{{ translate('total_quantity') }}</td><td>{{ (int)($statistics['total_quantity'] ?? 0) }}</td></tr>
    <tr>
        <td class="left">{{ translate('top_from_branch') }}</td>
        <td>
            @php($topFrom = $statistics['top_from_branch'] ?? null)
            @if(is_array($topFrom))
                {{ ($topFrom['name'] ?? '-') . ' (' . (int)($topFrom['count'] ?? 0) . ')' }}
            @elseif(is_object($topFrom))
                {{ data_get($topFrom, 'name', '-') . ' (' . (int)data_get($topFrom, 'count', 0) . ')' }}
            @else
                -
            @endif
        </td>
    </tr>
    <tr>
        <td class="left">{{ translate('top_to_branch') }}</td>
        <td>
            @php($topTo = $statistics['top_to_branch'] ?? null)
            @if(is_array($topTo))
                {{ ($topTo['name'] ?? '-') . ' (' . (int)($topTo['count'] ?? 0) . ')' }}
            @elseif(is_object($topTo))
                {{ data_get($topTo, 'name', '-') . ' (' . (int)data_get($topTo, 'count', 0) . ')' }}
            @else
                -
            @endif
        </td>
    </tr>
    </tbody>
</table>

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
    @forelse($transfers as $index => $transfer)
        @php($quantity = collect($transfer->products ?? [])->sum('quantity'))
        @php($status = collect($transfer->products ?? [])->pluck('status')->filter()->map(fn($value) => translate(strtolower((string)$value)))->unique()->implode(', '))
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $transfer->transfer_date ? \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') : '-' }}</td>
            <td class="left">{{ data_get($transfer, 'fromBranch.branch_name', data_get($transfer, 'from_branch.branch_name', '-')) }}</td>
            <td class="left">{{ data_get($transfer, 'toBranch.branch_name', data_get($transfer, 'to_branch.branch_name', '-')) }}</td>
            <td>{{ (int)$quantity }}</td>
            <td>{{ $status !== '' ? $status : '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">{{ translate('no_data_found') }}</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>

