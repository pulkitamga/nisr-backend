@extends('layouts.back-end.app')

@section('title', translate('stock_transfer_report'))

@section('content')
    @php($isRtl = Session::get('direction') === 'rtl')
    <div class="content container-fluid {{ $isRtl ? 'text-right' : 'text-left' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}" alt="">
                {{ translate('stock_transfer_report') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form id="transfer-report-filter-form" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('date_range') }}</label>
                        <select class="form-control" id="transfer-date-type" name="date_type">
                            <option value="this_year">{{ translate('this_year') }}</option>
                            <option value="this_month">{{ translate('this_month') }}</option>
                            <option value="this_week">{{ translate('this_week') }}</option>
                            <option value="today">{{ translate('today') }}</option>
                            <option value="custom_date">{{ translate('custom_range') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2 custom-date-range" id="transfer-from-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('from') }}</label>
                        <input type="date" class="form-control" id="transfer-from" name="from">
                    </div>

                    <div class="col-md-2 custom-date-range" id="transfer-to-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('to') }}</label>
                        <input type="date" class="form-control" id="transfer-to" name="to">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('from_branch') }}</label>
                        <select class="form-control" id="from-branch-id" name="from_branch_id">
                            <option value="">{{ translate('all') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('to_branch') }}</label>
                        <select class="form-control" id="to-branch-id" name="to_branch_id">
                            <option value="">{{ translate('all') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('status') }}</label>
                        <select class="form-control" id="transfer-status" name="status">
                            <option value="">{{ translate('all') }}</option>
                            <option value="pending">{{ translate('pending') }}</option>
                            <option value="approved">{{ translate('approved') }}</option>
                            <option value="rejected">{{ translate('rejected') }}</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                        <button type="submit" id="transfer-load-btn" class="btn btn--primary">{{ translate('filter') }}</button>
                        <button type="button" id="transfer-reset-btn" class="btn btn-outline-secondary">{{ translate('reset') }}</button>
                        <button type="button" id="transfer-export-excel" class="btn btn-outline-success">
                            <i class="tio-download-to me-1"></i>{{ translate('excel') }}
                        </button>
                        <button type="button" id="transfer-export-pdf" class="btn btn-outline-danger">
                            <i class="tio-download-to me-1"></i>{{ translate('PDF') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('total_transfers') }}</small>
                        <h4 class="mb-0" id="stat-total-transfers">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('pending') }}</small>
                        <h4 class="mb-0" id="stat-pending">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('approved') }}</small>
                        <h4 class="mb-0" id="stat-approved">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('rejected') }}</small>
                        <h4 class="mb-0" id="stat-rejected">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('total_quantity') }}</small>
                        <h4 class="mb-0" id="stat-total-qty">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('top_route') }}</small>
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
                <h4 class="mb-0">{{ translate('transfer_details') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('date') }}</th>
                            <th>{{ translate('from_branch') }}</th>
                            <th>{{ translate('to_branch') }}</th>
                            <th class="text-end">{{ translate('items') }}</th>
                            <th>{{ translate('status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="transfer-table-body">
                        <tr>
                            <td colspan="6" class="text-center py-4">{{ translate('loading') }}...</td>
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
            const resetBtn = document.getElementById('transfer-reset-btn');
            const exportExcelBtn = document.getElementById('transfer-export-excel');
            const exportPdfBtn = document.getElementById('transfer-export-pdf');
            const tableBody = document.getElementById('transfer-table-body');
            const chartCtx = document.getElementById('transfer-report-chart').getContext('2d');
            const dateTypeEl = document.getElementById('transfer-date-type');
            const fromEl = document.getElementById('transfer-from');
            const toEl = document.getElementById('transfer-to');
            let transferChart = null;

            const text = {
                loading: @json(translate('loading')),
                filter: @json(translate('filter')),
                failedToLoad: @json(translate('failed_to_load_report_data')),
                noData: @json(translate('no_data_found')),
                all: @json(translate('all')),
                statusLabels: {
                    pending: @json(translate('pending')),
                    approved: @json(translate('approved')),
                    rejected: @json(translate('rejected')),
                },
            };

            const toInt = (value) => Number(value || 0);

            const toggleCustomDate = () => {
                const isCustom = dateTypeEl.value === 'custom_date';
                document.querySelectorAll('.custom-date-range').forEach((element) => {
                    element.style.display = isCustom ? '' : 'none';
                });

                if (!isCustom) {
                    fromEl.value = '';
                    toEl.value = '';
                }
            };

            const buildPayload = () => {
                const payload = {
                    date_type: dateTypeEl.value || 'this_year',
                    from_branch_id: document.getElementById('from-branch-id').value || null,
                    to_branch_id: document.getElementById('to-branch-id').value || null,
                    status: document.getElementById('transfer-status').value || null,
                };

                if (payload.date_type === 'custom_date') {
                    payload.from = fromEl.value || null;
                    payload.to = toEl.value || null;
                }

                return payload;
            };

            const buildQueryString = (payload) => {
                const params = new URLSearchParams();
                Object.entries(payload).forEach(([key, value]) => {
                    if (value === null || value === '') {
                        return;
                    }
                    params.append(key, value);
                });
                return params.toString();
            };

            const setLoading = (loading) => {
                loadBtn.disabled = loading;
                loadBtn.textContent = loading ? `${text.loading}...` : text.filter;
            };

            const renderStats = (stats) => {
                document.getElementById('stat-total-transfers').textContent = toInt(stats.total_transfers).toLocaleString();
                document.getElementById('stat-pending').textContent = toInt(stats.pending_transfers).toLocaleString();
                document.getElementById('stat-approved').textContent = toInt(stats.approved_transfers).toLocaleString();
                document.getElementById('stat-rejected').textContent = toInt(stats.rejected_transfers).toLocaleString();
                document.getElementById('stat-total-qty').textContent = toInt(stats.total_quantity).toLocaleString();

                const fromBranch = stats.top_from_branch && stats.top_from_branch.name
                    ? `${stats.top_from_branch.name} (${toInt(stats.top_from_branch.count)})`
                    : '-';
                const toBranch = stats.top_to_branch && stats.top_to_branch.name
                    ? `${stats.top_to_branch.name} (${toInt(stats.top_to_branch.count)})`
                    : '-';

                document.getElementById('stat-top-route').textContent = `${fromBranch} -> ${toBranch}`;
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
                    fill: false,
                }));

                transferChart = new Chart(chartCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels || [],
                        datasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            };

            const summarizeStatus = (products) => {
                const statuses = (products || [])
                    .map((product) => product.status)
                    .filter(Boolean);

                if (!statuses.length) {
                    return '-';
                }

                return [...new Set(statuses.map((status) => {
                    const key = String(status).toLowerCase();
                    return text.statusLabels[key] || status;
                }))].join(', ');
            };

            const renderTable = (transfers) => {
                const rows = transfers || [];
                if (!rows.length) {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4">${text.noData}</td></tr>`;
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
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4">${text.loading}...</td></tr>`;

                try {
                    const payload = buildPayload();
                    const response = await fetch('{{ route('admin.stock.transfer-report-data') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || text.failedToLoad);
                    }

                    renderStats(data.statistics || {});
                    renderChart(data.chartData || {});
                    renderTable(data.transfers || []);
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message || text.failedToLoad}</td></tr>`;
                } finally {
                    setLoading(false);
                }
            };

            const runExport = (type) => {
                const payload = buildPayload();
                const query = buildQueryString(payload);
                const url = type === 'excel'
                    ? `{{ route('admin.stock.transfer-report-export-excel') }}?${query}`
                    : `{{ route('admin.stock.transfer-report-export-pdf') }}?${query}`;

                window.open(url, '_blank');
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                loadReport();
            });

            dateTypeEl.addEventListener('change', toggleCustomDate);

            resetBtn.addEventListener('click', () => {
                form.reset();
                dateTypeEl.value = 'this_year';
                toggleCustomDate();
                loadReport();
            });

            exportExcelBtn.addEventListener('click', () => runExport('excel'));
            exportPdfBtn.addEventListener('click', () => runExport('pdf'));

            toggleCustomDate();
            loadReport();
        })();
    </script>
@endpush
