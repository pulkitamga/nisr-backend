<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_agent_sales_matrix_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            text-align: {{ (session('direction')==='rtl' || app()->getLocale()==='ar') ? 'right': 'left' }};
        }

        .report-header {
            background-color: #0f766e;
            background-gradient: linear #0f766e #0ea5a0 0 1 0 0.5;
            color: #ffffff;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            width: 100%;
            display: block;
        }

        .header-table {
            width: 100%;
            border: none;
        }

        .header-content h2 {
            color: #ffffff;
            font-size: 22px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .header-content p {
            color: #ffffff;
            margin: 0;
            font-size: 11px;
            opacity: 0.9;
        }

        /* KPI Card Container */
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

        .meta {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #f1f5f9;
        }

        .group-header {
            background: #e2e8f0;
            font-weight: 700;
        }

        .group-separator {
            border-right: 2px solid #334155 !important;
        }

        .left {
            text-align: {{ (session('direction')==='rtl' || app()->getLocale()==='ar') ? 'right': 'left' }};
        }

        .header-table td {
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }

        /* Summary section styles */
        .summary-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .summary-table th {
            background: #e2e8f0;
            font-weight: 700;
            padding: 8px 5px;
        }
        
        .summary-table td {
            padding: 6px 5px;
        }
        
        .summary-title {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #0f172a;
        }
        
        .text-end {
            text-align: right;
        }
        
        .font-weight-bold {
            font-weight: 700;
        }

        @if (session('direction') === 'rtl' || app()->getLocale() === 'ar')
            .group-separator {
                border-right: 0 !important;
                border-left: 2px solid #334155 !important;
            }
            .text-end {
                text-align: left;
            }
        @endif
    </style>
</head>

<body>
    <div class="report-header">
        <table class="header-table" style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td class="header-content"
                    style="text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }}; vertical-align: middle; border: none; background: none;">
                    <h2
                        style="color: #ffffff; font-size: 24px; margin: 0 0 2px 0; font-weight: bold; font-family: DejaVu Sans, sans-serif;">
                        {{ translate('crm_agent_sales_matrix_report') }}
                    </h2>
                    <p
                        style="color: #ffffff; margin: 0; font-size: 12px; opacity: 0.95; font-family: DejaVu Sans, sans-serif;">
                        {{ translate('report_period') }}: {{ $filters['from'] }} - {{ $filters['to'] }}
                    </p>
                </td>
                <td
                    style="text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'left' : 'right' }}; width: 25%; vertical-align: middle; border: none; background: none;">
                    @php $defaultLogoPath = public_path('storage/company/2025-07-08-686cba44bf91a.webp'); @endphp
                    @if (!empty($logo))
                        <img src="{{ $logo }}" style="max-height: 55px; max-width: 130px;">
                    @elseif(file_exists($defaultLogoPath))
                        <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($defaultLogoPath)) }}"
                            style="max-height: 55px; max-width: 130px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="kpi-container">
        <table class="kpi-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="kpi-label">{{ translate('total_batteries') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format($summary['grand']['total_batteries'] ?? 0) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('retail_batteries') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format($summary['grand']['retail_batteries'] ?? 0) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('wholesale_batteries') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format($summary['grand']['wholesale_batteries'] ?? 0) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="kpi-label">{{ translate('total_customers') }}</div>
                    <div class="kpi-value">
                        <strong>{{ number_format($summary['grand']['total_customers'] ?? 0) }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">{{ translate('period') }}</th>
                @foreach ($employeesForMatrix as $employee)
                    <th class="group-header {{ !$loop->last ? 'group-separator' : '' }}" colspan="4">
                        {{ $employee->name }}</th>
                @endforeach
                <th class="group-header" colspan="2">{{ translate('totals') }}</th>
            </tr>
            <tr>
                @foreach ($employeesForMatrix as $employee)
                    <th>{{ translate('retail_batteries') }}</th>
                    <th>{{ translate('retail_customers') }}</th>
                    <th>{{ translate('wholesale_batteries') }}</th>
                    <th class="{{ !$loop->last ? 'group-separator' : '' }}">{{ translate('wholesale_customers') }}
                    </th>
                @endforeach
                <th>{{ translate('total_batteries') }}</th>
                <th>{{ translate('total_customers') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthlyRows as $row)
                <tr>
                    <td class="left">{{ $row->month_label }}</td>
                    @foreach ($employeesForMatrix as $employee)
                        @php($cell = $row->employees[$employee->id] ?? null)
                        <td>{{ $cell['retail_batteries'] ?? 0 }}</td>
                        <td>{{ $cell['retail_customers'] ?? 0 }}</td>
                        <td>{{ $cell['wholesale_batteries'] ?? 0 }}</td>
                        <td class="{{ !$loop->last ? 'group-separator' : '' }}">
                            {{ $cell['wholesale_customers'] ?? 0 }}</td>
                    @endforeach
                    <td><strong>{{ $row->totals['total_batteries'] }}</strong></td>
                    <td><strong>{{ $row->totals['total_customers'] }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($employeesForMatrix) * 4 + 3 }}">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if ($monthlyRows->isNotEmpty())
            <tfoot>
                <tr>
                    <td class="left"><strong>{{ translate('grand_total') }}</strong></td>
                    @foreach ($employeesForMatrix as $employee)
                        @php($employeeTotal = $summary['per_employee']->firstWhere('employee_id', $employee->id))
                        <td><strong>{{ $employeeTotal->retail_batteries ?? 0 }}</strong></td>
                        <td><strong>{{ $employeeTotal->retail_customers ?? 0 }}</strong></td>
                        <td><strong>{{ $employeeTotal->wholesale_batteries ?? 0 }}</strong></td>
                        <td class="{{ !$loop->last ? 'group-separator' : '' }}">
                            <strong>{{ $employeeTotal->wholesale_customers ?? 0 }}</strong>
                        </td>
                    @endforeach
                    <td><strong>{{ $summary['grand']['total_batteries'] }}</strong></td>
                    <td><strong>{{ $summary['grand']['total_customers'] }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- SUMMARY SECTIONS - Batteries by Agent, Battery Totals by Type, Customer Totals by Type -->
    <div class="summary-container">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border: none;">
            <tr>
                <td width="33.33%" style="padding-right: 10px; vertical-align: top; border: none;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                        <div style="background-color: #f8fafc; padding: 8px 10px; font-weight: 700; font-size: 11px; border-bottom: 1px solid #e5e7eb;">
                            {{ translate('batteries_by_agent') }}
                        </div>
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <thead>
                                <tr>
                                    <th style="background-color: #f1f5f9; padding: 6px 5px; font-size: 9px; text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }};">{{ translate('employee') }}</th>
                                    <th style="background-color: #f1f5f9; padding: 6px 5px; font-size: 9px; text-align: center;">{{ translate('retail') }}</th>
                                    <th style="background-color: #f1f5f9; padding: 6px 5px; font-size: 9px; text-align: center;">{{ translate('wholesale') }}</th>
                                    <th style="background-color: #f1f5f9; padding: 6px 5px; font-size: 9px; text-align: center;">{{ translate('total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['per_employee'] as $item)
                                    <tr>
                                        <td style="padding: 5px; font-size: 9px; text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }};">{{ $item->employee_name }}</td>
                                        <td style="padding: 5px; font-size: 9px; text-align: center;">{{ $item->retail_batteries }}</td>
                                        <td style="padding: 5px; font-size: 9px; text-align: center;">{{ $item->wholesale_batteries }}</td>
                                        <td style="padding: 5px; font-size: 9px; text-align: center; font-weight: 700;">{{ $item->total_batteries }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 10px; text-align: center; font-size: 9px;">{{ translate('no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
                
                <td width="33.33%" style="padding-right: 10px; vertical-align: top; border: none;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                        <div style="background-color: #f8fafc; padding: 8px 10px; font-weight: 700; font-size: 11px; border-bottom: 1px solid #e5e7eb;">
                            {{ translate('battery_totals_by_type') }}
                        </div>
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <tbody>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #e5e7eb;">{{ translate('retail') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;"><strong>{{ $summary['batteries_by_type']['retail'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #e5e7eb;">{{ translate('wholesale') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;"><strong>{{ $summary['batteries_by_type']['wholesale'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; font-weight: 700;">{{ translate('total') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; font-weight: 700;">{{ $summary['batteries_by_type']['total'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                
                <td width="33.33%" style="vertical-align: top; border: none;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                        <div style="background-color: #f8fafc; padding: 8px 10px; font-weight: 700; font-size: 11px; border-bottom: 1px solid #e5e7eb;">
                            {{ translate('customer_totals_by_type') }}
                        </div>
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <tbody>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #e5e7eb;">{{ translate('retail') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;"><strong>{{ $summary['customers_by_type']['retail'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #e5e7eb;">{{ translate('wholesale') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;"><strong>{{ $summary['customers_by_type']['wholesale'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 10px; font-weight: 700;">{{ translate('total') }}</td>
                                    <td style="padding: 8px 10px; font-size: 10px; text-align: right; font-weight: 700;">{{ $summary['customers_by_type']['total'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div
        style="
    border-top: 1px dashed #d1d5db;
    margin-top: 20px;
    padding-top: 10px;
    font-size: 8px;
    color: #6b7280;
    text-align: center;
">
        <table width="100%" border="0" cellpadding="0" cellspacing="0"
            style="border-collapse: collapse; border: none;">
            <tr>
                <td width="20%"
                    style="text-align: {{ session('direction') === 'rtl' || app()->getLocale() === 'ar' ? 'right' : 'left' }}; color: #6b7280; border: none; padding: 2px;">
                    Page {PAGENO}
                </td>
                <td width="60%" style="text-align: center; border: none; padding: 2px;">
                    {{ translate('generated_on') }}: {{ now()->translatedFormat('j F Y, h:i A') }} |
                    {{ translate('crm_agent_sales_matrix_report') }}<br>
                    {{ translate('generated_by') }}: <span
                        style="color: red;">{{ ucfirst(auth()->user()->name ?? 'system') }}</span><br>
                    <span style="color: red;">{{ config('app.name') }}</span>
                </td>
                <td width="20%" style="border: none; padding: 2px;"></td>
            </tr>
        </table>
    </div>
</body>
</html>