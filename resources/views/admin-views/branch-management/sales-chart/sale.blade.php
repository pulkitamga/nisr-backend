@extends('layouts.back-end.app')

@section('title', 'Branch Stock Report')

@section('content')
    <div class="content container-fluid">

        <h2 class="mb-4">Branch Stock Report</h2>

        <!-- ================= FILTER BOX ================= -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- DATE TYPE SELECTOR  -->
                    <div class="col-md-3">
                        <label class="form-label">Date Type</label>
                        <select class="form-control" id="dateType">
                            <option value="">All Time</option>
                            <option value="day">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <!-- CUSTOM DATE FIELDS (initially hidden) -->
                    <div class="col-md-6 d-none" id="customDateFields">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" id="fromDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" id="toDate">
                            </div>
                        </div>
                    </div>

                    <!-- Branch Filter -->
                    <div class="col-md-4">
                        <label class="form-label">Select Branch</label>
                        <select class="form-control" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Filter -->
                    <div class="col-md-4">
                        <label class="form-label">Select Product</label>
                        <select class="form-control" id="productFilter">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-variations="{{ $product->variation }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Variation Filter (initially hidden) -->
                    <div class="col-md-4 d-none" id="variationWrapper">
                        <label class="form-label">Select Variation</label>
                        <select class="form-control" id="variationFilter">
                            <option value="">All Variations</option>
                        </select>
                    </div>

                    <!-- Apply Button with Loader -->
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="applyFilter">
                            <span class="apply-text">Apply Filter</span>
                            <span class="spinner-border spinner-border-sm d-none" id="applyLoader"></span>
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" id="resetFilter">
                            Reset
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success w-100" id="exportReport">
                            Export Excel
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger w-100" id="exportPDF">
                            Export PDF
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay d-none">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading stock data...</p>
        </div>

        <!-- ================= STATISTICS ================= -->
        <div class="row mb-4">

            <!-- TOTAL STOCK -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-primary">
                        <i class="tio-inventory fs-2"></i>
                    </div>
                    <h6 class="mb-1">Total Stock Quantity</h6>
                    <h3 id="totalStockQty" class="fw-bold">0</h3>
                </div>
            </div>

            <!-- TOTAL IN -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-success">
                        <i class="tio-arrow-upward fs-2"></i>
                    </div>
                    <h6 class="mb-1">Total In</h6>
                    <h3 id="totalStockIn" class="fw-bold text-success">0</h3>
                    <small class="text-muted">
                        Initial stock + adjustments + returns
                    </small>
                </div>
            </div>

            <!-- TOTAL OUT -->
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm h-100 border-0">
                    <div class="mb-2 text-danger">
                        <i class="tio-arrow-downward fs-2"></i>
                    </div>
                    <h6 class="mb-1">Total Out</h6>
                    <h3 id="totalStockOut" class="fw-bold text-danger">0</h3>
                    <small class="text-muted">
                        Sales + damage + manual reductions
                    </small>
                </div>
            </div>

        </div>


        <!-- ================= CHART ================= -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Branch Stock Chart</h5>
            </div>
            <div class="card-body">
                <canvas id="stockBarChart" height="300"></canvas>
            </div>
        </div>

        <!-- ================= DATA TABLE ================= -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Branch Stock Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="stockTable">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Product Name</th>
                                @if (request()->has('variation_type') && request('variation_type'))
                                    <th>Variation</th>
                                @endif
                                <th>Current Stock</th>
                                <th>Stock In/Out</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody">
                            <!-- Data will be populated by JavaScript -->
                            <tr>
                                <td colspan="7" class="text-center text-muted">No data available. Apply filters to see
                                    results.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="@if (request()->has('variation_type') && request('variation_type')) 5 @else 4 @endif" class="text-end">Total
                                    Stock:</th>
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
        let stockChart = null;
        let isFetching = false; // Flag to prevent multiple concurrent requests

        /* ================= HELPER FUNCTIONS ================= */
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

        /* ================= VARIATION FILTER HANDLER ================= */
        document.getElementById('productFilter').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const variationWrapper = document.getElementById('variationWrapper');
            const variationSelect = document.getElementById('variationFilter');

            // Reset variation dropdown
            variationSelect.innerHTML = '<option value="">All Variations</option>';
            variationWrapper.classList.add('d-none');

            // Get variations data from data attribute
            const variationsJson = selectedOption.getAttribute('data-variations');

            if (!variationsJson || variationsJson === 'null' || variationsJson === '') {
                return;
            }

            try {
                const variations = JSON.parse(variationsJson);

                if (!Array.isArray(variations) || variations.length === 0) {
                    return;
                }

                // Extract unique variation types
                const uniqueTypes = [];
                variations.forEach(variation => {
                    if (variation.type && !uniqueTypes.includes(variation.type)) {
                        uniqueTypes.push(variation.type);
                    }
                });

                // Add variation options
                uniqueTypes.forEach(type => {
                    if (type && type.trim() !== '') {
                        const option = document.createElement('option');
                        option.value = type;
                        option.textContent = type;
                        variationSelect.appendChild(option);
                    }
                });

                // Show variation filter if we have variations
                if (variationSelect.options.length > 1) {
                    variationWrapper.classList.remove('d-none');
                }

            } catch (error) {
                console.error('Error parsing variations:', error);
            }
        });

        /* ================= DATE TYPE HANDLER ================= */
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
        /* ================= FETCH & APPLY FILTER ================= */
        document.getElementById('applyFilter').addEventListener('click', function() {
            if (isFetching) return; // Prevent multiple clicks
            fetchStockData();
        });

        document.getElementById('resetFilter').addEventListener('click', function() {
            if (isFetching) return;

            document.getElementById('productFilter').value = '';
            document.getElementById('branchFilter').value = '';
            document.getElementById('variationFilter').value = '';
            document.getElementById('dateType').value = '';
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';

            document.getElementById('variationWrapper').classList.add('d-none');
            document.getElementById('customDateFields').classList.add('d-none');

            fetchStockData(); // load ALL data
        });

        /* ================= INITIAL LOAD ================= */
        document.addEventListener('DOMContentLoaded', function() {
            fetchStockData(); // load ALL data
        });

        /* ================= FETCH DATA (OPTIMIZED) ================= */
        function fetchStockData() {
            if (isFetching) return;

            const productId = document.getElementById('productFilter').value;
            const branchId = document.getElementById('branchFilter').value;
            const variationType = document.getElementById('variationFilter')?.value ?? '';
            const dateType = document.getElementById('dateType').value;

            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            const requestData = {
                type: 'stock',
                product_id: productId || null,
                branch_id: branchId || null,
                variation_type: variationType || null
            };

            // apply date ONLY if user selected it
            if (dateType) {
                requestData.date_type = dateType;
            }

            if (dateType === 'custom') {
                if (fromDate) requestData.from_date = fromDate;
                if (toDate) requestData.to_date = toDate;
            }

            console.log('REQUEST:', requestData);

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
                    console.log('RESPONSE:', data);

                    if (data.success) {
                        updateAllStatistics(data);
                        updateStockBarChart(data);
                        updateStockTable(data);
                    } else {
                        alert(data.message || 'Something went wrong');
                    }
                    showLoading(false);
                })
                .catch(err => {
                    console.error(err);
                    alert('Request failed');
                    showLoading(false);
                });
        }
        /* ================= UPDATE STATISTICS ================= */
        function updateAllStatistics(data) {
            if (!data.total_stats) {
                console.warn('Missing total_stats in API response');
                return;
            }

            document.getElementById('totalStockQty').innerText =
                Number(data.total_stats.current_stock ?? 0).toLocaleString();

            document.getElementById('totalStockIn').innerText =
                Number(data.total_stats.total_in ?? 0).toLocaleString();

            document.getElementById('totalStockOut').innerText =
                Number(data.total_stats.total_out ?? 0).toLocaleString();
        }

        /* ================= UPDATE BAR CHART (SHOW CURRENT STOCK PER BRANCH) ================= */
        function updateStockBarChart(data) {
            const ctx = document.getElementById('stockBarChart').getContext('2d');

            if (stockChart) {
                stockChart.destroy();
                stockChart = null;
            }

            let labels = [];
            let stockData = [];

            // ✅ GLOBAL / SINGLE BRANCH → Branch-wise chart
            if (data.mode === 'global-branch' || data.mode === 'branch-single') {
                const items = data.branches || [];

                items.forEach(item => {
                    labels.push(item.branch_name || `Branch ${item.branch_id}`);
                    stockData.push(Number(item.current_stock || 0));
                });
            }

            // ✅ BRANCH + ALL PRODUCTS → Product-wise chart
            else if (data.mode === 'branch-products') {
                const items = data.products || [];

                items.forEach(item => {
                    labels.push(item.product_name || 'N/A');
                    stockData.push(Number(item.current_stock || 0));
                });
            }

            // 🚫 No data safeguard
            if (labels.length === 0) {
                stockChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'Current Stock',
                            data: [0],
                            backgroundColor: '#e0e0e0'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                return;
            }

            stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Current Stock',
                        data: stockData,
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Stock Quantity'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: data.mode === 'branch-products' ?
                                    'Products' :
                                    'Branches'
                            }
                        }
                    }
                }
            });
        }

        /* ================= UPDATE DATA TABLE ================= */
        function updateTableTotals(qty) {
            document.getElementById('tableTotalStock').textContent = qty.toLocaleString();
        }

        /* ================= UPDATE DATA TABLE ================= */
        function updateStockTable(data, productName = 'All Products') {
            const tableBody = document.getElementById('stockTableBody');
            const hasVariation = document.getElementById('variationFilter').value !== '';
            const colSpan = hasVariation ? 7 : 6;

            let items = [];
            let isProductMode = false;

            // ✅ HANDLE ALL MODES CORRECTLY
            if (data.mode === 'global-branch' || data.mode === 'branch-single') {
                items = data.branches || [];
                isProductMode = false;
            } else if (data.mode === 'branch-products') {
                items = data.products || [];
                isProductMode = true;
            }

            if (!items || items.length === 0) {
                tableBody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" class="text-center text-muted">
                    No data available for the selected filters.
                </td>
            </tr>
        `;
                updateTableTotals(0);
                return;
            }

            let html = '';
            let totalQty = 0;
            let index = 1;

            items.forEach(item => {
                const currentStock = Number(item.current_stock || 0);
                totalQty += currentStock;

                let lastUpdated = '—';
                if (item.last_updated) {
                    lastUpdated = formatDateStandard(new Date(item.last_updated));
                }

                html += `
            <tr>
                <td>${index++}</td>
                <td>${item.branch_name}</td>
                <td>${isProductMode ? item.product_name : productName}</td>
                ${hasVariation ? `<td>${document.getElementById('variationFilter').value}</td>` : ''}
                <td>${currentStock.toLocaleString()}</td>
                <td>
                    In: ${item.total_in ?? 0}<br>
                    Out: ${item.total_out ?? 0}
                </td>
                <td>${lastUpdated}</td>
            </tr>
        `;
            });

            tableBody.innerHTML = html;
            updateTableTotals(totalQty);
        }

        /* ================= DATE FORMATTING FUNCTION ================= */
        function formatDateStandard(date) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const month = months[date.getMonth()];
            const day = date.getDate();
            const year = date.getFullYear();

            // Format time as 12-hour with AM/PM
            let hours = date.getHours();
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // Convert 0 to 12

            return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
        }
        /* ================= EXPORT FUNCTIONS ================= */
        document.getElementById('exportReport').addEventListener('click', function() {
            exportReport('excel');
        });

        document.getElementById('exportPDF').addEventListener('click', function() {
            exportReport('pdf');
        });

        function exportReport(exportType) {
            const productId = document.getElementById('productFilter').value;
            const branchId = document.getElementById('branchFilter')?.value ?? '';
            let variationType = document.getElementById('variationFilter')?.value ?? '';
            const dateType = document.getElementById('dateType').value;
            const fromDate = document.getElementById('fromDate')?.value ?? '';
            const toDate = document.getElementById('toDate')?.value ?? '';

            // Get chart as base64 image
            const chartCanvas = document.getElementById('stockBarChart');
            let chartImage = '';

            // Only capture chart if it exists
            if (chartCanvas && stockChart) {
                chartImage = stockChart.toBase64Image();
            }

            // Trim trailing dash from variation type
            if (variationType) {
                variationType = variationType.replace(/-$/, '');
            }

            // Show loading
            const btn = exportType === 'excel' ? document.getElementById('exportReport') : document.getElementById(
                'exportPDF');
            const originalText = btn.innerHTML;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...';
            btn.disabled = true;

            const requestData = {
                product_id: productId,
                branch_id: branchId,
                variation_type: variationType,
                date_type: dateType,
                export_type: exportType,
                chart_image: chartImage
            };

            // Only send dates if custom range and dates are filled
            if (dateType === 'custom') {
                if (fromDate) requestData.from_date = fromDate;
                if (toDate) requestData.to_date = toDate;
            }

            console.log('Export request data:', requestData);

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
                            throw new Error(err.message || 'Export failed');
                        });
                    }

                    if (exportType === 'pdf') {
                        return response.blob().then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'branch_stock_report.pdf';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                        });
                    } else {
                        return response.blob();
                    }
                })
                .then(blob => {
                    if (exportType === 'excel' && blob instanceof Blob) {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'branch_stock_report.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    console.error('Export error:', error);
                    alert('Export failed: ' + error.message);
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
            margin-left: -10px;
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
