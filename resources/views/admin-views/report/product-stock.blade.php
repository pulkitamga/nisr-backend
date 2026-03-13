@extends('layouts.back-end.app')
@section('title', translate('product_stock_analytics_report'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .stock-kpi-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            background: #fff;
            height: 100%;
        }

        .stock-kpi-title {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .stock-kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .stock-kpi-meta {
            font-size: 12px;
            color: #4b5563;
        }

        .stock-chart-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
            height: 100%;
        }
    </style>
@endpush

@section('content')
    @php($isRtl = Session::get('direction') === 'rtl')
    <div class="content container-fluid {{ $isRtl ? 'text-right' : 'text-left' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/seller_sale.png') }}" alt="">
                {{ translate('product_stock_analytics_report') }}
            </h2>
        </div>

        @include('admin-views.report.product-report-inline-menu')

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.stock.product-stock') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('category') }}</label>
                            <select class="js-select2-custom form-control" name="category_id">
                                <option value="all">{{ translate('all') }}</option>
                                @foreach($categories as $category)
                                    @php($categoryName = $category->name ?? $category->default_name ?? ('#' . $category->id))
                                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? 'all') == $category->id ? 'selected' : '' }}>
                                        {{ $categoryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('product') }}</label>
                            <select class="js-select2-custom form-control" name="product_ids[]" multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ in_array($product->id, $filters['product_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('branch') }}</label>
                            <select class="js-select2-custom form-control" name="branch_ids[]" multiple>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ in_array($branch->id, $filters['branch_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="product_stock_date_type">
                                <option value="this_year" {{ ($filters['date_type'] ?? 'this_year') === 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                <option value="this_month" {{ ($filters['date_type'] ?? '') === 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                <option value="this_week" {{ ($filters['date_type'] ?? '') === 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') === 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                <option value="custom_date" {{ ($filters['date_type'] ?? '') === 'custom_date' ? 'selected' : '' }}>{{ translate('custom_range') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2 custom-date-range" id="product_stock_from_div" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
                        </div>

                        <div class="col-md-2 custom-date-range" id="product_stock_to_div" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
                        </div>

                        <div class="col-md-12">
                            <label class="d-flex align-items-center gap-2 mb-0 mt-1">
                                <input type="checkbox" name="include_internal_transfer" value="1" {{ !empty($filters['include_internal_transfer']) ? 'checked' : '' }}>
                                <span>{{ translate('include_internal_transfers') }}</span>
                            </label>
                        </div>

                        <div class="col-md-12 d-flex flex-wrap gap-2 mt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.stock.product-stock') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.stock.product-stock-export', request()->query()) }}" class="btn btn-outline-success">
                                <i class="tio-download-to me-1"></i>{{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.stock.product-stock-export-pdf', request()->query()) }}" class="btn btn-outline-danger">
                                <i class="tio-download-to me-1"></i>{{ translate('PDF') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('total_current_stock') }}</div>
                    <div class="stock-kpi-value">{{ number_format((int)$summary['total_current_stock']) }}</div>
                    <div class="stock-kpi-meta">{{ translate('products_count') }}: {{ $summary['products_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('total_stock_in') }}</div>
                    <div class="stock-kpi-value text-success">{{ number_format((int)$summary['total_stock_in']) }}</div>
                    <div class="stock-kpi-meta">{{ translate('from') }} {{ $filters['from'] }} {{ translate('to') }} {{ $filters['to'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('total_stock_out') }}</div>
                    <div class="stock-kpi-value text-danger">{{ number_format((int)$summary['total_stock_out']) }}</div>
                    <div class="stock-kpi-meta">{{ translate('branches_count') }}: {{ $summary['branches_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('net_stock_movement') }}</div>
                    <div class="stock-kpi-value {{ (int)$summary['net_stock_movement'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format((int)$summary['net_stock_movement']) }}
                    </div>
                    <div class="stock-kpi-meta">{{ translate('stock_report') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-lg-8">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_movement_by_date') }}</h4>
                    <div id="stock-movement-date-chart"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_by_branch_chart') }}</h4>
                    <div id="stock-by-branch-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_by_product_chart') }}</h4>
                    <div id="stock-by-product-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('branch_and_product_chart') }}</h4>
                    <div id="stock-branch-product-chart"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_product') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th class="text-center">{{ translate('current_stock') }}</th>
                        <th class="text-center">{{ translate('stock_in') }}</th>
                        <th class="text-center">{{ translate('stock_out') }}</th>
                        <th class="text-center">{{ translate('net_stock_movement') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($stockByProductRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td class="text-center">{{ number_format((int)$row->current_stock) }}</td>
                            <td class="text-center text-success">{{ number_format((int)$row->stock_in) }}</td>
                            <td class="text-center text-danger">{{ number_format((int)$row->stock_out) }}</td>
                            <td class="text-center {{ (int)$row->net_movement >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format((int)$row->net_movement) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">{{ translate('no_data_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_branch') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th class="text-center">{{ translate('current_stock') }}</th>
                        <th class="text-center">{{ translate('products_count') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($stockByBranchRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td class="text-center">{{ number_format((int)$row->current_stock) }}</td>
                            <td class="text-center">{{ number_format((int)$row->products_count) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">{{ translate('no_data_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_branch_and_product') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th class="text-center">{{ translate('current_stock') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($stockByBranchProductRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td class="text-center">{{ number_format((int)$row->current_stock) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">{{ translate('no_data_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_movement_history') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('date') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th>{{ translate('type') }}</th>
                        <th>{{ translate('quantity') }}</th>
                        <th>{{ translate('category') }}</th>
                        <th>{{ translate('reference') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($movementRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $row->product_name }}
                                @if(!empty($row->variation))
                                    <small class="d-block text-muted">{{ translate('variation') }}: {{ $row->variation }}</small>
                                @endif
                            </td>
                            <td>{{ $row->branch_name }}</td>
                            <td>
                                <span class="{{ $row->type === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ $row->type === 'IN' ? translate('stock_in') : translate('stock_out') }}
                                </span>
                            </td>
                            <td class="{{ $row->type === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                                {{ $row->type === 'IN' ? '+' : '-' }} {{ number_format((int)$row->quantity) }}
                            </td>
                            <td>{{ $row->category }}</td>
                            <td>
                                <div>{{ $row->reference }}</div>
                                @if(!empty($row->remarks))
                                    <small class="text-muted d-block">{{ $row->remarks }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">{{ translate('no_data_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js') }}"></script>
    <script>
        (function () {
            const chartData = @json($chart);
            const stockInLabel = @json(translate('stock_in'));
            const stockOutLabel = @json(translate('stock_out'));
            const stockLabel = @json(translate('current_stock'));

            const renderChart = (selector, options) => {
                const el = document.querySelector(selector);
                if (!el) return;
                new ApexCharts(el, options).render();
            };

            renderChart('#stock-movement-date-chart', {
                chart: {type: 'line', height: 320, toolbar: {show: false}},
                stroke: {curve: 'smooth', width: 3},
                series: [
                    {name: stockInLabel, data: chartData.date_stock_in || []},
                    {name: stockOutLabel, data: chartData.date_stock_out || []}
                ],
                xaxis: {categories: chartData.date_labels || []},
                colors: ['#16a34a', '#dc2626'],
                dataLabels: {enabled: false}
            });

            renderChart('#stock-by-branch-chart', {
                chart: {type: 'bar', height: 320, toolbar: {show: false}},
                series: [{name: stockLabel, data: chartData.branch_values || []}],
                xaxis: {categories: chartData.branch_labels || [], labels: {rotate: -30}},
                dataLabels: {enabled: false},
                colors: ['#0ea5e9']
            });

            renderChart('#stock-by-product-chart', {
                chart: {type: 'bar', height: 320, toolbar: {show: false}},
                plotOptions: {bar: {horizontal: true, borderRadius: 4}},
                series: [{name: stockLabel, data: chartData.product_values || []}],
                xaxis: {categories: chartData.product_labels || []},
                dataLabels: {enabled: false},
                colors: ['#2563eb']
            });

            renderChart('#stock-branch-product-chart', {
                chart: {type: 'bar', height: 340, toolbar: {show: false}},
                plotOptions: {bar: {horizontal: true, borderRadius: 4}},
                series: [{name: stockLabel, data: chartData.branch_product_values || []}],
                xaxis: {categories: chartData.branch_product_labels || []},
                dataLabels: {enabled: false},
                colors: ['#7c3aed']
            });

            $('#product_stock_date_type').on('change', function () {
                const isCustom = $(this).val() === 'custom_date';
                $('.custom-date-range').toggle(isCustom);
            });
        })();
    </script>
@endpush
