(function () {
    'use strict';

    const config = window.branchStockTransferReportConfig || {};
    const routes = config.routes || {};
    const text = config.text || {};
    const form = document.getElementById('transfer-report-filter-form');

    if (!form || !routes.data) {
        return;
    }

    const loadBtn = document.getElementById('transfer-load-btn');
    const resetBtn = document.getElementById('transfer-reset-btn');
    const exportExcelBtn = document.getElementById('transfer-export-excel');
    const exportPdfBtn = document.getElementById('transfer-export-pdf');
    const tableBody = document.getElementById('transfer-table-body');
    const chartCanvas = document.getElementById('transfer-report-chart');
    const dateTypeEl = document.getElementById('transfer-date-type');
    const fromEl = document.getElementById('transfer-from');
    const toEl = document.getElementById('transfer-to');

    if (!loadBtn || !resetBtn || !exportExcelBtn || !exportPdfBtn || !tableBody || !chartCanvas || !dateTypeEl || !fromEl || !toEl) {
        return;
    }

    const chartCtx = chartCanvas.getContext('2d');
    let transferChart = null;

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
            from_branch_id: document.getElementById('from-branch-id')?.value || null,
            to_branch_id: document.getElementById('to-branch-id')?.value || null,
            status: document.getElementById('transfer-status')?.value || null,
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

        transferChart = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: (chartData.datasets || []).map((dataset) => ({
                    label: dataset.label,
                    data: dataset.data || [],
                    borderColor: dataset.borderColor || '#2563eb',
                    backgroundColor: dataset.backgroundColor || 'rgba(37, 99, 235, 0.2)',
                    borderWidth: dataset.borderWidth || 2,
                    tension: dataset.tension !== undefined ? dataset.tension : 0.1,
                    fill: false,
                })),
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
            return text[key] || status;
        }))].join(', ');
    };

    const formatDate = (date) => {
        if (!date) {
            return '-';
        }

        return new Date(date).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    };

    const updateReportPeriod = (filters) => {
        if (!filters) {
            return;
        }

        const period = `${formatDate(filters.from)} ${text.to || 'to'} ${formatDate(filters.to)}`;
        document.getElementById('chart-period').textContent = period;
        document.getElementById('transfer-period').textContent = period;
    };

    const renderTable = (transfers) => {
        const rows = transfers || [];
        if (!rows.length) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4">${text.noData || 'No data found'}</td></tr>`;
            return;
        }

        tableBody.innerHTML = rows.map((transfer, index) => {
            const quantity = (transfer.products || []).reduce((sum, product) => sum + toInt(product.quantity), 0);
            const from = (transfer.from_branch && transfer.from_branch.branch_name)
                || (transfer.fromBranch && transfer.fromBranch.branch_name)
                || '-';
            const to = (transfer.to_branch && transfer.to_branch.branch_name)
                || (transfer.toBranch && transfer.toBranch.branch_name)
                || '-';

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(transfer.transfer_date)}</td>
                    <td>${from}</td>
                    <td>${to}</td>
                    <td class="text-end">${quantity.toLocaleString()}</td>
                    <td>${summarizeStatus(transfer.products)}</td>
                </tr>
            `;
        }).join('');
    };

    const loadReport = async () => {
        setLoading(true);
        tableBody.innerHTML = '';

        try {
            const response = await fetch(routes.data, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(buildPayload()),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || text.failedToLoad || 'Failed to load report');
            }

            renderStats(data.statistics || {});
            renderChart(data.chartData || {});
            renderTable(data.transfers || []);
            updateReportPeriod(data.filters || {});
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message || text.failedToLoad || 'Failed to load report'}</td></tr>`;
        } finally {
            setLoading(false);
        }
    };

    const runExport = (type) => {
        const route = type === 'excel' ? routes.excel : routes.pdf;
        if (!route) {
            return;
        }

        window.open(`${route}?${buildQueryString(buildPayload())}`, '_blank');
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
