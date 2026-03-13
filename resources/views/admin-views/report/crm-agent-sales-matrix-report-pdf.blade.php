<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_agent_sales_matrix_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            text-align: {{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'right' : 'left' }};
        }
        h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: center; }
        th { background: #f1f5f9; }
        .group-header { background: #e2e8f0; font-weight: 700; }
        .group-separator { border-right: 2px solid #334155 !important; }
        .left { text-align: {{ (session('direction') === 'rtl' || app()->getLocale() === 'ar') ? 'right' : 'left' }}; }
        @if((session('direction') === 'rtl' || app()->getLocale() === 'ar'))
        .group-separator {
            border-right: 0 !important;
            border-left: 2px solid #334155 !important;
        }
        @endif
    </style>
</head>
<body>
    <h2>{{ translate('crm_agent_sales_matrix_report') }}</h2>
    <div class="meta">
        <div>{{ translate('from') }}: {{ $filters['from'] }} | {{ translate('to') }}: {{ $filters['to'] }}</div>
        <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
    </div>

    <table>
        <thead>
        <tr>
            <th>{{ translate('metric') }}</th>
            <th>{{ translate('value') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr><td class="left">{{ translate('total_batteries') }}</td><td>{{ $summary['grand']['total_batteries'] }}</td></tr>
        <tr><td class="left">{{ translate('retail_batteries') }}</td><td>{{ $summary['grand']['retail_batteries'] }}</td></tr>
        <tr><td class="left">{{ translate('wholesale_batteries') }}</td><td>{{ $summary['grand']['wholesale_batteries'] }}</td></tr>
        <tr><td class="left">{{ translate('total_customers') }}</td><td>{{ $summary['grand']['total_customers'] }}</td></tr>
        </tbody>
    </table>

    <table>
        <thead>
        <tr>
            <th rowspan="2">{{ translate('period') }}</th>
            @foreach($employeesForMatrix as $employee)
                <th class="group-header {{ !$loop->last ? 'group-separator' : '' }}" colspan="4">{{ $employee->name }}</th>
            @endforeach
            <th class="group-header" colspan="2">{{ translate('totals') }}</th>
        </tr>
        <tr>
            @foreach($employeesForMatrix as $employee)
                <th>{{ translate('retail_batteries') }}</th>
                <th>{{ translate('retail_customers') }}</th>
                <th>{{ translate('wholesale_batteries') }}</th>
                <th class="{{ !$loop->last ? 'group-separator' : '' }}">{{ translate('wholesale_customers') }}</th>
            @endforeach
            <th>{{ translate('total_batteries') }}</th>
            <th>{{ translate('total_customers') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($monthlyRows as $row)
            <tr>
                <td class="left">{{ $row->month_label }}</td>
                @foreach($employeesForMatrix as $employee)
                    @php($cell = $row->employees[$employee->id] ?? null)
                    <td>{{ $cell['retail_batteries'] ?? 0 }}</td>
                    <td>{{ $cell['retail_customers'] ?? 0 }}</td>
                    <td>{{ $cell['wholesale_batteries'] ?? 0 }}</td>
                    <td class="{{ !$loop->last ? 'group-separator' : '' }}">{{ $cell['wholesale_customers'] ?? 0 }}</td>
                @endforeach
                <td><strong>{{ $row->totals['total_batteries'] }}</strong></td>
                <td><strong>{{ $row->totals['total_customers'] }}</strong></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ (count($employeesForMatrix) * 4) + 3 }}">{{ translate('no_data_found') }}</td>
            </tr>
        @endforelse
        </tbody>
        @if($monthlyRows->isNotEmpty())
            <tfoot>
            <tr>
                <td class="left"><strong>{{ translate('grand_total') }}</strong></td>
                @foreach($employeesForMatrix as $employee)
                    @php($employeeTotal = $summary['per_employee']->firstWhere('employee_id', $employee->id))
                    <td><strong>{{ $employeeTotal->retail_batteries ?? 0 }}</strong></td>
                    <td><strong>{{ $employeeTotal->retail_customers ?? 0 }}</strong></td>
                    <td><strong>{{ $employeeTotal->wholesale_batteries ?? 0 }}</strong></td>
                    <td class="{{ !$loop->last ? 'group-separator' : '' }}"><strong>{{ $employeeTotal->wholesale_customers ?? 0 }}</strong></td>
                @endforeach
                <td><strong>{{ $summary['grand']['total_batteries'] }}</strong></td>
                <td><strong>{{ $summary['grand']['total_customers'] }}</strong></td>
            </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
