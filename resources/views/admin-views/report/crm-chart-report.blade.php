@extends('layouts.back-end.app')

@php($isRtl = get_direction() === 'rtl')
@section('title', translate('Agent Sales CRM Report'))

@section('content')
<div class="content container-fluid {{ $isRtl ? 'text-end' : 'text-start' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <h2 class="mb-4">{{ translate('Agent Sales CRM Report') }}</h2>
    
    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ translate('Filters') }}</h5>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Agent Filter - Custom Dropdown -->
                    <div class="col-md-4" id="agentFilter">
                        <label class="form-label">{{ translate('Select Agents') }}</label>
                        <div class="custom-multiselect">
                            <div class="select-box" onclick="toggleDropdown('agentDropdown')">
                                <div class="form-control d-flex justify-content-between align-items-center" style="cursor: pointer;">
                                    <span id="agentDisplayText">{{ translate('All Agents') }}</span>
                                    <i class="tio-chevron-down"></i>
                                </div>
                            </div>
                            <div id="agentDropdown" class="dropdown-checkboxes" style="display: none;">
                                <div class="dropdown-header p-2 border-bottom">
                                    <button type="button" class="btn btn-sm btn-link p-0 me-2" onclick="selectAllAgents(true)">{{ translate('Select All') }}</button>
                                    <button type="button" class="btn btn-sm btn-link p-0" onclick="selectAllAgents(false)">{{ translate('Clear All') }}</button>
                                </div>
                                <div class="dropdown-content p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input agent-checkbox" type="checkbox" value="0" id="agent0" checked>
                                        <label class="form-check-label" for="agent0">{{ translate('Agent A') }}</label>
                                    </div>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input agent-checkbox" type="checkbox" value="1" id="agent1" checked>
                                        <label class="form-check-label" for="agent1">{{ translate('Agent B') }}</label>
                                    </div>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input agent-checkbox" type="checkbox" value="2" id="agent2" checked>
                                        <label class="form-check-label" for="agent2">{{ translate('Agent C') }}</label>
                                    </div>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input agent-checkbox" type="checkbox" value="3" id="agent3" checked>
                                        <label class="form-check-label" for="agent3">{{ translate('Agent D') }}</label>
                                    </div>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input agent-checkbox" type="checkbox" value="4" id="agent4" checked>
                                        <label class="form-check-label" for="agent4">{{ translate('Agent E') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Type Filter -->
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Sales Type') }}</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input salesType" type="checkbox" value="retail" checked name="sales_types[]" id="retailCheck">
                                <label class="form-check-label" for="retailCheck">{{ translate('retail') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input salesType" type="checkbox" value="wholesale" checked name="sales_types[]" id="wholesaleCheck">
                                <label class="form-check-label" for="wholesaleCheck">{{ translate('wholesale') }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Month Filter - Custom Dropdown -->
                    <div class="col-md-4" id="monthFilter">
                        <label class="form-label">{{ translate('Select Months') }}</label>
                        <div class="custom-multiselect">
                            <div class="select-box" onclick="toggleDropdown('monthDropdown')">
                                <div class="form-control d-flex justify-content-between align-items-center" style="cursor: pointer;">
                                    <span id="monthDisplayText">{{ translate('All Months') }}</span>
                                    <i class="tio-chevron-down"></i>
                                </div>
                            </div>
                            <div id="monthDropdown" class="dropdown-checkboxes" style="display: none;">
                                <div class="dropdown-header p-2 border-bottom">
                                    <button type="button" class="btn btn-sm btn-link p-0 me-2" onclick="selectAllMonths(true)">{{ translate('Select All') }}</button>
                                    <button type="button" class="btn btn-sm btn-link p-0" onclick="selectAllMonths(false)">{{ translate('Clear All') }}</button>
                                </div>
                                <div class="dropdown-content p-2" style="max-height: 200px; overflow-y: auto;">
                                    <?php
                                    $months = [translate('January'), translate('February'), translate('March'), translate('April'), translate('May'), translate('June'),
                                              translate('July'), translate('August'), translate('September'), translate('October'), translate('November'), translate('December')];
                                    ?>
                                    @foreach($months as $index => $month)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input month-checkbox" type="checkbox" value="{{ $index }}" id="month{{ $index }}" checked>
                                        <label class="form-check-label" for="month{{ $index }}">{{ $month }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Type & Buttons Row -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('Chart Type') }}</label>
                        <select id="chartType" class="form-control">
                            <option value="bar">{{ translate('Bar Chart') }}</option>
                            <option value="line">{{ translate('Line Chart') }}</option>
                            <option value="stacked">{{ translate('Stacked Bar') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary" id="applyFilters">
                            <i class="tio-filter-list"></i> {{ translate('Generate Chart') }}
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <i class="tio-restore"></i> {{ translate('reset') }}
                        </button>
                        <button type="button" class="btn btn-success" id="downloadChart">
                            <i class="tio-download-to"></i> {{ translate('Download Chart') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rest of your content remains the same -->
    <!-- CRM Chart Container -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0" id="chartTitle">{{ translate('Agent Sales Overview') }}</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-secondary" id="filterStatus">{{ translate('All Agents') }} | {{ translate('All Months') }}</span>
                <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllFilters">
                    <i class="tio-clear"></i> {{ translate('Clear All') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body p-3">
                            <h6 class="card-title">{{ translate('Total Sales') }}</h6>
                            <h3 id="totalSales" class="mb-0">0</h3>
                            <small>{{ translate('Across all agents & months') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body p-3">
                            <h6 class="card-title">{{ translate('Avg. per Agent') }}</h6>
                            <h3 id="avgPerAgent" class="mb-0">0</h3>
                            <small>{{ translate('Average sales per agent') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body p-3">
                            <h6 class="card-title">{{ translate('Top Agent') }}</h6>
                            <h3 id="topAgent" class="mb-0">-</h3>
                            <small>{{ translate('Highest performing') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body p-3">
                            <h6 class="card-title">{{ translate('Best Month') }}</h6>
                            <h3 id="bestMonth" class="mb-0">-</h3>
                            <small>{{ translate('Highest sales month') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="row">
                <div class="col-md-12">
                    <div class="chart-container" style="position: relative; height: 500px; width: 100%;">
                        <canvas id="crmChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Legend -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-light">
                        <h6 class="mb-2">{{ translate('Chart Legend:') }}</h6>
                        <div id="chartLegend" class="d-flex flex-wrap gap-3">
                            <!-- Legend will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Small Comparison Charts -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('Monthly Comparison') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 250px;">
                                <canvas id="monthlyComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('Agent Performance') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 250px;">
                                <canvas id="agentPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
/* ---------- GLOBAL VARIABLES ---------- */
let mainChart;
let monthlyChart;
let agentChart;
const i18n = {
    noAgentsSelected: @json(translate('No agents selected')),
    allAgents: @json(translate('All Agents')),
    noMonthsSelected: @json(translate('No months selected')),
    allMonths: @json(translate('All Months')),
    chartGeneratedSuccessfully: @json(translate('Chart generated successfully!')),
    filtersResetToDefault: @json(translate('Filters reset to default!')),
    allAgentsAllMonths: @json(translate('All Agents | All Months')),
    agentCountLabel: @json(translate('Agent(s)')),
    monthCountLabel: @json(translate('Month(s)')),
    bar: @json(translate('Bar')),
    line: @json(translate('Line')),
    stackedBar: @json(translate('Stacked Bar')),
    agentSales: @json(translate('Agent Sales')),
    chart: @json(translate('chart')),
    months: @json(translate('months')),
    salesAmount: @json(translate('Sales Amount')),
    sales: @json(translate('sales')),
    monthlySales: @json(translate('Monthly Sales')),
    retail: @json(translate('retail')),
    wholesale: @json(translate('wholesale')),
    chartDownloadedSuccessfully: @json(translate('Chart downloaded successfully!'))
};
const isRtl = @json($isRtl);
let currentFilters = {
    agents: [0, 1, 2, 3, 4], // All agents selected by default
    salesTypes: ['retail', 'wholesale'],
    months: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] // All months
};

/* ---------- STATIC DATA ---------- */
const agents = [@json(translate('Agent A')), @json(translate('Agent B')), @json(translate('Agent C')), @json(translate('Agent D')), @json(translate('Agent E'))];
const months = [@json(translate('jan')), @json(translate('feb')), @json(translate('mar')), @json(translate('apr')), @json(translate('may')), @json(translate('jun')),
                @json(translate('jul')), @json(translate('aug')), @json(translate('sep')), @json(translate('oct')), @json(translate('nov')), @json(translate('dec'))];

// Static data: [agent][month][type]
const salesData = [
    // Agent A
    [
        [120, 85], [95, 110], [130, 75], [110, 95], [125, 80],
        [140, 90], [115, 100], [105, 120], [135, 85], [150, 95],
        [125, 105], [145, 110]
    ],
    // Agent B
    [
        [80, 65], [70, 85], [95, 70], [85, 90], [100, 75],
        [110, 85], [90, 95], [85, 105], [100, 80], [115, 90],
        [95, 100], [110, 95]
    ],
    // Agent C
    [
        [60, 45], [55, 60], [75, 50], [65, 70], [80, 55],
        [90, 65], [70, 75], [65, 85], [80, 60], [95, 70],
        [75, 80], [90, 75]
    ],
    // Agent D
    [
        [90, 70], [80, 95], [110, 80], [95, 105], [115, 85],
        [125, 95], [100, 110], [95, 120], [115, 90], [130, 100],
        [110, 115], [125, 105]
    ],
    // Agent E
    [
        [70, 55], [65, 75], [85, 60], [75, 85], [90, 65],
        [100, 75], [80, 85], [75, 95], [90, 70], [105, 80],
        [85, 90], [100, 85]
    ]
];

// Color palettes
const colorPalette = {
    retail: ['#3498db', '#2980b9', '#1f618d', '#154360', '#0b3b6b'],
    wholesale: ['#2ecc71', '#27ae60', '#219653', '#1b8743', '#156833'],
    agents: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'],
    months: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', 
             '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
             '#F8C471', '#82E0AA']
};

/* ---------- CUSTOM DROPDOWN FUNCTIONS ---------- */
let dropdownsOpen = {
    agent: false,
    month: false
};

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const agentDropdown = document.getElementById('agentDropdown');
    const monthDropdown = document.getElementById('monthDropdown');
    const agentSelectBox = document.querySelector('#agentFilter .select-box');
    const monthSelectBox = document.querySelector('#monthFilter .select-box');
    
    if (agentDropdown && agentSelectBox && !agentDropdown.contains(event.target) && !agentSelectBox.contains(event.target)) {
        agentDropdown.style.display = 'none';
        dropdownsOpen.agent = false;
    }
    
    if (monthDropdown && monthSelectBox && !monthDropdown.contains(event.target) && !monthSelectBox.contains(event.target)) {
        monthDropdown.style.display = 'none';
        dropdownsOpen.month = false;
    }
});

function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const isVisible = dropdown.style.display === 'block';
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-checkboxes').forEach(d => {
        d.style.display = 'none';
    });
    
    // Toggle current dropdown
    dropdown.style.display = isVisible ? 'none' : 'block';
    
    // Update state
    if (dropdownId === 'agentDropdown') {
        dropdownsOpen.agent = !isVisible;
    } else {
        dropdownsOpen.month = !isVisible;
    }
}

function selectAllAgents(selectAll) {
    const checkboxes = document.querySelectorAll('.agent-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll;
    });
    updateAgentDisplay();
    updateCurrentFilters();
    generateCharts();
}

function selectAllMonths(selectAll) {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll;
    });
    updateMonthDisplay();
    updateCurrentFilters();
    generateCharts();
}

function updateAgentDisplay() {
    const checkboxes = document.querySelectorAll('.agent-checkbox:checked');
    const displayText = document.getElementById('agentDisplayText');
    
    if (checkboxes.length === 0) {
        displayText.textContent = i18n.noAgentsSelected;
        displayText.style.color = '#dc3545';
    } else if (checkboxes.length === 5) {
        displayText.textContent = i18n.allAgents;
        displayText.style.color = '';
    } else {
        const selectedNames = Array.from(checkboxes).map(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            return label ? label.textContent : `Agent ${String.fromCharCode(65 + parseInt(cb.value))}`;
        });
        displayText.textContent = selectedNames.join(', ');
        displayText.style.color = '';
    }
}

function updateMonthDisplay() {
    const checkboxes = document.querySelectorAll('.month-checkbox:checked');
    const displayText = document.getElementById('monthDisplayText');
    const monthNames = [@json(translate('January')), @json(translate('February')), @json(translate('March')), @json(translate('April')), @json(translate('May')), @json(translate('June')),
                       @json(translate('July')), @json(translate('August')), @json(translate('September')), @json(translate('October')), @json(translate('November')), @json(translate('December'))];
    
    if (checkboxes.length === 0) {
        displayText.textContent = i18n.noMonthsSelected;
        displayText.style.color = '#dc3545';
    } else if (checkboxes.length === 12) {
        displayText.textContent = i18n.allMonths;
        displayText.style.color = '';
    } else {
        const selectedNames = Array.from(checkboxes).map(cb => {
            return monthNames[parseInt(cb.value)].substring(0, 3);
        });
        displayText.textContent = selectedNames.join(', ');
        displayText.style.color = '';
    }
}

// Update display when checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to checkboxes
    document.querySelectorAll('.agent-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateAgentDisplay();
            updateCurrentFilters();
            generateCharts();
        });
    });
    
    document.querySelectorAll('.month-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMonthDisplay();
            updateCurrentFilters();
            generateCharts();
        });
    });
    
    // Initialize displays
    updateAgentDisplay();
    updateMonthDisplay();
});

