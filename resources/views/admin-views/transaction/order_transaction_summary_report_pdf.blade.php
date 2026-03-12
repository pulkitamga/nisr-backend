<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Order Transaction Statement - '.$duration }}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/admin/order-transaction.css') }}">
</head>

<?php
    $companyLogo = getWebConfig(name: 'company_web_logo');
?>

<body> 
<table class="content-position">
    <tr>
        <td>
            <table class="bs-0">
                <tr>
                    <th class="h3 p-0 text-left">
                        {{translate('order_Transaction_Statement')}}
                    </th>
                    <th class="p-0 text-right">
                        <img class="logo" src="{{ getStorageImages(path: $companyLogo, type: 'backend-logo') }}"  alt="">
                    </th>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="pt-0">
            <table class="bs-0">
                <tr>
                    <td class="p-0 text-left">
                        <b class="bold black">{{translate('date')}}</b> : {{ date('F d, Y') }} <span
                                class="block h-5"></span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="content-position">
    <tr>
        <td class="pt-0">
            <table class="bs-0">
                <tr>
                    <td class="p-0 text-left">
                        <table>
                            <tr>
                                <th class="bold black p-0 text-left p-3">{{translate('duration')}}</th>
                                <td class="p-0 p-3 text-capitalize">: {{ $duration }}</td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left p-3">{{translate('vendor_Info')}}</th>
                                <td class="p-0 p-3">:
                                    {{ $seller_info }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left p-3">{{translate('customer_Info')}}</th>
                                <td class="p-0 p-3">:
                                    {{ $customer_info }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left p-3">{{translate('status')}}</th>
                                <td class="p-0 p-3">:
                                    {{ $status }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="p-0 text-left">
                        <table>
                            <tr>
                                <th class="bold black p-0 text-left">{{translate('total_Order')}} </th>
                                <td class="p-0p-3">:
                                    {{ $data['total_orders'] }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left">{{translate('in_House_Order')}}</th>
                                <td class="p-0p-3">:
                                    {{ $data['in_house_orders'] }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left">{{translate('vendor_Order')}}</th>
                                <td class="p-0p-3">:
                                    {{ $data['seller_orders'] }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left">{{translate('total_In-House_Products')}}</th>
                                <td class="p-0p-3">:
                                    {{ $data['total_in_house_products'] }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bold black p-0 text-left">{{translate('total_Stores')}}</th>
                                <td class="p-0p-3">:
                                    {{ $data['total_stores'] }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td class="pt-0">
            <table class="bs-0 __product-table inter">
                <tbody>
                <tr>
                    <td class="pl-0 pr-0" style="background: #0177CD !important;color: white;font-weight: bold;">{{translate('SL')}}</td>
                    <td style="background: #0177CD !important;color: white;font-weight: bold;">{{translate('details')}}</td>
                    <td class="text-right" style="background: #0177CD !important;color: white;font-weight: bold;">{{translate('amount')}}</td>
                </tr>
                <tr>
                    <td class="text-center">1</td>
                    <td>{{translate('total_Ordered_Product_Price')}}</td>
                    <td class="text-right">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_ordered_product_price']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>{{translate('total_Product_Discount')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_product_discount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td>{{translate('total_Coupon_Discount')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_coupon_discount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">4</td>
                    <td>{{translate('total_Discounted_Amount')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_discounted_amount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">5</td>
                    <td>{{translate('total')}} {{translate('VAT/TAX')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_tax']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">6</td>
                    <td>{{translate('total_Delivery_Charge')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_delivery_charge']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">6</td>
                    <td>{{translate('total_Deliveryman_incentive')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_deliveryman_incentive']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">7</td>
                    <td>{{translate('total_Order_Amount')}}</td>
                    <td class="text-right">
                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_order_amount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table class="content-position">
    <tr>
        <th class="text-left black bold"><b>{{translate('additional_information')}}</b></th>
        <th class="text-right black bold"><b>{{translate('totals')}}</b></th>
    </tr>
    <tbody class="bs-0 __product-table inter add-info-border-top-bottom">
    <tr>
        <td>
            {{translate('admin_Discount')}}
        </td>
        <td class="text-right">
            {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_admin_discount']), currencyCode: getCurrencyCode()) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ translate('vendor_Discount') }}
        </td>
        <td class="text-right">
            {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_seller_discount']), currencyCode: getCurrencyCode()) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ translate('admin_Commission') }}
        </td>
        <td class="text-right">
            {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_admin_commission']), currencyCode: getCurrencyCode()) }}
        </td>
    </tr>
    <tr>
        <td>
            {{translate('admin_Net_Income')}}
        </td>
        <td class="text-right">
            {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_admin_net_income']), currencyCode: getCurrencyCode()) }}
        </td>
    </tr>
    <tr>
        <td>
            {{translate('vendor_Net_Income')}}
        </td>
        <td class="text-right">
            {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_seller_net_income']), currencyCode: getCurrencyCode()) }}
        </td>
    </tr>
    </tbody>
</table>

{{-- Order Statistics Chart (Bar Chart) --}}
<table class="content-position" style="margin-top: 20px;">
    <tr>
        <th colspan="2" class="text-left black bold" style="padding: 10px 0;">
            <h3>{{ translate('order_Statistics') }}</h3>
        </th>
    </tr>
    <tr>
        <td colspan="2">
            @php
                $chartData = $order_transaction_chart['order_amount'] ?? [];
                $maxValue = !empty($chartData) ? max($chartData) : 1;
                $barWidth = 30; // width of each bar in pixels
                $chartHeight = 150;
            @endphp
            <svg width="100%" height="200" viewBox="0 0 {{ count($chartData) * ($barWidth + 10) + 50 }} 200" preserveAspectRatio="xMidYMid meet">
                @foreach($chartData as $label => $value)
                    @php
                        $barHeight = ($value / $maxValue) * $chartHeight;
                        $x = 30 + $loop->index * ($barWidth + 15);
                        $y = 180 - $barHeight;
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" fill="#0177CD" />
                    <text x="{{ $x + $barWidth/2 }}" y="195" font-size="10" text-anchor="middle">{{ $label }}</text>
                    <text x="{{ $x + $barWidth/2 }}" y="{{ $y - 5 }}" font-size="8" text-anchor="middle">{{ number_format($value, 2) }}</text>
                @endforeach
                <line x1="20" y1="180" x2="{{ count($chartData) * ($barWidth + 15) + 30 }}" y2="180" stroke="black" stroke-width="1" />
                <line x1="20" y1="20" x2="20" y2="180" stroke="black" stroke-width="1" />
            </svg>
        </td>
    </tr>
</table>

{{-- Payment Statistics Chart (Pie Chart) --}}
<table class="content-position" style="margin-top: 30px;">
    <tr>
        <th colspan="2" class="text-left black bold" style="padding: 10px 0;">
            <h3>{{ translate('payment_Statistics') }}</h3>
        </th>
    </tr>
    <tr>
        <td style="width: 50%; vertical-align: top;">
            @php
                $payments = [
                    'cash_payment' => $payment_data['cash_payment'] ?? 0,
                    'digital_payment' => $payment_data['digital_payment'] ?? 0,
                    'wallet_payment' => $payment_data['wallet_payment'] ?? 0,
                    'offline_payment' => $payment_data['offline_payment'] ?? 0,
                ];
                $total = array_sum($payments);
                $colors = ['#004188', '#0177CD', '#A2CEEE', '#CDE6F5'];
                $startAngle = 0;
                $cx = 100; $cy = 100; $r = 80;
            @endphp
            <svg width="200" height="200" viewBox="0 0 200 200">
                @foreach($payments as $key => $value)
                    @if($value > 0 && $total > 0)
                        @php
                            $percentage = $value / $total;
                            $endAngle = $startAngle + $percentage * 360;
                            // Convert angles to radians
                            $startRad = deg2rad($startAngle);
                            $endRad = deg2rad($endAngle);
                            $x1 = $cx + $r * cos($startRad);
                            $y1 = $cy + $r * sin($startRad);
                            $x2 = $cx + $r * cos($endRad);
                            $y2 = $cy + $r * sin($endRad);
                            $largeArcFlag = $percentage > 0.5 ? 1 : 0;
                            $pathData = "M $cx,$cy L $x1,$y1 A $r,$r 0 $largeArcFlag,1 $x2,$y2 Z";
                        @endphp
                        <path d="{{ $pathData }}" fill="{{ $colors[$loop->index] }}" stroke="white" stroke-width="1" />
                        @php $startAngle = $endAngle; @endphp
                    @endif
                @endforeach
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="40" fill="white" />
            </svg>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 20px;">
            <table style="width: 100%;">
                @foreach($payments as $key => $value)
                    <tr>
                        <td>
                            <span style="display:inline-block; width:12px; height:12px; background:{{ $colors[$loop->index] }}; margin-right:5px;"></span>
                            {{ translate(str_replace('_payment', '', $key)) }}
                        </td>
                        <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $value), currencyCode: getCurrencyCode()) }}</td>
                    </tr>
                @endforeach
                <tr style="border-top:1px solid #ddd;">
                    <td><strong>{{ translate('total') }}</strong></td>
                    <td class="text-right"><strong>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $total), currencyCode: getCurrencyCode()) }}</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>
<table class="">
    <tr>
        <th class="content-position-y bg-light py-4 footer">
            <div class="d-flex justify-content-center gap-2">
                <div class="mb-2">
                    <i class="fa fa-phone"></i>
                    {{translate('phone')}}
                    : {{ $company_phone }}
                </div>
                <div class="mb-2">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    {{translate('email')}}
                    : {{ $company_email }}
                </div>
            </div>
            <div class="mb-2">
                {{url('/')}}
            </div>
            <div>
                {{translate('all_copy_right_reserved_©_'.date('Y').'_').$company_name}}
            </div>
        </th>
    </tr>
</table>
</body>
</html>