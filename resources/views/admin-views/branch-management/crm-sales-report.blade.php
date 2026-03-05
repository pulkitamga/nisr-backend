@extends('layouts.back-end.app')

@section('title', 'CRM Sales Report')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}" alt="">
                CRM Sales Report
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form id="crm-sales-filter-form" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select class="form-control" name="year" id="crm-year" required>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ (int) $year === (int) date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select class="form-control" name="month" id="crm-month">
                            <option value="">All Months</option>
                            @foreach ($months as $monthNumber => $monthName)
                                <option value="{{ $monthNumber }}">{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Sale Type</label>
                        <select class="form-control" name="sale_type" id="crm-sale-type">
                            <option value="">All</option>
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agents</label>
                        <select class="form-control" name="agent_ids[]" id="crm-agent-ids" multiple>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave empty to include all agents.</small>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" id="crm-load-btn" class="btn btn--primary w-100">
                            Load Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Sales</small>
                        <h4 class="mb-0" id="crm-total-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Retail Sales</small>
                        <h4 class="mb-0" id="crm-retail-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Wholesale Sales</small>
                        <h4 class="mb-0" id="crm-wholesale-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Top Agent</small>
                        <h6 class="mb-0" id="crm-top-agent">-</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <canvas id="crm-sales-chart" height="110"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h4 class="mb-0">Period Summary</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Period</th>
                            <th class="text-end">Retail Sales</th>
                            <th class="text-end">Wholesale Sales</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Retail Orders</th>
                            <th class="text-end">Wholesale Orders</th>
                            <th class="text-end">Total Orders</th>
                            <th class="text-end">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="crm-sales-table-body">
                        <tr>
                            <td colspan="8" class="text-center py-4">Loading...</td>
                        </tr>
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

        (function () {
            const form = document.getElementById('crm-sales-filter-form');
            const loadBtn = document.getElementById('crm-load-btn');
            const tableBody = document.getElementById('crm-sales-table-body');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const chartCtx = document.getElementById('crm-sales-chart').getContext('2d');
            let crmChart = null;

            const fmt = (value) => Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const selectedValues = (selectElement) => {
                return Array.from(selectElement.selectedOptions)
                    .map((option) => Number(option.value))
                    .filter((value) => Number.isInteger(value) && value > 0);
            };

            const setLoading = (loading) => {
                loadBtn.disabled = loading;
                loadBtn.textContent = loading ? 'Loading...' : 'Load Report';
            };

            const renderStats = (stats) => {
                document.getElementById('crm-total-sales').textContent = fmt(stats.total_sales);
                document.getElementById('crm-retail-sales').textContent = fmt(stats.retail_sales);
                document.getElementById('crm-wholesale-sales').textContent = fmt(stats.wholesale_sales);
                document.getElementById('crm-top-agent').textContent = stats.top_agent || '-';
            };

            const renderChart = (chartData) => {
                if (crmChart) {
                    crmChart.destroy();
                }

                const datasets = (chartData.datasets || []).map((dataset) => ({
                    label: dataset.label,
                    data: dataset.data || [],
                    borderColor: dataset.borderColor || '#2563eb',
                    backgroundColor: dataset.backgroundColor || 'rgba(37, 99, 235, 0.2)',
                    borderWidth: dataset.borderWidth || 2,
                    fill: Boolean(dataset.fill),
                    tension: dataset.tension !== undefined ? dataset.tension : 0.3
                }));

                crmChart = new Chart(chartCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels || [],
                        datasets
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
            };

            const renderTable = (pivotData) => {
                const rows = Object.values(pivotData || {});
                if (!rows.length) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No data found.</td></tr>';
                    return;
                }

                tableBody.innerHTML = rows.map((row) => {
                    const totals = row.totals || {};
                    return `
                        <tr>
                            <td>${row.period !== undefined && row.period !== null ? row.period : '-'}</td>
                            <td class="text-end">${fmt(totals.retail_sales)}</td>
                            <td class="text-end">${fmt(totals.wholesale_sales)}</td>
                            <td class="text-end">${fmt(totals.total_sales)}</td>
                            <td class="text-end">${totals.retail_orders || 0}</td>
                            <td class="text-end">${totals.wholesale_orders || 0}</td>
                            <td class="text-end">${totals.total_orders || 0}</td>
                            <td class="text-end">${fmt(totals.total_quantity)}</td>
                        </tr>
                    `;
                }).join('');
            };

            const loadReport = async () => {
                setLoading(true);
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">Loading...</td></tr>';

                try {
                    const payload = {
                        year: Number(document.getElementById('crm-year').value),
                        month: document.getElementById('crm-month').value || null,
                        sale_type: document.getElementById('crm-sale-type').value || null,
                        agent_ids: selectedValues(document.getElementById('crm-agent-ids'))
                    };

                    const response = await fetch('{{ route('admin.crm.sales-report-data') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Failed to load report.');
                    }

                    renderStats(data.statistics || {});
                    renderChart(data.chartData || {});
                    renderTable(data.pivotData || {});
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${error.message || 'Failed to load report.'}</td></tr>`;
                } finally {
                    setLoading(false);
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                loadReport();
            });

            loadReport();
        })();
    </script>
@endpush
