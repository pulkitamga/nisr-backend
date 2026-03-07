@extends('layouts.back-end.app')

@section('title', translate('claims_report'))

@push('css_or_js')
    <style>
        .warranty-report-page {
            --wr-primary: #0f766e;
            --wr-secondary: #155e75;
            --wr-soft: rgba(15, 118, 110, 0.12);
        }

        .warranty-report-page .report-hero {
            background: linear-gradient(135deg, var(--wr-primary) 0%, var(--wr-secondary) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(8, 89, 84, 0.24);
        }

        .warranty-report-page .filter-card,
        .warranty-report-page .metric-card,
        .warranty-report-page .chart-card,
        .warranty-report-page .table-card {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }

        .warranty-report-page .metric-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .warranty-report-page .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.12);
        }

        .warranty-report-page .metric-label {
            color: #64748b;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .warranty-report-page .metric-value {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .warranty-report-page .badge-soft {
            background: var(--wr-soft);
            color: var(--wr-primary);
            border-radius: 999px;
            font-size: 0.75rem;
            padding: 4px 10px;
            font-weight: 600;
        }

        .warranty-report-page .table thead th {
            text-transform: uppercase;
            font-size: 0.74rem;
            letter-spacing: 0.03em;
            border-top: none;
            white-space: nowrap;
        }

        .warranty-report-page .chart-holder {
            min-height: 280px;
        }
    </style>
@endpush

@section('content')
    @php
        $isRtl = session('direction') === 'rtl'
            || (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
        $claimStatuses = [
            'all',
            'new',
            'triage_pending',
            'approved',
            'rma_issued',
            'received',
            'repair_pending',
            'replacement_pending',
            'diagnosis_pending',
            'qc_pending',
            'shipped_ready',
            'dispatched',
            'resolved',
            'closed',
            'rejected',
            'waiting_customer',
            'waiting_parts',
            'waiting_payment',
        ];
    @endphp

    <div class="content container-fluid warranty-report-page {{ $isRtl ? 'text-right' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h1 mb-1">{{ translate('claims_report') }}</h2>
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
                                <option value="this_year" {{ ($filters['date_type'] ?? 'this_year') == 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                <option value="this_month" {{ ($filters['date_type'] ?? '') == 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                <option value="this_week" {{ ($filters['date_type'] ?? '') == 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') == 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                <option value="custom_date" {{ ($filters['date_type'] ?? '') == 'custom_date' ? 'selected' : '' }}>{{ translate('custom_range') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 custom-date-range" style="{{ ($filters['date_type'] ?? '') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('from') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2 custom-date-range" style="{{ ($filters['date_type'] ?? '') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('status') }}</label>
                            <select class="form-control" name="status">
                                @foreach($claimStatuses as $status)
                                    <option value="{{ $status }}" {{ ($filters['status'] ?? 'all') === $status ? 'selected' : '' }}>
                                        {{ $status === 'all' ? translate('all') : translate($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('search') }}</label>
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}"
                                   placeholder="{{ translate('search_by_claim_or_serial') }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.warranty.report.claims') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.warranty.report.claims', array_merge(request()->query(), ['download' => 'excel'])) }}"
                               class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <a href="{{ route('admin.warranty.report.claims', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                               class="btn btn-outline-danger">{{ translate('PDF') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('total_claims') }}</p>
                        <p class="metric-value">{{ number_format((int)$kpi['total_claims']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('claim_rate') }}</p>
                        <p class="metric-value">{{ number_format((float)$kpi['claim_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('open_claims') }}</p>
                        <p class="metric-value">{{ number_format((int)$kpi['open_claims']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('resolved') }}</p>
                        <p class="metric-value">{{ number_format((int)$kpi['resolved_claims']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('claims_volume_trend') }}</h4>
                        <span class="badge-soft">{{ translate('date_range') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="claims-trend-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('claim_status_mix') }}</h4>
                        <span class="badge-soft">{{ translate('distribution') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="claims-status-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ translate('claims_table') }}</h4>
                <span class="badge badge-soft-dark">{{ number_format((int)$claims->total()) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('claim_number') }}</th>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('product') }}</th>
                            <th>{{ translate('customer') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>{{ translate('submitted_at') }}</th>
                            <th>{{ translate('sla_due') }}</th>
                            <th>{{ translate('branch') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            @php
                                $customerName = trim(
                                    ((string)($claim->warranty?->user?->f_name ?? '')) . ' ' . ((string)($claim->warranty?->user?->l_name ?? ''))
                                );
                                if ($customerName === '') {
                                    $customerName = $claim->warranty?->activated_by_name ?? $claim->activated_by_name ?? '-';
                                }
                                $statusClass = in_array($claim->status, ['resolved', 'closed'])
                                    ? 'success'
                                    : (in_array($claim->status, ['rejected']) ? 'danger' : 'warning');
                            @endphp
                            <tr>
                                <td>{{ $claims->firstItem() + $loop->index }}</td>
                                <td class="font-weight-semibold">{{ $claim->claim_number }}</td>
                                <td>{{ $claim->serial_number }}</td>
                                <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                                <td>{{ $customerName }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $statusClass }}">
                                        {{ translate($claim->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($claim->submitted_at ?? $claim->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ optional($claim->resolution_due)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $claim->branch?->branch_name ?? '-' }}</td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-outline-info"
                                       href="{{ route('admin.warranty.claim.view', $claim->id) }}">
                                        {{ translate('view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer border-0">
                {!! $claims->links() !!}
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

            const trendCtx = document.getElementById('claims-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                            label: @json(translate('claims')),
                            data: trendData.counts || [],
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(15, 118, 110, 0.16)',
                            borderWidth: 3,
                            tension: 0.35,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            const statusCtx = document.getElementById('claims-status-chart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels || [],
                        datasets: [{
                            data: statusData.counts || [],
                            backgroundColor: ['#0f766e', '#1d4ed8', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b', '#06b6d4', '#84cc16']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
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
