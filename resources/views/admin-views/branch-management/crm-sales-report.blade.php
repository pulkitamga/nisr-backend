@extends('layouts.back-end.app')

@section('title', 'CRM Sales Report')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/customer.png') }}" alt="">
                CRM Sales Report
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select class="form-control" id="yearFilter">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (int)$year === (int)date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select class="form-control" id="monthFilter">
                            <option value="">All Months</option>
                            @foreach($months as $monthIndex => $monthName)
                                <option value="{{ $monthIndex }}">{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sale Type</label>
                        <select class="form-control" id="saleTypeFilter">
                            <option value="">All</option>
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Agents</label>
                        <select class="form-control" id="agentFilter" multiple>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave empty to include all agents.</small>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn--primary w-100" id="loadReportBtn">Load Report</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Total Sales</div>
                        <h3 class="mb-0" id="statTotalSales">0.00</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Retail Sales</div>
                        <h3 class="mb-0" id="statRetailSales">0.00</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Wholesale Sales</div>
                        <h3 class="mb-0" id="statWholesaleSales">0.00</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Top Agent</div>
                        <h5 class="mb-0" id="statTopAgent">-</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <canvas id="salesChart" height="110"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Retail Sales</th>
                            <th>Wholesale Sales</th>
                            <th>Total Sales</th>
                            <th>Retail Orders</th>
                            <th>Wholesale Orders</th>
                            <th>Total Orders</th>
                        </tr>
                    </thead>
                    <tbody id="pivotTableBody">
                        <tr><td colspan="7" class="text-center text-muted">Load report to view data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let salesChart = null;

        function toMoney(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function getPayload() {
            const selectedAgents = Array.from(document.getElementById('agentFilter').selectedOptions).map(option => option.value);
            return {
                year: document.getElementById('yearFilter').value,
                month: document.getElementById('monthFilter').value || null,
                sale_type: document.getElementById('saleTypeFilter').value || null,
                agent_ids: selectedAgents
            };
        }

        function renderStats(statistics) {
            document.getElementById('statTotalSales').textContent = toMoney(statistics.total_sales);
            document.getElementById('statRetailSales').textContent = toMoney(statistics.retail_sales);
            document.getElementById('statWholesaleSales').textContent = toMoney(statistics.wholesale_sales);
            document.getElementById('statTopAgent').textContent = statistics.top_agent || '-';
        }

        function renderTable(pivotData) {
            const body = document.getElementById('pivotTableBody');
            const rows = Object.values(pivotData || {});
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No data found.</td></tr>';
                return;
            }

            body.innerHTML = rows.map(row => `
                <tr>
                    <td>${row.period}</td>
                    <td>${toMoney(row.totals.retail_sales)}</td>
                    <td>${toMoney(row.totals.wholesale_sales)}</td>
                    <td>${toMoney(row.totals.total_sales)}</td>
                    <td>${row.totals.retail_orders || 0}</td>
                    <td>${row.totals.wholesale_orders || 0}</td>
                    <td>${row.totals.total_orders || 0}</td>
                </tr>
            `).join('');
        }

        function renderChart(chartData) {
            const ctx = document.getElementById('salesChart');
            if (salesChart) {
                salesChart.destroy();
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        async function loadReport() {
            const button = document.getElementById('loadReportBtn');
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const response = await fetch('{{ route('admin.crm.sales-report-data') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(getPayload())
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load report data.');
                }

                renderStats(result.statistics || {});
                renderChart(result.chartData || { labels: [], datasets: [] });
                renderTable(result.pivotData || {});
            } catch (error) {
                toastr.error(error.message || 'Failed to load report');
            } finally {
                button.disabled = false;
                button.textContent = 'Load Report';
            }
        }

        document.getElementById('loadReportBtn').addEventListener('click', loadReport);
        loadReport();
    </script>
@endpush

