@php
    $isRtl = session('direction') === 'rtl' || app()->getLocale() === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('inhouse_product_sale_report') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        /* Force page breaks */
        .page-break {
            page-break-before: always;
            margin-top: 20px;
        }
        
        /* HEADER */
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

        /* CHART SECTIONS - Using table for reliable layout */
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
        }
        
        /* Chart sizing */
        .row1-chart-img {
            max-height: 300px;
            width: 100%;
            object-fit: contain;
        }
        
        .row2-chart-img {
            max-height: 350px;
            width: 100%;
            object-fit: contain;
        }
        
        .row3-chart-img {
            max-height: 400px;
            width: 100%;
            object-fit: contain;
        }

        /* TABLE */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 25px;
            overflow: hidden;
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
        }
        
        .table-header h4 {
            margin: 0;
            font-size: 13px;
            color: white;
        }

        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .matrix-table th {
            background: #e5e7eb;
            padding: 8px 6px;
            font-weight: 600;
        }

        .matrix-table th,
        .matrix-table td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
        }
        
        .matrix-table td:last-child,
        .matrix-table th:last-child {
            text-align: right;
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
        @if ($isRtl)
            .header-content { float: right; text-align: right; }
            .logo-container { float: left; text-align: left; }
            .matrix-table td:last-child,
            .matrix-table th:last-child {
                text-align: left;
            }
        @endif
    </style>
</head>

<body>

    <!-- HEADER with Logo - Same as CRM report -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('inhouse_product_sale_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $filters['from'] }} - {{ $filters['to'] }}</p>
            <p>{{ translate('generated_on') }}: {{ now()->format('d M Y, h:i A') }}</p>
            @if (!empty($filters['states'] ?? []))
                <p>{{ translate('state') }}: {{ implode(', ', $filters['states']) }}</p>
            @endif
            @if (!empty($filters['cities'] ?? []))
                <p>{{ translate('city') }}: {{ implode(', ', $filters['cities']) }}</p>
            @endif
            @if (!empty($filters['areas'] ?? []))
                <p>{{ translate('area') }}: {{ implode(', ', $filters['areas']) }}</p>
            @endif
            @if ($locationFiltersApplied ?? false)
                <p>{{ translate('wholesale_is_excluded_when_retail_address_filters_are_applied') }}</p>
            @endif
        </div>
        <div class="logo-container">
            @php
                $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp');
                $logoSrc = '';
                $logoData = $company_web_logo ?? '';
                $filename = is_array($logoData) ? ($logoData['key'] ?? '') : (is_string($logoData) ? $logoData : '');
                
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
            
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ translate('logo') }}" style="max-width:100px; max-height:50px; object-fit:contain;">
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
                    <div class="kpi-label">{{ translate('total_sales') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['total_amount']), currencyCode: getCurrencyCode()) }}</div>
                    <div class="kpi-meta">{{ translate('qty') }}: {{ $summary['total_qty'] }}</div>
                 </td>
                 <td>
                    <div class="kpi-label">{{ translate('POS') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['pos_amount']), currencyCode: getCurrencyCode()) }}</div>
                    <div class="kpi-meta">{{ translate('qty') }}: {{ $summary['pos_qty'] }}</div>
                 </td>
                 <td>
                    <div class="kpi-label">{{ translate('online') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['online_amount']), currencyCode: getCurrencyCode()) }}</div>
                    <div class="kpi-meta">{{ translate('qty') }}: {{ $summary['online_qty'] }}</div>
                 </td>
                 <td>
                    <div class="kpi-label">{{ translate('wholesale') }}</div>
                    <div class="kpi-value">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['wholesale_amount']), currencyCode: getCurrencyCode()) }}</div>
                    <div class="kpi-meta">{{ translate('qty') }}: {{ $summary['wholesale_qty'] }}</div>
                 </td>
              </tr>
         </table>
    </div>

    <!-- ROW 1: Sales by Date (66%) + Channel Mix (34%) -->
    <table class="chart-row-table" cellpadding="0" cellspacing="0">
         <tr>
            <td width="66%">
                <div class="chart-box">
                    <div class="chart-title">{{ translate('sales_by_date') }}</div>
                    @if(!empty($chartImages['trend']))
                        <img src="{{ $chartImages['trend'] }}" class="chart-image row1-chart-img">
                    @endif
                </div>
             </td>
            <td width="34%">
                <div class="chart-box">
                    <div class="chart-title">{{ translate('channel_mix') }}</div>
                    @if(!empty($chartImages['channel']))
                        <img src="{{ $chartImages['channel'] }}" class="chart-image row1-chart-img">
                    @endif
                </div>
             </td>
         </tr>
     </table>

    <!-- PAGE 2: ROW 2 (Branch & Sales Type + Sales Type & Product) -->
    <div class="page-break">
        <table class="chart-row-table" cellpadding="0" cellspacing="0">
             <tr>
                <td width="50%">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('branch_and_sales_type') }}</div>
                        @if(!empty($chartImages['branch_type']))
                            <img src="{{ $chartImages['branch_type'] }}" class="chart-image row2-chart-img">
                        @endif
                    </div>
                 </td>
                <td width="50%">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('sales_type_and_product') }}</div>
                        @if(!empty($chartImages['product_type']))
                            <img src="{{ $chartImages['product_type'] }}" class="chart-image row2-chart-img">
                        @endif
                    </div>
                 </td>
             </tr>
         </table>
    </div>

    <!-- PAGE 3: ROW 3 (Branch & Product - Full Width) -->
    <div class="page-break">
        <div class="chart-box">
            <div class="chart-title">{{ translate('branch_and_product') }}</div>
            @if(!empty($chartImages['branch_product']))
                <img src="{{ $chartImages['branch_product'] }}" class="chart-image row3-chart-img">
            @endif
        </div>
    </div>

    <!-- PAGE 4: Retail location charts -->
    <div class="page-break">
        <table class="chart-row-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('sales_by_state') }}</div>
                        @if (!empty($chartImages['state']))
                            <img src="{{ $chartImages['state'] }}" class="chart-image row2-chart-img">
                        @endif
                    </div>
                </td>
                <td width="50%">
                    <div class="chart-box">
                        <div class="chart-title">{{ translate('sales_by_city') }}</div>
                        @if (!empty($chartImages['city']))
                            <img src="{{ $chartImages['city'] }}" class="chart-image row2-chart-img">
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="chart-box">
            <div class="chart-title">{{ translate('sales_by_area') }}</div>
            @if (!empty($chartImages['area']))
                <img src="{{ $chartImages['area'] }}" class="chart-image row3-chart-img">
            @endif
        </div>
    </div>

    <!-- TABLES: Start on PAGE 5 -->
    <div class="page-break">
        @php
            $sections = [
                'pos' => ['title' => 'POS', 'data' => $posRows],
                'online' => ['title' => 'online', 'data' => $onlineRows],
                'wholesale' => ['title' => 'wholesale', 'data' => $wholesaleRows],
            ];
        @endphp

        @foreach($sections as $type => $section)
            <div class="table-container">
                <div class="table-header">
                    <h4>{{ translate($section['title']) }}</h4>
                    @if(($filters['date_type'] ?? 'this_year') == 'this_year')
                        <span style="color: white; font-size: 10px; margin-left: 8px;">{{ translate('monthly_breakdown') }}</span>
                    @elseif(($filters['date_type'] ?? '') == 'this_month')
                        <span style="color: white; font-size: 10px; margin-left: 8px;">{{ translate('daily_breakdown') }}</span>
                    @elseif(($filters['date_type'] ?? '') == 'this_week')
                        <span style="color: white; font-size: 10px; margin-left: 8px;">{{ translate('weekly_breakdown') }}</span>
                    @endif
                </div>

                <table class="matrix-table">
                    <thead>
                         <tr>
                            <th style="width: 5%;">{{ translate('sl') }}</th>
                            <th style="width: 12%;">{{ translate('period') }}</th>
                            <th style="width: 25%;">{{ translate('product') }}</th>
                            <th style="width: 20%;">{{ translate('branch') }}</th>
                            <th style="width: 10%;">{{ translate('qty') }}</th>
                            <th style="width: 10%;">{{ translate('orders') }}</th>
                            <th style="width: 18%;">{{ translate('sales') }}</th>
                         </tr>
                    </thead>
                    <tbody>
                        @forelse($section['data'] as $index => $row)
                             <tr>
                                 <td>{{ $index + 1 }}</td>
                                 <td>
                                    @if(($filters['date_type'] ?? 'this_year') == 'this_year')
                                        {{ $row->period_label ?? '' }} {{ now()->format('Y') }}
                                    @elseif(($filters['date_type'] ?? '') == 'this_month')
                                        {{ translate('day') }} {{ $row->period_label ?? '' }}
                                    @elseif(($filters['date_type'] ?? '') == 'this_week')
                                        {{ $row->period_label ?? '' }}
                                    @elseif(($filters['date_type'] ?? '') == 'today')
                                        {{ translate('today') }}
                                    @else
                                        {{ $row->period_label ?? '' }}
                                    @endif
                                 </td>
                                 <td>{{ $row->product_name ?? '-' }}</td>
                                 <td>{{ $row->branch_name ?? '-' }}</td>
                                 <td>{{ number_format($row->total_qty ?? 0) }}</td>
                                 <td>{{ number_format($row->total_orders ?? 0) }}</td>
                                <td style="text-align: right;">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->total_amount ?? 0), currencyCode: getCurrencyCode()) }}
                                 </td>
                             </tr>
                        @empty
                             <tr>
                                <td colspan="7" style="text-align: center; padding: 15px;">
                                    {{ translate('no_data_found') }}
                                 </td>
                             </tr>
                        @endforelse
                    </tbody>
                 </table>
            </div>
        @endforeach
    </div>

    <div class="page-break">
        @php
            $locationSections = [
                ['title' => translate('state_sales_summary'), 'column' => translate('state'), 'data' => $retailStateRows],
                ['title' => translate('city_sales_summary'), 'column' => translate('city'), 'data' => $retailCityRows],
                ['title' => translate('area_sales_summary'), 'column' => translate('area'), 'data' => $retailAreaRows],
            ];
        @endphp

        @foreach($locationSections as $section)
            <div class="table-container">
                <div class="table-header">
                    <h4>{{ $section['title'] }}</h4>
                </div>

                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">{{ translate('sl') }}</th>
                            <th style="width: 34%;">{{ $section['column'] }}</th>
                            <th style="width: 16%;">{{ translate('qty') }}</th>
                            <th style="width: 16%;">{{ translate('orders') }}</th>
                            <th style="width: 26%;">{{ translate('sales') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($section['data'] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->location_name ?? '-' }}</td>
                                <td>{{ number_format($row->total_qty ?? 0) }}</td>
                                <td>{{ number_format($row->total_orders ?? 0) }}</td>
                                <td style="text-align: right;">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->total_amount ?? 0), currencyCode: getCurrencyCode()) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 15px;">
                                    {{ translate('no_data_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

</body>
</html>
