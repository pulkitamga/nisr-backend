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
 
        /* HEADER */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
 
        .header-content { float: left; width: 70%; }
        .logo-container { float: right; width: 25%; text-align: right; }
        .logo-container img { max-width: 80px; max-height: 50px; object-fit: contain; }
        .header-content h2 { margin:0 0 5px 0; font-size:16px; }
        .header-content p { margin:0; font-size:10px; opacity:0.9; }
 
        .clearfix::after { content:""; clear:both; display:table; }
 
        /* KPI CARDS */
        .kpi-container { background:#f3f6fb; padding:10px; border-radius:8px; margin-bottom:15px; }
        .kpi-table { width:100%; border-collapse: separate; border-spacing:8px; }
        .kpi-table td { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px; text-align:center; }
        .kpi-label { font-size:9px; color:#6b7280; text-transform:uppercase; margin-bottom:3px; }
        .kpi-value { font-size:16px; font-weight:bold; color:#111827; }
 
        /* CHART */
        .chart-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; background:#fff; margin-bottom:15px; }
        .chart-title { font-size:12px; font-weight:bold; margin-bottom:8px; color:#0f766e; border-bottom:2px solid #0f766e; padding-bottom:4px; }
        .chart-image { width:100%; height:auto; display:block; margin:0 auto; max-height:280px; object-fit:contain; }
 
        /* SUMMARY TABLE */
        .summary-table, .detail-table { width:100%; border-collapse:collapse; margin-bottom:15px; }
        .summary-table th, .detail-table th { background:#0f766e; color:white; padding:6px 4px; font-weight:600; text-align:center; }
        .summary-table td, .detail-table td { padding:5px; border:1px solid #cbd5e1; }
        .summary-table td:first-child { text-align:center; width:10%; }
        .summary-table td:nth-child(2) { text-align:left; width:60%; }
        .summary-table td:last-child { text-align:right; width:30%; }
        .summary-table tr:nth-child(even) { background:#f9fafb; }
        .text-end { text-align:right !important; }
 
        /* SECTION HEADING */
        .section-heading { margin-top:20px; margin-bottom:8px; font-size:12px; font-weight:bold; color:#0f766e; border-left:3px solid #0f766e; padding-left:8px; }
        .section-heading small { font-weight:normal; font-size:9px; color:#6b7280; }
 
        /* PAGE BREAK */
        .page-break { page-break-before: always; }
 
        /* FOOTER */
        .footer { margin-top:20px; text-align:center; color:#6b7280; font-size:8px; border-top:1px dashed #d1d5db; padding-top:6px; }
 
        /* RTL Support */
        @if (session('direction') === 'rtl')
            .header-content { float:right; text-align:right; }
            .logo-container { float:left; text-align:left; }
            .section-heading { border-left:none; border-right:3px solid #0f766e; padding-left:0; padding-right:10px; text-align:right; }
            .summary-table td:nth-child(2) { text-align:right; }
            .detail-table td:last-child, .detail-table th:last-child { text-align:left; }
            .text-end { text-align:left !important; }
        @endif
    </style>
</head>
<body>
 
    @php
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
            $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            if (file_exists($defaultLogoPath)) {
                $logoSrc = 'data:image/webp;base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }
        }
    @endphp
 
    <!-- HEADER -->
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
 
    <!-- CHART -->
    @if(!empty($chartImageExpense))
    <div class="chart-box">
        <div class="chart-title">{{ translate('expense_Trend') }} <small>({{ $dateRange }})</small></div>
        <img src="{{ $chartImageExpense }}" class="chart-image">
    </div>
    @endif
 
    <!-- SUMMARY TABLE - NEW PAGE -->
    <div class="page-break"></div>
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
                <td class="text-end">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>{{ translate('free_Delivery_Amount') }}</td>
                <td class="text-end">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>{{ translate('coupon_Discount_Amount') }}</td>
                <td class="text-end">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>{{ translate('free_Shipping_Over_Order_Amount_Discount') }}</td>
                <td class="text-end">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount'] ?? 0), currencyCode: getCurrencyCode()) }}</td>
            </tr>
        </tbody>
    </table>
 
    <!-- DETAIL TABLE - NEW PAGE -->
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
                <td class="text-end">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->discount_amount ?? 0), currencyCode: getCurrencyCode()) }}</td>
                <td>{{ $transaction->free_delivery_bearer ?? '-' }}</td>
                <td>{{ $transaction->coupon_discount_bearer ?? '-' }}</td>
                <td>{{ $transaction->created_at ? $transaction->created_at->format('d M Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
 
    <!-- FOOTER -->
    <div class="footer" style="text-align:center; margin-top:20px;">
        <div style="display:inline-block; text-align:center;">
            <div>
                {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} | {{ translate('expense_transaction_report') }}
            </div>
            <div>
                {{ translate('generated_by') }}: <span style="color:red;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span>
            </div>
            <div>
                <span style="color:red;">{{ config('app.name') }}</span>
            </div>
        </div>
    </div>
 
</body>
</html>