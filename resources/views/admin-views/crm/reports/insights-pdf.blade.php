<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_insights_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
            unicode-bidi: embed;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            direction: {{ $isRtl ?? false ? 'rtl' : 'ltr' }};
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: {{ $isRtl ?? false ? 'right' : 'left' }};
        }

        th {
            background: #f3f4f6;
        }

        .metric-line {
            margin: 0 0 6px;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>{{ translate('crm_insights_report') }}</h2>
    <p class="metric-line">
        {{ translate('report_period') }}:
        <span class="value-ltr">{{ $snapshotFrom->format('Y-m-d') }} - {{ $snapshotTo->format('Y-m-d') }}</span>
    </p>
    <p class="metric-line">{{ translate('messages') }}: <span
            class="value-ltr">{{ number_format((int) ($kpi['message_count'] ?? 0)) }}</span></p>
    <p class="metric-line">{{ translate('deals') }}: <span
            class="value-ltr">{{ number_format((int) ($kpi['deal_count'] ?? 0)) }}</span></p>

    @if (!empty($trendChart))
        <h3>{{ translate('crm_trend') }}</h3>
        <img src="{{ $trendChart }}" style="width:100%; margin-bottom:20px;">
    @endif

    @if (!empty($stageChart))
        <h3>{{ translate('deal_stage_mix') }}</h3>
        <img src="{{ $stageChart }}" style="width:60%; margin-bottom:20px;">
    @endif

    @if (!empty($statusChart))
        <h3>{{ translate('message_status_distribution') }}</h3>
        <img src="{{ $statusChart }}" style="width:100%; margin-bottom:20px;">
    @endif
    <table>
        <thead>
            <tr>
                <th>{{ translate('owner') }}</th>
                <th>{{ translate('deals') }}</th>
                <th>{{ translate('total_value') }}</th>
                <th>{{ translate('avg_value') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topOwners as $owner)
                @php($avg = (int) $owner->deals_count > 0 ? (float) $owner->total_value / (int) $owner->deals_count : 0)
                <tr>
                    <td>{{ $owner->owner_name }}</td>
                    <td>{{ number_format((int) $owner->deals_count) }}</td>
                    <td>{{ number_format((float) $owner->total_value, 2) }}</td>
                    <td>{{ number_format($avg, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
