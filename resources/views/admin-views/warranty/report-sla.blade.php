@extends('layouts.back-end.app')

@section('title', translate('sla_report'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .warranty-sla-page {
            --wr-primary: #0f4c81;
            --wr-secondary: #1d4ed8;
            --wr-soft: rgba(29, 78, 216, 0.14);
        }

        .warranty-sla-page .report-hero {
            background: linear-gradient(135deg, var(--wr-primary) 0%, var(--wr-secondary) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(30, 64, 175, 0.24);
        }

        .warranty-sla-page .filter-card,
        .warranty-sla-page .metric-card,
        .warranty-sla-page .chart-card,
        .warranty-sla-page .table-card {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }

        .warranty-sla-page .metric-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .warranty-sla-page .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.12);
        }

        .warranty-sla-page .metric-label {
            color: #64748b;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .warranty-sla-page .metric-value {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .warranty-sla-page .badge-soft {
            background: var(--wr-soft);
            color: var(--wr-secondary);
            border-radius: 999px;
            font-size: 0.75rem;
            padding: 4px 10px;
            font-weight: 600;
        }

        .warranty-sla-page .table thead th {
            text-transform: uppercase;
            font-size: 0.74rem;
            letter-spacing: 0.03em;
            border-top: none;
            white-space: nowrap;
        }

        .warranty-sla-page .chart-holder {
            min-height: 280px;
        }
    </style>
@endpush

@section('content')
    @php
        $isRtl =
            session('direction') === 'rtl' ||
            (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
    @endphp

    <div class="content container-fluid warranty-sla-page {{ $isRtl ? 'text-right' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h1 mb-1">{{ translate('sla_report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('report_period') }}:
                        {{ $fromDate->format('M d, Y') }} - {{ $toDate->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">
                    {{ translate('updated') }} {{ now()->format('M d, Y h:i A') }}
                </span>
            </div>
        </div>

        <div class="card filter-card mb-3">
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
                            style="{{ ($filters['date_type'] ?? '') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2 custom-date-range"
                            style="{{ ($filters['date_type'] ?? '') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('sla_type') }}</label>
                            <select class="form-control" name="sla_type">
                                <option value="all" {{ ($filters['sla_type'] ?? 'all') === 'all' ? 'selected' : '' }}>
                                    {{ translate('all') }}</option>
                                <option value="response"
                                    {{ ($filters['sla_type'] ?? '') === 'response' ? 'selected' : '' }}>
                                    {{ translate('first_response_sla') }}</option>
                                <option value="resolution"
                                    {{ ($filters['sla_type'] ?? '') === 'resolution' ? 'selected' : '' }}>
                                    {{ translate('resolution_sla') }}</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.warranty.report.sla') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.warranty.report.sla', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <a href="{{ route('admin.warranty.report.sla', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                                class="btn btn-outline-danger pdf-download-btn">{{ translate('PDF') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('total_deadlines') }}</p>
                        <p class="metric-value">{{ number_format((int) $kpi['total_deadlines']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('sla_compliance') }}</p>
                        <p class="metric-value">{{ number_format((float) $kpi['compliance'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('on_time') }}</p>
                        <p class="metric-value">{{ number_format((int) $kpi['on_time']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('avg_breach_hours') }}</p>
                        <p class="metric-value">
                            {{ $kpi['avg_breach_hours'] !== null ? number_format((float) $kpi['avg_breach_hours'], 1) . 'h' : translate('na') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('sla_compliance') }}</h4>
                        <span class="badge-soft">{{ translate('on_time') }} / {{ translate('breached') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="sla-compliance-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('sla_type') }}</h4>
                        <span class="badge-soft">{{ translate('distribution') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="sla-type-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('breached_claims') }}</h4>
                        <span class="badge-soft">{{ number_format((int) $kpi['breached']) }}</span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <h1 class="display-4 text-danger mb-1">{{ number_format((int) $kpi['breached']) }}</h1>
                            <p class="text-muted mb-0">{{ translate('deadline_breaches_in_period') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card chart-card mb-3">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ translate('sla_trend') }}</h4>
                <span class="badge-soft">{{ translate('total') }} / {{ translate('breached') }}</span>
            </div>
            <div class="card-body chart-holder">
                <canvas id="sla-trend-chart"></canvas>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ translate('sla_details') }}</h4>
                <span class="badge badge-soft-dark">{{ number_format((int) $slaDetails->total()) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('sla_type') }}</th>
                            <th>{{ translate('claim_number') }}</th>
                            <th>{{ translate('serial_no') }}</th>
                            <th>{{ translate('product') }}</th>
                            <th>{{ translate('due_date') }}</th>
                            <th>{{ translate('completed_at') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>{{ translate('claim_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slaDetails as $row)
                            @php
                                $slaTypeLabel =
                                    $row->sla_type_key === 'response'
                                        ? translate('first_response_sla')
                                        : translate('resolution_sla');
                            @endphp
                            <tr>
                                <td>{{ $slaDetails->firstItem() + $loop->index }}</td>
                                <td>{{ $slaTypeLabel }}</td>
                                <td class="font-weight-semibold">{{ $row->claim_number }}</td>
                                <td>{{ $row->serial_number }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->due_date)->format('Y-m-d H:i') }}</td>
                                <td>
                                    {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->format('Y-m-d H:i') : '-' }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-soft-{{ (int) $row->is_within_sla === 1 ? 'success' : 'danger' }}">
                                        {{ (int) $row->is_within_sla === 1 ? translate('on_time') : translate('breached') }}
                                    </span>
                                </td>
                                <td>{{ translate($row->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">{{ translate('no_data_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer border-0">
                {!! $slaDetails->links() !!}
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/chart.js/dist/Chart.min.js') }}"></script>
    <script>
        'use strict';

        (function() {
            const complianceData = @json($slaComplianceChartData);
            const typeData = @json($slaTypeChartData);
            const trendData = @json($slaTrendChartData);

            const complianceCtx = document.getElementById('sla-compliance-chart');
            if (complianceCtx) {
                new Chart(complianceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: complianceData.labels || [],
                        datasets: [{
                            data: complianceData.counts || [],
                            backgroundColor: ['#16a34a', '#dc2626']
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

            const typeCtx = document.getElementById('sla-type-chart');
            if (typeCtx) {
                new Chart(typeCtx, {
                    type: 'bar',
                    data: {
                        labels: typeData.labels || [],
                        datasets: [{
                            label: @json(translate('total_deadlines')),
                            data: typeData.counts || [],
                            backgroundColor: ['#2563eb', '#f59e0b', '#64748b'],
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

            const trendCtx = document.getElementById('sla-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                            type: 'line',
                            label: @json(translate('total_deadlines')),
                            data: trendData.total || [],
                            borderColor: '#1d4ed8',
                            backgroundColor: 'rgba(29, 78, 216, 0.2)',
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true
                        }, {
                            type: 'bar',
                            label: @json(translate('breached')),
                            data: trendData.breached || [],
                            backgroundColor: 'rgba(220, 38, 38, 0.7)',
                            borderRadius: 8
                        }]
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
        })();

        $(document).ready(function() {
            $('#date_type').on('change', function() {
                if ($(this).val() === 'custom_date') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            });

            $('.pdf-download-btn').on('click', function(e) {
                e.preventDefault();

                const href = $(this).attr('href');

                const complianceCanvas = document.getElementById('sla-compliance-chart');
                const typeCanvas = document.getElementById('sla-type-chart');
                const trendCanvas = document.getElementById('sla-trend-chart');

                if (!complianceCanvas || !typeCanvas || !trendCanvas) {
                    window.open(href, '_blank');
                    return;
                }

                const complianceImage = complianceCanvas.toDataURL('image/png');
                const typeImage = typeCanvas.toDataURL('image/png');
                const trendImage = trendCanvas.toDataURL('image/png');

                console.log(complianceImage.length);
                console.log(typeImage.length);
                console.log(trendImage.length);

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = href;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);

                const compliance = document.createElement('input');
                compliance.type = 'hidden';
                compliance.name = 'compliance_chart';
                compliance.value = complianceImage;
                form.appendChild(compliance);

                const type = document.createElement('input');
                type.type = 'hidden';
                type.name = 'type_chart';
                type.value = typeImage;
                form.appendChild(type);

                const trend = document.createElement('input');
                trend.type = 'hidden';
                trend.name = 'trend_chart';
                trend.value = trendImage;
                form.appendChild(trend);

                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
@endpush
