@extends('layouts.back-end.app')

@section('title', translate('wholesale_pipeline_report'))

@push('css_or_js')
    <style>
        .wholesale-pipeline-page {
            --wp-primary: #1d4ed8;
            --wp-primary-soft: rgba(29, 78, 216, 0.12);
            --wp-muted: #5f6672;
        }

        .wholesale-pipeline-page .report-hero {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(37, 99, 235, 0.26);
        }

        .wholesale-pipeline-page .kpi-card {
            border: 1px solid rgba(37, 99, 235, 0.12);
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .wholesale-pipeline-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.12);
        }

        .wholesale-pipeline-page .kpi-label {
            color: var(--wp-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 600;
        }

        .wholesale-pipeline-page .kpi-value {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .wholesale-pipeline-page .badge-soft {
            background: var(--wp-primary-soft);
            color: var(--wp-primary);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 10px;
        }

        .wholesale-pipeline-page .insight-list li {
            border-bottom: 1px dashed #d8dee8;
            padding-bottom: 10px;
            margin-bottom: 10px;
            color: #243046;
        }

        .wholesale-pipeline-page .insight-list li:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .wholesale-pipeline-page .table thead th {
            border-top: none;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-size: 0.76rem;
        }
    </style>
@endpush

@section('content')
    @php
        $isRtl =
            session('direction') === 'rtl' ||
            (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
    @endphp
    <div class="content container-fluid wholesale-pipeline-page {{ $isRtl ? 'text-end' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">{{ translate('wholesale_pipeline_report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('report_period') }}: {{ $snapshotFromDisplay }} - {{ $snapshotToDisplay }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">
                    {{ translate('updated') }}
                    {{ now()->translatedFormat('d F Y h:i A') }}
                </span>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
                            <label class="form-label mb-1">{{ translate('tier') }}</label>
                            <select class="form-control" name="tier">
                                <option value="">{{ translate('all') }}</option>
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier }}"
                                        {{ ($filters['tier'] ?? '') === $tier ? 'selected' : '' }}>{{ $tier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.wholesale.dashboard.reports.pipeline') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.pipeline', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success"><i class="tio-download-to me-1"></i>
                                {{ translate('excel') }}</a>
                            <a href="{{ route('admin.wholesale.dashboard.reports.pipeline', array_merge(request()->query(), ['download' => 'pdf'])) }}"
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
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('purchase_orders') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['purchase_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('quotations') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['quotation_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('confirmed_orders') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['confirmed_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('end_to_end_conversion') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['end_to_end_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('po_to_quote') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['purchase_to_quotation_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('quote_to_confirmed') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['quotation_to_confirmed_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('cycle_time') }}</p>
                        <p class="kpi-value">
                            {{ $kpi['avg_po_to_quote_hours'] !== null ? number_format((float) $kpi['avg_po_to_quote_hours'], 1) . 'h' : translate('na') }}
                            /
                            {{ $kpi['avg_quote_to_confirm_hours'] !== null ? number_format((float) $kpi['avg_quote_to_confirm_hours'], 1) . 'h' : translate('na') }}
                        </p>
                        <small class="text-muted">{{ translate('po_to_quote_slash_quote_to_confirmed') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('stage_trend') }}{{ $snapshotFromDisplay }} -
                            {{ $snapshotToDisplay }}</h4>
                        <span class="badge-soft">{{ translate('pipeline_velocity') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-pipeline-trend" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('stage_snapshot') }}</h4>
                        <span class="badge-soft">{{ $snapshotFromDisplay }} - {{ $snapshotToDisplay }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-stage-snapshot" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('top_product_volume') }}</h4>
                        <span class="badge-soft">{{ translate('share') }}
                            {{ number_format((float) $kpi['top_product_share'], 1) }}%</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-product-volume" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('wholesaler_tier_mix') }}</h4>
                        <span class="badge-soft">{{ translate('active_accounts') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-tier-mix" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h4 class="mb-0">{{ translate('tier_revenue_breakdown') }}{{ $snapshotFromDisplay }} -
                            {{ $snapshotToDisplay }}</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('tier') }}</th>
                                    <th class="text-end">{{ translate('orders') }}</th>
                                    <th class="text-end">{{ translate('revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tierRevenue as $row)
                                    <tr>
                                        <td>{{ $row->tier_name }}</td>
                                        <td class="text-end">{{ number_format((int) $row->orders_count) }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            {{ translate('no_tier_revenue_data_in_this_period') }}
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

        (function() {
            const stageData = @json($pipelineStageChartData);
            const trendData = @json($pipelineTrendChartData);
            const productData = @json($topProductsChartData);
            const tierMixData = @json($tierMixChartData);

            const stageCtx = document.getElementById('wholesale-stage-snapshot');
            if (stageCtx) {
                new Chart(stageCtx, {
                    type: 'bar',
                    data: {
                        labels: stageData.labels || [],
                        datasets: [{
                            label: @json(translate('count')),
                            data: stageData.counts || [],
                            backgroundColor: ['#1d4ed8', '#2563eb', '#38bdf8'],
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            const trendCtx = document.getElementById('wholesale-pipeline-trend');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                                label: @json(translate('purchase_orders')),
                                data: trendData.purchase || [],
                                borderColor: '#1d4ed8',
                                backgroundColor: 'rgba(29, 78, 216, 0.16)',
                                tension: 0.32,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('quotations')),
                                data: trendData.quotation || [],
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.16)',
                                tension: 0.32,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('confirmed_orders')),
                                data: trendData.confirmed || [],
                                borderColor: '#38bdf8',
                                backgroundColor: 'rgba(56, 189, 248, 0.18)',
                                tension: 0.32,
                                borderWidth: 2,
                                fill: false
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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            const productCtx = document.getElementById('wholesale-product-volume');
            if (productCtx) {
                const labels = (productData.labels || []).length ? productData.labels : [@json(translate('no_data'))];
                const quantities = (productData.quantities || []).length ? productData.quantities : [0];

                new Chart(productCtx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: @json(translate('units')),
                            data: quantities,
                            backgroundColor: 'rgba(14, 165, 233, 0.55)',
                            borderColor: '#0284c7',
                            borderWidth: 1.5,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            const tierCtx = document.getElementById('wholesale-tier-mix');
            if (tierCtx) {
                const labels = (tierMixData.labels || []).length ? tierMixData.labels : [@json(translate('no_data'))];
                const counts = (tierMixData.counts || []).length ? tierMixData.counts : [1];

                new Chart(tierCtx, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd',
                                '#bfdbfe'
                            ],
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
        // PDF Download with chart images - EXACTLY like CRM Insights
        $(document).ready(function() {
            $('.btn-outline-danger[href*="download=pdf"]').on('click', function(e) {
                e.preventDefault();

                // Get all chart canvases
                const stageCanvas = document.getElementById('wholesale-stage-snapshot');
                const trendCanvas = document.getElementById('wholesale-pipeline-trend');
                const productCanvas = document.getElementById('wholesale-product-volume');
                const tierCanvas = document.getElementById('wholesale-tier-mix');

                if (stageCanvas && trendCanvas && productCanvas && tierCanvas) {
                    // Convert canvases to base64 images
                    const stageImage = stageCanvas.toDataURL('image/png');
                    const trendImage = trendCanvas.toDataURL('image/png');
                    const productImage = productCanvas.toDataURL('image/png');
                    const tierImage = tierCanvas.toDataURL('image/png');

                    // Get current URL
                    const url = new URL($(this).attr('href'));

                    // Create a form to POST the images
                    const form = $('<form method="POST" action="' + url.pathname + '"></form>');
                    form.append('@csrf');
                    form.append('<input type="hidden" name="download" value="pdf">');
                    form.append('<input type="hidden" name="stage_snapshot_chart" value="' + stageImage +
                        '">');
                    form.append('<input type="hidden" name="pipeline_trend_chart" value="' + trendImage +
                        '">');
                    form.append('<input type="hidden" name="top_products_chart" value="' + productImage +
                        '">');
                    form.append('<input type="hidden" name="tier_mix_chart" value="' + tierImage + '">');

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
