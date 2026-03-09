@extends('layouts.back-end.app')

@section('title', translate('wholesale_revenue_report'))

@push('css_or_js')
    <style>
        .wholesale-report-page {
            --wr-accent: #0f766e;
            --wr-accent-soft: rgba(15, 118, 110, 0.12);
            --wr-text-muted: #5f6672;
        }

        .wholesale-report-page .report-hero {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 118, 110, 0.24);
        }

        .wholesale-report-page .kpi-card {
            border: 1px solid rgba(15, 118, 110, 0.12);
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .wholesale-report-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 118, 110, 0.1);
        }

        .wholesale-report-page .kpi-label {
            color: var(--wr-text-muted);
            font-size: 0.82rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .wholesale-report-page .kpi-value {
            font-size: 1.55rem;
            font-weight: 700;
            margin: 0;
            color: #102a43;
        }

        .wholesale-report-page .insight-list li {
            border-bottom: 1px dashed #d8dee8;
            padding-bottom: 10px;
            margin-bottom: 10px;
            color: #28364a;
        }

        .wholesale-report-page .insight-list li:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .wholesale-report-page .table thead th {
            border-top: none;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .wholesale-report-page .badge-soft {
            background: var(--wr-accent-soft);
            color: var(--wr-accent);
            font-weight: 600;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.75rem;
        }
    </style>
@endpush

@section('content')
    @php
        $isRtl = session('direction') === 'rtl'
            || (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
    @endphp
    <div class="content container-fluid wholesale-report-page {{ $isRtl ? 'text-right' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">{{ translate('wholesale_revenue_report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('report_period') }}: {{ $snapshotFrom->format('M d, Y') }} - {{ $snapshotTo->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">{{ translate('updated') }} {{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="date_type">
                                <option value="this_year" {{ ($filters['date_type'] ?? 'this_year') == 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                <option value="this_month" {{ ($filters['date_type'] ?? '') == 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                <option value="this_week" {{ ($filters['date_type'] ?? '') == 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') == 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                <option value="custom_date" {{ ($filters['date_type'] ?? '') == 'custom_date' ? 'selected' : '' }}>{{ translate('custom_range') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 custom-date-range" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2 custom-date-range" style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('wholesaler') }}</label>
                            <select class="form-control" name="wholesaler_id">
                                <option value="0">{{ translate('all') }}</option>
                                @foreach($wholesalers as $wholesaler)
                                    <option value="{{ $wholesaler->id }}" {{ (int)($filters['wholesaler_id'] ?? 0) === (int)$wholesaler->id ? 'selected' : '' }}>
                                        {{ $wholesaler->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('payment_status') }}</label>
                            <select class="form-control" name="payment_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ translate('paid') }}</option>
                                <option value="unpaid" {{ ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ translate('unpaid') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('delivery_status') }}</label>
                            <select class="form-control" name="delivery_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="pending" {{ ($filters['delivery_status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ translate('pending') }}</option>
                                <option value="partial" {{ ($filters['delivery_status'] ?? '') === 'partial' ? 'selected' : '' }}>{{ translate('partial') }}</option>
                                <option value="fulfilled" {{ ($filters['delivery_status'] ?? '') === 'fulfilled' ? 'selected' : '' }}>{{ translate('fulfilled') }}</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue', array_merge(request()->query(), ['download' => 'excel'])) }}" class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue', array_merge(request()->query(), ['download' => 'pdf'])) }}" class="btn btn-outline-danger">{{ translate('PDF') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('revenue_90d') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['total_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('paid_revenue') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['paid_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('average_order_value') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['avg_order_value'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('fulfillment_rate') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['fulfillment_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('revenue_trend_last_12_months') }}</h4>
                        <span class="badge-soft">{{ translate('orders_plus_revenue') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-revenue-trend" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('delivery_status_mix') }}</h4>
                        <span class="badge-soft">90D</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-delivery-mix" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h4 class="mb-0">{{ translate('top_wholesalers_by_revenue_90d') }}</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ translate('wholesaler') }}</th>
                                    <th>{{ translate('company') }}</th>
                                    <th class="text-end">{{ translate('orders') }}</th>
                                    <th class="text-end">{{ translate('revenue') }}</th>
                                    <th class="text-end">{{ translate('collection') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topWholesalers as $row)
                                    @php
                                        $user = $row->wholeseller;
                                        $companyName = $user?->wholesalerBusiness?->company_name ?? '-';
                                        $displayName = $user?->name ?? $user?->f_name ?? (translate('wholesaler') . ' #' . $row->wholesaler_id);
                                        $collection = (float) $row->total_revenue > 0 ? ((float) $row->paid_revenue / (float) $row->total_revenue) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-weight-semibold">{{ $displayName }}</td>
                                        <td>{{ $companyName }}</td>
                                        <td class="text-end">{{ (int) $row->orders_count }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total_revenue, 2) }}</td>
                                        <td class="text-end">{{ number_format($collection, 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">{{ translate('no_wholesale_orders_found_in_this_period') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('insights') }}</h4>
                        <span class="badge-soft">{{ translate('auto_summary') }}</span>
                    </div>
                    <div class="card-body">
                        <ol class="insight-list pl-3 mb-0">
                            @foreach ($insights as $insight)
                                <li>{{ $insight }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/chart.js/dist/Chart.min.js') }}"></script>
    <script>
        'use strict';

        (function() {
            const trendData = @json($trendChartData);
            const deliveryData = @json($deliveryChartData);
            const currencySymbol = document.getElementById('get-currency-symbol')?.dataset?.currencySymbol || '';
            const fmtMoney = (value) => `${currencySymbol}${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;

            const trendCtx = document.getElementById('wholesale-revenue-trend');
            if (trendCtx) {
                new Chart(trendCtx, {
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                                type: 'bar',
                                label: @json(translate('orders')),
                                data: trendData.orders || [],
                                backgroundColor: 'rgba(15, 118, 110, 0.22)',
                                borderColor: '#0f766e',
                                borderWidth: 1,
                                yAxisID: 'yOrders'
                            },
                            {
                                type: 'line',
                                label: @json(translate('revenue')),
                                data: trendData.revenue || [],
                                borderColor: '#0f766e',
                                backgroundColor: 'rgba(15, 118, 110, 0.12)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: false,
                                yAxisID: 'yRevenue'
                            },
                            {
                                type: 'line',
                                label: @json(translate('paid_revenue')),
                                data: trendData.paid_revenue || [],
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20, 184, 166, 0.16)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                yAxisID: 'yRevenue'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.yAxisID === 'yRevenue') {
                                            return `${context.dataset.label}: ${fmtMoney(context.raw)}`;
                                        }
                                        return `${context.dataset.label}: ${Number(context.raw || 0).toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            yRevenue: {
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    callback: (value) => fmtMoney(value)
                                },
                                grid: {
                                    color: 'rgba(16, 42, 67, 0.08)'
                                }
                            },
                            yOrders: {
                                type: 'linear',
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            const deliveryCtx = document.getElementById('wholesale-delivery-mix');
            if (deliveryCtx) {
                const labels = (deliveryData.labels || []).length ? deliveryData.labels : [@json(translate('no_data'))];
                const counts = (deliveryData.counts || []).length ? deliveryData.counts : [1];

                new Chart(deliveryCtx, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: ['#0f766e', '#14b8a6', '#f59e0b', '#64748b', '#ef4444', '#8b5cf6'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        })();

        $(document).ready(function() {
            $('#date_type').on('change', function() {
                if ($(this).val() === 'custom_date') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            });
        });
    </script>
@endpush
