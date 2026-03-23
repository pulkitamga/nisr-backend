<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('Order Transaction Statement') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 15px;
            direction: {{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }};
            text-align: {{ session('direction') ?? (app()->getLocale() === 'ar' ? 'right' : 'left') }};
        }

        /* Force page breaks */
        .page-break {
            page-break-before: always;
            margin-top: 20px;
        }

        /* HEADER - Green gradient like Inhouse report */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .header-content {
            float: left;
            width: 70%;
        }

        .logo-container {
            float: right;
            width: 25%;
            text-align: right;
        }

       .logo-container img {
    max-width: 100px;
    max-height: 50px;
    object-fit: contain;
}

        .header-content h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: white;
        }

        .header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 11px;
            color: white;
        }

        /* Clear float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI */
        .kpi-container {
            background: #f3f6fb;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .kpi-table td {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .kpi-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .kpi-meta {
            font-size: 9px;
            color: #4b5563;
            margin-top: 4px;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }

        .info-table td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
        }

        /* CHART SECTIONS */
        .chart-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .chart-row-table td {
            vertical-align: top;
            padding: 0 8px;
        }

        .chart-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            height: 100%;
        }

        .chart-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
        }

        .chart-image {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 280px;
            object-fit: contain;
        }

        /* TABLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 15px;
        }

        .data-table th {
            background: #0f766e;
            color: white;
            padding: 8px 6px;
            font-weight: 600;
            text-align: center;
        }

        .data-table td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .data-table td:last-child,
        .data-table th:last-child {
            text-align: right;
        }

        .text-right {
            text-align: right !important;
        }

        /* BADGES */
        .badge-soft-success {
            background: #22c55e20;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 500;
        }

        .badge-soft-warning {
            background: #f59e0b20;
            color: #b45309;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 500;
        }

        /* SECTION HEADING - Green like Inhouse report */
        .section-heading {
            margin-top: 25px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            border-left: 3px solid #0f766e;
            padding-left: 10px;
        }

        .section-heading small {
            font-weight: normal;
            font-size: 10px;
            color: #6b7280;
        }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        /* RTL Support */
        @if (session('direction') === 'rtl')
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }

            .section-heading {
                border-left: none;
                border-right: 3px solid #0f766e;
                padding-left: 0;
                padding-right: 10px;
                text-align: right;
            }

            .data-table td:last-child,
            .data-table th:last-child {
                text-align: left;
            }

            .text-right {
                text-align: left !important;
            }
        @endif
    </style>
</head>

