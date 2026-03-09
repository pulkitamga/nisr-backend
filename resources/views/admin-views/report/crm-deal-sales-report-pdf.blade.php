<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_sales_performance_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};
        }

        h2 {
            margin: 0 0 8px;
        }

        .meta {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #f3f4f6;
        }

        .left {
            text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};
        }
    </style>
</head>

<body>
    <h2>{{ translate('crm_sales_performance_report') }}</h2>
    <div class="meta">
        <div>{{ translate('from') }}: {{ $filters['from'] }} | {{ translate('to') }}: {{ $filters['to'] }}</div>
        <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
    </div>
    <table width="100%" style="border:0;margin-bottom:15px;">
        <tr>

            <td width="50%" style="border:0;text-align:center">
                <h3>Won vs Lost by Employee</h3>
                @if (!empty($employeeChart))
                    <img src="{{ $employeeChart }}" width="100%">
                @endif
            </td>

            <td width="50%" style="border:0;text-align:center">
                <h3>Overall Deal Status Split</h3>
                @if (!empty($statusChart))
                    <img src="{{ $statusChart }}" width="100%">
                @endif
            </td>

        </tr>
    </table>
    <table width="100%" style="border:0;margin-bottom:15px;">
        <tr>

            <td width="100%" style="border:0;text-align:center">
                <h3>Won Sales: Retail vs Wholesale by Employee</h3>

                @if (!empty($retailWholesaleChart))
                    <img src="{{ $retailWholesaleChart }}" width="100%">
                @endif

            </td>

        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>{{ translate('metric') }}</th>
                <th>{{ translate('value') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left">{{ translate('won_sales') }}</td>
                <td>{{ number_format((float) $summary['won_sales_total'], 2) }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('retail_won_sales') }}</td>
                <td>{{ number_format((float) $summary['retail_won_sales'], 2) }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('wholesale_won_sales') }}</td>
                <td>{{ number_format((float) $summary['wholesale_won_sales'], 2) }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('won_deals') }}</td>
                <td>{{ (int) $summary['won_count'] }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('lost_deals') }}</td>
                <td>{{ (int) $summary['lost_count'] }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('total_deals') }}</td>
                <td>{{ (int) $summary['total_deals'] }}</td>
            </tr>
        </tbody>
    </table>

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
                @foreach ($section->employees as $row)
                    <tr>
                        <td class="left">{{ $section->department_name }}</td>
                        <td class="left">{{ $row->employee_name }}</td>
                        <td>{{ number_format((float) $row->retail_won_sales, 2) }}</td>
                        <td>{{ number_format((float) $row->wholesale_won_sales, 2) }}</td>
                        <td>{{ number_format((float) $row->won_sales_total, 2) }}</td>
                        <td>{{ (int) $row->won_count }}</td>
                        <td>{{ (int) $row->lost_count }}</td>
                        <td>{{ (int) $row->total_deals }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="left"><strong>{{ $section->department_name }}</strong></td>
                    <td class="left"><strong>{{ translate('department_total') }}</strong></td>
                    <td><strong>{{ number_format((float) $section->totals['retail_won_sales'], 2) }}</strong></td>
                    <td><strong>{{ number_format((float) $section->totals['wholesale_won_sales'], 2) }}</strong></td>
                    <td><strong>{{ number_format((float) $section->totals['won_sales_total'], 2) }}</strong></td>
                    <td><strong>{{ (int) $section->totals['won_count'] }}</strong></td>
                    <td><strong>{{ (int) $section->totals['lost_count'] }}</strong></td>
                    <td><strong>{{ (int) $section->totals['total_deals'] }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="left"><strong>{{ translate('grand_total') }}</strong></td>
                <td><strong>-</strong></td>
                <td><strong>{{ number_format((float) $summary['retail_won_sales'], 2) }}</strong></td>
                <td><strong>{{ number_format((float) $summary['wholesale_won_sales'], 2) }}</strong></td>
                <td><strong>{{ number_format((float) $summary['won_sales_total'], 2) }}</strong></td>
                <td><strong>{{ (int) $summary['won_count'] }}</strong></td>
                <td><strong>{{ (int) $summary['lost_count'] }}</strong></td>
                <td><strong>{{ (int) $summary['total_deals'] }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>