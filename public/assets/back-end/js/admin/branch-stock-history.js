(function () {
    'use strict';

    const configNode = document.getElementById('branch-stock-history-config');
    if (!configNode) {
        return;
    }

    const modalElement = document.getElementById('stockHistoryModal');
    const historyTableBody = document.getElementById('historyTableBody');
    const historyTableContainer = document.getElementById('historyTableContainer');
    const noHistoryMessage = document.getElementById('noHistoryMessage');
    const exportButton = document.getElementById('exportStockHistory');

    if (!modalElement || !historyTableBody || !historyTableContainer || !noHistoryMessage || !exportButton) {
        return;
    }

    const getModalInstance = () => {
        if (window.bootstrap && window.bootstrap.Modal) {
            return window.bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        if (window.jQuery) {
            return {
                show() {
                    window.jQuery(modalElement).modal('show');
                },
                hide() {
                    window.jQuery(modalElement).modal('hide');
                },
            };
        }

        return null;
    };

    const setModalText = (id, value) => {
        const node = document.getElementById(id);
        if (node) {
            node.textContent = value;
        }
    };

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    };

    const renderRows = (rows) => {
        if (!rows.length) {
            historyTableBody.innerHTML = '';
            historyTableContainer.classList.add('d-none');
            noHistoryMessage.classList.remove('d-none');
            return;
        }

        historyTableBody.innerHTML = rows.map((row) => `
            <tr>
                <td>${escapeHtml(row.date)}</td>
                <td><span class="${escapeHtml(row.type_class)} font-weight-bold">${escapeHtml(row.type_label)}</span></td>
                <td class="${escapeHtml(row.type_class)} font-weight-bold">${escapeHtml(row.quantity_label)}</td>
                <td>
                    <strong>${escapeHtml(row.reference)}</strong><br>
                    <small class="text-muted">${escapeHtml(row.description)}</small>
                </td>
                <td><span class="${escapeHtml(row.status_class)}">${escapeHtml(row.status_label)}</span></td>
            </tr>
        `).join('');

        historyTableContainer.classList.remove('d-none');
        noHistoryMessage.classList.add('d-none');
    };

    const openHistoryModal = async (button) => {
        const params = new URLSearchParams({
            branch_id: button.dataset.branchId || '',
            product_id: button.dataset.productId || '',
            variation_type: button.dataset.variationType || '',
            variation_key: button.dataset.variationKey || '',
        });

        historyTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4">${escapeHtml(configNode.dataset.loading || 'Loading...')}</td></tr>`;
        historyTableContainer.classList.remove('d-none');
        noHistoryMessage.classList.add('d-none');

        const modal = getModalInstance();
        if (modal) {
            modal.show();
        }

        try {
            const response = await fetch(`${configNode.dataset.historyUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || configNode.dataset.failed || 'Unable to load stock history');
            }

            setModalText('modalBranchName', data.branch_name || '-');
            setModalText('modalProductName', data.product_name || '-');
            setModalText('modalVariation', data.variation_label || configNode.dataset.defaultVariation || '-');
            setModalText('modalCurrentStock', data.current_stock ?? '-');
            exportButton.href = data.export_url || '#';

            renderRows(Array.isArray(data.history) ? data.history : []);
        } catch (error) {
            historyTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">${escapeHtml(error.message || configNode.dataset.failed || 'Unable to load stock history')}</td></tr>`;
            historyTableContainer.classList.remove('d-none');
            noHistoryMessage.classList.add('d-none');
        }
    };

    document.addEventListener('click', function (event) {
        const historyButton = event.target.closest('.view-history-btn');
        if (historyButton) {
            event.preventDefault();
            openHistoryModal(historyButton);
            return;
        }

        const closeButton = event.target.closest('.branch-history-close');
        if (!closeButton) {
            return;
        }

        const modal = getModalInstance();
        if (modal) {
            modal.hide();
        }
    });
})();