/* ---------- INITIALIZATION ---------- */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with default filters
    generateCharts();
    
    // Set up event listeners for other buttons
    setupEventListeners();
});

/* ---------- EVENT LISTENERS ---------- */
function setupEventListeners() {
    // Generate Chart Button
    document.getElementById('applyFilters').addEventListener('click', function() {
        generateCharts();
        showNotification(i18n.chartGeneratedSuccessfully, 'success');
    });
    
    // Reset Filters Button
    document.getElementById('resetFilters').addEventListener('click', function() {
        resetToDefaultFilters();
    });
    
    // Clear All Filters Button
    document.getElementById('clearAllFilters').addEventListener('click', function() {
        resetToDefaultFilters();
    });
    
    // Download Chart Button
    document.getElementById('downloadChart').addEventListener('click', function() {
        downloadChartImage();
    });
    
    // Chart Type Change
    document.getElementById('chartType').addEventListener('change', function() {
        generateCharts();
    });
    
    // Sales Type Change
    document.querySelectorAll('.salesType').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCurrentFilters();
            generateCharts();
        });
    });
}

/* ---------- FILTER FUNCTIONS ---------- */
function updateCurrentFilters() {
    // Get selected agents from custom checkboxes
    const selectedAgentCheckboxes = [...document.querySelectorAll('.agent-checkbox:checked')];
    currentFilters.agents = selectedAgentCheckboxes.map(cb => parseInt(cb.value));
    
    // Get selected sales types
    const selectedSalesTypes = [...document.querySelectorAll('.salesType:checked')];
    currentFilters.salesTypes = selectedSalesTypes.map(cb => cb.value);
    
    // Get selected months from custom checkboxes
    const selectedMonthCheckboxes = [...document.querySelectorAll('.month-checkbox:checked')];
    currentFilters.months = selectedMonthCheckboxes.map(cb => parseInt(cb.value));
}

