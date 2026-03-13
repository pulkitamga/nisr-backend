<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('warranty_claims_report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        /* Header Styles with Logo - Green like CRM */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
<<<<<<< ahmed5
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
=======
            overflow: hidden;
>>>>>>> local
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

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI Container - Using table layout for reliable single row */
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
            border-left: 4px solid;
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
            margin: 0;
            text-align: left;
        }

<<<<<<< ahmed5
=======
        /* KPI Card Colors */
        .td-total { border-left-color: #3498db !important; }
        .td-total .kpi-value { color: #3498db; }
        .td-new { border-left-color: #f39c12 !important; }
        .td-new .kpi-value { color: #f39c12; }
        .td-approved { border-left-color: #2ecc71 !important; }
        .td-approved .kpi-value { color: #2ecc71; }
        .td-rejected { border-left-color: #e74c3c !important; }
        .td-rejected .kpi-value { color: #e74c3c; }
        .td-pending { border-left-color: #9b59b6 !important; }
        .td-pending .kpi-value { color: #9b59b6; }
        .td-resolved { border-left-color: #34495e !important; }
        .td-resolved .kpi-value { color: #34495e; }

        /* Filter Summary */
        .filter-summary {
            background: #f9fafb;
            border-left: 5px solid #0f766e;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 10px;
        }

        /* Chart Container */
        .chart-container {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            background: white;
            margin-bottom: 24px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .chart-header h4 {
            margin: 0;
            font-size: 16px;
        }

        .badge-soft-primary {
            background: rgba(15, 118, 110, 0.1);
            color: #0f766e;
            border-radius: 999px;
            font-size: 12px;
            padding: 5px 10px;
            font-weight: 600;
        }

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 350px;
        }

        /* Table Styles */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h5 {
            margin: 0;
            font-size: 16px;
        }

        .badge-soft-dark {
            background: #e9ecef;
            color: #495057;
            border-radius: 999px;
            font-size: 12px;
            padding: 5px 10px;
            margin-left: 10px;
        }

>>>>>>> local
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            padding: 12px 8px;
            text-align: center;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: #fdfdfd;
        }

        .value-ltr {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
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

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        /* RTL Support */
        @if (app()->getLocale() == 'ar')
            .header-content {
                float: right;
                text-align: right;
            }

            .logo-container {
                float: left;
                text-align: left;
            }
        @endif
    </style>
</head>
<body>

<<<<<<< ahmed5
    <!-- हेडर -->
    <div class="header">
        <h2>{{ translate('warranty_claims_report') }}</h2>
        <p>
            {{ translate('generated_on') }}: <span class="bidi-ltr">{{ now()->format('d M Y H:i A') }}</span> &nbsp; | &nbsp;
            {{ translate('By') }}: System
        </p>
    </div>

    <!-- फिल्टर अप्लाइड सेक्शन -->
    <div class="filters-section">
        <h4>🔍 {{ translate('filters_applied') }}</h4>
        <div class="filters-grid">
            <div class="filter-item"><strong>{{ translate('date_range') }}:</strong> <span class="bidi-ltr">{{ $filters['date_range'] }}</span></div>
            <div class="filter-item"><strong>{{ translate('branch') }}:</strong> {{ $filters['branch'] }}</div>
                        <div class="filter-item"><strong>{{ translate('product') }}:</strong> {{ $filters['product'] }}</div>
            <div class="filter-item"><strong>{{ translate('status') }}:</strong> {{ $filters['status'] }}</div>
            @if($filters['search'])
            <div class="filter-item"><strong>{{ translate('search') }}:</strong> {{ $filters['search'] }}</div>
=======
    <!-- Modern Header with Logo -->
    <div class="report-header clearfix">
        <div class="header-content">
            <h2>{{ translate('warranty_claims_report') }}</h2>
            <p>{{ translate('report_period') }}: {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</p>
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
>>>>>>> local
            @endif
        </div>
    </div>

    <!-- KPI Cards - 6 in a single row using table layout -->
    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="td-total">
                    <div class="kpi-label">{{ translate('total_claims') }}</div>
                    <div class="kpi-value">{{ $cards['total'] }}</div>
                </td>
                <td class="td-new">
                    <div class="kpi-label">{{ translate('new') }}</div>
                    <div class="kpi-value">{{ $cards['new'] }}</div>
                </td>
                <td class="td-approved">
                    <div class="kpi-label">{{ translate('approved') }}</div>
                    <div class="kpi-value">{{ $cards['approved'] }}</div>
                </td>
                <td class="td-rejected">
                    <div class="kpi-label">{{ translate('rejected') }}</div>
                    <div class="kpi-value">{{ $cards['rejected'] }}</div>
                </td>
                <td class="td-pending">
                    <div class="kpi-label">{{ translate('pending') }}</div>
                    <div class="kpi-value">{{ $cards['pending'] }}</div>
                </td>
                <td class="td-resolved">
                    <div class="kpi-label">{{ translate('resolved') }}</div>
                    <div class="kpi-value">{{ $cards['resolved'] }}</div>
                </td>
            </tr>
        </table>
    </div>

<<<<<<< ahmed5
    <!-- डेली ब्रेकडाउन रिपोर्ट -->
    <h3 style="margin:20px 0 10px;">📅 {{ translate('daily_breakdown_report') }}</h3>
    <table class="daily-table">
        <thead>
            <tr>
                <th>{{ translate('date') }}</th>
                <th>{{ translate('total') }}</th>
                <th>{{ translate('new') }}</th>
                <th>{{ translate('approved') }}</th>
                <th>{{ translate('rma_issued') }}</th>
                <th>{{ translate('received') }}</th>
                <th>{{ translate('repair_pending') }}</th>
                <th>{{ translate('replacement_pending') }}</th>
                <th>{{ translate('qc_pending') }}</th>
                <th>{{ translate('dispatched') }}</th>
                <th>{{ translate('resolved') }}</th>
                <th>{{ translate('rejected') }}</th>
                <th>{{ translate('closed') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyBreakdown as $day)
            <tr>
                <td>{{ $day['date'] }}</td>
                <td><strong>{{ $day['total'] }}</strong></td>
                <td>{{ $day['new'] }}</td>
                <td>{{ $day['approved'] }}</td>
                <td>{{ $day['rma_issued'] }}</td>
                <td>{{ $day['received'] }}</td>
                <td>{{ $day['repair_pending'] }}</td>
                <td>{{ $day['replacement_pending'] }}</td>
                <td>{{ $day['qc_pending'] }}</td>
                <td>{{ $day['dispatched'] }}</td>
                <td>{{ $day['resolved'] }}</td>
                <td>{{ $day['rejected'] }}</td>
                <td>{{ $day['closed'] }}</td>
            </tr>
            @empty
            <tr><td colspan="13" style="text-align:center;">{{ translate('no_data_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(count($claims) > 0)
    <h3 style="margin:20px 0 10px;">📋 {{ translate('claims_list') }}</h3>
    <table>
        <thead>
            <tr>
                <th>{{ translate('SL') }}</th>
                <th>{{ translate('claim_number') }}</th>
                <th>{{ translate('serial') }}</th>
                <th>{{ translate('product') }}</th>
                <th>{{ translate('status') }}</th>
                <th>{{ translate('customer') }}</th>
                <th>{{ translate('submitted_at') }}</th>
                <th>{{ translate('sla_due') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claims as $index => $claim)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $claim->claim_number }}</td>
                <td>{{ $claim->serial_number }}</td>
                <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                <td>{{ translate($claim->status) }}</td>
                <td>{{ $claim->warranty?->user?->name ?? $claim->warranty?->activated_by_name ?? '' }}</td>
                <td>{{ $claim->submitted_at ? $claim->submitted_at->format('Y-m-d H:i') : '' }}</td>
                <td>{{ $claim->resolution_due ? $claim->resolution_due->format('Y-m-d H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
=======
    <!-- Chart - Using actual chart image from controller -->
    @if(!empty($chartImage))
    <div class="chart-container">
        <div class="chart-header">
            <h4>{{ translate('claims_by_day') }} ({{ translate('stacked') }})</h4>
            <span class="">{{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</span>
        </div>
        <img src="{{ $chartImage }}" class="chart-image" alt="Claims Chart" />
    </div>
    @endif

    <!-- Claims List Table -->
    <div class="table-container">
        <div class="table-header">
            <h5>{{ translate('claims_list') }} <span class="badge-soft-dark">{{ count($claims) }}</span></h5>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('SL') }}</th>
                    <th>{{ translate('claim_number') }}</th>
                    <th>{{ translate('serial') }}</th>
                    <th>{{ translate('product') }}</th>
                    <th>{{ translate('warranty_months') }}</th>
                    <th>{{ translate('warranty_end_date') }}</th>
                    <th>{{ translate('remaining') }}</th>
                    <th>{{ translate('status') }}</th>
                    <th>{{ translate('customer') }}</th>
                    <th>{{ translate('branch') }}</th>
                    <th>{{ translate('submitted_at') }}</th>
                    <th>{{ translate('sla_due') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($claims as $index => $claim)
                    @php
                        $warranty = $claim->warranty;
                        $productName = $warranty?->product?->name ?? '-';
                        $warrantyMonths = $warranty?->warranty_months ?? '-';
                        $endDate = $warranty?->end_date ? $warranty->end_date->format('Y-m-d') : '-';

                        // Remaining calculation
                        if ($warranty && $warranty->end_date) {
                            $now = now()->startOfDay();
                            $end = $warranty->end_date->startOfDay();
                            if ($now > $end) {
                                $remaining = '<span class="badge badge-danger">' . translate('expired') . '</span>';
                            } else {
                                $months = $now->diffInMonths($end);
                                $days = $now->copy()->addMonths($months)->diffInDays($end);
                                if ($months > 0 && $days > 0) {
                                    $remaining = $months . ' ' . translate('months') . ' ' . $days . ' ' . translate('days');
                                } elseif ($months > 0) {
                                    $remaining = $months . ' ' . translate('months');
                                } else {
                                    $remaining = $days . ' ' . translate('days');
                                }
                            }
                        } else {
                            $remaining = '-';
                        }
>>>>>>> local

                        $badgeClass = match ($claim->status) {
                            'new', 'waiting_customer', 'waiting_parts', 'waiting_payment' => 'badge-warning',
                            'rejected', 'closed' => 'badge-danger',
                            default => 'badge-success',
                        };
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="value-ltr"><strong>{{ $claim->claim_number }}</strong></td>
                        <td class="value-ltr">{{ $claim->serial_number }}</td>
                        <td>{{ $productName }}</td>
                        <td class="value-ltr">{{ $warrantyMonths }}</td>
                        <td class="value-ltr">{{ $endDate }}</td>
                        <td>{!! $remaining !!}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ ucwords(str_replace('_', ' ', $claim->status)) }}
                            </span>
                        </td>
                        <td>{{ $claim->warranty?->user?->name ?? ($claim->warranty?->activated_by_name ?? '') }}</td>
                        <td>{{ $claim->branch?->branch_name ?? '-' }}</td>
                        <td class="value-ltr">{{ $claim->submitted_at ? $claim->submitted_at->format('Y-m-d H:i') : '' }}</td>
                        <td class="value-ltr">{{ $claim->resolution_due ? $claim->resolution_due->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="no-data-message">{{ translate('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
     <!-- FOOTER -->
 
    <div style="
border-top:1px dashed #d1d5db;
margin-top:20px;
padding-top:8px;
font-size:9px;
color:#6b7280;
">
 
        <table width="100%">
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
        </table>
 
    </div>

</body>
</html>
