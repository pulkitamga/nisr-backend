<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Admin Earning Report') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-family: 'Inter', sans-serif;
            color: #333542;
        }
        body {
            font-size: .75rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }
        .customers {
            border-collapse: collapse;
            width: 100%;
        }
        .customers thead th {
            background-color: #0177CD;
            color: #fff;
            padding: 8px;
            font-size: 11px;
            text-align: left;
        }
        .customers tbody th,
        .customers tbody td {
            background-color: #FAFCFF;
            padding: 8px;
            font-size: 11px;
        }
        .text-end {
            text-align: right;
        }
        .content-position-y {
            padding: 0px 40px;
        }
        .bg-light {
            background-color: #F7F7F7;
        }
        .py-4 {
            padding-top: 24px;
            padding-bottom: 24px;
        }
        .d-flex {
            display: flex;
        }
        .justify-content-center {
            justify-content: center;
        }
        .gap-2 {
            gap: 8px;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .footer {
            width: 100%;
        }
        .chart-container {
            margin: 20px 0;
        }
        .chart {
            display: flex;
            align-items: flex-end;
            height: 150px;
            gap: 8px;
            margin-top: 15px;
        }
        .bar-wrapper {
            flex: 1;
            text-align: center;
        }
        .bar {
            background-color: #0177CD;
            border-radius: 4px 4px 0 0;
            width: 100%;
            min-height: 2px; /* छोटे मानों के लिए */
        }
        .bar-label {
            margin-top: 5px;
            font-size: 9px;
        }
        .bar-value {
            font-size: 8px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="max-w-595px" style="min-height: 100vh; display:flex; flex-direction: column; margin: 0 auto; max-width: 595px;">
    <!-- हेडर -->
    <div>
        <table class="bs-0 mb-30 px-10" style="width: 100%;">
            <tr>
                <th class="content-position-y text-start">
                    <h2>{{ translate('admin_Earning_Report') }}</h2>
                    <p class="fz-14">{{ translate('DATE') }} : <span style="font-weight: normal">{{ date('d/m/Y') }}</span></p>
                </th>
                <th class="content-position-y text-end">
                    @php
                        // PDF में लोगो दिखाने के लिए बेस64 एनकोडिंग
                        $logoPath = public_path('storage/app/public/company/' . $pdfData['company_logo']);
                        if (file_exists($logoPath)) {
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoSrc = 'data:image/png;base64,' . $logoData;
                        } else {
                            $logoSrc = dynamicAsset(path: 'public/assets/back-end/img/logo.png');
                        }
                    @endphp
                    <img height="50" src="{{ $logoSrc }}" alt="">
                </th>
            </tr>
        </table>
    </div>

    <!-- अवधि -->
    <div class="content-position-y fz-12">
        <p class="fz-14"><b>{{ translate('Duration') }}</b> : 
            @if($pdfData['duration'] == 'this_year')
                {{ translate('this_Year') }}
            @elseif($pdfData['duration'] == 'this_month')
                {{ translate('this_Month') }}
            @elseif($pdfData['duration'] == 'this_week')
                {{ translate('this_Week') }}
            @elseif($pdfData['duration'] == 'today')
                {{ translate('today') }}
            @elseif($pdfData['duration'] == 'custom_date')
                {{ translate('custom_Date') }} ({{ request('from') }} - {{ request('to') }})
            @else
                {{ $pdfData['duration'] }}
            @endif
        </p>
    </div>

    <!-- चार्ट (यदि डेटा हो) -->
    @if(!empty($pdfData['chart_labels']) && count($pdfData['chart_labels']) > 0)
    <div class="chart-container content-position-y">
        <h4>{{ translate('Earning Overview') }}</h4>
        <div class="chart">
            @foreach($pdfData['chart_labels'] as $index => $label)
                @php
                    $value = $pdfData['chart_values'][$index];
                    $height = $pdfData['max_chart_value'] > 0 ? ($value / $pdfData['max_chart_value']) * 100 : 2;
                @endphp
                <div class="bar-wrapper">
                    <div class="bar" style="height: {{ $height }}px;"></div>
                    <div class="bar-label">{{ $label }}</div>
                    <div class="bar-value">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($value)) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- सारांश तालिका -->
    <div class="content-position-y">
        <table class="customers bs-0">
            <thead>
                <tr>
                    <th>{{ translate('SL') }}</th>
                    <th>{{ translate('Details') }}</th>
                    <th class="text-end">{{ translate('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ translate('in-House_Earning') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['inhouse_earning'])) }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>{{ translate('admin_Commission') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['admin_commission'])) }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>{{ translate('earning_From_Shipping') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['shipping_earn'])) }}</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>{{ translate('delivery_man_incentive') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['deliveryman_incentive'])) }}</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>{{ translate('discount_Given') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['discount_given'])) }}</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>{{ translate('total_Tax') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['total_tax'])) }}</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>{{ translate('refund_Given') }}</td>
                    <td class="text-end">{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['refund_given'])) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #e9ecef;">
                    <td colspan="2" class="text-end"><b>{{ translate('total_Earning') }}</b></td>
                    <td class="text-end"><b>{{ \App\Utils\BackEndHelper::set_symbol(\App\Utils\BackEndHelper::usd_to_currency($pdfData['total_earning'])) }}</b></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- फुटर (हमेशा नीचे रहेगा) -->
    <div style="margin-top: 40px;">
        <table style="width:100%;">
            <tr>
                <td class="content-position-y bg-light py-4 footer" style="background-color: #F7F7F7; padding: 24px 40px;">
                    <div class="d-flex justify-content-center gap-2" style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <div class="mb-2">
                            <i class="fa fa-phone"></i> {{ translate('Phone') }} : {{ $pdfData['company_phone'] }}
                        </div>
                        <div class="mb-2">
                            <i class="fa fa-envelope"></i> {{ translate('Email') }} : {{ $pdfData['company_email'] }}
                        </div>
                    </div>
                    <div class="mb-2 text-center">{{ url('/') }}</div>
                    <div class="text-center">{{ translate('all_copy_right_reserved_©_'.date('Y').'_').$pdfData['company_name'] }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>