@extends('layouts.back-end.app')
@section('title', translate('crm_agent_sales_matrix_report'))
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
        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
        }
        .summary-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 13px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid {{ $isRtl ? 'text-right' : 'text-left' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
                {{ translate('crm_agent_sales_matrix') }}
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
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="crm_agent_date_type">
                                <option value="this_year" {{ ($filters['date_type'] ?? 'this_year') === 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                <option value="this_month" {{ ($filters['date_type'] ?? '') === 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                <option value="this_week" {{ ($filters['date_type'] ?? '') === 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') === 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                <option value="custom_date" {{ ($filters['date_type'] ?? '') === 'custom_date' ? 'selected' : '' }}>{{ translate('custom_range') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 custom-date-range" id="crm_agent_from_div" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
                        </div>
                        <div class="col-md-2 custom-date-range" id="crm_agent_to_div" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.report.crm-agent-sales-matrix') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.report.crm-agent-sales-matrix-export-excel', request()->query()) }}" class="btn btn-outline-success">
                                <i class="tio-download-to me-1"></i> {{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.report.crm-agent-sales-matrix-export-pdf', request()->query()) }}" class="btn btn-outline-danger">
                                <i class="tio-download-to me-1"></i> {{ translate('PDF') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('total_batteries') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['total_batteries'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('retail_batteries') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['retail_batteries'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('wholesale_batteries') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['wholesale_batteries'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('total_customers') }}</div>
                    <div class="report-kpi-value">{{ $summary['grand']['total_customers'] }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('agent_sales_matrix_retail_wholesale') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered matrix-table mb-0">
                    <thead>
                    <tr>
                        <th class="matrix-sticky" rowspan="2">{{ translate('period') }}</th>
                        @foreach($employeesForMatrix as $employee)
                            <th class="matrix-head-agent {{ !$loop->last ? 'agent-separator' : '' }}" colspan="4">{{ $employee->name }}</th>
                        @endforeach
                        <th class="matrix-head-agent totals-separator" colspan="2">{{ translate('totals') }}</th>
                    </tr>
                    <tr>
                        @foreach($employeesForMatrix as $employee)
                            <th class="matrix-head-sub">{{ translate('retail_batteries') }}</th>
                            <th class="matrix-head-sub">{{ translate('retail_customers') }}</th>
                            <th class="matrix-head-sub">{{ translate('wholesale_batteries') }}</th>
                            <th class="matrix-head-sub {{ !$loop->last ? 'agent-separator' : '' }}">{{ translate('wholesale_customers') }}</th>
                        @endforeach
                        <th class="matrix-head-sub totals-separator">{{ translate('batteries') }}</th>
                        <th class="matrix-head-sub">{{ translate('customers') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($monthlyRows as $row)
                        <tr>
                            <td class="matrix-sticky">{{ $row->month_label }}</td>
                            @foreach($employeesForMatrix as $employee)
                                @php($cell = $row->employees[$employee->id] ?? null)
                                <td class="text-center">{{ $cell['retail_batteries'] ?? 0 }}</td>
                                <td class="text-center">{{ $cell['retail_customers'] ?? 0 }}</td>
                                <td class="text-center">{{ $cell['wholesale_batteries'] ?? 0 }}</td>
                                <td class="text-center {{ !$loop->last ? 'agent-separator' : '' }}">{{ $cell['wholesale_customers'] ?? 0 }}</td>
                            @endforeach
                            <td class="text-center font-weight-bold totals-separator">{{ $row->totals['total_batteries'] }}</td>
                            <td class="text-center font-weight-bold">{{ $row->totals['total_customers'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (count($employeesForMatrix) * 4) + 3 }}" class="text-center py-4">
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
                                <td class="text-center">{{ $employeeTotal->retail_batteries ?? 0 }}</td>
                                <td class="text-center">{{ $employeeTotal->retail_customers ?? 0 }}</td>
                                <td class="text-center">{{ $employeeTotal->wholesale_batteries ?? 0 }}</td>
                                <td class="text-center {{ !$loop->last ? 'agent-separator' : '' }}">{{ $employeeTotal->wholesale_customers ?? 0 }}</td>
                            @endforeach
                            <td class="text-center totals-separator">{{ $summary['grand']['total_batteries'] }}</td>
                            <td class="text-center">{{ $summary['grand']['total_customers'] }}</td>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-lg-4">
                <div class="summary-card card h-100">
                    <div class="card-header">{{ translate('batteries_by_agent') }}</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>{{ translate('employee') }}</th>
                                    <th class="text-center">{{ translate('retail') }}</th>
                                    <th class="text-center">{{ translate('wholesale') }}</th>
                                    <th class="text-center">{{ translate('total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($summary['per_employee'] as $item)
                                    <tr>
                                        <td>{{ $item->employee_name }}</td>
                                        <td class="text-center">{{ $item->retail_batteries }}</td>
                                        <td class="text-center">{{ $item->wholesale_batteries }}</td>
                                        <td class="text-center font-weight-bold">{{ $item->total_batteries }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-3">{{ translate('no_data_found') }}</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-card card h-100">
                    <div class="card-header">{{ translate('battery_totals_by_type') }}</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <td>{{ translate('retail') }}</td>
                                <td class="text-end">{{ $summary['batteries_by_type']['retail'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ translate('wholesale') }}</td>
                                <td class="text-end">{{ $summary['batteries_by_type']['wholesale'] }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>{{ translate('total') }}</td>
                                <td class="text-end">{{ $summary['batteries_by_type']['total'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-card card h-100">
                    <div class="card-header">{{ translate('customer_totals_by_type') }}</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <td>{{ translate('retail') }}</td>
                                <td class="text-end">{{ $summary['customers_by_type']['retail'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ translate('wholesale') }}</td>
                                <td class="text-end">{{ $summary['customers_by_type']['wholesale'] }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>{{ translate('total') }}</td>
                                <td class="text-end">{{ $summary['customers_by_type']['total'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(function () {
            $('#crm_agent_date_type').on('change', function () {
                const isCustom = $(this).val() === 'custom_date';
                $('.custom-date-range').toggle(isCustom);
            });
        });
    </script>
@endpush