<body>

    @php
        $isRtl = session('direction') === 'rtl' || app()->getLocale() === 'ar';
    @endphp

    <!-- HEADER with Logo - SAME as Inhouse report -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('order_Transaction_Statement') }}</h2>
            <p>{{ translate('report_period') }}: {{ $dateRange }}</p>
        </div>

        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                $logoSrc = '';
                $logoData = $company_web_logo ?? '';
                $filename = is_array($logoData) ? $logoData['key'] ?? '' : (is_string($logoData) ? $logoData : '');

                if (!empty($filename)) {
                    $logoPath = storage_path('app/public/company/' . $filename);
                    if (!file_exists($logoPath)) {
                        $logoPath = public_path('storage/company/' . $filename);
                    }
                    if (file_exists($logoPath)) {
                        $imageData = file_get_contents($logoPath);
                        if ($imageData !== false) {
                            $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                            $logoSrc = 'data:image/' . $mime . ';base64,' . base64_encode($imageData);
                        }
                    }
                }
            @endphp

            @if ($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ translate('logo') }}"
                    style="max-width:100px; max-height:50px; object-fit:contain;">
            @elseif(file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    style="max-width:100px; max-height:50px; object-fit:contain;">
            @endif
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_Orders') }}</div>
                    <div class="kpi-value">{{ number_format($kpi_data['total_orders'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('in_House_Orders') }}</div>
                    <div class="kpi-value">{{ number_format($kpi_data['in_house_orders'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('vendor_Orders') }}</div>
                    <div class="kpi-value">{{ number_format($kpi_data['seller_orders'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('total_In_House_Products') }}</div>
                    <div class="kpi-value">{{ number_format($kpi_data['total_in_house_products'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('total_Stores') }}</div>
                    <div class="kpi-value">{{ number_format($kpi_data['total_stores'] ?? 0) }}</div>
                </td>
            </tr>
        </table>
    </div>


    <!-- CHARTS ROW (two columns side by side) -->
    @if (!empty($chartImageOrder) || !empty($chartImagePayment))
        <table class="chart-row-table" cellpadding="0" cellspacing="0">
            <tr>
                @if (!empty($chartImageOrder))
                    <td width="50%">
                        <div class="chart-box">
                            <div class="chart-title">{{ translate('order_Statistics') }}
                                <small>({{ $dateRange }})</small></div>
                            <img src="{{ $chartImageOrder }}" class="chart-image">
                        </div>
                    </td>
                @endif
                @if (!empty($chartImagePayment))
                    <td width="50%">
                        <div class="chart-box">
                            <div class="chart-title">{{ translate('payment_Statistics') }}
                                <small>({{ $dateRange }})</small></div>
                            <img src="{{ $chartImagePayment }}" class="chart-image">
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    <!-- MAIN SUMMARY TABLE -->
    <div class="section-heading">
        {{ translate('summary') }}
        <small>({{ $dateRange }})</small>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">{{ translate('SL') }}</th>
                <th style="width: 60%;">{{ translate('details') }}</th>
                <th style="width: 30%;">{{ translate('amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ translate('total_Ordered_Product_Price') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_ordered_product_price'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>{{ translate('total_Product_Discount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_product_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>{{ translate('total_Coupon_Discount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>4</td>
                <td>{{ translate('total_Discounted_Amount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_discounted_amount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>5</td>
                <td>{{ translate('total_VAT/TAX') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_tax'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>6</td>
                <td>{{ translate('total_Delivery_Charge') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_delivery_charge'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>7</td>
                <td>{{ translate('total_Deliveryman_incentive') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_deliveryman_incentive'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr style="background: #f0f9f9; font-weight: bold;">
                <td>8</td>
                <td>{{ translate('total_Order_Amount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_order_amount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ADDITIONAL INFORMATION TABLE -->
    <div class="section-heading">
        {{ translate('additional_information') }}
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">{{ translate('additional_information') }}</th>
                <th style="width: 30%;">{{ translate('totals') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ translate('admin_Discount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>{{ translate('vendor_Discount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_seller_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>{{ translate('admin_Commission') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_commission'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr style="background: #f0f9f9; font-weight: bold;">
                <td>{{ translate('admin_Net_Income') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_net_income'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>{{ translate('vendor_Net_Income') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_seller_net_income'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DETAILED TRANSACTIONS TABLE -->
    <div class="section-heading">
        {{ translate('transaction_details') }}
        <small>({{ translate('period') }}: {{ $dateRange }})</small>
    </div>
    <table class="data-table" style="font-size: 7px;">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('order_id') }}</th>
                <th>{{ translate('shop_name') }}</th>
                <th>{{ translate('customer_name') }}</th>
                <th>{{ translate('total_product_amount') }}</th>
                <th>{{ translate('product_discount') }}</th>
                <th>{{ translate('coupon_discount') }}</th>
                <th>{{ translate('discounted_amount') }}</th>
                <th>{{ translate('VAT/TAX') }}</th>
                <th>{{ translate('shipping_charge') }}</th>
                <th>{{ translate('order_amount') }}</th>
                <th>{{ translate('delivered_by') }}</th>
                <th>{{ translate('deliveryman_incentive') }}</th>
                <th>{{ translate('admin_discount') }}</th>
                <th>{{ translate('admin_commission') }}</th>
                <th>{{ translate('admin_net_income') }}</th>
                <th>{{ translate('payment_method') }}</th>
                <th>{{ translate('payment_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $index => $transaction)
                @if ($transaction->order)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaction->order_id }}</td>
                        <td>
                            @if ($transaction['seller_is'] == 'admin')
                                {{ getWebConfig('company_name') }}
                            @else
                                {{ $transaction->seller->shop->name ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if (!$transaction->order->is_guest && isset($transaction->customer))
                                {{ $transaction->customer->f_name }} {{ $transaction->customer->l_name }}
                            @elseif($transaction->order->is_guest)
                                {{ translate('guest_customer') }}
                            @else
                                {{ translate('not_found') }}
                            @endif
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->orderDetails[0]?->order_details_sum_price ?? 0), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->orderDetails[0]?->order_details_sum_discount ?? 0), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->order->discount_amount ?? 0), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: ($transaction->orderDetails[0]?->order_details_sum_price ?? 0) - ($transaction->orderDetails[0]?->order_details_sum_discount ?? 0) - (isset($transaction->order->coupon) && $transaction->order->coupon->coupon_type != 'free_delivery' ? $transaction->order->discount_amount : 0)), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction['tax']), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->order->shipping_cost), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->order->order_amount), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td>{{ $transaction['delivered_by'] }}</td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->order->delivery_type == 'self_delivery' && $transaction->order->delivery_man_id ? $transaction->order->deliveryman_charge : 0), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            @php
                                $admin_coupon_discount =
                                    $transaction->order->coupon_discount_bearer == 'inhouse' &&
                                    $transaction->order->discount_type == 'coupon_discount'
                                        ? $transaction->order->discount_amount
                                        : 0;
                                $admin_shipping_discount =
                                    $transaction->order->free_delivery_bearer == 'admin' &&
                                    $transaction->order->is_shipping_free
                                        ? $transaction->order->extra_discount
                                        : 0;
                            @endphp
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $admin_coupon_discount + $admin_shipping_discount), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction['admin_commission']), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td class="text-right">
                            @php
                                $admin_net_income = 0;
                                if ($transaction['seller_is'] == 'admin') {
                                    $admin_net_income += $transaction['order_amount'] + $transaction['tax'];
                                }
                                if (
                                    isset($transaction->order->deliveryMan) &&
                                    $transaction->order->deliveryMan->seller_id == 0
                                ) {
                                    $admin_net_income += $transaction['delivery_charge'];
                                }
                                $admin_net_income += $transaction['admin_commission'];

                                if (
                                    $transaction->order->delivery_type == 'self_delivery' &&
                                    ($transaction->order->shipping_responsibility == 'inhouse_shipping' ||
                                        $transaction->order->seller_is == 'admin')
                                ) {
                                    $admin_net_income -= $transaction->order->deliveryman_charge;
                                }

                                if ($transaction['seller_is'] == 'seller') {
                                    if ($transaction->order->shipping_responsibility == 'inhouse_shipping') {
                                        $admin_net_income -=
                                            $transaction->order->coupon_discount_bearer == 'inhouse'
                                                ? $admin_coupon_discount
                                                : 0;
                                        $admin_net_income +=
                                            $transaction->order->coupon_discount_bearer == 'seller' &&
                                            isset($transaction->order->coupon) &&
                                            $transaction->order->coupon->coupon_type == 'free_delivery'
                                                ? $seller_coupon_discount
                                                : 0;
                                        $admin_net_income +=
                                            $transaction->order->free_delivery_bearer == 'seller'
                                                ? $seller_shipping_discount
                                                : 0;
                                    } elseif ($transaction->order->shipping_responsibility == 'sellerwise_shipping') {
                                        $admin_net_income -=
                                            $transaction->order->coupon_discount_bearer == 'inhouse'
                                                ? $admin_coupon_discount
                                                : 0;
                                        $admin_net_income -=
                                            $transaction->order->free_delivery_bearer == 'admin'
                                                ? $admin_shipping_discount
                                                : 0;
                                    }
                                }
                            @endphp
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $admin_net_income), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td>{{ str_replace('_', ' ', $transaction['payment_method']) }}</td>
                        <td>
                            <span
                                class="{{ $transaction['status'] == 'disburse' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                                {{ translate(str_replace('_', ' ', $transaction['status'])) }}
                            </span>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER - Green themed like Inhouse report -->
    <div style="border-top:1px dashed #d1d5db;margin-top:20px;padding-top:8px;font-size:9px;color:#6b7280;">
        <table width="100%">
            <tr>
                <td width="20%" style="text-align:left; color:#0f766e;">
                    Page {PAGENO}
                </td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('order_transaction_statement') }}<br>
                    {{ translate('generated_by') }}: <span
                        style="color:#0f766e;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color:#0f766e;">{{ config('app.name') }}</span>
                </td>
                <td width="20%"></td>
            </tr>
        </table>
    </div>

</body>

</html>
