@php
    $isRtl = $isRtl ?? (app()->getLocale() === 'ar' || session('direction') === 'rtl');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        h1 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .meta {
            margin-bottom: 12px;
            color: #4b5563;
        }

        .kpi-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .kpi-grid td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            width: 25%;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 10px;
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h1>{{ translate('claims_report') }}</h1>
    <div class="meta">
        {{ translate('report_period') }}:
        {{ $fromDate->format('Y-m-d') }} - {{ $toDate->format('Y-m-d') }}
    </div>

    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_claims') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['total_claims'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('claim_rate') }}</div>
                <div class="kpi-value">{{ number_format((float)($kpi['claim_rate'] ?? 0), 1) }}%</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('open_claims') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['open_claims'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('resolved') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['resolved_claims'] ?? 0)) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('claim_number') }}</th>
                <th>{{ translate('serial') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('customer') }}</th>
                <th>{{ translate('status') }}</th>
                <th>{{ translate('submitted_at') }}</th>
                <th>{{ translate('sla_due') }}</th>
                <th>{{ translate('branch') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($claimsForPdf as $claim)
                @php
                    $customerName = trim(
                        ((string)($claim->warranty?->user?->f_name ?? '')) . ' ' . ((string)($claim->warranty?->user?->l_name ?? ''))
                    );
                    if ($customerName === '') {
                        $customerName = $claim->warranty?->activated_by_name ?? $claim->activated_by_name ?? '-';
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $claim->claim_number }}</td>
                    <td>{{ $claim->serial_number }}</td>
                    <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td>{{ translate($claim->status) }}</td>
                    <td>{{ optional($claim->submitted_at ?? $claim->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($claim->resolution_due)->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>{{ $claim->branch?->branch_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
