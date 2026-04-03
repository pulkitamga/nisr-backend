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
        $isRtl =
            session('direction') === 'rtl' ||
            (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
    @endphp
    <div class="content container-fluid wholesale-report-page {{ $isRtl ? 'text-end' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">{{ translate('wholesale_revenue_report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('report_period') }}: {{ $dateRange }}
                    </p>

                </div>
                <span class="badge badge-light text-dark">{{ translate('updated') }}
                    {{ now()->translatedFormat('d F Y h:i A') }}</span>

            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="date_type">
                                <option value="this_year"
                                    {{ ($filters['date_type'] ?? 'this_year') == 'this_year' ? 'selected' : '' }}>
                                    {{ translate('this_year') }}
                                </option>
                                <option value="this_month"
                                    {{ ($filters['date_type'] ?? '') == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_month') }}
                                </option>
                                <option value="this_week"
                                    {{ ($filters['date_type'] ?? '') == 'this_week' ? 'selected' : '' }}>
                                    {{ translate('this_week') }}
                                </option>
                                <option value="today" {{ ($filters['date_type'] ?? '') == 'today' ? 'selected' : '' }}>
                                    {{ translate('today') }}
                                </option>
                                <option value="custom_date"
                                    {{ ($filters['date_type'] ?? '') == 'custom_date' ? 'selected' : '' }}>
                                    {{ translate('custom_range') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 custom-date-range"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2 custom-date-range"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('wholesaler') }}</label>
                            <select class="form-control" name="wholesaler_id">
                                <option value="0">{{ translate('all') }}</option>
                                @foreach ($wholesalers as $wholesaler)
                                    <option value="{{ $wholesaler->id }}"
                                        {{ (int) ($filters['wholesaler_id'] ?? 0) === (int) $wholesaler->id ? 'selected' : '' }}>
                                        {{ $wholesaler->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('payment_status') }}</label>
                            <select class="form-control" name="payment_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="paid"
                                    {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>
                                    {{ translate('paid') }}
                                </option>
                                <option value="unpaid"
                                    {{ ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>
                                    {{ translate('unpaid') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('delivery_status') }}</label>
                            <select class="form-control" name="delivery_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="pending"
                                    {{ ($filters['delivery_status'] ?? '') === 'pending' ? 'selected' : '' }}>
                                    {{ translate('pending') }}
                                </option>
                                <option value="partial"
                                    {{ ($filters['delivery_status'] ?? '') === 'partial' ? 'selected' : '' }}>
                                    {{ translate('partial') }}
                                </option>
                                <option value="fulfilled"
                                    {{ ($filters['delivery_status'] ?? '') === 'fulfilled' ? 'selected' : '' }}>
                                    {{ translate('fulfilled') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success"><i class="tio-download-to me-1"></i>
                                {{ translate('excel') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.revenue', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                                class="btn btn-outline-danger"> <i class="tio-download-to me-1"></i>
                                {{ translate('PDF') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    @php
                        $start = \Carbon\Carbon::parse($snapshotFrom);
                        $end = \Carbon\Carbon::parse($snapshotTo);

                        if ($start->format('M Y') === $end->format('M Y')) {
                            $shortDateRange = $start->translatedFormat('d') . '–' . $end->translatedFormat('d M Y');
                        } else {
                            $shortDateRange = $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M Y');
                        }
                    @endphp
                    <div class="card-body">
                        {{ translate('revenue') }} ({{ $shortDateRange }})
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
                        <h4 class="mb-0"> {{ translate('revenue_trend') }} ({{ $dateRange }})</h4>
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
                        <span class="badge-soft">{{ $dateRange }}</span>
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
                        <div class="card-header border-0">
                            <h4 class="mb-0">
                                {{ translate('top_wholesalers_by_revenue') }} ({{ $dateRange }})
                            </h4>
                        </div>
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
                                        $displayName =
                                            $user?->name ??
                                            ($user?->f_name ?? translate('wholesaler') . ' #' . $row->wholesaler_id);
                                        $collection =
                                            (float) $row->total_revenue > 0
                                                ? ((float) $row->paid_revenue / (float) $row->total_revenue) * 100
                                                : 0;
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
                                        <td colspan="6" class="text-center text-muted py-4">
                                            {{ translate('no_wholesale_orders_found_in_this_period') }}
                                        </td>
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
                        <ol class="insight-list ps-3 mb-0">
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

        $(document).ready(function() {
            // Date range toggle
            $('#date_type').on('change', function() {
                if ($(this).val() === 'custom_date') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            });

            // Get chart data from PHP
            const trendData = @json($trendChartData);
            const deliveryData = @json($deliveryChartData);

            console.log('Trend Data:', trendData);
            console.log('Delivery Data:', deliveryData);

            // Format money function
            const currencySymbol = '{{ session('currency_symbol ') ?? '$' }}';
            const fmtMoney = (value) => `${currencySymbol}${Number(value || 0).toFixed(2)}`;

            // Revenue Trend Chart
            const trendCtx = document.getElementById('wholesale-revenue-trend');
            if (trendCtx && trendData && trendData.labels && trendData.labels.length > 0) {
                new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: trendData.labels,
                        datasets: [{
                                label: '{{ translate('orders ') }}',
                                data: trendData.orders || [],
                                backgroundColor: 'rgba(15, 118, 110, 0.22)',
                                borderColor: '#0f766e',
                                borderWidth: 1,
                                type: 'bar',
                                yAxisID: 'A'
                            },
                            {
                                label: '{{ translate('revenue ') }}',
                                data: trendData.revenue || [],
                                borderColor: '#0f766e',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: false,
                                type: 'line',
                                yAxisID: 'B'
                            },
                            {
                                label: '{{ translate('paid_revenue ') }}',
                                data: trendData.paid_revenue || [],
                                borderColor: '#14b8a6',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                type: 'line',
                                yAxisID: 'B'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [{
                                    id: 'A',
                                    type: 'linear',
                                    position: 'left',
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 1,
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '{{ translate('
                                                                            orders ') }}'
                                    }
                                },
                                {
                                    id: 'B',
                                    type: 'linear',
                                    position: 'right',
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            return fmtMoney(value);
                                        }
                                    },
                                    gridLines: {
                                        drawOnChartArea: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '{{ translate('
                                                                            revenue ') }}'
                                    }
                                }
                            ]
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const dataset = data.datasets[tooltipItem.datasetIndex];
                                    const label = dataset.label || '';
                                    const value = tooltipItem.yLabel;

                                    if (dataset.yAxisID === 'B') {
                                        return label + ': ' + fmtMoney(value);
                                    }
                                    return label + ': ' + value.toLocaleString();
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        }
                    }
                });
            } else {
                console.log('No trend data available');
                if (trendCtx) {
                    trendCtx.parentNode.innerHTML =
                        '<div class="text-center py-4">{{ translate('
                                        no_data_available ') }}</div>';
                }
            }

            // Delivery Status Chart
            const deliveryCtx = document.getElementById('wholesale-delivery-mix');
            if (deliveryCtx && deliveryData && deliveryData.labels && deliveryData.labels.length > 0) {
                const total = deliveryData.counts.reduce((a, b) => a + b, 0);

                new Chart(deliveryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: deliveryData.labels,
                        datasets: [{
                            data: deliveryData.counts,
                            backgroundColor: ['#0f766e', '#14b8a6', '#f59e0b', '#64748b', '#ef4444',
                                '#8b5cf6'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const label = data.labels[tooltipItem.index] || '';
                                    const value = data.datasets[0].data[tooltipItem.index];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) :
                                        0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                });
            } else {
                console.log('No delivery data available');
                if (deliveryCtx) {
                    deliveryCtx.parentNode.innerHTML =
                        '<div class="text-center py-4">{{ translate('
                                        no_data_available ') }}</div>';
                }
            }
        });
        // PDF Download with chart images - EXACTLY like CRM Insights
        $(document).ready(function() {
            $('.btn-outline-danger[href*="download=pdf"]').on('click', function(e) {
                e.preventDefault();

                // Get the trend and delivery chart canvases
                const trendCanvas = document.getElementById('wholesale-revenue-trend');
                const deliveryCanvas = document.getElementById('wholesale-delivery-mix');

                if (trendCanvas && deliveryCanvas) {
                    // Convert canvases to base64 images
                    const trendImage = trendCanvas.toDataURL('image/png');
                    const deliveryImage = deliveryCanvas.toDataURL('image/png');

                    // Get current URL
                    const url = new URL($(this).attr('href'));

                    // Create a form to POST the images
                    const form = $('<form method="POST" action="' + url.pathname + '"></form>');
                    form.append('@csrf');
                    form.append('<input type="hidden" name="download" value="pdf">');
                    form.append('<input type="hidden" name="trend_chart" value="' + trendImage + '">');
                    form.append('<input type="hidden" name="delivery_chart" value="' + deliveryImage +
                        '">');

                    // Add all current query parameters from the URL
                    url.searchParams.forEach((value, key) => {
                        form.append('<input type="hidden" name="' + key + '" value="' + value +
                            '">');
                    });

                    $('body').append(form);
                    form.submit();
                } else {
                    // Fallback to regular link if canvases not found
                    window.location.href = $(this).attr('href');
                }
            });
        });
    </script>
@endpush
