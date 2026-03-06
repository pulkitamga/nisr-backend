<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="UTF-8">
    <title>{{ translate('crm_employee_channel_assignment_report') }}</title>
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

        .total-separator {
            border-left: 2px solid #111827 !important;
        }

        .left {
            text-align: {{ session('direction') === 'rtl' ? 'right' : 'left' }};
        }

        @if (session('direction') === 'rtl')
            .group-separator {
                border-right: 0 !important;
                border-left: 2px solid #334155 !important;
            }

            .total-separator {
                border-left: 0 !important;
                border-right: 2px solid #111827 !important;
            }
        @endif
    </style>
</head>

<body>
    <h2>{{ translate('crm_employee_channel_assignment_report') }}</h2>
    <div class="meta">
        <div>{{ translate('from') }}: {{ $filters['from'] }} | {{ translate('to') }}: {{ $filters['to'] }}</div>
        <div>{{ translate('exported_at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</div>
    </div>
    @if (!empty($channelChart))
        <div style="margin-top:20px;text-align:center;">
            <h3>Won Sales: Retail vs Wholesale by Month</h3>
            <img src="{{ $channelChart }}" style="width:100%;">
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>{{ translate('metric') }}</th>
                <th>{{ translate('value') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left">{{ translate('total_interactions') }}</td>
                <td>{{ $summary['grand']['total_count'] }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('retail_interactions') }}</td>
                <td>{{ $summary['grand']['retail_count'] }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('wholesale_interactions') }}</td>
                <td>{{ $summary['grand']['wholesale_count'] }}</td>
            </tr>
            <tr>
                <td class="left">{{ translate('active_employees') }}</td>
                <td>{{ $summary['active_employees'] }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">{{ translate('month') }}</th>
                @foreach ($employeesForMatrix as $employee)
                    <th class="group-header {{ !$loop->last ? 'group-separator' : '' }}" colspan="3">
                        {{ $employee->name }}</th>
                @endforeach
                <th class="group-header total-separator" colspan="3">{{ translate('totals') }}</th>
            </tr>
            <tr>
                @foreach ($employeesForMatrix as $employee)
                    <th>{{ translate('retail') }}</th>
                    <th>{{ translate('wholesale') }}</th>
                    <th class="{{ !$loop->last ? 'group-separator' : '' }}">{{ translate('total') }}</th>
                @endforeach
                <th class="total-separator">{{ translate('retail_total') }}</th>
                <th>{{ translate('wholesale_total') }}</th>
                <th>{{ translate('total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthlyRows as $row)
                <tr>
                    <td class="left">{{ $row->month_label }}</td>
                    @foreach ($employeesForMatrix as $employee)
                        @php($cell = $row->employees[$employee->id] ?? null)
                        <td>{{ $cell['retail_count'] ?? 0 }}</td>
                        <td>{{ $cell['wholesale_count'] ?? 0 }}</td>
                        <td class="{{ !$loop->last ? 'group-separator' : '' }}">{{ $cell['total_count'] ?? 0 }}</td>
                    @endforeach
                    <td class="total-separator"><strong>{{ $row->totals['retail_count'] }}</strong></td>
                    <td><strong>{{ $row->totals['wholesale_count'] }}</strong></td>
                    <td><strong>{{ $row->totals['total_count'] }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($employeesForMatrix) * 3 + 4 }}">{{ translate('no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if ($monthlyRows->isNotEmpty())
            <tfoot>
                <tr>
                    <td class="left"><strong>{{ translate('grand_total') }}</strong></td>
                    @foreach ($employeesForMatrix as $employee)
                        @php($employeeTotal = $summary['per_employee']->firstWhere('employee_id', $employee->id))
                        <td><strong>{{ $employeeTotal->retail_count ?? 0 }}</strong></td>
                        <td><strong>{{ $employeeTotal->wholesale_count ?? 0 }}</strong></td>
                        <td class="{{ !$loop->last ? 'group-separator' : '' }}">
                            <strong>{{ $employeeTotal->total_count ?? 0 }}</strong></td>
                    @endforeach
                    <td class="total-separator"><strong>{{ $summary['grand']['retail_count'] }}</strong></td>
                    <td><strong>{{ $summary['grand']['wholesale_count'] }}</strong></td>
                    <td><strong>{{ $summary['grand']['total_count'] }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>
