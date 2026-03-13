<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Order Report Statement - '.$data['date_type'] }}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
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
        .customers tbody td {
            background-color: #FAFCFF;
            padding: 8px;
            font-size: 11px;
        }
        .text-right {
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
            min-height: 2px;
        }
        .bar-label {
            margin-top: 5px;
            font-size: 9px;
        }
        .bar-value {
            font-size: 8px;
        }
        .logo {
            max-height: 50px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <table class="content-position" style="width:100%; margin-bottom:20px;">
        <tr>
            <td class="text-left">
                <h2>{{translate('order_Report_Statement')}}</h2>
                <p class="fz-14">{{translate('date')}} : <span style="font-weight: normal">{{ date('d/m/Y') }}</span></p>
            </td>
            <td class="text-right">
                @php
    $logoArray = $data['company_web_logo'] ?? null;
    $logoKey = is_array($logoArray) ? ($logoArray['key'] ?? '') : '';
    $logoPath = $logoKey ? storage_path('app/public/company/' . $logoKey) : '';
    
    if ($logoPath && file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    } else {
        $logoSrc = '';
    }
@endphp
@if($logoSrc)
    <img class="logo" src="{{ $logoSrc }}" alt="">
@endif
            </td>
        </tr>
    </table>

    <table class="content-position-y" style="width:100%; margin-bottom:15px;">
        <tr>
            <td>
                <table>
                    <tr><th class="bold black text-left">{{translate('duration')}}</th><td>: {{ str_replace('_',' ', $data['date_type']) }}</td></tr>
                    <tr><th class="bold black text-left">{{translate('vendor_Info')}}</th><td>: {{ ucfirst($data['seller']) }}</td></tr>
                </table>
            </td>
            <td>
                <table>
                    <tr><th class="bold black text-left">{{translate('total_Order')}}</th><td>: {{ $data['total_orders'] }}</td></tr>
                    <tr><th class="bold black text-left">{{translate('type')}}</th><td>: {{ ucfirst($data['type']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if(!empty($data['chart_labels']) && count($data['chart_labels']) > 0)
    <div class="chart-container content-position-y">
        <h4>{{ translate('order_Statistics') }}</h4>
        <div class="chart">
            @foreach($data['chart_labels'] as $index => $label)
                @php
                    $value = $data['chart_values'][$index];
                    $height = $data['max_chart_value'] > 0 ? ($value / $data['max_chart_value']) * 100 : 2;
                @endphp
                <div class="bar-wrapper">
                    <div class="bar" style="height: {{ $height }}px;"></div>
                    <div class="bar-label">{{ $label }}</div>
                    <div class="bar-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $value), currencyCode: getCurrencyCode()) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="content-position-y">
        <table class="customers">
            <thead>
                <tr>
                    <th>{{translate('SL')}}</th>
                    <th>{{translate('details')}}</th>
                    <th class="text-right">{{translate('amount')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>{{translate('total_Ordered_Amount')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_order_amount']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>2</td><td>{{translate('total_Product_Discount')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_product_discount']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>3</td><td>{{translate('total_Coupon_Discount')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_coupon_discount']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>4</td><td>{{translate('total_Shipping_Charge')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_delivery_charge']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>5</td><td>{{translate('total')}} {{translate('VAT/TAX')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_tax']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>6</td><td>{{translate('total_Order_Commission')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_order_commission']), currencyCode: getCurrencyCode()) }}</td></tr>
                <tr><td>7</td><td>{{translate('total_Deliveryman_Incentive')}}</td><td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_deliveryman_incentive']), currencyCode: getCurrencyCode()) }}</td></tr>
            </tbody>
        </table>
    </div>

    <!-- फुटर -->
    <div style="margin-top: 30px;">
        <table style="width:100%;">
            <tr>
                <td class="content-position-y bg-light py-4 footer" style="background-color: #F7F7F7; padding: 24px 40px;">
                    <div class="d-flex justify-content-center gap-2">
                        <div><i class="fa fa-phone"></i> {{translate('phone')}} : {{ $data['company_phone'] }}</div>
                        <div><i class="fa fa-envelope"></i> {{translate('email')}} : {{ $data['company_email'] }}</div>
                    </div>
                    <div class="text-center">{{ url('/') }}</div>
                    <div class="text-center">{{translate('all_copy_right_reserved_©_'.date('Y').'_').$data['company_name'] }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>