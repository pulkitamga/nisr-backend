@extends('layouts.back-end.app')

@section('title', translate('crm_insights_report'))

@push('css_or_js')
    <style>
        .crm-insights-page {
            --crm-accent: #0f766e;
            --crm-accent-soft: rgba(15, 118, 110, 0.12);
            --crm-muted: #5f6672;
        }

        .crm-insights-page .report-hero {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a0 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(15, 118, 110, 0.24);
        }

        .crm-insights-page .kpi-card {
            border: 1px solid rgba(15, 118, 110, 0.14);
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .crm-insights-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 118, 110, 0.12);
        }

        .crm-insights-page .kpi-label {
            color: var(--crm-muted);
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .crm-insights-page .kpi-value {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .crm-insights-page .badge-soft {
            background: var(--crm-accent-soft);
            color: var(--crm-accent);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 10px;
        }

        .crm-insights-page .insight-list li {
            border-bottom: 1px dashed #d8dee8;
            padding-bottom: 10px;
            margin-bottom: 10px;
            color: #243046;
        }

        .crm-insights-page .insight-list li:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .crm-insights-page .table thead th {
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
    <div class="content container-fluid crm-insights-page {{ $isRtl ? 'text-end' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">{{ translate('crm_insights_report') }}</h2>
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
                        <div class="col-md-2">
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
                            <label class="form-label mb-1">{{ translate('department') }}</label>
                            <select class="form-control" name="department_id">
                                <option value="0">{{ translate('all') }}</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ (int) ($filters['department_id'] ?? 0) === (int) $department->id ? 'selected' : '' }}>
                                        {{ $department->getTranslatedField('name') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('owner') }}</label>
                            <select class="form-control" name="owner_id">
                                <option value="0">{{ translate('all') }}</option>
                                @foreach ($owners as $owner)
                                    <option value="{{ $owner->id }}"
                                        {{ (int) ($filters['owner_id'] ?? 0) === (int) $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('message_status') }}</label>
                            <select class="form-control" name="message_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="new"
                                    {{ ($filters['message_status'] ?? '') === 'new' ? 'selected' : '' }}>
                                    {{ translate('new') }}</option>
                                <option value="converted"
                                    {{ ($filters['message_status'] ?? '') === 'converted' ? 'selected' : '' }}>
                                    {{ translate('converted') }}</option>
                                <option value="spam"
                                    {{ ($filters['message_status'] ?? '') === 'spam' ? 'selected' : '' }}>
                                    {{ translate('spam') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('deal_status') }}</label>
                            <select class="form-control" name="deal_status">
                                <option value="">{{ translate('all') }}</option>
                                <option value="open" {{ ($filters['deal_status'] ?? '') === 'open' ? 'selected' : '' }}>
                                    {{ translate('open') }}</option>
                                <option value="won" {{ ($filters['deal_status'] ?? '') === 'won' ? 'selected' : '' }}>
                                    {{ translate('won') }}</option>
                                <option value="lost" {{ ($filters['deal_status'] ?? '') === 'lost' ? 'selected' : '' }}>
                                    {{ translate('lost') }}</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.crm.insights-report') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.crm.insights-report', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <button type="button" id="export-insights-pdf" class="btn btn-outline-danger">
                                {{ translate('PDF') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('messages') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['message_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('leads') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['lead_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('deals') }}</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['deal_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('pipeline_value') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['total_deal_value'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('lead_to_deal') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['lead_to_deal_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">{{ translate('win_rate') }}</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['deal_win_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                       <h4 class="mb-0">{{ translate('crm_trend') }} ({{ $snapshotFrom->format('d M, Y') }} - {{ $snapshotTo->format('d M, Y') }})</h4>
                        <span class="badge-soft">{{ translate('messages_leads_deals') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="crm-trend-chart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('deal_stage_mix') }}</h4>
                        <span class="badge-soft">{{ $snapshotFrom->format('d M, Y') }} - {{ $snapshotTo->format('d M, Y') }} </span>
                    </div>
                    <div class="card-body">
                        <canvas id="crm-stage-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('message_status_distribution') }}</h4>
                        <span class="badge-soft">{{ translate('queue_health') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="crm-message-status-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
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

        <div class="card">
            <div class="card-header border-0">
                @php
                    // Extract just the date part from chart title
                    $datePart = match ($filters['date_type'] ?? 'this_year') {
                        'today' => '(' . $snapshotFrom->format('j F Y') . ')',
                        'this_week' => '(' . $snapshotFrom->format('M d') . ' - ' . $snapshotTo->format('M d, Y') . ')',
                        'this_month' => '(' . $snapshotFrom->format('F Y') . ')',
                        'this_year' => '(' . $snapshotFrom->format('Y') . ')',
                        'custom_date' => '(' .
                            $snapshotFrom->format('j F Y') .
                            ' - ' .
                            $snapshotTo->format('j F Y') .
                            ')',
                        default => '(' . translate('last_12_months') . ')',
                    };
                @endphp

                <h4 class="mb-0">{{ translate('top_deal_owners_by_value') }} ({{ $snapshotFrom->format('d M, Y') }} - {{ $snapshotTo->format('d M, Y') }})</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('owner') }}</th>
                            <th class="text-end">{{ translate('deals') }}</th>
                            <th class="text-end">{{ translate('total_value') }}</th>
                            <th class="text-end">{{ translate('avg_value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topOwners as $owner)
                            @php
                                $averageValue =
                                    (int) $owner->deals_count > 0
                                        ? (float) $owner->total_value / (int) $owner->deals_count
                                        : 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-semibold">{{ $owner->owner_name }}</td>
                                <td class="text-end">{{ number_format((int) $owner->deals_count) }}</td>
                                <td class="text-end">{{ number_format((float) $owner->total_value, 2) }}</td>
                                <td class="text-end">{{ number_format($averageValue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ translate('no_owner_activity_in_this_period') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            const stageData = @json($dealStageChartData);
            const statusData = @json($messageStatusChartData);

            const trendCtx = document.getElementById('crm-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                                label: @json(translate('messages')),
                                data: trendData.messages || [],
                                borderColor: '#0f766e',
                                backgroundColor: 'rgba(15, 118, 110, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('leads')),
                                data: trendData.leads || [],
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20, 184, 166, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('deals')),
                                data: trendData.deals || [],
                                borderColor: '#1d4ed8',
                                backgroundColor: 'rgba(29, 78, 216, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: @json(translate('won_deals')),
                                data: trendData.won_deals || [],
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.14)',
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

            const stageCtx = document.getElementById('crm-stage-chart');
            if (stageCtx) {
                new Chart(stageCtx, {
                    type: 'doughnut',
                    data: {
                        labels: stageData.labels || [],
                        datasets: [{
                            data: stageData.counts || [],
                            backgroundColor: ['#0f766e', '#14b8a6', '#1d4ed8', '#0ea5e9', '#f59e0b',
                                '#e11d48', '#64748b', '#84cc16'
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

            const statusCtx = document.getElementById('crm-message-status-chart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'bar',
                    data: {
                        labels: statusData.labels || [],
                        datasets: [{
                            label: @json(translate('messages')),
                            data: statusData.counts || [],
                            backgroundColor: 'rgba(15, 118, 110, 0.75)',
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
        const exportBtn = document.getElementById('export-insights-pdf');

        if (exportBtn) {
            exportBtn.addEventListener('click', function() {

                const trendChart = document.getElementById('crm-trend-chart');
                const stageChart = document.getElementById('crm-stage-chart');
                const statusChart = document.getElementById('crm-message-status-chart');

                const trendImg = trendChart ? trendChart.toDataURL('image/png') : '';
                const stageImg = stageChart ? stageChart.toDataURL('image/png') : '';
                const statusImg = statusChart ? statusChart.toDataURL('image/png') : '';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.crm.insights-report') }}";

                // CSRF
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                // Download flag
                const download = document.createElement('input');
                download.type = 'hidden';
                download.name = 'download';
                download.value = 'pdf';
                form.appendChild(download);

                // Chart images
                const charts = {
                    trend_chart: trendImg,
                    stage_chart: stageImg,
                    status_chart: statusImg
                };

                Object.keys(charts).forEach(name => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = charts[name];
                    form.appendChild(input);
                });

                // ⭐ Include current filters
                const params = new URLSearchParams(window.location.search);

                params.forEach((value, key) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }
    </script>
@endpush