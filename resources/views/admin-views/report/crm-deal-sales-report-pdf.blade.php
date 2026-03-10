<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_sales_performance_report') }}</title>
    <style>
        /* Modern mPDF Styles */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 1.2cm;
            text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};
            line-height: 1.4;
        }

        /* Header Styles */
        .report-header {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .report-header h1 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: bold;
        }

        .report-header .meta-info {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            opacity: 0.9;
            margin-top: 10px;
        }

        .meta-item {
            display: inline-block;
            margin-{{ session('direction') === 'rtl' ? 'left' : 'right' }}: 20px;
        }

        /* Summary Cards - Using Table for mPDF Compatibility */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 5px;
            text-align: center;
        }

        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }

        /* Chart Containers */
        .chart-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .chart-col {
            flex: 1;
        }

        .chart-container {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .chart-title {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin: 0 0 15px 0;
        }

        .chart-image {
            width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: contain;
        }

        /* Table Styles */
        .table-container {
            margin-top: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .table-header {
            background: #0f766e;
            color: white;
            padding: 12px 15px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
            padding: 10px 5px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        td {
            padding: 8px 5px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        /* Department Section Styling */
        .department-row {
            background: #f8fafc;
            font-weight: 600;
        }

        .department-row td {
            background: #e6f0f0;
            border-bottom: 2px solid #0f766e;
        }

        .department-name {
            font-weight: 700;
            color: #0f766e;
        }

        .grand-total-row {
            background: #0f766e;
            color: white;
            font-weight: 700;
        }

        .grand-total-row td {
            background: #0f766e;
            color: white;
            border-color: #1e293b;
        }

        /* Alternating row colors */
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        /* RTL Specific */
        @if (session('direction') === 'rtl')
            .summary-card {
                text-align: center;
            }

            .meta-item {
                margin-left: 20px;
                margin-right: 0;
            }
        @endif
    </style>
</head>

<body>
    <!-- Modern Header -->

    <div class="report-header">
        <h1>{{ translate('crm_sales_performance_report') }}</h1>
        <table width="100%" style="margin-top:10px;font-size:11px;color:white;">
            <tr>
                <td>
                    <strong>{{ translate('from') }}:</strong> {{ $filters['from'] }}
                    &nbsp;&nbsp;
                    <strong>{{ translate('to') }}:</strong> {{ $filters['to'] }}
                </td>

                <td align="right">
                    <strong>{{ translate('exported_at') }}:</strong>
                    {{ optional($exportedAt ?? now())->format('d M Y, H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- First Row: Two Charts Side by Side -->
    <div class="chart-row">
        <!-- Won vs Lost by Employee Chart -->
        @if (!empty($employeeChart))
            <div class="chart-col">
                <div class="chart-container">
                    <div class="chart-title">{{ translate('won_vs_lost_by_employee') }}
                        <small>({{ $periodLabel ?? '' }})</small></div>
                    <img src="{{ $employeeChart }}" class="chart-image" alt="Won vs Lost by Employee">
                </div>
            </div>
        @endif

        <!-- Overall Deal Status Split Chart -->
        @if (!empty($statusChart))
            <div class="chart-col">
                <div class="chart-container">
                    <div class="chart-title">{{ translate('overall_deal_status_split') }}</div>
                    <img src="{{ $statusChart }}" class="chart-image" alt="Deal Status Split">
                </div>
            </div>
        @endif
    </div>

    <!-- Second Row: Full Width Chart -->
    @if (!empty($retailWholesaleChart))
        <div style="margin-bottom: 20px;">
            <div class="chart-container">
                <div class="chart-title">{{ translate('won_sales_retail_vs_wholesale_by_employee') }}
                    <small>({{ $periodLabel ?? '' }})</small></div>
                <img src="{{ $retailWholesaleChart }}" style="width:100%; max-height:200px; object-fit:contain;"
                    alt="Retail vs Wholesale">
            </div>
        </div>
    @endif

    <!-- Detailed Department & Employee Breakdown -->
    <div class="table-container">
        <div class="table-header">
            <h3>{{ translate('department_performance_breakdown') }} <small
                    style="font-weight:normal; opacity:0.9;">({{ $filters['from'] }} - {{ $filters['to'] }})</small>
            </h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ translate('department') }}</th>
                    <th>{{ translate('employee') }}</th>
                    <th>{{ translate('retail_won_sales') }}</th>
                    <th>{{ translate('wholesale_won_sales') }}</th>
                    <th>{{ translate('won_sales') }}</th>
                    <th>{{ translate('won') }}</th>
                    <th>{{ translate('lost') }}</th>
                    <th>{{ translate('total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departmentSections as $section)
                    <!-- Employee Rows -->
                    @foreach ($section->employees as $row)
                        <tr>
                            <td style="text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};">
                                {{ $section->department_name }}</td>
                            <td style="text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};">
                                <strong>{{ $row->employee_name }}</strong></td>
                            <td>{{ number_format((float) $row->retail_won_sales, 2) }}</td>
                            <td>{{ number_format((float) $row->wholesale_won_sales, 2) }}</td>
                            <td>{{ number_format((float) $row->won_sales_total, 2) }}</td>
                            <td>{{ (int) $row->won_count }}</td>
                            <td>{{ (int) $row->lost_count }}</td>
                            <td>{{ (int) $row->total_deals }}</td>
                        </tr>
                    @endforeach

                    <!-- Department Total Row -->
                    <tr class="department-row">
                        <td colspan="2"
                            style="text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};">
                            <strong>{{ $section->department_name }} {{ translate('total') }}</strong></td>
                        <td><strong>{{ number_format((float) $section->totals['retail_won_sales'], 2) }}</strong></td>
                        <td><strong>{{ number_format((float) $section->totals['wholesale_won_sales'], 2) }}</strong>
                        </td>
                        <td><strong>{{ number_format((float) $section->totals['won_sales_total'], 2) }}</strong></td>
                        <td><strong>{{ (int) $section->totals['won_count'] }}</strong></td>
                        <td><strong>{{ (int) $section->totals['lost_count'] }}</strong></td>
                        <td><strong>{{ (int) $section->totals['total_deals'] }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">{{ translate('no_data_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <!-- Grand Total Row -->
            <tfoot>
                <tr style="background: #0f766e; color: white;">
                    <td colspan="2"
                        style="text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }}; background: #0f766e; color: white;">
                        <strong>{{ translate('grand_total') }}</strong>
                    </td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ number_format((float) ($summary['retail_won_sales'] ?? 0), 2) }}</strong></td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ number_format((float) ($summary['wholesale_won_sales'] ?? 0), 2) }}</strong></td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ number_format((float) ($summary['won_sales_total'] ?? 0), 2) }}</strong></td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ (int) ($summary['won_count'] ?? 0) }}</strong></td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ (int) ($summary['lost_count'] ?? 0) }}</strong></td>
                    <td style="background: #0f766e; color: white;">
                        <strong>{{ (int) ($summary['total_deals'] ?? 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ translate('crm_sales_performance_report') }} | {{ translate('generated') }}:
        {{ now()->format('d M Y, H:i') }} | {{ translate('page') }} 1/1
    </div>
</body>

</html>
