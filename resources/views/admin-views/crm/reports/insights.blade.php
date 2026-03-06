@extends('layouts.back-end.app')

@section('title', 'CRM Insights Report')

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
    <div class="content container-fluid crm-insights-page">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">CRM Insights Report</h2>
                    <p class="mb-0 opacity-75">
                        Message, lead, and deal intelligence from {{ $snapshotFrom->format('M d, Y') }} to {{ $snapshotTo->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">Updated {{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Messages</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['message_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Leads</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['lead_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Deals</p>
                        <p class="kpi-value">{{ number_format((int) $kpi['deal_count']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Pipeline Value</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['total_deal_value'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Lead to Deal</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['lead_to_deal_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Win Rate</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['deal_win_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">CRM Trend (Last 12 Months)</h4>
                        <span class="badge-soft">Messages, Leads, Deals</span>
                    </div>
                    <div class="card-body">
                        <canvas id="crm-trend-chart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Deal Stage Mix</h4>
                        <span class="badge-soft">90D</span>
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
                        <h4 class="mb-0">Message Status Distribution</h4>
                        <span class="badge-soft">Queue Health</span>
                    </div>
                    <div class="card-body">
                        <canvas id="crm-message-status-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
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

        <div class="card">
            <div class="card-header border-0">
                <h4 class="mb-0">Top Deal Owners by Value (90D)</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Owner</th>
                            <th class="text-end">Deals</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-end">Avg Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topOwners as $owner)
                            @php
                                $averageValue = (int) $owner->deals_count > 0 ? (float) $owner->total_value / (int) $owner->deals_count : 0;
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
                                <td colspan="5" class="text-center text-muted py-4">No owner activity in this period.</td>
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
                                label: 'Messages',
                                data: trendData.messages || [],
                                borderColor: '#0f766e',
                                backgroundColor: 'rgba(15, 118, 110, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: 'Leads',
                                data: trendData.leads || [],
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20, 184, 166, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: 'Deals',
                                data: trendData.deals || [],
                                borderColor: '#1d4ed8',
                                backgroundColor: 'rgba(29, 78, 216, 0.14)',
                                tension: 0.35,
                                borderWidth: 2,
                                fill: false
                            },
                            {
                                label: 'Won Deals',
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
                            backgroundColor: ['#0f766e', '#14b8a6', '#1d4ed8', '#0ea5e9', '#f59e0b', '#e11d48', '#64748b', '#84cc16']
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
                            label: 'Messages',
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
    </script>
@endpush
