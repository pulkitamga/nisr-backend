@extends('layouts.back-end.app')
@section('title', translate('crm_employee_channel_assignment_report'))
@php($isRtl = Session::get('direction') === 'rtl')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .report-kpi-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            background: #fff;
            height: 100%;
        }
        .report-kpi-title {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .report-kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }
        .report-chart-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
            height: 100%;
        }
        .matrix-table th,
        .matrix-table td {
            font-size: 12px;
            padding: 6px 8px;
            white-space: nowrap;
        }
        .matrix-head-agent {
            background: #e6f0ff;
            text-align: center;
            border-left: 1px solid #d8e3f7;
            border-right: 1px solid #d8e3f7;
            border-bottom: 2px solid #334155;
        }
        .matrix-head-sub {
            background: #f3f7ff;
            text-align: center;
        }
        .agent-separator {
            border-right: 3px solid #334155 !important;
        }
        .totals-separator {
            border-left: 3px solid #111827 !important;
        }
        .matrix-sticky {
            position: sticky;
            {{ $isRtl ? 'right: 0;' : 'left: 0;' }}
            background: #fff;
            z-index: 2;
        }
        @if($isRtl)
        .agent-separator {
            border-right: 0 !important;
            border-left: 3px solid #334155 !important;
        }
        .totals-separator {
            border-left: 0 !important;
            border-right: 3px solid #111827 !important;
        }
        @endif
    </style>
@endpush

@section('content')
    @php($dateRange = $filters['from'] . ' - ' . $filters['to'])
    <div class="content container-fluid {{ $isRtl ? 'text-right' : 'text-left' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
                {{ translate('crm_employee_channel_report') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('department') }}</label>
                            <select class="js-select2-custom form-control" name="department_ids[]" multiple>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ in_array($department->id, $filters['department_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('employee') }}</label>
                            <select class="js-select2-custom form-control" name="employee_ids[]" multiple>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ in_array($employee->id, $filters['employee_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('channel') }}</label>
                            <select class="js-select2-custom form-control" name="channels[]" multiple>
                                @foreach($channelOptions as $channel)
                                    <option value="{{ $channel->value }}" {{ in_array($channel->value, $filters['channels'] ?? []) ? 'selected' : '' }}>
                                        {{ $channel->label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.report.crm-employee-channel-assignment') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.report.crm-employee-channel-assignment-export-excel', request()->query()) }}" class="btn btn-outline-success">
                                <i class="tio-download-to {{ $isRtl ? 'ml-1' : 'mr-1' }}"></i> {{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.report.crm-employee-channel-assignment-export-pdf', request()->query()) }}" class="btn btn-outline-danger">
                                <i class="tio-download-to {{ $isRtl ? 'ml-1' : 'mr-1' }}"></i> {{ translate('PDF') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('total_interactions') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['total_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('retail_interactions') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['retail_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('wholesale_interactions') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['wholesale_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('active_employees') }}</div>
                    <div class="report-kpi-value">{{ $summary['active_employees'] }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('monthly_assigned_interactions_by_channel') }} ({{ $dateRange }})</h4>
                    <div id="crm-employee-channel-monthly-chart"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('employee_channel_assignment_matrix') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered matrix-table mb-0">
                    <thead>
                    <tr>
                        <th class="matrix-sticky" rowspan="2">{{ translate('month') }}</th>
                        @foreach($employeesForMatrix as $employee)
                            <th class="matrix-head-agent {{ !$loop->last ? 'agent-separator' : '' }}" colspan="3">{{ $employee->name }}</th>
                        @endforeach
                        <th class="matrix-head-agent totals-separator" colspan="3">{{ translate('totals') }}</th>
                    </tr>
                    <tr>
                        @foreach($employeesForMatrix as $employee)
                            <th class="matrix-head-sub">{{ translate('retail') }}</th>
                            <th class="matrix-head-sub">{{ translate('wholesale') }}</th>
                            <th class="matrix-head-sub {{ !$loop->last ? 'agent-separator' : '' }}">{{ translate('total') }}</th>
                        @endforeach
                        <th class="matrix-head-sub totals-separator">{{ translate('retail_total') }}</th>
                        <th class="matrix-head-sub">{{ translate('wholesale_total') }}</th>
                        <th class="matrix-head-sub">{{ translate('total') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($monthlyRows as $row)
                        <tr>
                            <td class="matrix-sticky">{{ $row->month_label }}</td>
                            @foreach($employeesForMatrix as $employee)
                                @php($cell = $row->employees[$employee->id] ?? null)
                                <td class="text-center">{{ $cell['retail_count'] ?? 0 }}</td>
                                <td class="text-center">{{ $cell['wholesale_count'] ?? 0 }}</td>
                                <td class="text-center {{ !$loop->last ? 'agent-separator' : '' }}">{{ $cell['total_count'] ?? 0 }}</td>
                            @endforeach
                            <td class="text-center font-weight-bold totals-separator">{{ $row->totals['retail_count'] }}</td>
                            <td class="text-center font-weight-bold">{{ $row->totals['wholesale_count'] }}</td>
                            <td class="text-center font-weight-bold">{{ $row->totals['total_count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (count($employeesForMatrix) * 3) + 4 }}" class="text-center py-4">
                                {{ translate('no_data_found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if($monthlyRows->isNotEmpty())
                        <tfoot>
                        <tr class="table-active font-weight-bold">
                            <td class="matrix-sticky">{{ translate('grand_total') }}</td>
                            @foreach($employeesForMatrix as $employee)
                                @php($employeeTotal = $summary['per_employee']->firstWhere('employee_id', $employee->id))
                                <td class="text-center">{{ $employeeTotal->retail_count ?? 0 }}</td>
                                <td class="text-center">{{ $employeeTotal->wholesale_count ?? 0 }}</td>
                                <td class="text-center {{ !$loop->last ? 'agent-separator' : '' }}">{{ $employeeTotal->total_count ?? 0 }}</td>
                            @endforeach
                            <td class="text-center totals-separator">{{ $summary['grand']['retail_count'] }}</td>
                            <td class="text-center">{{ $summary['grand']['wholesale_count'] }}</td>
                            <td class="text-center">{{ $summary['grand']['total_count'] }}</td>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('total_interactions_by_employee') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('employee') }}</th>
                        <th class="text-center">{{ translate('retail') }}</th>
                        <th class="text-center">{{ translate('wholesale') }}</th>
                        <th class="text-center">{{ translate('total_interactions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($summary['per_employee'] as $item)
                        <tr>
                            <td>{{ $item->employee_name }}</td>
                            <td class="text-center">{{ $item->retail_count }}</td>
                            <td class="text-center">{{ $item->wholesale_count }}</td>
                            <td class="text-center font-weight-bold">{{ $item->total_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-3">{{ translate('no_data_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js')}}"></script>
    <script>
        (function () {
            const chartData = @json($chart);
            const retailLabel = @json(translate('retail'));
            const wholesaleLabel = @json(translate('wholesale'));

            const el = document.querySelector('#crm-employee-channel-monthly-chart');
            if (!el) return;

            new ApexCharts(el, {
                chart: {type: 'bar', height: 350, stacked: true, toolbar: {show: false}},
                series: [
                    {name: retailLabel, data: chartData.retail},
                    {name: wholesaleLabel, data: chartData.wholesale}
                ],
                xaxis: {categories: chartData.labels, labels: {rotate: -30}},
                dataLabels: {enabled: false},
                colors: ['#1f8ef1', '#f59e0b'],
                legend: {position: 'bottom'},
                noData: {text: @json(translate('no_data_found'))}
            }).render();
        })();
    </script>
@endpush

