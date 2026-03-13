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
            width: 20%;
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
    <h1>{{ translate('sla_report') }}</h1>
    <div class="meta">
        {{ translate('report_period') }}:
        {{ $fromDate->format('Y-m-d') }} - {{ $toDate->format('Y-m-d') }}
    </div>

    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_deadlines') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['total_deadlines'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('sla_compliance') }}</div>
                <div class="kpi-value">{{ number_format((float)($kpi['compliance'] ?? 0), 1) }}%</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('on_time') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['on_time'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('breached') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['breached'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('avg_breach_hours') }}</div>
                <div class="kpi-value">
                    {{ ($kpi['avg_breach_hours'] ?? null) !== null ? number_format((float)$kpi['avg_breach_hours'], 1) . 'h' : translate('na') }}
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('sla_type') }}</th>
                <th>{{ translate('claim_number') }}</th>
                <th>{{ translate('serial_no') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('due_date') }}</th>
                <th>{{ translate('completed_at') }}</th>
                <th>{{ translate('status') }}</th>
                <th>{{ translate('claim_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($slaRowsForPdf as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->sla_type_key === 'response' ? translate('first_response_sla') : translate('resolution_sla') }}</td>
                    <td>{{ $row->claim_number }}</td>
                    <td>{{ $row->serial_number }}</td>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->due_date)->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td>{{ (int)$row->is_within_sla === 1 ? translate('on_time') : translate('breached') }}</td>
                    <td>{{ translate($row->status) }}</td>
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
