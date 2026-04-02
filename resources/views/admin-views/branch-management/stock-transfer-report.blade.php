@extends('layouts.back-end.app')

@section('title', translate('stock_transfer_report'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/branch-management.css') }}">
@endpush

@section('content')
    @php($isRtl = Session::get('direction') === 'rtl')
    <div class="content container-fluid {{ $isRtl ? 'text-end' : 'text-start' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}"
                    alt="">
                {{ translate('stock_transfer_report') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form id="transfer-report-filter-form" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1">{{ translate('date_range') }}</label>
                        <select class="form-control" id="transfer-date-type" name="date_type">
                            <option value="this_year">{{ translate('this_year') }}</option>
                            <option value="this_month">{{ translate('this_month') }}</option>
                            <option value="this_week">{{ translate('this_week') }}</option>
                            <option value="today">{{ translate('today') }}</option>
                            <option value="custom_date">{{ translate('custom_range') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3 custom-date-range" id="transfer-from-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('from') }}</label>
                        <input type="date" class="form-control" id="transfer-from" name="from">
                    </div>

                    <div class="col-md-3 custom-date-range" id="transfer-to-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('to') }}</label>
                        <input type="date" class="form-control" id="transfer-to" name="to">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1">{{ translate('from_branch') }}</label>
                        <select class="form-control" id="from-branch-id" name="from_branch_id">
                            <option value="">{{ translate('all') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->getTranslatedField('branch_name') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1">{{ translate('to_branch') }}</label>
                        <select class="form-control" id="to-branch-id" name="to_branch_id">
                            <option value="">{{ translate('all') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->getTranslatedField('branch_name') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1">{{ translate('status') }}</label>
                        <select class="form-control" id="transfer-status" name="status">
                            <option value="">{{ translate('all') }}</option>
                            <option value="pending">{{ translate('pending') }}</option>
                            <option value="approved">{{ translate('approved') }}</option>
                            <option value="rejected">{{ translate('rejected') }}</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" id="transfer-load-btn"
                            class="btn btn--primary">{{ translate('filter') }}</button>

                        <button type="button" id="transfer-reset-btn"
                            class="btn btn-outline-secondary">{{ translate('reset') }}</button>

                        <button type="button" id="transfer-export-excel" class="btn btn-outline-success">
                            <i class="tio-download-to me-1"></i>{{ translate('excel') }}
                        </button>

                        <button type="button" id="transfer-export-pdf" class="btn btn-outline-danger">
                            <i class="tio-download-to me-1"></i>{{ translate('PDF') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('total_transfers') }}</small>
                        <h4 class="mb-0" id="stat-total-transfers">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('pending') }}</small>
                        <h4 class="mb-0" id="stat-pending">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('approved') }}</small>
                        <h4 class="mb-0" id="stat-approved">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('rejected') }}</small>
                        <h4 class="mb-0" id="stat-rejected">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('total_quantity') }}</small>
                        <h4 class="mb-0" id="stat-total-qty">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('top_route') }}</small>
                        <small class="d-block" id="stat-top-route">-</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">
                    {{ translate('stock_transfer_chart') }}

                    <small class="text-muted ms-2">
                        (<span id="chart-period">-</span>)
                    </small>
                </h4>
            </div>

            <div class="card-body">
                <canvas id="transfer-report-chart" height="400"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('transfer_details') }}
                    <small class="text-muted ms-2">
                        (<span id="transfer-period">-</span>)
                    </small>
                </h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('date') }}</th>
                            <th>{{ translate('from_branch') }}</th>
                            <th>{{ translate('to_branch') }}</th>
                            <th class="text-end">{{ translate('items') }}</th>
                            <th>{{ translate('status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="transfer-table-body">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/chart.js/dist/Chart.min.js') }}"></script>
    @include('admin-views.branch-management.partials._stock-transfer-report-js-config')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/branch-stock-transfer-report.js') }}"></script>
@endpush
