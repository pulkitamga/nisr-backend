<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('wholesale_revenue_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            direction: {{ ($isRtl ?? false) ? 'rtl' : 'ltr' }};
            text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }};
            unicode-bidi: embed;
            margin: 20px;
        }
        h2 {
            color: #1d4ed8;
            margin-bottom: 15px;
        }
        .metric-line { 
            margin: 0 0 8px; 
            font-size: 14px;
        }
        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
            text-align: left;
            font-weight: 600;
        }
        .chart-container {
            margin: 25px 0;
            page-break-inside: avoid;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .chart-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1f2937;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 8px;
        }
        .chart-image {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: contain;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .col-6 {
            width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border: 1px solid #d1d5db;
        }
        th, td { 
            border: 1px solid #d1d5db; 
            padding: 10px; 
            text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }};
        }
        th { 
            background: #f3f4f6;
            font-weight: 600;
        }
        .table-title {
            font-size: 16px;
            font-weight: bold;
            margin: 30px 0 15px;
            color: #1f2937;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 8px;
        }
    </style>
</head>
<body>
    <h2>{{ translate('wholesale_revenue_report') }}</h2>
    
    <p class="metric-line">
        {{ translate('report_period') }}:
        <span class="value-ltr">{{ $snapshotFrom->format('Y-m-d') }} - {{ $snapshotTo->format('Y-m-d') }}</span>
    </p>

    <!-- Graph 1: Revenue Trend Chart -->
    @if(!empty($revenueTrendChartImage))
    <div class="chart-container">
        <div class="chart-title">{{ translate('revenue_trend') }}</div>
        <img src="{{ $revenueTrendChartImage }}" class="chart-image" alt="Revenue Trend">
    </div>
    @endif

    <!-- Graph 2: Delivery Status Chart -->
    @if(!empty($deliveryStatusChartImage))
    <div class="chart-container">
        <div class="chart-title">{{ translate('delivery_status_breakdown') }}</div>
        <img src="{{ $deliveryStatusChartImage }}" class="chart-image" alt="Delivery Status">
    </div>
    @endif

    <!-- Table: Top Wholesalers -->
    <div class="table-title">{{ translate('top_wholesalers') }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ translate('wholesaler') }}</th>
                <th>{{ translate('orders') }}</th>
                <th>{{ translate('revenue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topWholesalers as $row)
                <tr>
                    <td>{{ $row->wholeseller?->name ?? ('#' . $row->wholesaler_id) }}</td>
                    <td class="value-ltr">{{ number_format((int)$row->orders_count) }}</td>
                    <td class="value-ltr">{{ number_format((float)$row->total_revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">{{ translate('no_data_available') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>