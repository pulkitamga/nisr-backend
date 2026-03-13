<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('voip_insights_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            direction: {{ ($isRtl ?? false) ? 'rtl' : 'ltr' }};
            text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }};
            unicode-bidi: embed;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            direction: {{ ($isRtl ?? false) ? 'rtl' : 'ltr' }};
        }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: {{ ($isRtl ?? false) ? 'right' : 'left' }}; }
        th { background: #f3f4f6; }
        .metric-line { margin: 0 0 6px; }
        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>{{ translate('voip_insights_report') }}</h2>
    <p class="metric-line">
        {{ translate('report_period') }}:
        <span class="value-ltr">{{ $snapshotFrom->format('Y-m-d') }} - {{ $snapshotTo->format('Y-m-d') }}</span>
    </p>
    <p class="metric-line">{{ translate('total_calls') }}: <span class="value-ltr">{{ number_format((int)($kpi['total_calls'] ?? 0)) }}</span></p>
    <p class="metric-line">{{ translate('completed') }}: <span class="value-ltr">{{ number_format((int)($kpi['completed_calls'] ?? 0)) }}</span></p>
    <table>
        <thead>
        <tr>
            <th>{{ translate('agent') }}</th>
            <th>{{ translate('calls') }}</th>
            <th>{{ translate('total_duration') }} ({{ translate('minutes') }})</th>
            <th>{{ translate('avg_duration') }} ({{ translate('minutes') }})</th>
        </tr>
        </thead>
        <tbody>
        @foreach($topAgents as $agent)
            <tr>
                <td>{{ $agent->agent_name }}</td>
                <td>{{ number_format((int)$agent->calls_count) }}</td>
                <td>{{ number_format(((float)$agent->total_duration) / 60, 1) }}</td>
                <td>{{ number_format(((float)$agent->avg_duration) / 60, 1) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
