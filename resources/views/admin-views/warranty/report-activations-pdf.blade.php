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

        .chart-container {
            margin: 20px 0;
        }
        .chart-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .bar-chart {
            display: flex;
            align-items: flex-end;
            height: 150px;
            gap: 8px;
            margin-top: 10px;
        }
        .bar-wrapper {
            flex: 1;
            text-align: center;
        }
        .bar {
            background-color: #0177CD;
            border-radius: 4px 4px 0 0;
            width: 100%;
            min-height: 2px;
        }
        .bar-label {
            margin-top: 5px;
            font-size: 9px;
        }
        .bar-value {
            font-size: 8px;
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

    <!-- KPI Grid -->
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

    <!-- Activation Method Chart -->
    @if(!empty($activationMethodChartData['labels']) && count($activationMethodChartData['labels']) > 0)
        <div class="chart-container">
            <div class="chart-title">{{ translate('activations_by_method') }}</div>
            <div class="bar-chart">
                @php $maxCount = max($activationMethodChartData['counts']); @endphp
                @foreach($activationMethodChartData['labels'] as $index => $label)
                    @php
                        $count = $activationMethodChartData['counts'][$index] ?? 0;
                        $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 2;
                    @endphp
                    <div class="bar-wrapper">
                        <div class="bar" style="height: {{ $height }}px;"></div>
                        <div class="bar-label">{{ $label }}</div>
                        <div class="bar-value">{{ $count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Activation Trend Chart -->
    @if(!empty($activationTrendChartData['labels']) && count($activationTrendChartData['labels']) > 0)
        <div class="chart-container">
            <div class="chart-title">{{ translate('activations_trend') }}</div>
            <div class="bar-chart">
                @php $maxCount = max($activationTrendChartData['counts']); @endphp
                @foreach($activationTrendChartData['labels'] as $index => $label)
                    @php
                        $count = $activationTrendChartData['counts'][$index] ?? 0;
                        $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 2;
                    @endphp
                    <div class="bar-wrapper">
                        <div class="bar" style="height: {{ $height }}px;"></div>
                        <div class="bar-label">{{ $label }}</div>
                        <div class="bar-value">{{ $count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Method Breakdown Table (Optional) -->
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

    <!-- Activations Table -->
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