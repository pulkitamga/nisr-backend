@extends('layouts.back-end.app')

@section('title', 'Wholesale Revenue Report')

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
    <div class="content container-fluid wholesale-report-page">
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div>
                    <h2 class="h1 mb-1">Wholesale Revenue Report</h2>
                    <p class="mb-0 opacity-75">
                        Financial and fulfillment snapshot from {{ $snapshotFrom->format('M d, Y') }} to {{ $snapshotTo->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">Updated {{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">90D Revenue</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['total_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Paid Revenue</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['paid_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Average Order Value</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['avg_order_value'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <p class="kpi-label mb-2">Fulfillment Rate</p>
                        <p class="kpi-value">{{ number_format((float) $kpi['fulfillment_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Revenue Trend (Last 12 Months)</h4>
                        <span class="badge-soft">Orders + Revenue</span>
                    </div>
                    <div class="card-body">
                        <canvas id="wholesale-revenue-trend" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Delivery Status Mix</h4>
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
                        <h4 class="mb-0">Top Wholesalers by Revenue (90D)</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Wholesaler</th>
                                    <th>Company</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Collection</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topWholesalers as $row)
                                    @php
                                        $user = $row->wholeseller;
                                        $companyName = $user?->wholesalerBusiness?->company_name ?? '-';
                                        $displayName = $user?->name ?? $user?->f_name ?? ('Wholesaler #' . $row->wholesaler_id);
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
                                        <td colspan="6" class="text-center text-muted py-4">No wholesale orders found in this period.</td>
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
                                label: 'Orders',
                                data: trendData.orders || [],
                                backgroundColor: 'rgba(15, 118, 110, 0.22)',
                                borderColor: '#0f766e',
                                borderWidth: 1,
                                yAxisID: 'yOrders'
                            },
                            {
                                type: 'line',
                                label: 'Revenue',
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
                                label: 'Paid Revenue',
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
                const labels = (deliveryData.labels || []).length ? deliveryData.labels : ['No Data'];
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
    </script>
@endpush
