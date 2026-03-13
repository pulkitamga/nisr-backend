@php
    $isRtl = $isRtl ?? (app()->getLocale() === 'ar' || session('direction') === 'rtl');
    $dateRange = $fromDate->format('M d, Y') . ' - ' . $toDate->format('M d, Y');
    $hasData = isset($activationRowsForPdf) && count($activationRowsForPdf) > 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('activation_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        /* Header Styles with Logo - Green like CRM */
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
            font-size: 20px;
        }

        .header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 11px;
        }

        /* Clear float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI Metrics - Table layout for mPDF */
        .kpi-container {
            background-color: #f3f6fb;
            padding: 10px 5px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            table-layout: fixed;
        }

        .kpi-table td {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px !important;
            padding: 12px 10px;
            vertical-align: top;
            height: 55px;
            text-align: left;
        }

        .kpi-label {
            color: #5f6672;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            margin: 0 0 8px 0;
            text-align: center;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-align: left;
        }

<<<<<<< ahmed5
=======
        .kpi-value.percentage {
            color: #0f766e;
        }

        /* Chart Row - Table layout for mPDF */
        .chart-row {
            width: 100%;
            margin-bottom: 20px;
            display: block;
            overflow: hidden;
        }

        .chart-trend {
            width: 68%;
            float: left;
            margin-right: 2%;
        }

        .chart-stage {
            width: 30%;
            float: left;
        }

        .chart-col {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            background: white;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 5px;
            margin: 0 0 12px 0;
        }

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 170px;
        }

        /* Table Styles - EXACT same as CRM Insights */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 15px;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 10px 12px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 14px;
        }

>>>>>>> local
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #e5e7eb;
            font-weight: 600;
            padding: 8px 6px;
            text-align: center;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* No Data Message */
        .no-data-message {
            text-align: center;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            margin: 20px 0;
        }

        /* Footer - EXACT same as CRM Insights */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        /* RTL Support */
        @if ($isRtl)
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }

            .chart-trend {
                float: right;
                margin-right: 0;
                margin-left: 2%;
            }

            .chart-stage {
                float: right;
            }
        @endif
    </style>
</head>
<body>

    <!-- Modern Header with Logo - Green like CRM -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('activation_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $fromDate->format('M d, Y') }} - {{ $toDate->format('M d, Y') }}</p>
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
            @endphp
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px;">
            @elseif(file_exists($defaultLogoPath))
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                    alt="Logo" style="max-width:100px; max-height:50px;">
            @endif
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_activations') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int)($kpi['total_activations'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('activation_rate') }}</div>
                    <div class="kpi-value percentage"><strong>{{ number_format((float)($kpi['activation_rate'] ?? 0), 1) }}%</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('active_warranties') }}</div>
                    <div class="kpi-value"><strong>{{ number_format((int)($kpi['active_warranties'] ?? 0)) }}</strong></div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('avg_warranty_months') }}</div>
                    <div class="kpi-value"><strong>
                        {{ ($kpi['avg_warranty_months'] ?? null) !== null ? number_format((float)$kpi['avg_warranty_months'], 1) : translate('na') }}
                    </strong></div>
                </td>
            </tr>
        </table>
    </div>

