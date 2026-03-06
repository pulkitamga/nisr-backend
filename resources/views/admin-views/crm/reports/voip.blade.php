@extends('layouts.back-end.app')

@section('title', 'VOIP Insights Report')

@push('css_or_js')
    <style>
        .voip-report-page {
            --vp-primary: #1e40af;
            --vp-primary-soft: rgba(30, 64, 175, 0.12);
            --vp-muted: #5f6672;
        }

        .voip-report-page .report-hero {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(37, 99, 235, 0.26);
        }

        .voip-report-page .kpi-card {
            border: 1px solid rgba(37, 99, 235, 0.14);
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .voip-report-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.12);
        }

        .voip-report-page .kpi-label {
            color: var(--vp-muted);
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .voip-report-page .kpi-value {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .voip-report-page .badge-soft {
            background: var(--vp-primary-soft);
            color: var(--vp-primary);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 10px;
        }

        .voip-report-page .insight-list li {
            border-bottom: 1px dashed #d8dee8;
            padding-bottom: 10px;
            margin-bottom: 10px;
            color: #243046;
        }

        .voip-report-page .insight-list li:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .voip-report-page .table thead th {
            border-top: none;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-size: 0.76rem;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid voip-report-page">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">VOIP Insights Report</h2>
                    <p class="mb-0 opacity-75">
                        Call center operational snapshot from {{ $snapshotFrom->format('M d, Y') }} to {{ $snapshotTo->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">Updated {{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Total Calls</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['total_calls']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Completed</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['completed_calls']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Answer Rate</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['answer_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Avg Duration</p>
                        <p class="kpi-value">{{ number_format(((float) $kpi['avg_duration_seconds']) / 60, 1) }}m</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Unique Contacts</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['unique_contacts']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Active Agents</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['active_agents']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">VOIP Trend (Last 12 Months)</h4>
                        <span class="badge-soft">Volume, Completion, Duration</span>
                    </div>
                    <div class="card-body">
                        <canvas id="voip-trend-chart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Call Status Mix</h4>
                        <span class="badge-soft">90D</span>
                    </div>
                    <div class="card-body">
                        <canvas id="voip-status-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Direction Split</h4>
                        <span class="badge-soft">Inbound vs Outbound</span>
                    </div>
                    <div class="card-body">
                        <canvas id="voip-direction-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Hourly Load</h4>
                        <span class="badge-soft">Call Peaks</span>
                    </div>
                    <div class="card-body">
                        <canvas id="voip-hourly-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h4 class="mb-0">Top Agents by Call Volume (90D)</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Agent</th>
                                    <th class="text-end">Calls</th>
                                    <th class="text-end">Total Duration</th>
                                    <th class="text-end">Avg Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topAgents as $agent)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-weight-semibold">{{ $agent->agent_name }}</td>
                                        <td class="text-end">{{ number_format((int) $agent->calls_count) }}</td>
                                        <td class="text-end">{{ number_format(((float) $agent->total_duration) / 60, 1) }}m</td>
                                        <td class="text-end">{{ number_format(((float) $agent->avg_duration) / 60, 1) }}m</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No agent call data in this period.</td>
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
                        <h4 class="mb-0">Insights</h4>
                        <span class="badge-soft">Auto Summary</span>
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
            const directionData = @json($directionChartData);
            const hourlyData = @json($hourlyChartData);

            const trendCtx = document.getElementById('voip-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                                type: 'bar',
                                label: 'Total Calls',
                                data: trendData.calls || [],
                                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                                borderColor: '#2563eb',
                                borderWidth: 1,
                                yAxisID: 'yCalls'
                            },
                            {
                                type: 'line',
                                label: 'Completed Calls',
                                data: trendData.completed || [],
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20, 184, 166, 0.15)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                yAxisID: 'yCalls'
                            },
                            {
                                type: 'line',
                                label: 'Avg Duration (sec)',
                                data: trendData.avg_duration || [],
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.15)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                yAxisID: 'yDuration'
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
                            yCalls: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            yDuration: {
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
            }

            const statusCtx = document.getElementById('voip-status-chart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels || [],
                        datasets: [{
                            data: statusData.counts || [],
                            backgroundColor: ['#2563eb', '#14b8a6', '#f59e0b', '#e11d48', '#64748b', '#84cc16']
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

            const directionCtx = document.getElementById('voip-direction-chart');
            if (directionCtx) {
                new Chart(directionCtx, {
                    type: 'pie',
                    data: {
                        labels: directionData.labels || [],
                        datasets: [{
                            data: directionData.counts || [],
                            backgroundColor: ['#2563eb', '#0ea5e9', '#94a3b8']
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

            const hourlyCtx = document.getElementById('voip-hourly-chart');
            if (hourlyCtx) {
                new Chart(hourlyCtx, {
                    type: 'bar',
                    data: {
                        labels: hourlyData.labels || [],
                        datasets: [{
                            label: 'Calls',
                            data: hourlyData.counts || [],
                            backgroundColor: 'rgba(30, 64, 175, 0.75)',
                            borderRadius: 6
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
                            },
                            x: {
                                ticks: {
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
@endpush
