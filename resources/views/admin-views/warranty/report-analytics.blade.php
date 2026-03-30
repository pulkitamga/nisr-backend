@extends('layouts.back-end.app')

@section('title', translate('warranty_analytics_report'))

@push('css_or_js')
    <style>
        .warranty-analytics-page {
            --wa-primary: #b45309;
            --wa-primary-soft: rgba(180, 83, 9, 0.14);
            --wa-muted: #5f6672;
        }

        .warranty-analytics-page .report-hero {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(217, 119, 6, 0.26);
        }

        .warranty-analytics-page .kpi-card {
            border: 1px solid rgba(180, 83, 9, 0.14);
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .warranty-analytics-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(180, 83, 9, 0.12);
        }

        .warranty-analytics-page .kpi-label {
            color: var(--wa-muted);
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .warranty-analytics-page .kpi-value {
            margin: 0;
            font-size: 1.42rem;
            font-weight: 700;
            color: #0f172a;
        }

        .warranty-analytics-page .badge-soft {
            background: var(--wa-primary-soft);
            color: var(--wa-primary);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 10px;
        }

        .warranty-analytics-page .insight-list li {
            border-bottom: 1px dashed #d8dee8;
            padding-bottom: 10px;
            margin-bottom: 10px;
            color: #243046;
        }

        .warranty-analytics-page .insight-list li:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .warranty-analytics-page .table thead th {
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
    <div class="content container-fluid warranty-analytics-page {{ $isRtl ? 'text-end' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">{{ translate('warranty_analytics_report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('report_period') }}: {{ $snapshotFrom->format('M d, Y') }} -
                        {{ $snapshotTo->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">{{ translate('updated') }}
                    {{ now()->format('M d, Y h:i A') }}</span>
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
                                    {{ translate('this_year') }}</option>
                                <option value="this_month"
                                    {{ ($filters['date_type'] ?? '') == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_month') }}</option>
                                <option value="this_week"
                                    {{ ($filters['date_type'] ?? '') == 'this_week' ? 'selected' : '' }}>
                                    {{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') == 'today' ? 'selected' : '' }}>
                                    {{ translate('today') }}</option>
                                <option value="custom_date"
                                    {{ ($filters['date_type'] ?? '') == 'custom_date' ? 'selected' : '' }}>
                                    {{ translate('custom_range') }}</option>
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
                            <label class="form-label mb-1">{{ translate('claim_status') }}</label>
                            <input type="text" class="form-control" name="claim_status"
                                value="{{ $filters['claim_status'] ?? '' }}" placeholder="{{ translate('all') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('product') }}</label>
                            <select class="form-control" name="product_id">
                                <option value="0">{{ translate('all') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ (int) ($filters['product_id'] ?? 0) === (int) $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.warranty.report.analytics') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.warranty.report.analytics', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <a href="{{ route('admin.warranty.report.analytics', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                                class="btn btn-outline-danger">{{ translate('PDF') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('warranties') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['total_warranties']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('activated') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['activated_in_period']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('total_claims') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['total_claims']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('open_claims') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['open_claims']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('claim_rate') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['claim_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('closure_rate') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['closure_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('sla_compliance') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['sla_compliance'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('avg_resolution') }}</p>
                        <p class="kpi-value">
                            {{ $kpi['avg_resolution_hours'] !== null ? number_format((float) $kpi['avg_resolution_hours'], 1) . 'h' : translate('na') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('warranty_trend_last_12_months') }}</h4>
                        <span class="badge-soft">
                            {{ $snapshotFrom->format('M d, Y') }} - {{ $snapshotTo->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <canvas id="warranty-trend-chart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('claim_status_mix') }}</h4>
                        <span class="badge-soft">
                            {{ $snapshotFrom->format('M d, Y') }} - {{ $snapshotTo->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <canvas id="warranty-status-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('open_claim_aging') }}</h4>
                        <span class="badge-soft">{{ translate('backlog_risk') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="warranty-aging-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('charge_mix') }}</h4>
                        <span class="badge-soft">{{ translate('amount_by_type') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="warranty-charge-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                       <h4 class="mb-0">{{ translate('top_products_by_claim_volume') }}</h4>
                        <span class="badge-soft">{{ translate('charge_value') }}
                            {{ number_format((float) $kpi['total_charge_amount'], 2) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ translate('product') }}</th>
                                    <th class="text-end">{{ translate('claims') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-weight-semibold">{{ $product->product_name }}</td>
                                        <td class="text-end">{{ number_format((int) $product->claims_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            {{ translate('no_product_claim_records_in_this_period') }}</td>
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
            const statusData = @json($statusChartData);
            const agingData = @json($agingChartData);
            const chargeData = @json($chargeChartData);

            const trendCtx = document.getElementById('warranty-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                                label: @json(translate('activations')),
                                data: trendData.activations || [],
                                borderColor: '#d97706',
                                backgroundColor: 'rgba(217, 119, 6, 0.15)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('claims')),
                                data: trendData.claims || [],
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220, 38, 38, 0.15)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('resolved')),
                                data: trendData.resolved || [],
                                borderColor: '#0f766e',
                                backgroundColor: 'rgba(15, 118, 110, 0.15)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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

            const statusCtx = document.getElementById('warranty-status-chart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels || [],
                        datasets: [{
                            data: statusData.counts || [],
                            backgroundColor: ['#dc2626', '#f59e0b', '#2563eb', '#0f766e', '#7c3aed',
                                '#64748b', '#14b8a6', '#84cc16'
                            ]
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

            const agingCtx = document.getElementById('warranty-aging-chart');
            if (agingCtx) {
                new Chart(agingCtx, {
                    type: 'bar',
                    data: {
                        labels: agingData.labels || [],
                        datasets: [{
                            label: @json(translate('open_claims')),
                            data: agingData.counts || [],
                            backgroundColor: ['#0ea5e9', '#22c55e', '#f59e0b', '#ef4444'],
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

            const chargeCtx = document.getElementById('warranty-charge-chart');
            if (chargeCtx) {
                new Chart(chargeCtx, {
                    type: 'bar',
                    data: {
                        labels: chargeData.labels || [],
                        datasets: [{
                            label: @json(translate('amount')),
                            data: chargeData.amounts || [],
                            backgroundColor: 'rgba(180, 83, 9, 0.75)',
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
                                beginAtZero: true
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
        // PDF Download with chart images
        $('.btn-outline-danger[href*="download=pdf"]').on('click', function(e) {
            e.preventDefault();

            const $button = $(this);
            let href = $button.attr('href');

            setTimeout(function() {
                const trendCanvas = document.getElementById('warranty-trend-chart');
                const statusCanvas = document.getElementById('warranty-status-chart');
                const agingCanvas = document.getElementById('warranty-aging-chart');
                const chargeCanvas = document.getElementById('warranty-charge-chart');

                if (trendCanvas && statusCanvas && agingCanvas && chargeCanvas) {
                    try {
                        const trendImage = trendCanvas.toDataURL('image/png');
                        const statusImage = statusCanvas.toDataURL('image/png');
                        const agingImage = agingCanvas.toDataURL('image/png');
                        const chargeImage = chargeCanvas.toDataURL('image/png');

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = href;

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        const trendInput = document.createElement('input');
                        trendInput.type = 'hidden';
                        trendInput.name = 'trend_chart';
                        trendInput.value = trendImage;
                        form.appendChild(trendInput);

                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'status_chart';
                        statusInput.value = statusImage;
                        form.appendChild(statusInput);

                        const agingInput = document.createElement('input');
                        agingInput.type = 'hidden';
                        agingInput.name = 'aging_chart';
                        agingInput.value = agingImage;
                        form.appendChild(agingInput);

                        const chargeInput = document.createElement('input');
                        chargeInput.type = 'hidden';
                        chargeInput.name = 'charge_chart';
                        chargeInput.value = chargeImage;
                        form.appendChild(chargeInput);

                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.forEach((value, key) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                    } catch (error) {
                        console.error('Error capturing charts:', error);
                        window.open(href, '_blank');
                    }
                } else {
                    window.open(href, '_blank');
                }
            }, 500);
        });
    </script>
@endpush