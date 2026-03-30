@extends('layouts.back-end.app')
@section('title', translate('product_stock_analytics_report'))

@php
    use Carbon\Carbon;

    if (
        isset($filters['date_type']) &&
        $filters['date_type'] == 'custom_date' &&
        !empty($filters['from']) &&
        !empty($filters['to'])
    ) {
        $fromDate = Carbon::parse($filters['from'])->format('d M, Y');
        $toDate = Carbon::parse($filters['to'])->format('d M, Y');
        $dateRange = $fromDate . ' - ' . $toDate;
    } else {
        $dateType = $filters['date_type'] ?? 'this_year';
        switch ($dateType) {
            case 'this_year':
                $fromDate = Carbon::now()->startOfYear()->format('d M, Y');
                $toDate = Carbon::now()->endOfYear()->format('d M, Y');
                $dateRange = $fromDate . ' - ' . $toDate;
                break;
            case 'this_month':
                $fromDate = Carbon::now()->startOfMonth()->format('d M, Y');
                $toDate = Carbon::now()->endOfMonth()->format('d M, Y');
                $dateRange = $fromDate . ' - ' . $toDate;
                break;
            case 'this_week':
                $fromDate = Carbon::now()->startOfWeek()->format('d M, Y');
                $toDate = Carbon::now()->endOfWeek()->format('d M, Y');
                $dateRange = $fromDate . ' - ' . $toDate;
                break;
            case 'today':
                $dateRange = Carbon::now()->format('d M, Y');
                break;
            default:
                $fromDate = Carbon::now()->startOfYear()->format('d M, Y');
                $toDate = Carbon::now()->endOfYear()->format('d M, Y');
                $dateRange = $fromDate . ' - ' . $toDate;
        }
    }
    $updatedAt = Carbon::now()->format('M d, Y h:i A');
