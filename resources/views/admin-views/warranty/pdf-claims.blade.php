<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('warranty_claims_report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 12px;
        }
        .filters-section {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #4a90e2;
        }
        .filters-section h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .filter-item {
            font-size: 11px;
        }
        .filter-item strong {
            color: #2c3e50;
        }
        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
            justify-content: space-between;
        }
        .card {
            flex: 1 1 150px;
            background: #ffffff;
            border-radius: 10px;
            padding: 12px 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #eef2f7;
            text-align: center;
        }
        .card .title {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .card .value {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }
        /* रंगीन कार्ड */
        .card-total { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .card-new { background: #fff3e0; border-left: 4px solid #ff9800; }
        .card-approved { background: #e8f5e9; border-left: 4px solid #4caf50; }
        .card-rejected { background: #ffebee; border-left: 4px solid #f44336; }
        .card-pending { background: #ede7f6; border-left: 4px solid #673ab7; }
        .card-resolved { background: #e0f2f1; border-left: 4px solid #009688; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #357abd;
        }
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .daily-table th {
            background-color: #2c3e50;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #95a5a6;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .filter-summary {
            font-size: 10px;
            background: #ecf0f1;
            padding: 8px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .text-left { text-align: left; }
    </style>
</head>
<body>

    <!-- हेडर -->
    <div class="header">
        <h2>{{ translate('warranty_claims_report') }}</h2>
        <p>
            {{ translate('generated_on') }}: {{ now()->format('d M Y H:i A') }} &nbsp; | &nbsp;
            {{ translate('by') }}: System
        </p>
    </div>

    <!-- फिल्टर अप्लाइड सेक्शन -->
    <div class="filters-section">
        <h4>🔍 {{ translate('filters_applied') }}</h4>
        <div class="filters-grid">
            <div class="filter-item"><strong>{{ translate('date_range') }}:</strong> {{ $filters['date_range'] }}</div>
            <div class="filter-item"><strong>{{ translate('branch') }}:</strong> {{ $filters['branch'] }}</div>
                        <div class="filter-item"><strong>{{ translate('product') }}:</strong> {{ $filters['product'] }}</div>
            <div class="filter-item"><strong>{{ translate('status') }}:</strong> {{ $filters['status'] }}</div>
            @if($filters['search'])
            <div class="filter-item"><strong>{{ translate('search') }}:</strong> {{ $filters['search'] }}</div>
            @endif
        </div>
        <div style="margin-top:8px;"><strong>{{ translate('report_period') }}:</strong> {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</div>
    </div>

    <!-- समरी स्टैटिस्टिक्स (रंगीन कार्ड) -->
    <h3 style="margin-bottom:10px;">📊 {{ translate('summary_statistics') }}</h3>
    <div class="summary-cards">
        <div class="card card-total">
            <div class="title">{{ translate('total_claims') }}</div>
            <div class="value">{{ $cards['total'] }}</div>
        </div>
        <div class="card card-new">
            <div class="title">{{ translate('new') }}</div>
            <div class="value">{{ $cards['new'] }}</div>
        </div>
        <div class="card card-approved">
            <div class="title">{{ translate('approved') }}</div>
            <div class="value">{{ $cards['approved'] }}</div>
        </div>
        <div class="card card-rejected">
            <div class="title">{{ translate('rejected') }}</div>
            <div class="value">{{ $cards['rejected'] }}</div>
        </div>
        <div class="card card-pending">
            <div class="title">{{ translate('pending') }}</div>
            <div class="value">{{ $cards['pending'] }}</div>
        </div>
        <div class="card card-resolved">
            <div class="title">{{ translate('resolved') }}</div>
            <div class="value">{{ $cards['resolved'] }}</div>
        </div>
    </div>

    <!-- डेली ब्रेकडाउन रिपोर्ट -->
    <h3 style="margin:20px 0 10px;">📅 {{ translate('daily_breakdown_report') }}</h3>
    <table class="daily-table">
        <thead>
            <tr>
                <th>{{ translate('date') }}</th>
                <th>{{ translate('total') }}</th>
                <th>{{ translate('new') }}</th>
                <th>{{ translate('approved') }}</th>
                <th>{{ translate('rma_issued') }}</th>
                <th>{{ translate('received') }}</th>
                <th>{{ translate('repair_pending') }}</th>
                <th>{{ translate('replacement_pending') }}</th>
                <th>{{ translate('qc_pending') }}</th>
                <th>{{ translate('dispatched') }}</th>
                <th>{{ translate('resolved') }}</th>
                <th>{{ translate('rejected') }}</th>
                <th>{{ translate('closed') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyBreakdown as $day)
            <tr>
                <td>{{ $day['date'] }}</td>
                <td><strong>{{ $day['total'] }}</strong></td>
                <td>{{ $day['new'] }}</td>
                <td>{{ $day['approved'] }}</td>
                <td>{{ $day['rma_issued'] }}</td>
                <td>{{ $day['received'] }}</td>
                <td>{{ $day['repair_pending'] }}</td>
                <td>{{ $day['replacement_pending'] }}</td>
                <td>{{ $day['qc_pending'] }}</td>
                <td>{{ $day['dispatched'] }}</td>
                <td>{{ $day['resolved'] }}</td>
                <td>{{ $day['rejected'] }}</td>
                <td>{{ $day['closed'] }}</td>
            </tr>
            @empty
            <tr><td colspan="13" style="text-align:center;">{{ translate('no_data_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(count($claims) > 0)
    <h3 style="margin:20px 0 10px;">📋 {{ translate('claims_list') }}</h3>
    <table>
        <thead>
            <tr>
                <th>{{ translate('SL') }}</th>
                <th>{{ translate('claim_number') }}</th>
                <th>{{ translate('serial') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('status') }}</th>
                <th>{{ translate('customer') }}</th>
                <th>{{ translate('submitted_at') }}</th>
                <th>{{ translate('sla_due') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claims as $index => $claim)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $claim->claim_number }}</td>
                <td>{{ $claim->serial_number }}</td>
                <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                <td>{{ translate($claim->status) }}</td>
                <td>{{ $claim->warranty?->user?->name ?? $claim->warranty?->activated_by_name ?? '' }}</td>
                <td>{{ $claim->submitted_at ? $claim->submitted_at->format('Y-m-d H:i') : '' }}</td>
                <td>{{ $claim->resolution_due ? $claim->resolution_due->format('Y-m-d H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="filter-summary">
        <strong>{{ translate('filter_summary') }}:</strong>
        {{ translate('date') }}: {{ $filters['date_range'] }} |
        {{ translate('branch') }}: {{ $filters['branch'] }} |
        {{ translate('status') }}: {{ $filters['status'] }} |
        {{ translate('product') }}: {{ $filters['product'] }}
    </div>

    <div class="footer">
        {{ translate('generated_on') }}: {{ now()->format('d M Y H:i:s') }} |
        {{ translate('total_records') }}: {{ count($claims) }}
    </div>

</body>
</html>