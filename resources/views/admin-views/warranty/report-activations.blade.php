@extends('layouts.back-end.app')

@section('title', translate('activation_report'))

@push('css_or_js')
    <style>
        .warranty-activation-page {
            --wr-primary: #8b5cf6;
            --wr-secondary: #6d28d9;
            --wr-soft: rgba(139, 92, 246, 0.14);
        }

        .warranty-activation-page .report-hero {
            background: linear-gradient(135deg, var(--wr-primary) 0%, var(--wr-secondary) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(109, 40, 217, 0.24);
        }

        .warranty-activation-page .filter-card,
        .warranty-activation-page .metric-card,
        .warranty-activation-page .chart-card,
        .warranty-activation-page .table-card {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }

        .warranty-activation-page .metric-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .warranty-activation-page .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(109, 40, 217, 0.12);
        }

        .warranty-activation-page .metric-label {
            color: #64748b;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .warranty-activation-page .metric-value {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .warranty-activation-page .badge-soft {
            background: var(--wr-soft);
            color: var(--wr-secondary);
            border-radius: 999px;
            font-size: 0.75rem;
            padding: 4px 10px;
            font-weight: 600;
        }

        .warranty-activation-page .table thead th {
            text-transform: uppercase;
            font-size: 0.74rem;
            letter-spacing: 0.03em;
            border-top: none;
            white-space: nowrap;
        }

        .warranty-activation-page .chart-holder {
            min-height: 280px;
        }
    </style>
@endpush

@section('content')
    @php
        $isRtl =
            session('direction') === 'rtl' ||
            (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
        $activationMethods = [
            'all',
            'user_public_form',
            'admin_manual',
            'auto_activation',
            'mobile_app',
            'order_activation',
            'replacement',
            'unknown',
        ];
        if (!in_array($filters['activation_method'] ?? 'all', $activationMethods, true)) {
            $activationMethods[] = $filters['activation_method'];
        }
    @endphp

    <div class="content container-fluid warranty-activation-page {{ $isRtl ? 'text-right' : '' }}"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h1 mb-1">{{ translate('activation_report') }}</h2>
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
                            <label class="form-label mb-1">{{ translate('activation_method') }}</label>
                            <select class="form-control" name="activation_method">
                                @foreach ($activationMethods as $method)
                                    <option value="{{ $method }}"
                                        {{ ($filters['activation_method'] ?? 'all') === $method ? 'selected' : '' }}>
                                        {{ $method === 'all' ? translate('all') : translate($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">{{ translate('search') }}</label>
                            <input type="text" class="form-control" name="search"
                                value="{{ $filters['search'] ?? '' }}" placeholder="{{ translate('search_by_serial') }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn--primary">{{ translate('filter') }}</button>
                            <a href="{{ route('admin.warranty.report.activations') }}"
                                class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                            <a href="{{ route('admin.warranty.report.activations', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                class="btn btn-outline-success">{{ translate('excel') }}</a>
                            <a href="{{ route('admin.warranty.report.activations', array_merge(request()->query(), ['download' => 'pdf'])) }}"
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
                        <p class="metric-label">{{ translate('total_activations') }}</p>
                        <p class="metric-value">{{ number_format((int) $kpi['total_activations']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('activation_rate') }}</p>
                        <p class="metric-value">{{ number_format((float) $kpi['activation_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('active_warranties') }}</p>
                        <p class="metric-value">{{ number_format((int) $kpi['active_warranties']) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="metric-label">{{ translate('avg_warranty_months') }}</p>
                        <p class="metric-value">
                            {{ $kpi['avg_warranty_months'] !== null ? number_format((float) $kpi['avg_warranty_months'], 1) : translate('na') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('activation_trend') }}</h4>
                        <span class="badge-soft">{{ translate('date_range') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="activation-trend-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('activations_by_method') }}</h4>
                        <span class="badge-soft">{{ translate('distribution') }}</span>
                    </div>
                    <div class="card-body chart-holder">
                        <canvas id="activation-method-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="card table-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('activation_method') }}</h4>
                        <span class="badge-soft">{{ translate('share') }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('method') }}</th>
                                    <th class="text-end">{{ translate('total') }}</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($methodBreakdown as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-end">{{ number_format((int) $row['count']) }}</td>
                                        <td class="text-end">{{ number_format((float) $row['percentage'], 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            {{ translate('no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card table-card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('top_products') }}</h4>
                        <span class="badge-soft">{{ translate('total_activations') }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ translate('product') }}</th>
                                    <th class="text-end">{{ translate('total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $product->product_name }}</td>
                                        <td class="text-end">{{ number_format((int) $product->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            {{ translate('no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ translate('activation_details') }}</h4>
                <span class="badge badge-soft-dark">{{ number_format((int) $activations->total()) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('product') }}</th>
                            <th>{{ translate('customer') }}</th>
                            <th>{{ translate('branch') }}</th>
                            <th>{{ translate('activation_method') }}</th>
                            <th>{{ translate('activation_date') }}</th>
                            <th>{{ translate('warranty_end') }}</th>
                            <th>{{ translate('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activations as $warranty)
                            @php
                                $customerName = trim(
                                    ((string) ($warranty->user?->f_name ?? '')) .
                                        ' ' .
                                        ((string) ($warranty->user?->l_name ?? '')),
                                );
                                if ($customerName === '') {
                                    $customerName = $warranty->activated_by_name ?? '-';
                                }
                                $statusClass = $warranty->status === 'active' ? 'success' : 'secondary';
                            @endphp
                            <tr>
                                <td>{{ $activations->firstItem() + $loop->index }}</td>
                                <td class="font-weight-semibold">{{ $warranty->serial_number }}</td>
                                <td>{{ $warranty->product?->name ?? '-' }}</td>
                                <td>{{ $customerName }}</td>
                                <td>{{ $warranty->branch?->branch_name ?? '-' }}</td>
                                <td>{{ translate($warranty->activation_method ?: 'unknown') }}</td>
                                <td>{{ optional($warranty->activation_date)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ optional($warranty->end_date)->format('Y-m-d') ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge badge-soft-{{ $statusClass }}">{{ translate($warranty->status) }}</span>
                                </td>
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
                {!! $activations->links() !!}
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/chart.js/dist/Chart.min.js') }}"></script>
    <script>
        'use strict';

        (function() {
            const trendData = @json($activationTrendChartData);
            const methodData = @json($activationMethodChartData);

            const trendCtx = document.getElementById('activation-trend-chart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels || [],
                        datasets: [{
                            label: @json(translate('total_activations')),
                            data: trendData.counts || [],
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, 0.16)',
                            borderWidth: 3,
                            tension: 0.35,
                            fill: true
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

            const methodCtx = document.getElementById('activation-method-chart');
            if (methodCtx) {
                new Chart(methodCtx, {
                    type: 'doughnut',
                    data: {
                        labels: methodData.labels || [],
                        datasets: [{
                            data: methodData.counts || [],
                            backgroundColor: ['#7c3aed', '#0ea5e9', '#22c55e', '#f59e0b', '#ef4444',
                                '#64748b', '#14b8a6'
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
                const trendCanvas = document.getElementById('activation-trend-chart');
                const methodCanvas = document.getElementById('activation-method-chart');

                if (trendCanvas && methodCanvas) {
                    try {
                        const trendImage = trendCanvas.toDataURL('image/png');
                        const methodImage = methodCanvas.toDataURL('image/png');

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

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = 'method_chart';
                        methodInput.value = methodImage;
                        form.appendChild(methodInput);

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
