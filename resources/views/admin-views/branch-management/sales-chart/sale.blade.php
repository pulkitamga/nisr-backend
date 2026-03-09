@extends('layouts.back-end.app')

@section('title', translate('branch_stock_report'))

@section('content') 
    <div class="content container-fluid">

        <h2 class="mb-4">{{ translate('branch_stock_report') }}</h2>

        <!-- ================= FILTER BOX ================= -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('filters') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end justify-content-center">
                    <!-- DATE TYPE SELECTOR  -->
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('date_type') }}</label>
                        <select class="form-control" id="dateType">
                            <option value="">{{ translate('all_time') }}</option>
                            <option value="day">{{ translate('today') }}</option>
                            <option value="week">{{ translate('this_week') }}</option>
                            <option value="month">{{ translate('this_month') }}</option>
                            <option value="custom">{{ translate('custom_range') }}</option>
                        </select>
                    </div>
                    <!-- CUSTOM DATE FIELDS (initially hidden) -->
                    <div class="col-md-6 d-none" id="customDateFields">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('from_date') }}</label>
                                <input type="date" class="form-control" id="fromDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('to_date') }}</label>
                                <input type="date" class="form-control" id="toDate">
                            </div>
                        </div>
                    </div>

                    <!-- Branch Filter -->
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('select_branch') }}</label>
                        <select class="form-control" id="branchFilter">
                            <option value="">{{ translate('all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Filter -->
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('select_product') }}</label>
                        <select class="form-control" id="productFilter">
                            <option value="">{{ translate('all_products') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-variations="{{ $product->variation }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Variation Filter (initially hidden) -->
                    <div class="col-md-4 d-none" id="variationWrapper">
                        <label class="form-label">{{ translate('select_variation') }}</label>
                        <select class="form-control" id="variationFilter">
                            <option value="">{{ translate('all_variations') }}</option>
                        </select>
                    </div>

                    <!-- Apply Button with Loader -->
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="applyFilter">
                            <span class="apply-text">{{ translate('apply_filter') }}</span>
                            <span class="spinner-border spinner-border-sm d-none" id="applyLoader"></span>
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" id="resetFilter">
                            {{ translate('reset') }}
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success w-100" id="exportReport">
                            {{ translate('export_excel') }}
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger w-100" id="exportPDF">
                            {{ translate('export_pdf') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay d-none">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">{{ translate('loading') }}...</span>
            </div>
            <p class="mt-3">{{ translate('loading_stock_data') }}...</p>
        </div>

        <!-- ================= STATISTICS ================= -->
        <div class="row mb-4">

            <!-- TOTAL STOCK -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-primary">
                        <i class="tio-inventory fs-2"></i>
                    </div>
                    <h6 class="mb-1">{{ translate('total_stock_quantity') }}</h6>
                    <h3 id="totalStockQty" class="fw-bold">0</h3>
                </div>
            </div>

            <!-- TOTAL IN -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-success">
                        <i class="tio-arrow-upward fs-2"></i>
                    </div>
                    <h6 class="mb-1">{{ translate('total_in') }}</h6>
                    <h3 id="totalStockIn" class="fw-bold text-success">0</h3>
                    <small class="text-muted">
                        {{ translate('initial_stock_plus_adjustments_plus_returns') }}
                    </small>
                </div>
            </div>

            <!-- TOTAL OUT -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-danger">
                        <i class="tio-arrow-downward fs-2"></i>
                    </div>
                    <h6 class="mb-1">{{ translate('total_out') }}</h6>
                    <h3 id="totalStockOut" class="fw-bold text-danger">0</h3>
                    <small class="text-muted">
                        {{ translate('sales_plus_damage_plus_manual_reductions') }}
                    </small>
                </div>
            </div>

        </div>


        <!-- ================= CHART ================= -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('branch_stock_chart') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="stockBarChart" height="300"></canvas>
            </div>
        </div>

        <!-- ================= DATA TABLE ================= -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('branch_stock_details') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="stockTable">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ translate('branch_name') }}</th>
                                <th>{{ translate('product_name') }}</th>
                                @if (request()->has('variation_type') && request('variation_type'))
                                    <th>{{ translate('variation') }}</th>
                                @endif
                                <th>{{ translate('current_stock') }}</th>
                                <th>{{ translate('stock_in_out') }}</th>
                                <th>{{ translate('last_updated') }}</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody">
                            <!-- Data will be populated by JavaScript -->
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ translate('no_data_available_apply_filters_to_see_results') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="@if (request()->has('variation_type') && request('variation_type')) 5 @else 4 @endif" class="text-end">{{ translate('total_stock') }}:</th>
                                <th id="tableTotalStock">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const branchStockI18n = {
            allVariations: @json(translate('all_variations')),
            allProducts: @json(translate('all_products')),
            noData: @json(translate('no_data')),
            noDataSelectedFilters: @json(translate('no_data_available_for_the_selected_filters')),
            notAvailable: @json(translate('na_symbol')),
            inLabel: @json(translate('in')),
            outLabel: @json(translate('out')),
            currentStock: @json(translate('current_stock')),
            stockQuantity: @json(translate('stock_quantity')),
            products: @json(translate('products')),
            branches: @json(translate('branches')),
            somethingWentWrong: @json(translate('something_went_wrong')),
            requestFailed: @json(translate('request_failed')),
            exportFailed: @json(translate('export_failed')),
            exporting: @json(translate('exporting')),
            months: [@json(translate('jan')), @json(translate('feb')), @json(translate('mar')), @json(translate('apr')), @json(translate('may')), @json(translate('jun')), @json(translate('jul')), @json(translate('aug')), @json(translate('sep')), @json(translate('oct')), @json(translate('nov')), @json(translate('dec'))],
            am: @json(translate('am')),
            pm: @json(translate('pm'))
        };

        let stockChart = null;
        let isFetching = false;

        function showLoading(show = true) {
            const overlay = document.getElementById('loadingOverlay');
            const applyBtn = document.getElementById('applyFilter');
            const applyText = applyBtn.querySelector('.apply-text');
            const applyLoader = applyBtn.querySelector('#applyLoader');

            if (show) {
                overlay.classList.remove('d-none');
                applyBtn.disabled = true;
                applyText.classList.add('d-none');
                applyLoader.classList.remove('d-none');
                isFetching = true;
            } else {
                overlay.classList.add('d-none');
                applyBtn.disabled = false;
                applyText.classList.remove('d-none');
                applyLoader.classList.add('d-none');
                isFetching = false;
            }
        }

        document.getElementById('productFilter').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const variationWrapper = document.getElementById('variationWrapper');
            const variationSelect = document.getElementById('variationFilter');

            variationSelect.innerHTML = `<option value="">${branchStockI18n.allVariations}</option>`;
            variationWrapper.classList.add('d-none');

            const variationsJson = selectedOption.getAttribute('data-variations');
            if (!variationsJson || variationsJson === 'null' || variationsJson === '') {
                return;
            }

            try {
                const variations = JSON.parse(variationsJson);
                if (!Array.isArray(variations) || variations.length === 0) {
                    return;
                }

                const uniqueTypes = [];
                variations.forEach(variation => {
                    if (variation.type && !uniqueTypes.includes(variation.type)) {
                        uniqueTypes.push(variation.type);
                    }
                });

                uniqueTypes.forEach(type => {
                    if (type && type.trim() !== '') {
                        const option = document.createElement('option');
                        option.value = type;
                        option.textContent = type;
                        variationSelect.appendChild(option);
                    }
                });

                if (variationSelect.options.length > 1) {
                    variationWrapper.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error parsing variations:', error);
            }
        });

        document.getElementById('dateType').addEventListener('change', function() {
            const customDateFields = document.getElementById('customDateFields');
            if (this.value === 'custom') {
                customDateFields.classList.remove('d-none');
            } else {
                customDateFields.classList.add('d-none');
                document.getElementById('fromDate').value = '';
                document.getElementById('toDate').value = '';
            }
        });

        document.getElementById('applyFilter').addEventListener('click', function() {
            if (!isFetching) {
                fetchStockData();
            }
        });

        document.getElementById('resetFilter').addEventListener('click', function() {
            if (isFetching) {
                return;
            }

            document.getElementById('productFilter').value = '';
            document.getElementById('branchFilter').value = '';
            document.getElementById('variationFilter').value = '';
            document.getElementById('dateType').value = '';
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
            document.getElementById('variationWrapper').classList.add('d-none');
            document.getElementById('customDateFields').classList.add('d-none');

            fetchStockData();
        });

        document.addEventListener('DOMContentLoaded', function() {
            fetchStockData();
        });

        function fetchStockData() {
            if (isFetching) {
                return;
            }

            const requestData = {
                type: 'stock',
                product_id: document.getElementById('productFilter').value || null,
                branch_id: document.getElementById('branchFilter').value || null,
                variation_type: document.getElementById('variationFilter')?.value || null
            };

            const dateType = document.getElementById('dateType').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            if (dateType) {
                requestData.date_type = dateType;
            }

            if (dateType === 'custom') {
                if (fromDate) requestData.from_date = fromDate;
                if (toDate) requestData.to_date = toDate;
            }

            showLoading(true);

            fetch("{{ route('admin.branch.sales-chart-data') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(requestData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateAllStatistics(data);
                        updateStockBarChart(data);
                        updateStockTable(data);
                    } else {
                        alert(data.message || branchStockI18n.somethingWentWrong);
                    }
                    showLoading(false);
                })
                .catch(() => {
                    alert(branchStockI18n.requestFailed);
                    showLoading(false);
                });
        }

        function updateAllStatistics(data) {
            if (!data.total_stats) {
                return;
            }

            document.getElementById('totalStockQty').innerText = Number(data.total_stats.current_stock || 0).toLocaleString();
            document.getElementById('totalStockIn').innerText = Number(data.total_stats.total_in || 0).toLocaleString();
            document.getElementById('totalStockOut').innerText = Number(data.total_stats.total_out || 0).toLocaleString();
        }

        function updateStockBarChart(data) {
            const ctx = document.getElementById('stockBarChart').getContext('2d');
            if (stockChart) {
                stockChart.destroy();
            }

            let labels = [];
            let stockData = [];

            if (data.mode === 'global-branch' || data.mode === 'branch-single') {
                (data.branches || []).forEach(item => {
                    labels.push(item.branch_name || `${branchStockI18n.branches} ${item.branch_id}`);
                    stockData.push(Number(item.current_stock || 0));
                });
            } else if (data.mode === 'branch-products') {
                (data.products || []).forEach(item => {
                    labels.push(item.product_name || branchStockI18n.notAvailable);
                    stockData.push(Number(item.current_stock || 0));
                });
            }

            if (labels.length === 0) {
                labels = [branchStockI18n.noData];
                stockData = [0];
            }

            stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: branchStockI18n.currentStock,
                        data: stockData,
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: branchStockI18n.stockQuantity
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: data.mode === 'branch-products' ? branchStockI18n.products : branchStockI18n.branches
                            }
                        }
                    }
                }
            });
        }

        function updateTableTotals(qty) {
            document.getElementById('tableTotalStock').textContent = qty.toLocaleString();
        }

        function updateStockTable(data, productName = branchStockI18n.allProducts) {
            const tableBody = document.getElementById('stockTableBody');
            const hasVariation = document.getElementById('variationFilter').value !== '';
            const colSpan = hasVariation ? 7 : 6;

            let items = [];
            let isProductMode = false;

            if (data.mode === 'global-branch' || data.mode === 'branch-single') {
                items = data.branches || [];
            } else if (data.mode === 'branch-products') {
                items = data.products || [];
                isProductMode = true;
            }

            if (!items.length) {
                tableBody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center text-muted">${branchStockI18n.noDataSelectedFilters}</td></tr>`;
                updateTableTotals(0);
                return;
            }

            let html = '';
            let totalQty = 0;

            items.forEach((item, index) => {
                const currentStock = Number(item.current_stock || 0);
                totalQty += currentStock;
                const lastUpdated = item.last_updated ? formatDateStandard(new Date(item.last_updated)) : branchStockI18n.notAvailable;
                const variationValue = hasVariation ? document.getElementById('variationFilter').value : '';

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.branch_name || ''}</td>
                        <td>${isProductMode ? (item.product_name || '') : productName}</td>
                        ${hasVariation ? `<td>${variationValue}</td>` : ''}
                        <td>${currentStock.toLocaleString()}</td>
                        <td>${branchStockI18n.inLabel}: ${item.total_in ?? 0}<br>${branchStockI18n.outLabel}: ${item.total_out ?? 0}</td>
                        <td>${lastUpdated}</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;
            updateTableTotals(totalQty);
        }

        function formatDateStandard(date) {
            const month = branchStockI18n.months[date.getMonth()];
            const day = date.getDate();
            const year = date.getFullYear();
            let hours = date.getHours();
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? branchStockI18n.pm : branchStockI18n.am;
            hours = hours % 12 || 12;

            return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
        }

        document.getElementById('exportReport').addEventListener('click', function() {
            exportReport('excel');
        });

        document.getElementById('exportPDF').addEventListener('click', function() {
            exportReport('pdf');
        });

        function exportReport(exportType) {
            const productId = document.getElementById('productFilter').value;
            const branchId = document.getElementById('branchFilter')?.value || '';
            let variationType = document.getElementById('variationFilter')?.value || '';
            const dateType = document.getElementById('dateType').value;
            const fromDate = document.getElementById('fromDate')?.value || '';
            const toDate = document.getElementById('toDate')?.value || '';
            const chartImage = stockChart ? stockChart.toBase64Image() : '';

            if (variationType) {
                variationType = variationType.replace(/-$/, '');
            }

            const btn = exportType === 'excel' ? document.getElementById('exportReport') : document.getElementById('exportPDF');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${branchStockI18n.exporting}...`;
            btn.disabled = true;

            const requestData = {
                product_id: productId,
                branch_id: branchId,
                variation_type: variationType,
                date_type: dateType,
                export_type: exportType,
                chart_image: chartImage
            };

            if (dateType === 'custom') {
                if (fromDate) requestData.from_date = fromDate;
                if (toDate) requestData.to_date = toDate;
            }

            fetch("{{ route('admin.branch.sales-export') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || branchStockI18n.exportFailed);
                        });
                    }

                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = exportType === 'pdf' ? 'branch_stock_report.pdf' : 'branch_stock_report.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert(`${branchStockI18n.exportFailed}: ${error.message}`);
                });
        }
    </script>
@endpush

@push('css')
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .btn.loading {
            position: relative;
            color: transparent !important;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 20px;
            height: 20px;
            margin-inline-start: -10px;
            margin-top: -10px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush


