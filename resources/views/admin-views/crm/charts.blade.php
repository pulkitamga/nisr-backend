@extends('layouts.back-end.app')

@section('title', translate('CRM Analytics Dashboard'))

@push('css_or_js')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" />

    <style>
        .chart-container {
            position: relative;
            height: 500px;
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #3498db;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #7f8c8d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .legend-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 25px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .legend-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            padding: 8px 12px;
            border-radius: 6px;
            background: white;
            border: 1px solid #e9ecef;
        }

        .legend-item:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 5px;
            margin-right: 12px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chart-controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            justify-content: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .chart-controls .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            border-width: 2px;
            transition: all 0.2s;
        }

        .chart-controls .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-controls .btn.active {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-primary.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        .btn-outline-success.active {
            background-color: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }

        .btn-outline-warning.active {
            background-color: #f39c12;
            color: white;
            border-color: #f39c12;
        }

        .btn-outline-info.active {
            background-color: #9b59b6;
            color: white;
            border-color: #9b59b6;
        }

        .btn-outline-danger.active {
            background-color: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .btn-outline-dark.active {
            background-color: #34495e;
            color: white;
            border-color: #34495e;
        }

        /* Stacked toggle */
        .stacked-toggle {
            margin-left: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 8px 15px;
            border-radius: 6px;
            border: 2px solid #e9ecef;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            font-weight: 500;
            color: #495057;
        }

        /* Data table */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            max-height: 400px;
            overflow-y: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            padding: 12px 15px;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
            background-color: #e9ecef;
            height: 24px;
        }

        .progress-bar {
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            line-height: 24px;
        }

        .badge {
            font-size: 12px;
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 6px;
        }

        /* Chart type selector */
        .chart-type-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-type-selector .form-control-sm {
            border-radius: 6px;
            border: 2px solid #e9ecef;
            font-weight: 500;
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-chart-bar-2 text-primary"></i>
                        {{ translate('CRM Analytics Dashboard') }}
                    </h1>
                    <p class="mb-0 text-muted">
                        {{ translate('Complete overview of all CRM activities in one chart') }}
                    </p>
                </div>
            </div>
        </div>
        @php
            $start = \Carbon\Carbon::today()->subDays(6)->toDateString();
            $end = \Carbon\Carbon::today()->toDateString();
        @endphp
        <!-- Filters -->
        <div class="filter-card">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ translate('Date Range') }}</label>
                    <input type="text" id="dateRange" class="form-control"
                        value="{{ $start }} - {{ $end }}">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ translate('Department') }}</label>
                    <select id="departmentFilter" class="form-control">
                        <option value="">{{ translate('All Departments') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                   <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">{{ translate('Pipeline') }}</label>
            <select id="pipelineFilter" class="form-control">
                <option value="">{{ translate('All Pipelines') }}</option>
                <option value="form">{{ translate('Form') }}</option>
                <option value="ticket">{{ translate('Ticket') }}</option>
                <option value="lead">{{ translate('Lead') }}</option>
            </select>
        </div>
        
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ translate('Message Type') }}</label>
                    <select id="messageType" class="form-control">
                        <option value="">{{ translate('All Types') }}</option>
                        <option value="complaint">{{ translate('Complaint') }}</option>
                         <option value="support">{{ translate('Support') }}</option>
                         <option value="career">{{ translate('Career') }}</option>
                        <option value="service">{{ translate('Service') }}</option>
                        <option value="retail">{{ translate('Retail') }}</option>
                        <option value="wholesale">{{ translate('Wholesale') }}</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ translate('Status') }}</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">{{ translate('All Status') }}</option>
                        <option value="new">{{ translate('New') }}</option>
                        <option value="processing">{{ translate('Processing') }}</option>
                        <option value="converted">{{ translate('Converted') }}</option>
                        {{-- <option value="ignored">{{ translate('Ignored') }}</option> --}}
                        <option value="spam">{{ translate('Spam') }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <button id="applyFilter" class="btn btn-primary">
                        <i class="tio-filter-list"></i> {{ translate('Apply Filters') }}
                    </button>
                    <button id="resetFilter" class="btn btn-secondary">
                        <i class="tio-refresh"></i> {{ translate('Reset') }}
                    </button>
                    <div class="chart-controls mt-3">
                        {{-- <button class="btn btn-outline-primary btn-sm active" data-key="total" onclick="toggleDataset('total')">
                        <i class="tio-checkbox"></i> Total
                    </button> --}}
                        <button class="btn btn-outline-success btn-sm active" data-key="assigned"
                            onclick="toggleDataset('assigned')">
                            <i class="tio-checkbox"></i> Assigned
                        </button>
                        <button class="btn btn-outline-warning btn-sm active" data-key="pending"
                            onclick="toggleDataset('pending')">
                            <i class="tio-checkbox"></i> Pending
                        </button>
                        <button class="btn btn-outline-info btn-sm active" data-key="converted"
                            onclick="toggleDataset('converted')">
                            <i class="tio-checkbox"></i> Converted
                        </button>
                        <button class="btn btn-outline-danger btn-sm active" data-key="ignored"
                            onclick="toggleDataset('ignored')">
                            <i class="tio-checkbox"></i> Ignored
                        </button>
                        <div class="stacked-toggle">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="stackedToggle"
                                    onchange="toggleStackedMode()">
                                <label class="form-check-label" for="stackedToggle">
                                    <strong>Stacked Bars</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4" id="summaryStats">
            <!-- Stats loaded via AJAX -->
        </div>

        <!-- Main Chart -->
        <div class="filter-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ translate('CRM Analytics Overview') }}</h4>
                <div class="chart-type-selector">
                    <select id="chartType" class="form-control form-control-sm">
                        <option value="line">Line Chart</option>
                        <option value="bar" selected>Bar Chart</option>
                        <option value="stackedBar">Stacked Bar Chart</option>
                        <option value="radar">Radar Chart</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="exportChart()">
                        <i class="tio-download-to"></i> Export
                    </button>
                   <button class="btn btn-sm btn-outline-success ms-2" onclick="exportExcel()">
    <i class="tio-file"></i> Excel
</button>

<button class="btn btn-sm btn-outline-danger ms-2" onclick="exportFullPDF()">
    <i class="tio-pdf"></i> PDF
</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="mainChart"></canvas>
            </div>
            <div class="legend-container" id="chartLegend">
                <!-- Legend loaded via AJAX -->
            </div>
        </div>

        <!-- Data Table -->
        <div class="filter-card">
            <h4 class="mb-3">{{ translate('Detailed Data') }}</h4>
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Total') }}</th>
                            <th>{{ translate('Assigned') }}</th>
                            <th>{{ translate('Pending') }}</th>
                            <th>{{ translate('Converted') }}</th>
                            <th>{{ translate('Ignored') }}</th>
                            <th>{{ translate('Spam') }}</th>
                            <th>{{ translate('Assigned %') }}<small title="Assigned messages divided by total messages for that day">
<i class="tio-info"></i>
</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
<!-- Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        (function() {
            'use strict';

            function loadScript(src, callback) {
                var script = document.createElement('script');
                script.src = src;
                script.onload = callback;
                document.head.appendChild(script);
            }

            function loadCss(href) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }

            function initializeDatePicker() {
                if (typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
                    if ($('#dateRange').length && !$('#dateRange').data('daterangepicker')) {
                        $('#dateRange').daterangepicker({
                            locale: {
                                format: 'YYYY-MM-DD',
                                applyLabel: 'Apply',
                                cancelLabel: 'Cancel'
                            },
                            startDate: moment().subtract(6, 'days'),
                            endDate: moment(),
                            ranges: {
                                'Today': [moment(), moment()],
                                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                'This Month': [moment().startOf('month'), moment().endOf('month')]
                            }
                        });
                    }
                }
            }

            function initializeChartComponents() {
                $(document).ready(function() {
                    initChartPage(); // <-- aapka main function
                });
            }

            function initAll() {
                initializeDatePicker();
                initializeChartComponents();
            }

            // STEP 1: Load moment.js
            if (typeof moment === 'undefined') {
                loadScript('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js', function() {

                    // STEP 2: Load daterangepicker
                    if (typeof $.fn.daterangepicker === 'undefined') {
                        loadCss('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
                        loadScript('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
                            function() {

                                // STEP 3: Load Chart.js
                                if (typeof Chart === 'undefined') {
                                    loadScript(
                                        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js',
                                        function() {
                                            setTimeout(initAll, 300);
                                        });
                                } else {
                                    setTimeout(initAll, 300);
                                }

                            });
                    } else {

                        // STEP 3: Load Chart.js
                        if (typeof Chart === 'undefined') {
                            loadScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js',
                                function() {
                                    setTimeout(initAll, 300);
                                });
                        } else {
                            setTimeout(initAll, 300);
                        }
                    }

                });
            } else {

                // STEP 2: Load daterangepicker
                if (typeof $.fn.daterangepicker === 'undefined') {
                    loadCss('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
                    loadScript('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', function() {

                        // STEP 3: Load Chart.js
                        if (typeof Chart === 'undefined') {
                            loadScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js',
                                function() {
                                    setTimeout(initAll, 300);
                                });
                        } else {
                            setTimeout(initAll, 300);
                        }

                    });
                } else {

                    // STEP 3: Load Chart.js
                    if (typeof Chart === 'undefined') {
                        loadScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', function() {
                            setTimeout(initAll, 300);
                        });
                    } else {
                        setTimeout(initAll, 300);
                    }
                }
            }

        })();
    </script>


    <script>
         let mainChart = null;
            let currentData = null;

        function initChartPage() {
            'use strict';

           
            let visibleDatasets = {
                'total': true,
                'assigned': true,
                'pending': true,
                'converted': true,
                'ignored': true,
                'spam': true
            };

            setTimeout(function() {
                loadChartData();
            }, 500);

            $('#applyFilter').off('click').on('click', function() {
                loadChartData();
            });

            $('#resetFilter').off('click').on('click', function() {

                if ($('#dateRange').data('daterangepicker')) {
                    $('#dateRange').data('daterangepicker')
                        .setStartDate(moment().subtract(6, 'days'));
                    $('#dateRange').data('daterangepicker').setEndDate(moment());
                }

                $('#departmentFilter').val('');
                $('#messageType').val('');
                $('#statusFilter').val('');
                $('#pipelineFilter').val('');



                loadChartData();
            });

          $('#chartType').off('change').on('change', function() {
    const isStacked = $(this).val() === 'stackedBar';
    $('#stackedToggle').prop('checked', isStacked);

    if (currentData) {
        renderChart(currentData);
    }
});

            function loadChartData() {

                const dateRange = $('#dateRange').val().split(' - ');
                const departmentId = $('#departmentFilter').val();
                const messageType = $('#messageType').val();
                const status = $('#statusFilter').val();
                const pipeline = $('#pipelineFilter').val();


                $('#applyFilter').prop('disabled', true).html('<i class="tio-refresh spinner"></i> Loading...');

                $.ajax({
                    url: '{{ route('admin.crm.chart.data') }}',
                    method: 'GET',
                    data: {
                        start_date: dateRange[0],
                        end_date: dateRange[1],
                        department_id: departmentId,
                        message_type: messageType,
                        status: status,
                        pipeline: pipeline
                    },
                    success: function(response) {
                        if (response.success) {
                            currentData = response.data;
                            renderChart(currentData);
                            updateSummaryStats(response.data.summary);
                            updateDataTable(response.data.daily_stats);
                            updateLegend(response.data.legend);
                        } else {
                            toastr.error(response.message || 'Failed to load data');
                        }

                        $('#applyFilter').prop('disabled', false).html(
                            '<i class="tio-filter-list"></i> Apply Filters');
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Error loading chart data: ' + error);
                        $('#applyFilter').prop('disabled', false).html(
                            '<i class="tio-filter-list"></i> Apply Filters');
                    }
                });
            }

            function renderChart(data) {

                const MAX_DAYS = 7;

                let slicedData = {
                    labels: data.labels.slice(-MAX_DAYS),
                    datasets: data.datasets.map(ds => ({
                        ...ds,
                        data: ds.data.slice(-MAX_DAYS)
                    }))
                };

                const canvas = document.getElementById('mainChart');
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                const chartType = $('#chartType').val() || 'line';

                if (mainChart) {
                    mainChart.destroy();
                }

                const datasets = prepareDatasets(slicedData.datasets);

                if (chartType === 'stackedBar') {
                    renderStackedBarChart(ctx, slicedData, datasets);
                } else {
                    renderNormalChart(ctx, slicedData, datasets, chartType);
                }
            }

            function renderNormalChart(ctx, data, datasets, chartType) {

                let options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(items) {
                                    return 'Date: ' + items[0].label;
                                },
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = context.raw || 0;
                                    return `${label}: ${value} messages`;
                                },
                                footer: function(items) {
                                    let total = items.reduce((sum, i) => sum + i.raw, 0);
                                    return `Total: ${total}`;
                                }
                            }
                        }

                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                };

                if (chartType === 'line') {
                    datasets = datasets.map(ds => ({
                        ...ds,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: ds.borderColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.3
                    }));
                }

                if (chartType === 'bar') {
                    datasets = datasets.map(ds => ({
                        ...ds,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }));

                    options.elements = {
                        bar: {
                            borderWidth: 2,
                            borderColor: '#fff',
                            borderRadius: 6,
                            borderSkipped: false
                        }
                    };
                }

                mainChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: options
                });
            }

            function renderStackedBarChart(ctx, data, datasets) {

                const stackedDatasets = datasets.map(ds => ({
                    ...ds,
                    stack: 'stack',
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }));

                mainChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: stackedDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    title: function(items) {
                                        return 'Date: ' + items[0].label;
                                    },
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const value = context.raw || 0;
                                        return `${label}: ${value} messages`;
                                    },
                                    footer: function(items) {
                                        let total = items.reduce((sum, i) => sum + i.raw, 0);
                                        return `Total: ${total}`;
                                    }
                                }
                            }

                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        },
                        elements: {
                            bar: {
                                borderWidth: 2,
                                borderColor: '#fff',
                                borderRadius: 6,
                                borderSkipped: false
                            }
                        }
                    }
                });
            }

            function prepareDatasets(datasets) {
                let result = [];

                datasets.forEach(dataset => {

                    // 🔴 CHECK: skip dataset if all values are 0
                    const hasData = dataset.data.some(val => Number(val) > 0);

                    if (visibleDatasets[dataset.key] && hasData) {
                        result.push({
                            label: dataset.label,
                            data: dataset.data,
                            backgroundColor: dataset.backgroundColor + 'CC',
                            borderColor: dataset.backgroundColor,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        });
                    }
                });

                return result;
            }

            function updateSummaryStats(summary) {
                $('#summaryStats').html(`
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #3498db">
                    <div class="stat-number text-primary">${summary.total || 0}</div>
                    <div class="stat-label">Total Messages</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #2ecc71">
                    <div class="stat-number text-success">${summary.assigned || 0}</div>
                    <div class="stat-label">Assigned</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #f39c12">
                    <div class="stat-number text-warning">${summary.pending || 0}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #9b59b6">
                    <div class="stat-number text-info">${summary.converted || 0}</div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #e74c3c">
                    <div class="stat-number text-danger">${summary.ignored || 0}</div>
                    <div class="stat-label">Ignored</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="stat-card" style="border-left-color: #34495e">
                    <div class="stat-number text-dark">${summary.spam || 0}</div>
                    <div class="stat-label">Spam</div>
                </div>
            </div>
        `);
            }

            function updateDataTable(dailyStats) {

                let tableRows = '';

                if (!dailyStats || dailyStats.length === 0) {
                    tableRows = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="mb-0 text-muted">No data found</p>
                    </td>
                </tr>
            `;
                } else {
                    dailyStats.forEach(stat => {

                        const assignedPercentage = stat.total > 0 ? Math.round((stat.assigned / stat.total) * 100) :
                            0;

                        let progressBarClass = 'bg-danger';
                        if (assignedPercentage > 70) progressBarClass = 'bg-success';
                        else if (assignedPercentage > 40) progressBarClass = 'bg-warning';

                        tableRows += `
                    <tr>
                        <td>${stat.date}</td>
                        <td><span class="badge bg-primary">${stat.total || 0}</span></td>
                        <td><span class="badge bg-success">${stat.assigned || 0}</span></td>
                        <td><span class="badge bg-warning">${stat.pending || 0}</span></td>
                        <td><span class="badge bg-info">${stat.converted || 0}</span></td>
                        <td><span class="badge bg-danger">${stat.ignored || 0}</span></td>
                        <td><span class="badge" style="background-color:#B2BEB5">${stat.spam || 0}</span></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar ${progressBarClass}"    style="width:${assignedPercentage}%; min-width:40px;">
                                    ${assignedPercentage}%
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                    });
                }

                $('#dataTable tbody').html(tableRows);
            }

            function updateLegend(legend) {
                let legendHtml = '';

                if (legend && legend.length > 0) {
                    legend.forEach(item => {
                        const isVisible = visibleDatasets[item.key];
                        const opacity = isVisible ? '1' : '0.4';

                        legendHtml += `
                    <div class="legend-item" onclick="toggleDataset('${item.key}')" style="opacity:${opacity}">
                        <div class="legend-color" style="background-color:${item.color}"></div>
                        <span>${item.label}</span>
                    </div>
                `;
                    });
                }

                $('#chartLegend').html(legendHtml);
            }

            window.toggleDataset = function(key) {
                visibleDatasets[key] = !visibleDatasets[key];

                const button = $(`.btn[data-key="${key}"]`);
                button.toggleClass('active', visibleDatasets[key]);

                if (currentData) {
                    renderChart(currentData);
                    updateLegend(currentData.legend);
                }
            };

            window.toggleStackedMode = function() {
                const isChecked = $('#stackedToggle').is(':checked');

                if (isChecked) {
                    $('#chartType').val('stackedBar');
                } else {
                    $('#chartType').val('bar');
                }

                if (currentData) {
                    renderChart(currentData);
                }
            };

            window.exportChart = function() {
                if (mainChart) {
                    const link = document.createElement('a');
                    link.download = "crm-chart.png";
                    link.href = mainChart.toBase64Image();
                    link.click();
                }
            };
            window.exportPDF = function() {

    const { jsPDF } = window.jspdf;

    const element = document.querySelector('.filter-card:nth-of-type(2)'); 
    // This selects the main chart card

    if (!element) return;

    html2canvas(element, {
        scale: 2,
        useCORS: true
    }).then(canvas => {

        const imgData = canvas.toDataURL('image/png');

        const pdf = new jsPDF('l', 'mm', 'a4'); // landscape
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();

        const imgWidth = pdfWidth - 20;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        pdf.setFontSize(14);
        pdf.text("CRM Analytics Report", 10, 10);

        pdf.addImage(imgData, 'PNG', 10, 20, imgWidth, imgHeight);

        pdf.save("crm-analytics-report.pdf");
    });
};

        }
 function getFilters() {
    const dateRange = $('#dateRange').val().split(' - ');

    return {
        start_date: dateRange[0],
        end_date: dateRange[1],
        department_id: $('#departmentFilter').val(),
        message_type: $('#messageType').val(),
        status: $('#statusFilter').val(),
        pipeline: $('#pipelineFilter').val()
    };
}

window.exportExcel = function () {
    const params = new URLSearchParams(getFilters()).toString();
    window.open(`{{ route('admin.crm.export.excel') }}?${params}`, '_blank');
};

window.exportFullPDF = function () {
    const params = new URLSearchParams(getFilters()).toString();
    window.open(`{{ route('admin.crm.export.pdf') }}?${params}`, '_blank');
};

 

    </script>
@endpush
