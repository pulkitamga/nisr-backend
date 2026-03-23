<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('Expense Transaction Statement') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 15px;
            direction: {{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }};
            text-align: {{ session('direction') ?? (app()->getLocale() === 'ar' ? 'right' : 'left') }};
        }

        /* HEADER - Green gradient */
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
            max-width: 80px;
            max-height: 50px;
            width: auto;
            height: auto;
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

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI CARDS */
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

        /* CHART */
        .chart-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            margin-bottom: 20px;
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
            max-height: 300px;
            object-fit: contain;
        }

        /* SUMMARY TABLE */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table th {
            background: #0f766e;
            color: white;
            padding: 10px 8px;
            font-weight: 600;
            text-align: center;
        }

        .summary-table td {
            padding: 8px;
            border: 1px solid #cbd5e1;
        }

        .summary-table td:first-child {
            text-align: center;
            width: 10%;
        }

        .summary-table td:nth-child(2) {
            text-align: left;
            width: 60%;
        }

        .summary-table td:last-child {
            text-align: right;
            width: 30%;
        }

        .summary-table tr:nth-child(even) {
            background: #f9fafb;
        }

        /* DETAIL TABLE */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 20px;
        }

        .detail-table th {
            background: #0f766e;
            color: white;
            padding: 8px 6px;
            font-weight: 600;
            text-align: center;
        }

        .detail-table td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .detail-table td:last-child,
        .detail-table th:last-child {
            text-align: right;
        }

        .text-right {
            text-align: right !important;
        }

        /* SECTION HEADING */
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
            margin-top: 25px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 10px;
        }

        /* RTL Support */
        @if (session('direction') === 'rtl')
            .header-content { float: right; text-align: right; }
            .logo-container { float: left; text-align: left; }
            .section-heading {
                border-left: none;
                border-right: 3px solid #0f766e;
                padding-left: 0;
                padding-right: 10px;
                text-align: right;
            }
            .summary-table td:nth-child(2) {
                text-align: right;
            }
            .detail-table td:last-child,
            .detail-table th:last-child {
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
        // Logo generation
        $logoSrc = '';
        $logoArray = $data['company_web_logo'] ?? [];
        $logoPath = null;
        
        if (is_array($logoArray) && isset($logoArray['key'])) {
            $logoPath = storage_path('app/public/company/' . $logoArray['key']);
        } elseif (is_string($logoArray) && !empty($logoArray)) {
            $logoPath = storage_path('app/public/company/' . $logoArray);
        }

        if (isset($logoPath) && file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
            $mime = $extension == 'svg' ? 'svg+xml' : $extension;
            $logoSrc = 'data:image/' . $mime . ';base64,' . $logoData;
        } else {
            $logoPathPublic = public_path('storage/company/' . (is_array($logoArray) ? ($logoArray['key'] ?? '') : $logoArray));
            if (file_exists($logoPathPublic)) {
                $logoData = base64_encode(file_get_contents($logoPathPublic));
                $extension = pathinfo($logoPathPublic, PATHINFO_EXTENSION);
                $mime = $extension == 'svg' ? 'svg+xml' : $extension;
                $logoSrc = 'data:image/' . $mime . ';base64,' . $logoData;
            }
        }
        
        // Default logo fallback
        if (empty($logoSrc)) {
            $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            if (file_exists($defaultLogoPath)) {
                $logoSrc = 'data:image/webp;base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }
        }
        
        $companyName = $data['company_name'] ?? config('app.name');
    @endphp

    <!-- HEADER with Logo -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('expense_Transaction_Statement') }}</h2>
            <p>{{ translate('duration') }}: {{ $dateRange }}</p>
            <p>{{ translate('generated_on') }}: {{ now()->format('d M Y, h:i A') }}</p>
        </div>
        @if($logoSrc)
        <div class="logo-container">
            <img src="{{ $logoSrc }}" alt="{{ translate('logo') }}">
        </div>
        @endif
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-container">
        <table class="kpi-table">
             <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_Expense_Amount') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('free_Delivery_Amount') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('coupon_Discount_Amount') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('free_Shipping_Over_Order_Amount_Discount') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</div>
                </td>
             </tr>
        </table>
    </div>

    <!-- CHART SECTION -->
    @if(!empty($chartImageExpense))
    <div class="chart-box">
        <div class="chart-title">{{ translate('expense_Trend') }} <small>({{ $dateRange }})</small></div>
        <img src="{{ $chartImageExpense }}" class="chart-image">
    </div>
    @endif

    <!-- SUMMARY TABLE -->
    <div class="section-heading">
        {{ translate('expense_summary') }}
        <small>({{ $dateRange }})</small>
    </div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>{{ translate('SL') }}</th>
                <th>{{ translate('details') }}</th>
                <th>{{ translate('amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ translate('total_Expense_Amount') }}</td>
                <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>{{ translate('free_Delivery_Amount') }}</td>
                <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>{{ translate('coupon_Discount_Amount') }}</td>
                <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>{{ translate('free_Shipping_Over_Order_Amount_Discount') }}</td>
                <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- DETAILED TRANSACTIONS TABLE -->
    @if(!empty($expense_transactions) && count($expense_transactions) > 0)
    <div class="section-heading">
        {{ translate('transaction_details') }}
        <small>({{ $dateRange }})</small>
    </div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('order_id') }}</th>
                <th>{{ translate('coupon_code') }}</th>
                <th>{{ translate('discount_type') }}</th>
                <th>{{ translate('discount_amount') }}</th>
                <th>{{ translate('free_delivery_bearer') }}</th>
                <th>{{ translate('coupon_discount_bearer') }}</th>
                <th>{{ translate('date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expense_transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->order_id ?? '-' }}</td>
                <td>{{ $transaction->coupon_code ?? '-' }}</td>
                <td>{{ $transaction->discount_type ?? '-' }}</td>
                <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->discount_amount ?? 0), currencyCode: getCurrencyCode()) }}</td>
                <td>{{ $transaction->free_delivery_bearer ?? '-' }}</td>
                <td>{{ $transaction->coupon_discount_bearer ?? '-' }}</td>
                <td>{{ $transaction->created_at ? $transaction->created_at->format('d M Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <div>
            {{ translate('phone') }}: {{ $data['company_phone'] }} | 
            {{ translate('email') }}: {{ $data['company_email'] }}
        </div>
        <div>{{ url('/') }}</div>
        <div>{{ translate('all_copy_right_reserved_©_' . date('Y') . '_') . $data['company_name'] }}</div>
    </div>

</body>
</html>