function resetToDefaultFilters() {
    // Reset to default values
    currentFilters = {
        agents: [0, 1, 2, 3, 4],
        salesTypes: ['retail', 'wholesale'],
        months: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
    };
    
    // Update checkboxes
    document.querySelectorAll('.agent-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.month-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.salesType').forEach(cb => cb.checked = true);
    
    // Update displays
    updateAgentDisplay();
    updateMonthDisplay();
    
    // Generate charts
    generateCharts();
    
    showNotification(i18n.filtersResetToDefault, 'info');
}

function updateFilterStatus() {
    const filterStatus = document.getElementById('filterStatus');
    const agentCount = currentFilters.agents.length;
    const monthCount = currentFilters.months.length;
    
    if (agentCount === 5 && monthCount === 12) {
        filterStatus.classList.remove('badge-info');
        filterStatus.classList.add('badge-secondary');
        filterStatus.textContent = i18n.allAgentsAllMonths;
    } else {
        filterStatus.classList.remove('badge-secondary');
        filterStatus.classList.add('badge-info');
        filterStatus.textContent = `${agentCount} ${i18n.agentCountLabel} | ${monthCount} ${i18n.monthCountLabel}`;
    }
}

/* ---------- CHART FUNCTIONS ---------- */
// Keep all your existing chart functions exactly as they were
// generateCharts(), generateMainChart(), prepareAgentData(), etc.
// They will work with the currentFilters object which is now updated from checkboxes

