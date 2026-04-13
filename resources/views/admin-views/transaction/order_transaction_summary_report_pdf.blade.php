@php
    $isRtl = session('direction') === 'rtl' || app()->getLocale() === 'ar';
    $isSingleVendorMode = getWebConfig(name: 'business_mode') !== 'multi';
    $transactionRows = collect($transactions)->filter(fn($transaction) => $transaction?->order)->values();
    $displayStatus = $status === 'all' ? translate('all_status') : translate($status);
    $displayCustomerInfo = $customer_info === 'all' ? translate('all_customer') : $customer_info;
    $displaySellerInfo = $seller_info === 'all' ? translate('all') : ($seller_info === 'inhouse' ? translate('inhouse') : $seller_info);
    $logoSrc = '';
    $logoFile = is_array($company_web_logo ?? null) ? ($company_web_logo['key'] ?? '') : ($company_web_logo ?? '');

    if (!$isSingleVendorMode && $logoFile) {
        $logoPath = storage_path('app/public/company/' . $logoFile);
        if (!file_exists($logoPath)) {
            $logoPath = public_path('storage/company/' . $logoFile);
        }

        if (file_exists($logoPath)) {
            $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
            $mime = $extension === 'svg' ? 'svg+xml' : $extension;
            $logoSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('order_Transaction_Statement') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; margin: 16px; direction: {{ $isRtl ? 'rtl' : 'ltr' }}; }
        .header { background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%); color: #fff; padding: 16px 20px; border-radius: 10px; margin-bottom: 18px; overflow: hidden; }
        .header-main { float: {{ $isRtl ? 'right' : 'left' }}; width: 72%; }
        .header-logo { float: {{ $isRtl ? 'left' : 'right' }}; width: 24%; text-align: {{ $isRtl ? 'left' : 'right' }}; }
        .header-logo img { max-width: 100px; max-height: 52px; object-fit: contain; }
        .clearfix::after { content: ""; display: table; clear: both; }
        .header h2 { margin: 0 0 6px; font-size: 18px; }
        .header p { margin: 0; font-size: 11px; opacity: .92; }
        .meta, .summary, .details { width: 100%; border-collapse: collapse; }
        .meta { margin-bottom: 18px; background: #f9fafb; }
        .meta td { border: 1px solid #e5e7eb; padding: 8px 12px; }
        .kpis { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 18px; }
        .kpis td { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; text-align: center; }
        .kpi-label { color: #6b7280; font-size: 9px; text-transform: uppercase; margin-bottom: 4px; }
        .kpi-value { color: #111827; font-size: 16px; font-weight: 700; }
        .charts { width: 100%; margin-bottom: 18px; }
        .chart-cell { vertical-align: top; width: 50%; }
        .chart-left { padding-{{ $isRtl ? 'left' : 'right' }}: 8px; }
        .chart-right { padding-{{ $isRtl ? 'right' : 'left' }}: 8px; }
        .chart-box { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #fff; }
        .chart-title { color: #0f766e; font-size: 13px; font-weight: 700; margin: 0 0 10px; padding-bottom: 6px; border-bottom: 2px solid #0f766e; }
        .chart-box img { width: 100%; max-height: 260px; object-fit: contain; }
        .chart-stats { margin-top: 10px; font-size: 9px; color: #374151; }
        .chart-stats div { margin-bottom: 4px; }
        .section-title { margin: 22px 0 10px; color: #0f766e; font-size: 13px; font-weight: 700; border-{{ $isRtl ? 'right' : 'left' }}: 3px solid #0f766e; padding-{{ $isRtl ? 'right' : 'left' }}: 10px; }
        .section-title small { color: #6b7280; font-size: 10px; font-weight: 400; }
        .summary th, .details th { background: #0f766e; color: #fff; padding: 8px 6px; font-weight: 600; text-align: center; }
        .summary td, .details td { border: 1px solid #cbd5e1; padding: 6px; text-align: center; vertical-align: top; }
        .summary td:last-child, .summary th:last-child, .details .amount { text-align: {{ $isRtl ? 'left' : 'right' }}; }
        .summary { font-size: 8px; margin-bottom: 18px; }
        .details { font-size: 7px; }
        .empty { border: 1px dashed #cbd5e1; border-radius: 10px; background: #f9fafb; color: #6b7280; text-align: center; padding: 18px; }
        .footer { margin-top: 18px; padding-top: 8px; border-top: 1px dashed #d1d5db; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="header-main">
            <h2>{{ translate('order_Transaction_Statement') }}</h2>
            <p>{{ translate('duration') }}: {{ $dateRange }}</p>
            <p>{{ translate('generated_on') }}: {{ now()->translatedFormat('d M Y, h:i A') }}</p>
        </div>
        @if (!$isSingleVendorMode && $logoSrc)
            <div class="header-logo">
                <img src="{{ $logoSrc }}" alt="{{ translate('logo') }}">
            </div>
        @endif
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ translate('customer_Info') }}</strong>: {{ $displayCustomerInfo }}</td>
            <td><strong>{{ translate('status') }}</strong>: {{ $displayStatus }}</td>
            @if (!$isSingleVendorMode)
                <td><strong>{{ translate('vendor_Info') }}</strong>: {{ $displaySellerInfo }}</td>
            @endif
        </tr>
    </table>

    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_Orders') }}</div>
                <div class="kpi-value">{{ $kpi_data['total_orders'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('in_House_Orders') }}</div>
                <div class="kpi-value">{{ $kpi_data['in_house_orders'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('in_House_Products') }}</div>
                <div class="kpi-value">{{ $kpi_data['total_in_house_products'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('total_Stores') }}</div>
                <div class="kpi-value">{{ $kpi_data['total_stores'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="charts">
        <tr>
            @if (!empty($chartImageOrder))
                <td class="chart-cell chart-left">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('order_Statistics') }}</div>
                        <img src="{{ $chartImageOrder }}" alt="{{ translate('order_Statistics') }}">
                    </div>
                </td>
            @endif
            @if (!empty($chartImagePayment))
                <td class="chart-cell chart-right">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('payment_statistics') }}</div>
                        <img src="{{ $chartImagePayment }}" alt="{{ translate('payment_statistics') }}">
                        <div class="chart-stats">
                            <div>{{ translate('cash_payments') }}: {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $payment_data['cash_payment'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                            <div>{{ translate('digital_payments') }}: {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $payment_data['digital_payment'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                            <div>{{ translate('wallet') }}: {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $payment_data['wallet_payment'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                            <div>{{ translate('offline_payments') }}: {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $payment_data['offline_payment'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                        </div>
                    </div>
                </td>
            @endif
        </tr>
    </table>

    <div class="section-title">{{ translate('order_summary') }} <small>({{ $dateRange }})</small></div>
    <table class="summary">
        <thead>
            <tr>
                <th style="width:8%;">{{ translate('SL') }}</th>
                <th style="width:62%;">{{ translate('details') }}</th>
                <th style="width:30%;">{{ translate('amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>1</td><td>{{ translate('total_Ordered_Product_Price') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_ordered_product_price'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>2</td><td>{{ translate('total_Product_Discount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_product_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>3</td><td>{{ translate('total_Coupon_Discount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>4</td><td>{{ translate('total_Discounted_Amount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_discounted_amount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>5</td><td>{{ translate('total') }} {{ translate('VAT/TAX') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_tax'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>6</td><td>{{ translate('total_Delivery_Charge') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_delivery_charge'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>7</td><td>{{ translate('total_Deliveryman_incentive') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_deliveryman_incentive'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>8</td><td>{{ translate('total_Order_Amount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_order_amount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>9</td><td>{{ translate('admin_discount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>10</td><td>{{ translate('seller_discount') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_seller_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>11</td><td>{{ translate('admin_commission') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_commission'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>12</td><td>{{ translate('admin_net_income') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_admin_net_income'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
            <tr><td>13</td><td>{{ translate('seller_net_income') }}</td><td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary_data['total_seller_net_income'] ?? 0), currencyCode: getCurrencyCode()) }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">{{ translate('transaction_details') }} <small>({{ $dateRange }})</small></div>
    @if ($transactionRows->isNotEmpty())
        <table class="details">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('order_id') }}</th>
                    <th>{{ translate('shop_name') }}</th>
                    <th>{{ translate('customer_name') }}</th>
                    <th class="amount">{{ translate('amount') }}</th>
                    <th class="amount">{{ translate('admin_commission') }}</th>
                    <th class="amount">{{ translate('admin_net_income') }}</th>
                    <th>{{ translate('payment_method') }}</th>
                    <th>{{ translate('payment_Status') }}</th>
                    <th>{{ translate('status') }}</th>
                    <th>{{ translate('date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactionRows as $index => $transaction)
                    @php
                        $adminCouponDiscount = ($transaction->order->coupon_discount_bearer == 'inhouse' && $transaction->order->discount_type == 'coupon_discount') ? $transaction->order->discount_amount : 0;
                        $adminShippingDiscount = ($transaction->order->free_delivery_bearer == 'admin' && $transaction->order->is_shipping_free) ? $transaction->order->extra_discount : 0;
                        $adminNetIncome = 0;

                        if ($transaction->seller_is == 'admin') {
                            $adminNetIncome += $transaction->order_amount + $transaction->tax;
                        }
                        if (isset($transaction->order->deliveryMan) && $transaction->order->deliveryMan->seller_id == 0) {
                            $adminNetIncome += $transaction->delivery_charge;
                        }
                        $adminNetIncome += $transaction->admin_commission;

                        if ($transaction->order->delivery_type == 'self_delivery' && ($transaction->order->shipping_responsibility == 'inhouse_shipping' || $transaction->order->seller_is == 'admin') && $transaction->order->delivery_man_id) {
                            $adminNetIncome -= $transaction->order->deliveryman_charge;
                        }

                        if ($transaction->seller_is == 'seller') {
                            if ($transaction->order->shipping_responsibility == 'inhouse_shipping') {
                                $adminNetIncome -= $transaction->order->coupon_discount_bearer == 'inhouse' ? $adminCouponDiscount : 0;
                                $adminNetIncome += ($transaction->order->coupon_discount_bearer == 'seller' && isset($transaction->order->coupon) && $transaction->order->coupon->coupon_type == 'free_delivery') ? $transaction->order->discount_amount : 0;
                                $adminNetIncome += $transaction->order->free_delivery_bearer == 'seller' ? $transaction->order->extra_discount : 0;
                            } elseif ($transaction->order->shipping_responsibility == 'sellerwise_shipping') {
                                $adminNetIncome -= $transaction->order->coupon_discount_bearer == 'inhouse' ? $adminCouponDiscount : 0;
                                $adminNetIncome -= $transaction->order->free_delivery_bearer == 'admin' ? $adminShippingDiscount : 0;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaction->order_id }}</td>
                        <td>
                            @if ($transaction->seller_is == 'admin')
                                {{ translate('inhouse') }}
                            @elseif(isset($transaction->seller?->shop?->name))
                                {{ $transaction->seller->shop->name }}
                            @else
                                {{ translate('not_found') }}
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
                        <td class="amount">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->order->order_amount ?? 0), currencyCode: getCurrencyCode()) }}</td>
                        <td class="amount">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->admin_commission ?? 0), currencyCode: getCurrencyCode()) }}</td>
                        <td class="amount">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $adminNetIncome), currencyCode: getCurrencyCode()) }}</td>
                        <td>{{ translate($transaction->order->payment_method ?? 'not_found') }}</td>
                        <td>{{ translate($transaction->order->payment_status ?? 'not_found') }}</td>
                        <td>{{ translate($transaction->status ?? 'not_found') }}</td>
                        <td>{{ $transaction->updated_at?->translatedFormat('d M Y') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">{{ translate('no_data_found') }}</div>
    @endif

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="20%" style="text-align:{{ $isRtl ? 'right' : 'left' }}; color:#0f766e;">Page {PAGENO}</td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }}<br>
                    {{ translate('order_Transaction_Statement') }}
                    @if (!$isSingleVendorMode)
                        <br>{{ translate('phone') }}: {{ $company_phone }} | {{ translate('email') }}: {{ $company_email }}
                    @endif
                </td>
                <td width="20%"></td>
            </tr>
        </table>
    </div>
</body>
</html>
