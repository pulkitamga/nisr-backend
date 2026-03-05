@extends('layouts.back-end.app')

@section('title', 'Stock Transfer Report')

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}" alt="">
                Stock Transfer Report
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form id="transfer-report-filter-form" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select class="form-control" id="transfer-year" required>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select class="form-control" id="transfer-month">
                            <option value="">All Months</option>
                            @foreach ($months as $monthNumber => $monthName)
                                <option value="{{ $monthNumber }}">{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Branch</label>
                        <select class="form-control" id="from-branch-id">
                            <option value="">All</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Branch</label>
                        <select class="form-control" id="to-branch-id">
                            <option value="">All</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="transfer-status">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" id="transfer-load-btn" class="btn btn--primary w-100">
                            Load Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Transfers</small>
                        <h4 class="mb-0" id="stat-total-transfers">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Pending</small>
                        <h4 class="mb-0" id="stat-pending">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Approved</small>
                        <h4 class="mb-0" id="stat-approved">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Rejected</small>
                        <h4 class="mb-0" id="stat-rejected">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Quantity</small>
                        <h4 class="mb-0" id="stat-total-qty">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Top Route</small>
                        <small class="d-block" id="stat-top-route">-</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <canvas id="transfer-report-chart" height="110"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h4 class="mb-0">Transfer Details</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th class="text-end">Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="transfer-table-body">
                        <tr>
                            <td colspan="6" class="text-center py-4">Loading...</td>
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
            const form = document.getElementById('transfer-report-filter-form');
            const loadBtn = document.getElementById('transfer-load-btn');
            const tableBody = document.getElementById('transfer-table-body');
            const csrfToken = '{{ csrf_token() }}';
            const chartCtx = document.getElementById('transfer-report-chart').getContext('2d');
            let transferChart = null;

            const toInt = (value) => Number(value || 0);

            const setLoading = (loading) => {
                loadBtn.disabled = loading;
                loadBtn.textContent = loading ? 'Loading...' : 'Load Report';
            };

            const setStats = (stats) => {
                document.getElementById('stat-total-transfers').textContent = toInt(stats.total_transfers).toLocaleString();
                document.getElementById('stat-pending').textContent = toInt(stats.pending_transfers).toLocaleString();
                document.getElementById('stat-approved').textContent = toInt(stats.approved_transfers).toLocaleString();
                document.getElementById('stat-rejected').textContent = toInt(stats.rejected_transfers).toLocaleString();
                document.getElementById('stat-total-qty').textContent = toInt(stats.total_quantity).toLocaleString();

                const from = stats.top_from_branch || '-';
                const to = stats.top_to_branch || '-';
                document.getElementById('stat-top-route').textContent = `${from} -> ${to}`;
            };

            const renderChart = (chartData) => {
                if (transferChart) {
                    transferChart.destroy();
                }

                const datasets = (chartData.datasets || []).map((dataset) => ({
                    label: dataset.label,
                    data: dataset.data || [],
                    borderColor: dataset.borderColor || '#2563eb',
                    backgroundColor: dataset.backgroundColor || 'rgba(37, 99, 235, 0.2)',
                    borderWidth: dataset.borderWidth || 2,
                    tension: dataset.tension !== undefined ? dataset.tension : 0.1,
                    fill: false
                }));

                transferChart = new Chart(chartCtx, {
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

            const summarizeStatus = (products) => {
                const statuses = (products || []).map((product) => product.status).filter(Boolean);
                if (!statuses.length) {
                    return '-';
                }

                return [...new Set(statuses)].join(', ');
            };

            const renderTable = (transfers) => {
                const rows = transfers || [];
                if (!rows.length) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No transfer data found.</td></tr>';
                    return;
                }

                tableBody.innerHTML = rows.map((transfer, index) => {
                    const quantity = (transfer.products || []).reduce((sum, product) => sum + toInt(product.quantity), 0);
                    const date = transfer.transfer_date ? new Date(transfer.transfer_date).toLocaleDateString() : '-';
                    const from = (transfer.from_branch && transfer.from_branch.branch_name) ||
                        (transfer.fromBranch && transfer.fromBranch.branch_name) || '-';
                    const to = (transfer.to_branch && transfer.to_branch.branch_name) ||
                        (transfer.toBranch && transfer.toBranch.branch_name) || '-';
                    const status = summarizeStatus(transfer.products);

                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${date}</td>
                            <td>${from}</td>
                            <td>${to}</td>
                            <td class="text-end">${quantity.toLocaleString()}</td>
                            <td>${status}</td>
                        </tr>
                    `;
                }).join('');
            };

            const loadReport = async () => {
                setLoading(true);
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Loading...</td></tr>';

                try {
                    const payload = {
                        year: toInt(document.getElementById('transfer-year').value),
                        month: document.getElementById('transfer-month').value || null,
                        from_branch_id: document.getElementById('from-branch-id').value || null,
                        to_branch_id: document.getElementById('to-branch-id').value || null,
                        status: document.getElementById('transfer-status').value || null
                    };

                    const response = await fetch('{{ route('admin.stock.transfer-report-data') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Failed to load report.');
                    }

                    setStats(data.statistics || {});
                    renderChart(data.chartData || {});
                    renderTable(data.transfers || []);
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message}</td></tr>`;
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