function generateCharts() {
    updateCurrentFilters();
    updateFilterStatus();
    
    // Update chart title
    const chartTitle = document.getElementById('chartTitle');
    const chartType = document.getElementById('chartType').value;
    const typeText = chartType === 'bar' ? i18n.bar : chartType === 'line' ? i18n.line : i18n.stackedBar;
    chartTitle.textContent = `${i18n.agentSales} - ${typeText} ${i18n.chart} (${currentFilters.months.length} ${i18n.months})`;
    
    // Generate main chart
    generateMainChart();
    
    // Generate small charts
    generateMonthlyComparisonChart();
    generateAgentPerformanceChart();
    
    // Update summary cards
    updateSummaryCards();
    
    // Update legend
    updateChartLegend();
}

function generateMainChart() {
    const ctx = document.getElementById('crmChart').getContext('2d');
    const chartType = document.getElementById('chartType').value;
    
    // Destroy existing chart if it exists
    if (mainChart) {
        mainChart.destroy();
    }
    
    // Prepare data based on chart type
    let datasets = [];
    const labels = currentFilters.months.map(m => months[m]);
    
    if (chartType === 'stacked') {
        // For stacked bar chart, group by month
        datasets = prepareStackedBarData();
    } else {
        // For regular bar/line charts, group by agent
        datasets = prepareAgentData();
    }
    
    // Chart configuration
    const config = {
        type: chartType === 'stacked' ? 'bar' : chartType,
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y.toLocaleString();
                            return label;
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: chartType === 'stacked',
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.salesAmount
                    }
                },
                x: {
                    stacked: chartType === 'stacked',
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: i18n.months
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            }
        }
    };
    
    mainChart = new Chart(ctx, config);
}