<<<<<<< ahmed5
    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-label">{{ translate('total_activations') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['total_activations'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('activation_rate') }}</div>
                <div class="kpi-value">{{ number_format((float)($kpi['activation_rate'] ?? 0), 1) }}%</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('active_warranties') }}</div>
                <div class="kpi-value">{{ number_format((int)($kpi['active_warranties'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="kpi-label">{{ translate('avg_warranty_months') }}</div>
                <div class="kpi-value">
                    {{ ($kpi['avg_warranty_months'] ?? null) !== null ? number_format((float)$kpi['avg_warranty_months'], 1) : translate('na') }}
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
=======
    <!-- Chart Row: Activation Trend + Method Distribution side by side -->
    @if(!empty($trendChartImage) || !empty($methodChartImage))
        <div class="chart-row">
            @if(!empty($trendChartImage))
                <div class="chart-trend">
                    <div class="chart-col" style="margin-right: 10px;">
                        <div class="chart-title">{{ translate('activations_trend') }} ({{ $dateRange }})</div>
                        <img src="{{ $trendChartImage }}" class="chart-image" alt="Activations Trend" />
                    </div>
                </div>
            @endif

            @if(!empty($methodChartImage))
                <div class="chart-stage">
                    <div class="chart-col">
                        <div class="chart-title">{{ translate('activations_by_method') }} ({{ $dateRange }})</div>
                        <img src="{{ $methodChartImage }}" class="chart-image" alt="Activations by Method" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Row with TWO tables side by side: Method Breakdown and Top Products -->
    <div class="chart-row" style="margin-bottom: 20px;">
        <!-- Method Breakdown Table (left side) -->
        @if(!empty($methodBreakdown) && count($methodBreakdown) > 0)
            <div class="chart-trend">
                <div class="table-container" style="margin-top: 0; margin-right: 10px;">
                    <div class="table-header">
                        <h3>{{ translate('activation_method') }}</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>{{ translate('method') }}</th>
                                <th class="text-center">{{ translate('total') }}</th>
                                <th class="text-center">{{ translate('percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($methodBreakdown as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-center">{{ number_format((int)$row['count']) }}</td>
                                    <td class="text-center">{{ number_format((float)$row['percentage'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Top Products Table (right side) -->
        @if(!empty($topProducts) && count($topProducts) > 0)
            <div class="chart-stage">
                <div class="table-container" style="margin-top: 0;">
                    <div class="table-header">
                        <h3>{{ translate('top_products') }}</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ translate('product') }}</th>
                                <th class="text-center">{{ translate('total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td class="text-center">{{ number_format((int)$product->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Activations Details Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('activation_details') }} ({{ $dateRange }})</h3>
        </div>
        
        @if($hasData)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('serial') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('customer') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th>{{ translate('activation_method') }}</th>
                        <th>{{ translate('activation_date') }}</th>
                        <th>{{ translate('warranty_end') }}</th>
                        <th>{{ translate('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activationRowsForPdf as $warranty)
                        @php
                            $customerName = trim(
                                ((string)($warranty->user?->f_name ?? '')) . ' ' . ((string)($warranty->user?->l_name ?? ''))
                            );
                            if ($customerName === '') {
                                $customerName = $warranty->activated_by_name ?? '-';
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="value-ltr"><strong>{{ $warranty->serial_number }}</strong></td>
                            <td>{{ $warranty->product?->name ?? '-' }}</td>
                            <td>{{ $customerName }}</td>
                            <td>{{ $warranty->branch?->branch_name ?? '-' }}</td>
                            <td>{{ translate($warranty->activation_method ?: 'unknown') }}</td>
                            <td class="value-ltr">{{ optional($warranty->activation_date)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="value-ltr">{{ optional($warranty->end_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $warranty->status)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data-message">
                {{ translate('no_data_found_for_selected_period') }}
            </div>
        @endif
    </div>

     <!-- FOOTER -->
    <div style="
border-top:1px dashed #d1d5db;
margin-top:20px;
padding-top:8px;
font-size:9px;
color:#6b7280;
">
 
        <table width="100%">
>>>>>>> local
            <tr>
 
                <td width="20%" style="text-align:left; color:red;">
                    Page {PAGENO}
                </td>
 
                <td width="60%" style="text-align:center;">
                    Generated on: {{ now()->translatedFormat('j F Y, h:i A') }} | CRM insights report<br>
                    Generated by: <span style="color:red;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color:red;">{{ config('app.name') }}</span>
                </td>
 
                <td width="20%"></td>
 
            </tr>
<<<<<<< ahmed5
        </thead>
        <tbody>
            @forelse($methodBreakdown as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format((int)$row['count']) }}</td>
                    <td>{{ number_format((float)$row['percentage'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ translate('serial') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('customer') }}</th>
                <th>{{ translate('branch') }}</th>
                <th>{{ translate('activation_method') }}</th>
                <th>{{ translate('activation_date') }}</th>
                <th>{{ translate('warranty_end') }}</th>
                <th>{{ translate('status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activationRowsForPdf as $warranty)
                @php
                    $customerName = trim(
                        ((string)($warranty->user?->f_name ?? '')) . ' ' . ((string)($warranty->user?->l_name ?? ''))
                    );
                    if ($customerName === '') {
                        $customerName = $warranty->activated_by_name ?? '-';
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $warranty->serial_number }}</td>
                    <td>{{ $warranty->product?->name ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td>{{ $warranty->branch?->branch_name ?? '-' }}</td>
                    <td>{{ translate($warranty->activation_method ?: 'unknown') }}</td>
                    <td>{{ optional($warranty->activation_date)->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>{{ optional($warranty->end_date)->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ translate($warranty->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
=======
        </table>
 
    </div>
>>>>>>> local
</body>
</html>