@endphp

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .list-unstyled {
            padding-left: 20px !important;
        }

        .report-hero {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(15, 118, 110, 0.24);
            width: 100%;
        }

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
    <div class="content container-fluid {{ $isRtl ? 'text-end' : 'text-start' }}">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="report-hero d-flex justify-content-between align-items-center flex-wrap">

                <!-- Left Side -->
                <div>
                    <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/seller_sale.png') }}"
                            alt="">
                        {{ translate('product_stock_analytics_report') }}
                    </h2>

                    <p class="mb-0 text-white">
                        <strong>{{ translate('report_period') }}:</strong> {{ $dateRange }}
                    </p>
                </div>

                <!-- Right Side -->
                <div class="mt-2 mt-md-0">
                    <div class="badge badge-soft-dark px-3 py-2">
                        <i class="tio-date-range"></i> {{ translate('updated') }} {{ $updatedAt }}
                    </div>
                </div>
            </div>
        </div>

        @include('admin-views.report.product-report-inline-menu')

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.stock.product-stock') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3" style="margin-bottom: 20px;">
                            <label class="form-label mb-1">{{ translate('category') }}</label>
                            <select class="js-select2-custom form-control" name="category_id">
                                <option value="all">{{ translate('all') }}</option>
                                @foreach ($categories as $category)
                                    @php($categoryName = $category->getTranslatedField('name') ?? '#' . $category->id)
                                    <option value="{{ $category->id }}"
                                        {{ ($filters['category_id'] ?? 'all') == $category->id ? 'selected' : '' }}>
                                        {{ $categoryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('product') }}</label>
                            <select class="js-select2-custom form-control" name="product_ids[]" multiple>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ in_array($product->id, $filters['product_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('branch') }}</label>
                            <select class="js-select2-custom form-control" name="branch_ids[]" multiple>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ in_array($branch->id, $filters['branch_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>

                        <div class="col-md-3" style="margin-bottom: 20px;">
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="product_stock_date_type">
                                <option value="this_year"
                                    {{ ($filters['date_type'] ?? 'this_year') === 'this_year' ? 'selected' : '' }}>
                                    {{ translate('this_year') }}</option>
                                <option value="this_month"
                                    {{ ($filters['date_type'] ?? '') === 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_month') }}</option>
                                <option value="this_week"
                                    {{ ($filters['date_type'] ?? '') === 'this_week' ? 'selected' : '' }}>
                                    {{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') === 'today' ? 'selected' : '' }}>
                                    {{ translate('today') }}</option>
                                <option value="custom_date"
                                    {{ ($filters['date_type'] ?? '') === 'custom_date' ? 'selected' : '' }}>
                                    {{ translate('custom_range') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2 custom-date-range" id="product_stock_from_div"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                        </div>

                        <div class="col-md-2 custom-date-range" id="product_stock_to_div"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                        </div>

                        <div class="col-md-12">
                            <label class="d-flex align-items-center gap-2 mb-0 mt-1">
                                <input type="checkbox" name="include_internal_transfer" value="1"
                                    {{ !empty($filters['include_internal_transfer']) ? 'checked' : '' }}>
                                <span>{{ translate('include_internal_transfers') }}</span>
                            </label>
                        </div>

                        <div class="col-md-12 d-flex flex-wrap gap-2 mt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.stock.product-stock') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.stock.product-stock-export', request()->query()) }}"
                                class="btn btn-outline-success">
                                <i class="tio-download-to me-1"></i>{{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.stock.product-stock-export-pdf', request()->query()) }}"
                                class="btn btn-outline-danger">
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
                    <div class="stock-kpi-value">{{ number_format((int) ($summary['total_current_stock'] ?? 0)) }}</div>
                    <div class="stock-kpi-meta">{{ translate('products_count') }}:
                        {{ number_format((int) ($summary['products_count'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('total_stock_in') }}</div>
                    <div class="stock-kpi-value text-success">{{ number_format((int) ($summary['total_stock_in'] ?? 0)) }}
                    </div>
                    <div class="stock-kpi-meta">{{ translate('from') }} {{ $filters['from'] ?? '' }}
                        {{ translate('to') }} {{ $filters['to'] ?? '' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('total_stock_out') }}</div>
                    <div class="stock-kpi-value text-danger">{{ number_format((int) ($summary['total_stock_out'] ?? 0)) }}
                    </div>
                    <div class="stock-kpi-meta">{{ translate('branches_count') }}:
                        {{ number_format((int) ($summary['branches_count'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stock-kpi-card">
                    <div class="stock-kpi-title">{{ translate('net_stock_movement') }}</div>
                    <div
                        class="stock-kpi-value {{ ((int) ($summary['net_stock_movement'] ?? 0)) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format((int) ($summary['net_stock_movement'] ?? 0)) }}
                    </div>
                    <div class="stock-kpi-meta">{{ translate('stock_report') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-lg-8">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_movement_by_date') }} ({{ $dateRange }})</h4>
                    <div id="stock-movement-date-chart"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_by_branch_chart') }} ({{ $dateRange }})</h4>
                    <div id="stock-by-branch-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('stock_by_product_chart') }} ({{ $dateRange }})</h4>
                    <div id="stock-by-product-chart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stock-chart-card">
                    <h4 class="mb-2">{{ translate('branch_and_product_chart') }} ({{ $dateRange }})</h4>
                    <div id="stock-branch-product-chart"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_product') }} ({{ $dateRange }})</h4>
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
                        @forelse($stockByProductRows ?? [] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td class="text-center">{{ number_format((int) $row->current_stock) }}</td>
                                <td class="text-center text-success">{{ number_format((int) $row->stock_in) }}</td>
                                <td class="text-center text-danger">{{ number_format((int) $row->stock_out) }}</td>
                                <td
                                    class="text-center {{ (int) $row->net_movement >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format((int) $row->net_movement) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_branch') }} ({{ $dateRange }})</h4>
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
                        @forelse($stockByBranchRows ?? [] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->branch_name }}</td>
                                <td class="text-center">{{ number_format((int) $row->current_stock) }}</td>
                                <td class="text-center">{{ number_format((int) $row->products_count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_by_branch_and_product') }} ({{ $dateRange }})</h4>
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
                        @forelse($stockByBranchProductRows ?? [] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->branch_name }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td class="text-center">{{ number_format((int) $row->current_stock) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('stock_movement_history') }} ({{ $dateRange }})</h4>
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
                        @forelse($movementRows ?? [] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{ $row->product_name }}
                                    @if (!empty($row->variation))
                                        <small class="d-block text-muted">{{ translate('variation') }}:
                                            {{ $row->variation }}</small>
                                    @endif
                                </td>
                                <td>{{ $row->branch_name }}</td>
                                <td>
                                    <span class="{{ $row->type === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                                        {{ $row->type === 'IN' ? translate('stock_in') : translate('stock_out') }}
                                    </span>
                                </td>
                                <td class="{{ $row->type === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ $row->type === 'IN' ? '+' : '-' }} {{ number_format((int) $row->quantity) }}
                                </td>
                                <td>{{ $row->category }}</td>
                                <td>
                                    <div>{{ $row->reference }}</div>
                                    @if (!empty($row->remarks))
                                        <small class="text-muted d-block">{{ $row->remarks }}</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chart ?? []);
            console.log('Chart Data:', chartData);

            const stockInLabel = @json(translate('stock_in'));
            const stockOutLabel = @json(translate('stock_out'));
            const stockLabel = @json(translate('current_stock'));

            function renderChart(selector, options) {
                const el = document.querySelector(selector);
                if (!el) {
                    console.warn('Element not found:', selector);
                    return;
                }
                const hasData = options.series && options.series.some(s => s.data && s.data.length > 0);
                if (!hasData) {
                    el.innerHTML = '<div class="text-center py-4">{{ translate('no_data_available') }}</div>';
                    return;
                }

                try {
                    new ApexCharts(el, options).render();
                } catch (e) {
                    console.error('Chart render error:', e);
                    el.innerHTML =
                        '<div class="text-center py-4 text-danger">{{ translate('error_loading_chart') }}</div>';
                }
            }

            renderChart('#stock-movement-date-chart', {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                series: [{
                        name: stockInLabel,
                        data: chartData.date_stock_in || []
                    },
                    {
                        name: stockOutLabel,
                        data: chartData.date_stock_out || []
                    }
                ],
                xaxis: {
                    categories: chartData.date_labels || []
                },
                colors: ['#16a34a', '#dc2626'],
                dataLabels: {
                    enabled: false
                }
            });

            renderChart('#stock-by-branch-chart', {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: stockLabel,
                    data: chartData.branch_values || []
                }],
                xaxis: {
                    categories: chartData.branch_labels || [],
                    labels: {
                        rotate: -30
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#0ea5e9']
            });

            renderChart('#stock-by-product-chart', {
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 4
                    }
                },
                series: [{
                    name: stockLabel,
                    data: chartData.product_values || []
                }],
                xaxis: {
                    categories: chartData.product_labels || []
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#2563eb']
            });

            renderChart('#stock-branch-product-chart', {
                chart: {
                    type: 'bar',
                    height: 340,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4
                    }
                },
                series: [{
                    name: stockLabel,
                    data: chartData.branch_product_values || []
                }],
                xaxis: {
                    categories: chartData.branch_product_labels || []
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#7c3aed']
            });

            setTimeout(async () => {

                const charts = [{
                        selector: '#stock-movement-date-chart',
                        key: 'movement'
                    },
                    {
                        selector: '#stock-by-branch-chart',
                        key: 'branch'
                    },
                    {
                        selector: '#stock-by-product-chart',
                        key: 'product'
                    },
                    {
                        selector: '#stock-branch-product-chart',
                        key: 'branchProduct'
                    }
                ];

                let chartImages = {};

                for (let chart of charts) {
                    const el = document.querySelector(chart.selector);

                    if (el && el.__apexcharts__) {
                        el.style.width = "800px";
                        el.style.height = "350px";

                        await new Promise(r => setTimeout(r, 300)); 

                        const {
                            imgURI
                        } = await el.__apexcharts__.dataURI();
                        chartImages[chart.key] = imgURI;
                    }
                }

                // 🔥 send to backend
                fetch("{{ route('admin.stock.product-stock-export-pdf') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            filters: @json($filters),
                            chartImages: chartImages
                        })
                    })
                    .then(res => res.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        window.open(url); // open PDF
                    });

            }, 2500);

            if (typeof $ !== 'undefined') {
                $('#product_stock_date_type').on('change', function() {
                    const isCustom = $(this).val() === 'custom_date';
                    $('.custom-date-range').toggle(isCustom);
                });
            } else {
                const dateTypeSelect = document.getElementById('product_stock_date_type');
                if (dateTypeSelect) {
                    dateTypeSelect.addEventListener('change', function() {
                        const isCustom = this.value === 'custom_date';
                        document.querySelectorAll('.custom-date-range').forEach(el => {
                            el.style.display = isCustom ? '' : 'none';
                        });
                    });
                }
            }
        });
    </script>
@endpush
