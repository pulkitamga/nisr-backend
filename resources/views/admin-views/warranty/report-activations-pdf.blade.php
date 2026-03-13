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
            font-size: 15px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
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
    <h1>{{ translate('activation_report') }}</h1>
    <div class="meta">
        {{ translate('report_period') }}:
        {{ $fromDate->format('Y-m-d') }} - {{ $toDate->format('Y-m-d') }}
    </div>

    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_activations') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['total_activations'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('activation_rate') }}</div>
                <div class="kpi-value">{{ number_format((float)($kpi['activation_rate'] ?? 0), 1) }}%</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('active_warranties') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['active_warranties'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('avg_warranty_months') }}</div>
                <div class="kpi-value">
                    {{ ($kpi['avg_warranty_months'] ?? null) !== null ? number_format((float)$kpi['avg_warranty_months'], 1) : translate('na') }}
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>{{ translate('method') }}</th>
                <th>{{ translate('total') }}</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($methodBreakdown as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format((int)$row['count']) }}</td>
                    <td>{{ number_format((float)$row['percentage'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('serial') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('customer') }}</th>
                <th>{{ translate('branch') }}</th>
                <th>{{ translate('activation_method') }}</th>
                <th>{{ translate('activation_date') }}</th>
                <th>{{ translate('warranty_end') }}</th>
                <th>{{ translate('status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activationRowsForPdf as $warranty)
                @php
                    $customerName = trim(
                        ((string)($warranty->user?->f_name ?? '')) . ' ' . ((string)($warranty->user?->l_name ?? ''))
                    );
                    if ($customerName === '') {
                        $customerName = $warranty->activated_by_name ?? '-';
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $warranty->serial_number }}</td>
                    <td>{{ $warranty->product?->name ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td>{{ $warranty->branch?->branch_name ?? '-' }}</td>
                    <td>{{ translate($warranty->activation_method ?: 'unknown') }}</td>
                    <td>{{ optional($warranty->activation_date)->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>{{ optional($warranty->end_date)->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ translate($warranty->status) }}</td>
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