function prepareAgentData() {
    const datasets = [];
    
    currentFilters.agents.forEach((agentIndex, idx) => {
        // Calculate total sales for each month for this agent
        const agentData = currentFilters.months.map(monthIndex => {
            const monthData = salesData[agentIndex][monthIndex];
            let total = 0;
            if (currentFilters.salesTypes.includes('retail')) total += monthData[0];
            if (currentFilters.salesTypes.includes('wholesale')) total += monthData[1];
            return total;
        });
        
        datasets.push({
            label: agents[agentIndex],
            data: agentData,
            backgroundColor: colorPalette.agents[idx % colorPalette.agents.length],
            borderColor: colorPalette.agents[idx % colorPalette.agents.length],
            borderWidth: 2,
            fill: false
        });
    });
    
    return datasets;
}

function prepareStackedBarData() {
    const datasets = [];
    let datasetIndex = 0;
    
    // Create datasets for each agent and sales type combination
    currentFilters.agents.forEach(agentIndex => {
        currentFilters.salesTypes.forEach((type, typeIdx) => {
            const typeData = currentFilters.months.map(monthIndex => {
                const monthData = salesData[agentIndex][monthIndex];
                return type === 'retail' ? monthData[0] : monthData[1];
            });
            
            datasets.push({
                label: `${agents[agentIndex]} - ${type.charAt(0).toUpperCase() + type.slice(1)}`,
                data: typeData,
                backgroundColor: type === 'retail' 
                    ? colorPalette.retail[agentIndex % colorPalette.retail.length]
                    : colorPalette.wholesale[agentIndex % colorPalette.wholesale.length],
                stack: `Stack ${agentIndex}`
            });
            
            datasetIndex++;
        });
    });
    
    return datasets;
}

