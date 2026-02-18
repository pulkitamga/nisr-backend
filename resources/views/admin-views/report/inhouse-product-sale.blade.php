@extends('layouts.back-end.app')
@section('title', translate('inhouse_product_sale_report'))
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
        .report-kpi-meta {
            font-size: 12px;
            color: #4b5563;
        }
        .report-chart-card {
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
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/inhouse_sale.png')}}" alt="">
                {{ translate('inhouse_sales_analytics') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('category') }}</label>
                            <select class="js-select2-custom form-control" name="category_id">
                                <option value="all">{{ translate('all') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['id'] }}" {{ ($filters['category_id'] ?? 'all') == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
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
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.report.inhouse-product-sale') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.report.inhouse-product-sale-export-excel', request()->query()) }}" class="btn btn-outline-success">
                                <i class="tio-download-to mr-1"></i> {{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.report.inhouse-product-sale-export-pdf', request()->query()) }}" class="btn btn-outline-danger">
                                <i class="tio-download-to mr-1"></i> {{ translate('PDF') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('total_sales') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['total_amount']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('qty') }}: {{ $summary['total_qty'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('POS') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['pos_amount']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('qty') }}: {{ $summary['pos_qty'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('online') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['online_amount']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('qty') }}: {{ $summary['online_qty'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('wholesale') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['wholesale_amount']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('qty') }}: {{ $summary['wholesale_qty'] }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-lg-8">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('sales_by_date') }}</h4>
                    <div id="inhouse-sales-trend-chart"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('channel_mix') }}</h4>
                    <div id="inhouse-sales-channel-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('branch_and_sales_type') }}</h4>
                    <div id="inhouse-branch-type-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('sales_type_and_product') }}</h4>
                    <div id="inhouse-product-type-chart"></div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('branch_and_product') }}</h4>
                    <div id="inhouse-branch-product-chart"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('POS') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th class="text-center">{{ translate('qty') }}</th>
                        <th class="text-center">{{ translate('orders') }}</th>
                        <th class="text-end">{{ translate('sales') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($posRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td class="text-center">{{ $row->total_qty }}</td>
                            <td class="text-center">{{ $row->total_orders }}</td>
                            <td class="text-end">
                                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->total_amount), currencyCode: getCurrencyCode()) }}
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
                <h4 class="mb-0">{{ translate('online') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th class="text-center">{{ translate('qty') }}</th>
                        <th class="text-center">{{ translate('orders') }}</th>
                        <th class="text-end">{{ translate('sales') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($onlineRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td class="text-center">{{ $row->total_qty }}</td>
                            <td class="text-center">{{ $row->total_orders }}</td>
                            <td class="text-end">
                                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->total_amount), currencyCode: getCurrencyCode()) }}
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
                <h4 class="mb-0">{{ translate('wholesale') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('branch') }}</th>
                        <th class="text-center">{{ translate('qty') }}</th>
                        <th class="text-center">{{ translate('orders') }}</th>
                        <th class="text-end">{{ translate('sales') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($wholesaleRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td class="text-center">{{ $row->total_qty }}</td>
                            <td class="text-center">{{ $row->total_orders }}</td>
                            <td class="text-end">
                                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->total_amount), currencyCode: getCurrencyCode()) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">{{ translate('no_data_found') }}</td></tr>
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
            const posLabel = @json(translate('POS'));
            const onlineLabel = @json(translate('online'));
            const wholesaleLabel = @json(translate('wholesale'));
            const salesLabel = @json(translate('sales'));

            const renderChart = (selector, options) => {
                const el = document.querySelector(selector);
                if (!el) return;
                new ApexCharts(el, options).render();
            };

            renderChart("#inhouse-sales-trend-chart", {
                chart: {type: 'line', height: 320, toolbar: {show: false}},
                stroke: {curve: 'smooth', width: 3},
                series: [
                    {name: posLabel, data: chartData.trend_pos},
                    {name: onlineLabel, data: chartData.trend_online},
                    {name: wholesaleLabel, data: chartData.trend_wholesale}
                ],
                xaxis: {categories: chartData.trend_labels},
                dataLabels: {enabled: false},
                colors: ['#1f8ef1', '#22c55e', '#f59e0b']
            });

            renderChart("#inhouse-sales-channel-chart", {
                chart: {type: 'donut', height: 320},
                series: chartData.channel_values,
                labels: [posLabel, onlineLabel, wholesaleLabel],
                colors: ['#1f8ef1', '#22c55e', '#f59e0b'],
                legend: {position: 'bottom'}
            });

            renderChart("#inhouse-branch-type-chart", {
                chart: {type: 'bar', height: 330, stacked: true, toolbar: {show: false}},
                series: [
                    {name: posLabel, data: chartData.branch_type_pos},
                    {name: onlineLabel, data: chartData.branch_type_online},
                    {name: wholesaleLabel, data: chartData.branch_type_wholesale}
                ],
                xaxis: {categories: chartData.branch_type_labels, labels: {rotate: -30}},
                dataLabels: {enabled: false},
                colors: ['#1f8ef1', '#22c55e', '#f59e0b']
            });

            renderChart("#inhouse-product-type-chart", {
                chart: {type: 'bar', height: 330, stacked: true, toolbar: {show: false}},
                series: [
                    {name: posLabel, data: chartData.product_type_pos},
                    {name: onlineLabel, data: chartData.product_type_online},
                    {name: wholesaleLabel, data: chartData.product_type_wholesale}
                ],
                xaxis: {categories: chartData.product_type_labels, labels: {rotate: -35}},
                dataLabels: {enabled: false},
                colors: ['#1f8ef1', '#22c55e', '#f59e0b']
            });

            renderChart("#inhouse-branch-product-chart", {
                chart: {type: 'bar', height: 360, toolbar: {show: false}},
                plotOptions: {bar: {horizontal: true, borderRadius: 4}},
                series: [{name: salesLabel, data: chartData.branch_product_values}],
                xaxis: {categories: chartData.branch_product_labels},
                dataLabels: {enabled: false},
                colors: ['#0ea5e9']
            });
        })();
    </script>
@endpush
