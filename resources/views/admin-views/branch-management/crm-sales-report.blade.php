@extends('layouts.back-end.app')

@section('title', translate('crm_sales_report'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    @php($isRtl = get_direction() === 'rtl')
    <div class="content container-fluid {{ $isRtl ? 'text-end' : 'text-start' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}" alt="">
                {{ translate('crm_sales_report') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form id="crm-sales-filter-form" class="row g-2 align-items-start">
                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('date_range') }}</label>
                        <select class="form-control" name="date_type" id="crm-date-type">
                            <option value="this_year">{{ translate('this_year') }}</option>
                            <option value="this_month">{{ translate('this_month') }}</option>
                            <option value="this_week">{{ translate('this_week') }}</option>
                            <option value="today">{{ translate('today') }}</option>
                            <option value="custom_date">{{ translate('custom_range') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2 custom-date-range" id="crm-from-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('from') }}</label>
                        <input type="date" class="form-control" id="crm-from" name="from">
                    </div>

                    <div class="col-md-2 custom-date-range" id="crm-to-wrapper" style="display:none;">
                        <label class="form-label mb-1">{{ translate('to') }}</label>
                        <input type="date" class="form-control" id="crm-to" name="to">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('sale_type') }}</label>
                        <select class="form-control" name="sale_type" id="crm-sale-type">
                            <option value="">{{ translate('all') }}</option>
                            <option value="retail">{{ translate('retail') }}</option>
                            <option value="wholesale">{{ translate('wholesale') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">{{ translate('employee') }}</label>
                        <select class="js-select2-custom form-control" name="agent_id" id="crm-agent-id">
                            <option value="">{{ translate('all') }}</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                        <button type="submit" id="crm-load-btn" class="btn btn--primary">{{ translate('filter') }}</button>
                        <button type="button" id="crm-reset-btn" class="btn btn-outline-secondary">{{ translate('reset') }}</button>
                        <button type="button" id="crm-export-excel" class="btn btn-outline-success">
                            <i class="tio-download-to me-1"></i>{{ translate('excel') }}
                        </button>
                        <button type="button" id="crm-export-pdf" class="btn btn-outline-danger">
                            <i class="tio-download-to me-1"></i>{{ translate('PDF') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('total_sales') }}</small>
                        <h4 class="mb-0" id="crm-total-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('retail_sales') }}</small>
                        <h4 class="mb-0" id="crm-retail-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('wholesale_sales') }}</small>
                        <h4 class="mb-0" id="crm-wholesale-sales">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ translate('top_agent') }}</small>
                        <h6 class="mb-0" id="crm-top-agent">-</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <canvas id="crm-sales-chart" height="210"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('period_summary') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('period') }}</th>
                            <th class="text-end">{{ translate('retail_sales') }}</th>
                            <th class="text-end">{{ translate('wholesale_sales') }}</th>
                            <th class="text-end">{{ translate('total_sales') }}</th>
                            <th class="text-end">{{ translate('retail_orders') }}</th>
                            <th class="text-end">{{ translate('wholesale_orders') }}</th>
                            <th class="text-end">{{ translate('total_orders') }}</th>
                            <th class="text-end">{{ translate('total_quantity') }}</th>
                        </tr>
                    </thead>
                    <tbody id="crm-sales-table-body">
                        <tr>
                            <td colspan="8" class="text-center py-4">{{ translate('loading') }}...</td>
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
            const resetBtn = document.getElementById('crm-reset-btn');
            const exportExcelBtn = document.getElementById('crm-export-excel');
            const exportPdfBtn = document.getElementById('crm-export-pdf');
            const tableBody = document.getElementById('crm-sales-table-body');
            const chartCtx = document.getElementById('crm-sales-chart').getContext('2d');
            const dateTypeEl = document.getElementById('crm-date-type');
            const fromEl = document.getElementById('crm-from');
            const toEl = document.getElementById('crm-to');
            let crmChart = null;

            const text = {
                loading: @json(translate('loading')),
                filter: @json(translate('filter')),
                failedToLoad: @json(translate('failed_to_load_report_data')),
                noData: @json(translate('no_data_found')),
            };

            const fmt = (value) => Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

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
                    sale_type: document.getElementById('crm-sale-type').value || null,
                    agent_id: Number(document.getElementById('crm-agent-id').value) || null,
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

                    params.append(key, String(value));
                });

                return params.toString();
            };

            const setLoading = (loading) => {
                loadBtn.disabled = loading;
                loadBtn.textContent = loading ? `${text.loading}...` : text.filter;
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
                    tension: dataset.tension !== undefined ? dataset.tension : 0.3,
                }));

                crmChart = new Chart(chartCtx, {
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

            const renderTable = (pivotData) => {
                const rows = Object.values(pivotData || {});
                if (!rows.length) {
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4">${text.noData}</td></tr>`;
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
                            <td class="text-end">${totals.total_quantity || 0}</td>
                        </tr>
                    `;
                }).join('');
            };

            const loadReport = async () => {
                setLoading(true);
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4">${text.loading}...</td></tr>`;

                try {
                    const payload = buildPayload();
                    const response = await fetch('{{ route('admin.crm.sales-report-data') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
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
                    renderTable(data.pivotData || {});
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${error.message || text.failedToLoad}</td></tr>`;
                } finally {
                    setLoading(false);
                }
            };

           const runExport = (type) => {
    const payload = buildPayload();
    const query = buildQueryString(payload);
    
    if (type === 'excel') {
        const url = `{{ route('admin.crm.sales-report-export-excel') }}?${query}`;
        window.open(url, '_blank');
    } else if (type === 'pdf') {
        // Capture chart for PDF
        const chartCanvas = document.getElementById('crm-sales-chart');
        
        if (!chartCanvas) {
            const url = `{{ route('admin.crm.sales-report-export-pdf') }}?${query}`;
            window.open(url, '_blank');
            return;
        }

        // Add small delay to ensure chart is rendered
        setTimeout(() => {
            try {
                const chartImage = chartCanvas.toDataURL('image/png');
                
                console.log('Chart captured:', {
                    length: chartImage.length,
                    valid: chartImage.startsWith('data:image/png;base64,')
                });

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('admin.crm.sales-report-export-pdf') }}`;
                form.target = '_blank';

                // Add CSRF token
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                // Add chart image
                const chartInput = document.createElement('input');
                chartInput.type = 'hidden';
                chartInput.name = 'chart_image';
                chartInput.value = chartImage;
                form.appendChild(chartInput);

                // Add all filter parameters
                Object.entries(payload).forEach(([key, value]) => {
                    if (value !== null && value !== '') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = String(value);
                        form.appendChild(input);
                    }
                });

                document.body.appendChild(form);
                form.submit();
                
                setTimeout(() => document.body.removeChild(form), 100);
                
            } catch (error) {
                console.error('Error capturing chart:', error);
                const url = `{{ route('admin.crm.sales-report-export-pdf') }}?${query}`;
                window.open(url, '_blank');
            }
        }, 500);
    }
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