function generateMonthlyComparisonChart() {
    const ctx = document.getElementById('monthlyComparisonChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    // Calculate monthly totals
    const monthlyTotals = currentFilters.months.map(monthIndex => {
        let monthTotal = 0;
        currentFilters.agents.forEach(agentIndex => {
            const monthData = salesData[agentIndex][monthIndex];
            if (currentFilters.salesTypes.includes('retail')) monthTotal += monthData[0];
            if (currentFilters.salesTypes.includes('wholesale')) monthTotal += monthData[1];
        });
        return monthTotal;
    });
    
    // Find best month
    const maxValue = Math.max(...monthlyTotals);
    const maxIndex = monthlyTotals.indexOf(maxValue);
    
    const backgroundColors = monthlyTotals.map((total, idx) => {
        return idx === maxIndex ? '#2ecc71' : '#3498db';
    });
    
    monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: currentFilters.months.map(m => months[m]),
            datasets: [{
                label: i18n.monthlySales,
                data: monthlyTotals,
                backgroundColor: backgroundColors,
                borderColor: backgroundColors.map(c => c.replace('0.8', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${i18n.sales}: ${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function generateAgentPerformanceChart() {
    const ctx = document.getElementById('agentPerformanceChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (agentChart) {
        agentChart.destroy();
    }
    
    // Calculate agent totals
    const agentTotals = currentFilters.agents.map(agentIndex => {
        let agentTotal = 0;
        currentFilters.months.forEach(monthIndex => {
            const monthData = salesData[agentIndex][monthIndex];
            if (currentFilters.salesTypes.includes('retail')) agentTotal += monthData[0];
            if (currentFilters.salesTypes.includes('wholesale')) agentTotal += monthData[1];
        });
        return agentTotal;
    });
    
    // Find top agent
    const maxValue = Math.max(...agentTotals);
    const maxIndex = agentTotals.indexOf(maxValue);
    
    const backgroundColors = agentTotals.map((total, idx) => {
        return idx === maxIndex ? '#f39c12' : colorPalette.agents[idx % colorPalette.agents.length];
    });
    
    agentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: currentFilters.agents.map(a => agents[a]),
            datasets: [{
                data: agentTotals,
                backgroundColor: backgroundColors,
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: isRtl ? 'left' : 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = agentTotals.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

function updateSummaryCards() {
    // Calculate totals
    let totalSales = 0;
    let agentSales = new Array(5).fill(0);
    let monthlySales = new Array(12).fill(0);
    let topAgentIndex = 0;
    let bestMonthIndex = 0;
    let maxAgentSales = 0;
    let maxMonthSales = 0;
    
    // Calculate sales totals
    currentFilters.agents.forEach(agentIndex => {
        currentFilters.months.forEach(monthIndex => {
            const agentData = salesData[agentIndex][monthIndex];
            let monthAgentTotal = 0;
            if (currentFilters.salesTypes.includes('retail')) monthAgentTotal += agentData[0];
            if (currentFilters.salesTypes.includes('wholesale')) monthAgentTotal += agentData[1];
            
            agentSales[agentIndex] += monthAgentTotal;
            monthlySales[monthIndex] += monthAgentTotal;
            totalSales += monthAgentTotal;
        });
        
        if (agentSales[agentIndex] > maxAgentSales) {
            maxAgentSales = agentSales[agentIndex];
            topAgentIndex = agentIndex;
        }
    });
    
    // Find best month
    currentFilters.months.forEach(monthIndex => {
        if (monthlySales[monthIndex] > maxMonthSales) {
            maxMonthSales = monthlySales[monthIndex];
            bestMonthIndex = monthIndex;
        }
    });
    
    // Update summary cards
    document.getElementById('totalSales').textContent = totalSales.toLocaleString();
    document.getElementById('avgPerAgent').textContent = currentFilters.agents.length > 0 
        ? Math.round(totalSales / currentFilters.agents.length).toLocaleString()
        : '0';
    document.getElementById('topAgent').textContent = agents[topAgentIndex];
    document.getElementById('bestMonth').textContent = months[bestMonthIndex];
}

function updateChartLegend() {
    const legendContainer = document.getElementById('chartLegend');
    let legendHTML = '';
    
    if (document.getElementById('chartType').value === 'stacked') {
        // Stacked chart legend
        currentFilters.agents.forEach((agentIndex, idx) => {
            legendHTML += `
                <div class="d-flex align-items-center gap-2">
                    <div class="legend-color" style="width: 20px; height: 20px; background-color: ${colorPalette.retail[idx % colorPalette.retail.length]};"></div>
                    <span>${agents[agentIndex]} ${i18n.retail}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="legend-color" style="width: 20px; height: 20px; background-color: ${colorPalette.wholesale[idx % colorPalette.wholesale.length]};"></div>
                    <span>${agents[agentIndex]} ${i18n.wholesale}</span>
                </div>
            `;
        });
    } else {
        // Regular chart legend
        currentFilters.agents.forEach((agentIndex, idx) => {
            legendHTML += `
                <div class="d-flex align-items-center gap-2">
                    <div class="legend-color" style="width: 20px; height: 20px; background-color: ${colorPalette.agents[idx % colorPalette.agents.length]};"></div>
                    <span>${agents[agentIndex]}</span>
                </div>
            `;
        });
    }
    
    legendContainer.innerHTML = legendHTML;
}

function downloadChartImage() {
    const link = document.createElement('a');
    link.download = `agent-sales-chart-${new Date().toISOString().split('T')[0]}.png`;
    link.href = document.getElementById('crmChart').toDataURL('image/png');
    link.click();
    
    showNotification(i18n.chartDownloadedSuccessfully, 'success');
}

/* ---------- NOTIFICATION FUNCTION ---------- */
function showNotification(message, type = 'success') {
    // Use your system's notification method here
    alert(message); // Simple alert for now
}
</script>

<style>
.chart-container {
    position: relative;
    height: 500px;
    width: 100%;
}

#monthlyComparisonChart, #agentPerformanceChart {
    height: 250px !important;
}

.legend-color {
    border-radius: 4px;
    border: 1px solid #ddd;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.85em;
    padding: 0.5em 1em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Custom Dropdown Styles */
.custom-multiselect {
    position: relative;
    width: 100%;
}

.dropdown-checkboxes {
    position: absolute;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 6px 12px rgba(0,0,0,0.175);
}

.dropdown-content {
    max-height: 200px;
    overflow-y: auto;
}

.dropdown-content::-webkit-scrollbar {
    width: 6px;
}

.dropdown-content::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.dropdown-content::-webkit-scrollbar-thumb {
    background-color: #dee2e6;
    border-radius: 4px;
}

.form-check {
    padding-inline-start: 0;
    margin-bottom: 0.25rem;
}

.form-check-input {
    margin-inline-end: 0.5rem;
}

.form-check-label {
    cursor: pointer;
}

.select-box {
    cursor: pointer;
}

.select-box .form-control {
    pointer-events: none;
    background-color: #fff;
}

/* Animation for charts */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

canvas {
    animation: fadeIn 0.5s ease-in;
}
</style>
@endpush
