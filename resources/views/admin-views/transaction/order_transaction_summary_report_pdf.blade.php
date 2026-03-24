<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">

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

        /* CHART SECTIONS - Two column layout */
        .charts-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-box {
            flex: 1;
            min-width: 250px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fff;
        }

        /* For RTL support */
        @if (session('direction') === 'rtl')
            .charts-row {
                direction: rtl;
            }
        @endif

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

        /* SUMMARY TABLE */
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

        /* DETAIL TABLE */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-top: 20px;
        }

        .detail-table th {
            background: #0f766e;
            color: white;
            padding: 6px 4px;
            font-weight: 600;
            text-align: center;
        }

        .detail-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .detail-table td:last-child,
        .detail-table th:last-child {
            text-align: center;
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

    <!-- HEADER with Logo - SAME as Order Transaction report -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('expense_Transaction_Statement') }}</h2>
            <p>{{ translate('duration') }}: {{ $dateRange }}</p>
            <p>{{ translate('generated_on') }}: {{ now()->format('d M Y, h:i A') }}</p>
        </div>

        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                $logoSrc = '';
                $logoData = $data['company_web_logo'] ?? '';
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
                    <div class="kpi-label">{{ translate('total_Expense_Amount') }}</div>
                    <div class="kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense'] ?? 0), currencyCode: getCurrencyCode()) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('free_Delivery_Amount') }}</div>
                    <div class="kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery'] ?? 0), currencyCode: getCurrencyCode()) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('coupon_Discount_Amount') }}</div>
                    <div class="kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                    </div>
                </td>

                <td>
                    <div class="kpi-label">{{ translate('free_Shipping_Over_Order_Amount_Discount') }}</div>
                    <div class="kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

   <!-- CHART SECTION - TABLE BASED -->
<table width="100%" style="margin-bottom:20px;">
    <tr>
        <!-- Order Trend -->
        @if (!empty($chartImageOrder))
        <td width="50%" style="vertical-align: top; padding-right:10px;">
            <div class="chart-box">
                <div class="chart-title">
                    {{ translate('order_trend') }} <small>({{ $dateRange }})</small>
                </div>
                <img src="{{ $chartImageOrder }}" class="chart-image">
            </div>
        </td>
        @endif

        <!-- Payment Chart -->
        @if (!empty($chartImagePayment))
        <td width="50%" style="vertical-align: top; padding-left:10px;">
            <div class="chart-box">
                <div class="chart-title">
                    {{ translate('payment_distribution') }}
                </div>
                <img src="{{ $chartImagePayment }}" class="chart-image">
            </div>
        </td>
        @endif
    </tr>
</table>

    <!-- MAIN SUMMARY TABLE -->
    <div class="section-heading">
        {{ translate('expense_summary') }}
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
                <td>{{ translate('total_Expense_Amount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_expense'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>{{ translate('free_Delivery_Amount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_delivery'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>{{ translate('coupon_Discount_Amount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['coupon_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
            <tr>
                <td>4</td>
                <td>{{ translate('free_Shipping_Over_Order_Amount_Discount') }}</td>
                <td class="text-right">
                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['free_over_amount_discount'] ?? 0), currencyCode: getCurrencyCode()) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DETAILED TRANSACTIONS TABLE -->
    @if (!empty($expense_transactions) && count($expense_transactions) > 0)
        <div class="section-heading">
            {{ translate('transaction_details') }}
            <small>({{ translate('period') }}: {{ $dateRange }})</small>
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
                @foreach ($expense_transactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaction->order_id ?? '-' }}</td>
                        <td>{{ $transaction->coupon_code ?? '-' }}</td>
                        <td>{{ $transaction->discount_type ?? '-' }}</td>
                        <td class="text-right">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->discount_amount ?? 0), currencyCode: getCurrencyCode()) }}
                        </td>
                        <td>{{ $transaction->free_delivery_bearer ?? '-' }}</td>
                        <td>{{ $transaction->coupon_discount_bearer ?? '-' }}</td>
                        <td>{{ $transaction->created_at ? $transaction->created_at->format('d M Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- FOOTER - Green themed like Order Transaction report -->
    <div style="border-top:1px dashed #d1d5db;margin-top:20px;padding-top:8px;font-size:9px;color:#6b7280;">
        <table width="100%">
            <tr>
                <td width="20%" style="text-align:left; color:#0f766e;">
                    Page {PAGENO}
                </td>
                <td width="60%" style="text-align:center;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('expense_transaction_statement') }}<br>
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