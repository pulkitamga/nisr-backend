<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Expense Transaction Statement - '.$data['duration'] }}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/admin/order-transaction.css') }}">
</head>

<body>
<table class="content-position">
    <tr>
        <td>
            <table class="bs-0">
                <tr>
                    <th class="h3 p-0 text-left">
                        {{translate('expense_Transaction_Statement')}}
                    </th>
                    <th class="p-0 text-right">
                        <img class="logo" src="{{getStorageImages(path:getWebConfig('company_web_logo'),type: 'backend-logo')}}"
                             alt="">
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
                        <span class="bold black p-0 text-left">{{translate('duration')}}</span> :
                        <span class="p-0 p-3 text-capitalize">{{ $data['duration'] }}</span>
                    </td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="p-0 text-left">
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
                    <td style="background-color: #0177CD !important; color: white; font-weight: bold;text-align:center">{{translate('SL')}}</td>
                    <td style="background-color: #0177CD !important; color: white; font-weight: bold">{{translate('details')}}</td>
                    <td class="text-right"
                        style="background-color: #0177CD !important; color: white; font-weight: bold">{{translate('amount')}}</td>
                </tr>
                <tr>
                    <td class="text-center">1</td>
                    <td>{{translate('total_Expense_Amount')}}</td>
                    <td class="text-right">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>{{translate('free_Delivery_Amount')}}</td>
                    <td class="text-right">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td>{{translate('coupon_Discount_Amount')}}</td>
                    <td class="text-right">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center">4</td>
                    <td>{{translate('free_Shipping_Over_Order_Amount_Discount')}}</td>
                    <td class="text-right">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount']), currencyCode: getCurrencyCode()) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

{{-- Expense Statistics Chart (Bar Chart) --}}
<table class="content-position" style="margin-top: 20px;">
    <tr>
        <th colspan="2" class="text-left black bold" style="padding: 10px 0;">
            <h3>{{ translate('expense_Statistics') }}</h3>
        </th>
    </tr>
    <tr>
        <td colspan="2">
            @php
                $chartData = $expense_transaction_chart['discount_amount'] ?? [];
                $maxValue = !empty($chartData) ? max($chartData) : 1;
                $barWidth = 30;
                $chartHeight = 150;
            @endphp
            <svg width="100%" height="200" viewBox="0 0 {{ count($chartData) * ($barWidth + 10) + 50 }} 200" preserveAspectRatio="xMidYMid meet">
                @foreach($chartData as $label => $value)
                    @php
                        $barHeight = ($value / $maxValue) * $chartHeight;
                        $x = 30 + $loop->index * ($barWidth + 15);
                        $y = 180 - $barHeight;
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" fill="#FF6B6B" />
                    <text x="{{ $x + $barWidth/2 }}" y="195" font-size="10" text-anchor="middle">{{ $label }}</text>
                    <text x="{{ $x + $barWidth/2 }}" y="{{ $y - 5 }}" font-size="8" text-anchor="middle">{{ number_format($value, 2) }}</text>
                @endforeach
                <line x1="20" y1="180" x2="{{ count($chartData) * ($barWidth + 15) + 30 }}" y2="180" stroke="black" stroke-width="1" />
                <line x1="20" y1="20" x2="20" y2="180" stroke="black" stroke-width="1" />
            </svg>
        </td>
    </tr>
</table>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<table class="">
    <tr>
        <th class="content-position-y bg-light py-4 footer">
            <div class="d-flex justify-content-center gap-2">
                <div class="mb-2">
                    <i class="fa fa-phone"></i>
                    {{translate('phone')}}
                    : {{ $data['company_phone'] }}
                </div>
                <div class="mb-2">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    {{translate('email')}}
                    : {{ $data['company_email'] }}
                </div>
            </div>
            <div class="mb-2">
                {{url('/')}}
            </div>
            <div>
                {{translate('all_copy_right_reserved_©_'.date('Y').'_').$data['company_name']}}
            </div>
        </th>
    </tr>
</table>
</body>
</html>