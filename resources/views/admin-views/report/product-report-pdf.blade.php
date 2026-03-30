<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Product Report - '.$data['date_type'] }}</title>
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
        table.product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.product-table th {
            background-color: #0177CD;
            color: white;
            padding: 6px;
            text-align: left;
        }
        table.product-table td {
            background-color: #FAFCFF;
            padding: 6px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <table style="width:100%; margin-bottom:20px;">
        <tr>
            <td class="text-start">
                <h2>{{ translate('product_Report') }}</h2>
                <p>{{ translate('date') }} : <span style="font-weight: normal">{{ date('d/m/Y') }}</span></p>
            </td>
            <td class="text-end">
                @php
                    $logoArray = $data['company_web_logo'] ?? [];
                    $logoKey = is_array($logoArray) ? ($logoArray['key'] ?? '') : $logoArray;
                    $logoPath = $logoKey ? storage_path('app/public/company/' . $logoKey) : '';

                    if ($logoPath && file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:image/png;base64,' . $logoData;
                    } else {
                        $logoSrc = '';
                    }
                @endphp
                @if($logoSrc)
                    <img class="logo" src="{{ $logoSrc }}" alt="{{ translate('logo') }}">
                @endif
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-bottom:15px;">
        <tr>
            <td>
                <table>
                    <tr><th class="text-start">{{ translate('duration') }}</th><td>: {{ str_replace('_',' ', $data['date_type']) }}</td></tr>
                    <tr><th class="text-start">{{ translate('vendor_Info') }}</th><td>: 
                        @if(is_object($data['seller']))
                            {{ $data['seller']->f_name ?? '' }} {{ $data['seller']->l_name ?? '' }}
                        @else
                            {{ ucfirst($data['seller']) }}
                        @endif
                    </td></tr>
                </table>
            </td>
            <td>
                <table>
                    <tr><th class="text-start">{{ translate('total_Products') }}</th><td>: {{ count($data['products']) }}</td></tr>
                    @if($data['search'])
                    <tr><th class="text-start">{{ translate('search') }}</th><td>: {{ $data['search'] }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @php
        $chartProducts = $data['chart_data']['total_product'] ?? [];
        $chartLabels = array_keys($chartProducts);
        $chartValues = array_values($chartProducts);
        $maxChartValue = !empty($chartValues) ? max($chartValues) : 1;
    @endphp
    @if(!empty($chartLabels))
    <div class="chart-container">
        <h4>{{ translate('product_Statistics') }}</h4>
        <div class="chart">
            @foreach($chartLabels as $index => $label)
                @php
                    $value = $chartValues[$index] ?? 0;
                    $height = $maxChartValue > 0 ? ($value / $maxChartValue) * 100 : 2;
                @endphp
                <div class="bar-wrapper">
                    <div class="bar" style="height: {{ $height }}px;"></div>
                    <div class="bar-label">{{ $label }}</div>
                    <div class="bar-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="margin-top: 20px;">
        <table class="product-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('product_Name') }}</th>
                    <th>{{ translate('unit_Price') }}</th>
                    <th>{{ translate('total_Sold_Amount') }}</th>
                    <th>{{ translate('total_Quantity_Sold') }}</th>
                    <th>{{ translate('avg_Value') }}</th>
                    <th>{{ translate('current_Stock') }}</th>
                    <th>{{ translate('avg_Rating') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['products'] as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($product->name, 20) }}</td>
                    <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product->unit_price ?? 0), currencyCode: getCurrencyCode()) }}</td>
                    <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product->orderDetails[0]->total_sold_amount ?? 0), currencyCode: getCurrencyCode()) }}</td>
                    <td>{{ $product->orderDetails[0]->product_quantity ?? 0 }}</td>
                    <td>
                        @php
                            $soldAmount = $product->orderDetails[0]->total_sold_amount ?? 0;
                            $quantity = $product->orderDetails[0]->product_quantity ?? 1;
                            $avgValue = $quantity > 0 ? $soldAmount / $quantity : 0;
                        @endphp
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $avgValue), currencyCode: getCurrencyCode()) }}
                    </td>
                    <td>
                        {{ $product->product_type == 'digital' ? ($product->status==1 ? translate('available') : translate('not_available')) : ($product->current_stock ?? 0) }}
                    </td>
                    <td>
                        @php
                            $avgRating = count($product->rating) > 0 ? number_format($product->rating[0]->average, 2, '.', ' ') : 0;
                        @endphp
                        {{ $avgRating }} ({{ $product->reviews->count() }})
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <table style="width:100%;">
            <tr>
                <td class="content-position-y bg-light py-4 footer" style="background-color: #F7F7F7; padding: 24px 40px;">
                    <div class="d-flex justify-content-center gap-2">
                        <div><i class="fa fa-phone"></i> {{ translate('phone') }} : {{ $data['company_phone'] ?? '' }}</div>
                        <div><i class="fa fa-envelope"></i> {{ translate('email') }} : {{ $data['company_email'] ?? '' }}</div>
                    </div>
                    <div class="text-center">{{ url('/') }}</div>
                    <div class="text-center">{{ translate('all_copy_right_reserved_©_'.date('Y').'_').($data['company_name'] ?? '') }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>