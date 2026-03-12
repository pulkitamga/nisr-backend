<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('warranty_analytics_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            direction: {{ ($isRtl ?? false) ? 'rtl' : 'ltr' }};
            text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }};
            unicode-bidi: embed;
        }
        h2 { margin-bottom: 6px; }
        .metric-line { margin: 0 0 6px; }
        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
            text-align: left;
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
            margin-top: 12px;
            direction: {{ ($isRtl ?? false) ? 'rtl' : 'ltr' }};
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }};
        }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>{{ translate('warranty_analytics_report') }}</h2>
    <p class="metric-line">
        {{ translate('report_period') }}:
        <span class="value-ltr">{{ $snapshotFrom->format('Y-m-d') }} - {{ $snapshotTo->format('Y-m-d') }}</span>
    </p>

    <!-- KPI Line -->
    <p class="metric-line">{{ translate('total_claims') }}: <span class="value-ltr">{{ number_format((int)($kpi['total_claims'] ?? 0)) }}</span></p>

    <!-- Status Distribution Chart -->
    @if(!empty($statusChartData['labels']) && count($statusChartData['labels']) > 0)
        <div class="chart-container">
            <div class="chart-title">{{ translate('claims_by_status') }}</div>
            <div class="bar-chart">
                @php $maxCount = max($statusChartData['counts']); @endphp
                @foreach($statusChartData['labels'] as $index => $label)
                    @php
                        $count = $statusChartData['counts'][$index] ?? 0;
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

    <!-- Aging Chart -->
    @if(!empty($agingChartData['labels']) && count($agingChartData['labels']) > 0)
        <div class="chart-container">
            <div class="chart-title">{{ translate('claim_aging') }}</div>
            <div class="bar-chart">
                @php $maxCount = max($agingChartData['counts']); @endphp
                @foreach($agingChartData['labels'] as $index => $label)
                    @php
                        $count = $agingChartData['counts'][$index] ?? 0;
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

    <!-- Charge Breakdown Chart -->
    @if(!empty($chargeChartData['labels']) && count($chargeChartData['labels']) > 0)
        <div class="chart-container">
            <div class="chart-title">{{ translate('charges_by_type') }}</div>
            <div class="bar-chart">
                @php $maxAmount = max($chargeChartData['amounts']); @endphp
                @foreach($chargeChartData['labels'] as $index => $label)
                    @php
                        $amount = $chargeChartData['amounts'][$index] ?? 0;
                        $height = $maxAmount > 0 ? ($amount / $maxAmount) * 100 : 2;
                    @endphp
                    <div class="bar-wrapper">
                        <div class="bar" style="height: {{ $height }}px;"></div>
                        <div class="bar-label">{{ $label }}</div>
                        <div class="bar-value">{{ number_format($amount, 2) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top Products Table -->
    <table>
        <thead>
            <tr>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('claims') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ number_format((int)$product->claims_count) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>