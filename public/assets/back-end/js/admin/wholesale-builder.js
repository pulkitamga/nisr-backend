(function (window, document, $) {
    'use strict';

    const config = window.wholesaleBuilderConfig;
    if (!config || !config.formId) {
        return;
    }

    const form = document.getElementById(config.formId);
    if (!form) {
        return;
    }

    const text = Object.assign({
        notAvailable: 'N/A',
        searchProductPlaceholder: 'Search for a product...',
        selectWholesalerPlaceholder: 'Select Wholesaler',
        remove: 'Remove',
        chargeName: 'Charge Name',
        discountName: 'Discount Name',
        value: 'Value',
        chargeValue: 'Charge Value',
        discountValue: 'Discount Value',
        quotationExists: 'Quotation No already exists',
        quotationAvailable: 'Quotation No is available',
        chooseWholesalerFirst: 'Please select a wholesaler first.',
        duplicateVariation: 'This variation is already added.',
        duplicateProductVariation: 'This product variation is already added.',
        emptyState: 'No product selected',
        validationTitle: 'Please fill all required fields',
        field: 'Field',
        required: 'is required.',
        confirmTitle: 'Are you sure?',
        confirmSubmit: 'Do you want to submit?',
        submit: 'Submit',
        cancel: 'Cancel',
        ok: 'OK',
        oops: 'Oops...',
    }, config.texts || {});

    const state = {
        chargeIndex: 0,
        discountIndex: 0,
    };

    const productSelect = document.getElementById('product_select');
    const wholesalerSelect = document.getElementById('wholesaler-select');
    const quotationInput = document.getElementById('quotation_no_input');
    const tableBody = document.getElementById('product_table_body');
    const finalPriceInput = document.getElementById('final_price');
    const wholesalerDiscountInput = document.getElementById('wholesaler_discount');
    const wholesalerDiscountAmountInput = document.getElementById('wholesaler_discount_amount');
    const submitButton = document.getElementById('submit_btn');
    const productDropdownWrapper = document.getElementById('product_dropdown_wrapper');
    const productToggle = document.getElementById('toggle_product_dropdown');
    const orderStatus = document.getElementById('order_no_status');
    const productSearch = document.getElementById('datatableSearch_');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showAlert(options) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire(options);
        }

        if (options.icon === 'question') {
            return Promise.resolve({
                isConfirmed: window.confirm([options.title, options.text].filter(Boolean).join('\n')),
            });
        }

        window.alert(options.text || options.title || '');
        return Promise.resolve({ isConfirmed: true });
    }

    function parseNumber(value) {
        const normalized = String(value ?? '').replace('%', '').trim();
        const parsed = parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function parseTaxDetails(input) {
        const raw = String(input?.value ?? '').trim();
        if (raw.endsWith('%')) {
            if (input) {
                input.dataset.taxMode = 'percent';
            }

            return {
                value: parseNumber(raw),
                mode: 'percent',
            };
        }

        const inferredMode = input?.dataset.taxMode === 'amount' ? 'amount' : 'percent';
        return {
            value: parseNumber(raw),
            mode: inferredMode,
        };
    }

    function calculateLineTotals(quantity, unitPrice, taxValue, taxMode) {
        const baseTotal = quantity * unitPrice;
        const taxAmount = taxMode === 'percent'
            ? baseTotal * (taxValue / 100)
            : taxValue;

        return {
            baseTotal,
            taxAmount,
            finalTotal: baseTotal + taxAmount,
        };
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
        }
    }

    function getProductRows() {
        if (!tableBody) {
            return [];
        }

        return Array.from(tableBody.querySelectorAll('tr[data-product-id]'));
    }

    function ensureEditableProductRows(scope) {
        const root = scope || tableBody || form;
        if (!root) {
            return;
        }

        root.querySelectorAll('.admin-qty, .admin-price, .admin-tax, .admin-final').forEach((input) => {
            input.disabled = false;
            input.readOnly = false;
            input.removeAttribute('disabled');
            input.removeAttribute('readonly');
        });
    }

    function removeEmptyState() {
        const row = document.getElementById('no_product_row');
        if (row) {
            row.remove();
        }
    }

    function ensureEmptyState() {
        if (!tableBody || getProductRows().length > 0 || document.getElementById('no_product_row')) {
            return;
        }

        tableBody.insertAdjacentHTML('beforeend', `
            <tr id="no_product_row">
                <td colspan="6" class="text-center text-muted p-10">${escapeHtml(text.emptyState)}</td>
            </tr>
        `);
    }

    function getSelectedWholesalerTier() {
        const tierInput = document.getElementById('ws-tier');
        return (tierInput?.value || '').trim();
    }

    function getWholesalerName() {
        const input = document.getElementById('ws-name');
        return (input?.value || '').trim() || text.notAvailable;
    }

    function getWholesalerTier() {
        return getSelectedWholesalerTier() || text.notAvailable;
    }

    function getSummaryNumberValue() {
        if (!config.summaryNumberTargetId) {
            return '';
        }

        return (quotationInput?.value || '').trim() || '--';
    }

    function updateBuilderSummary() {
        const productCount = getProductRows().length;
        const finalTotal = parseNumber(finalPriceInput?.value).toFixed(2);
        const discountAmount = parseNumber(wholesalerDiscountAmountInput?.value).toFixed(2);
        const wholesalerName = getWholesalerName();
        const wholesalerTier = getWholesalerTier();

        setText('summary-selected-wholesaler', wholesalerName);
        setText('builder-wholesaler-name', wholesalerName);
        setText('sticky-wholesaler-name', wholesalerName);
        setText('summary-selected-tier', wholesalerTier);
        setText('builder-wholesaler-tier', wholesalerTier);
        setText('summary-product-count', String(productCount));
        setText('builder-product-count', String(productCount));
        setText('sticky-product-count', String(productCount));
        setText('builder-discount-amount', discountAmount);
        setText('builder-final-total', finalTotal);
        setText('sticky-final-total', finalTotal);

        if (config.summaryNumberTargetId) {
            setText(config.summaryNumberTargetId, getSummaryNumberValue());
        }
    }

    function recalculateRow(row) {
        if (!row) {
            return;
        }

        const qty = parseNumber(row.querySelector('.admin-qty')?.value);
        const price = parseNumber(row.querySelector('.admin-price')?.value);
        const taxInput = row.querySelector('.admin-tax');
        const taxDetails = parseTaxDetails(taxInput);
        const finalInput = row.querySelector('.admin-final');

        if (finalInput) {
            const totals = calculateLineTotals(qty, price, taxDetails.value, taxDetails.mode);
            finalInput.value = totals.finalTotal.toFixed(2);
        }

        updateFinalPrice();
    }

    function updateFinalPrice() {
        const productRows = getProductRows();
        let baseTotal = 0;

        productRows.forEach((row) => {
            baseTotal += parseNumber(row.querySelector('.admin-final')?.value);
        });

        const discountAmount = baseTotal * (parseNumber(wholesalerDiscountInput?.value) / 100);
        if (wholesalerDiscountAmountInput) {
            wholesalerDiscountAmountInput.value = discountAmount.toFixed(2);
        }

        let total = baseTotal - discountAmount;

        form.querySelectorAll('[data-charge]').forEach((input) => {
            total += parseNumber(input.value);
        });

        form.querySelectorAll('[data-discount]').forEach((input) => {
            total -= parseNumber(input.value);
        });

        if (finalPriceInput) {
            finalPriceInput.value = total > 0 ? total.toFixed(2) : '0.00';
        }

        if (productRows.length === 0 && finalPriceInput) {
            finalPriceInput.value = '0.00';
        }

        updateBuilderSummary();
    }

    function setQuotationStatus(message, color, disableSubmit) {
        if (orderStatus) {
            orderStatus.textContent = message;
            orderStatus.style.color = color || '';
        }

        if (quotationInput) {
            quotationInput.style.borderColor = color || '';
        }

        if (submitButton) {
            submitButton.disabled = !!disableSubmit;
        }
    }

    function checkQuotationNumber(value) {
        if (!config.checkOrderUrl || !quotationInput) {
            return;
        }

        if (String(value || '').trim() === '') {
            setQuotationStatus('', '', false);
            return;
        }

        fetch(config.checkOrderUrl + '?order_no=' + encodeURIComponent(value))
            .then((response) => response.json())
            .then((data) => {
                if (data.exists) {
                    setQuotationStatus(text.quotationExists, '#dc2626', true);
                } else {
                    setQuotationStatus(text.quotationAvailable, '#16a34a', false);
                }
            })
            .catch(() => {
                setQuotationStatus('', '', false);
            });
    }

    function getSelectedOptionByWholesaleId(wholesaleId) {
        if (!productSelect) {
            return null;
        }

        return productSelect.querySelector(`option[value="${CSS.escape(String(wholesaleId))}"]`);
    }

    function findOptionForProduct(productId, variationType) {
        if (!productSelect) {
            return null;
        }

        const safeVariation = String(variationType || '');
        return Array.from(productSelect.options).find((option) => {
            return String(option.getAttribute('data-product-id') || '') === String(productId)
                && String(option.getAttribute('data-variation-type') || '') === safeVariation;
        }) || null;
    }

    function computeBasePrice(option) {
        if (!option) {
            return 0;
        }

        const prices = option.getAttribute('data-prices');
        if (prices) {
            const tier = getSelectedWholesalerTier().toLowerCase();
            try {
                const parsed = JSON.parse(prices);
                const match = parsed.find((item) => String(item.tier || '').toLowerCase() === tier);
                return parseNumber(match?.price);
            } catch (error) {
                return 0;
            }
        }

        return parseNumber(option.getAttribute('data-price'));
    }

    function updatePricesBasedOnWholesaler() {
        if (!config.requireWholesalerSelection) {
            return;
        }

        getProductRows().forEach((row) => {
            const productId = row.getAttribute('data-product-id');
            const variationType = row.getAttribute('data-variation-type') || '';
            const option = findOptionForProduct(productId, variationType);
            const basePrice = computeBasePrice(option);

            const priceInput = row.querySelector('.admin-price');
            const finalInput = row.querySelector('.admin-final');

            if (priceInput) {
                priceInput.value = basePrice.toFixed(2);
            }

            if (finalInput) {
                finalInput.value = basePrice.toFixed(2);
            }
        });

        updateFinalPrice();
    }

    function syncWholesalerSelection() {
        if (!wholesalerSelect) {
            return;
        }

        const selected = wholesalerSelect.options[wholesalerSelect.selectedIndex];
        const companyName = selected?.getAttribute('data-name') || '';
        const tier = selected?.getAttribute('data-tier') || '';
        const wholesalerId = selected?.value || '';
        const discount = selected?.getAttribute('data-wholesalediscount') || '0';

        setValue('ws-name', companyName || text.notAvailable);
        setValue('ws-tier', tier || text.notAvailable);
        setValue('ws-id', wholesalerId);

        if (wholesalerDiscountInput) {
            wholesalerDiscountInput.value = `${parseNumber(discount)}%`;
        }

        updatePricesBasedOnWholesaler();
        updateBuilderSummary();
    }

    function buildCreateRow(productId, variationType, productName, basePrice, tax) {
        return `
            <td class="px-4 py-2">
                ${escapeHtml(productName)}
                <input type="hidden" name="products[${escapeHtml(productId)}][product_id]" value="${escapeHtml(productId)}">
                <input type="hidden" name="products[${escapeHtml(productId)}][variation_type]" value="${escapeHtml(variationType)}">
            </td>
            <td class="px-4 py-2"><input type="number" name="products[${escapeHtml(productId)}][approved_quantity]" value="1" class="admin-qty border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="number" name="products[${escapeHtml(productId)}][price]" value="${basePrice.toFixed(2)}" class="admin-price border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="text" name="products[${escapeHtml(productId)}][tax]" value="${escapeHtml(String(tax))}%" data-tax-mode="percent" class="admin-tax border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="number" step="0.01" name="products[${escapeHtml(productId)}][final_price]" value="${basePrice.toFixed(2)}" class="admin-final border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><button type="button" class="remove-btn js-remove-product text-red-600 hover:underline">${escapeHtml(text.remove)}</button></td>
        `;
    }

    function buildOrderRow(productId, variationType, productName, basePrice, tax) {
        return `
            <td class="px-4 py-2">${escapeHtml(productName)}</td>
            <td class="px-4 py-2"><input type="number" name="products[${escapeHtml(productId)}][${escapeHtml(variationType)}][approved_quantity]" value="1" class="admin-qty border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="number" name="products[${escapeHtml(productId)}][${escapeHtml(variationType)}][price]" value="${basePrice.toFixed(2)}" class="admin-price border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="text" name="products[${escapeHtml(productId)}][${escapeHtml(variationType)}][tax]" value="${escapeHtml(String(tax))}%" data-tax-mode="percent" class="admin-tax border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><input type="number" step="0.01" name="products[${escapeHtml(productId)}][${escapeHtml(variationType)}][final_price]" value="${basePrice.toFixed(2)}" class="admin-final border px-2 py-1 rounded-md w-24"></td>
            <td class="px-4 py-2"><button type="button" class="remove-btn js-remove-product text-red-600 hover:underline">${escapeHtml(text.remove)}</button></td>
        `;
    }

    function addProductRow(option, selectedData) {
        if (!tableBody || !option) {
            return;
        }

        const productId = option.getAttribute('data-product-id') || selectedData.id;
        const variationType = option.getAttribute('data-variation-type') || '';
        const productName = selectedData.text;
        const basePrice = computeBasePrice(option);
        const tax = parseNumber(option.getAttribute('data-tax'));

        const isDuplicate = getProductRows().some((row) => {
            return String(row.getAttribute('data-product-id')) === String(productId)
                && String(row.getAttribute('data-variation-type') || '') === String(variationType);
        });

        if (isDuplicate) {
            showAlert({
                icon: 'warning',
                title: text.oops,
                text: config.mode === 'create' ? text.duplicateVariation : text.duplicateProductVariation,
                confirmButtonText: text.ok,
            });
            return;
        }

        removeEmptyState();

        const row = document.createElement('tr');
        row.setAttribute('data-product-id', productId);
        row.setAttribute('data-variation-type', variationType);
        row.innerHTML = config.mode === 'create'
            ? buildCreateRow(productId, variationType, productName, basePrice, tax)
            : buildOrderRow(productId, variationType, productName, basePrice, tax);

        tableBody.appendChild(row);
        ensureEditableProductRows(row);
        recalculateRow(row);

        if ($ && $.fn.select2 && $(productSelect).data('select2')) {
            $(productSelect).val(null).trigger('change');
        }
    }

    function createMetaField(type) {
        const isCharge = type === 'charge';
        const index = isCharge ? state.chargeIndex++ : state.discountIndex++;
        const container = document.createElement('div');
        container.className = 'flex gap-2 items-center mt-2';
        container.innerHTML = `
            <input type="text" name="${isCharge ? 'charges' : 'discounts'}[${index}][name]" placeholder="${escapeHtml(isCharge ? text.chargeName : text.discountName)}" class="flex-1 px-3 py-2 border rounded-md" />
            <input type="number" name="${isCharge ? 'charges' : 'discounts'}[${index}][value]" placeholder="${escapeHtml(text.value)}" class="px-3 py-2 border rounded-md" ${isCharge ? 'data-charge' : 'data-discount'} data-fieldname="${escapeHtml(isCharge ? text.chargeValue : text.discountValue)}" />
            <button type="button" class="btn btn-danger btn-sm square-btn js-remove-builder-field"><i class="tio-delete"></i></button>
        `;

        const target = document.getElementById(isCharge ? 'charges' : 'discounts');
        if (target) {
            target.appendChild(container);
        }
    }

    function resolveFieldLabel(field) {
        return field.dataset.fieldname
            || field.getAttribute('aria-label')
            || field.previousElementSibling?.innerText
            || field.name
            || text.field;
    }

    function validateRequiredFields() {
        const requiredFields = Array.from(form.querySelectorAll('[required]'));
        return requiredFields.find((field) => String(field.value || '').trim() === '') || null;
    }

    function initProductSearch() {
        if (!productSearch) {
            return;
        }

        productSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            tableBody?.querySelectorAll('tr').forEach((row) => {
                if (row.id === 'no_product_row') {
                    row.style.display = query ? 'none' : '';
                    return;
                }

                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }

    function initSelect2() {
        if (!$ || !$.fn.select2) {
            return;
        }

        if (productSelect) {
            $(productSelect).select2({
                placeholder: text.searchProductPlaceholder,
                matcher(params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    return data.text.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                },
            });

            $(productSelect).on('select2:select', function (event) {
                const option = this.querySelector(`option[value="${CSS.escape(String(event.params.data.id))}"]`);
                addProductRow(option, event.params.data);
            });
        }

        if (wholesalerSelect) {
            $(wholesalerSelect).select2({
                placeholder: text.selectWholesalerPlaceholder,
                allowClear: true,
                width: 'resolve',
            });
        }
    }

    function initSummernote() {
        if (!$ || !$.fn.summernote) {
            return;
        }

        $('.summernote').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
            ],
        });
    }

    if (productToggle) {
        productToggle.addEventListener('click', function () {
            if (config.requireWholesalerSelection && wholesalerSelect && !wholesalerSelect.value) {
                showAlert({
                    icon: 'warning',
                    title: text.oops,
                    text: text.chooseWholesalerFirst,
                    confirmButtonText: text.ok,
                });
                return;
            }

            if (productDropdownWrapper) {
                productDropdownWrapper.classList.toggle('hidden');
            }
        });
    }

    if (wholesalerSelect) {
        wholesalerSelect.addEventListener('change', syncWholesalerSelection);
    }

    if (quotationInput) {
        quotationInput.addEventListener('input', function () {
            updateBuilderSummary();
            checkQuotationNumber(this.value);
        });
    }

    form.addEventListener('input', function (event) {
        const target = event.target;

        if (target.matches('.admin-qty, .admin-price, .admin-tax')) {
            recalculateRow(target.closest('tr'));
            return;
        }

        if (target.matches('[data-charge], [data-discount], #wholesaler_discount')) {
            updateFinalPrice();
        }
    });

    form.addEventListener('click', function (event) {
        const removeProductButton = event.target.closest('.js-remove-product, .remove-btn');
        if (removeProductButton) {
            event.preventDefault();
            const row = removeProductButton.closest('tr');
            if (row) {
                row.remove();
                ensureEmptyState();
                updateFinalPrice();
            }
            return;
        }

        if (event.target.closest('.js-remove-builder-field')) {
            event.preventDefault();
            event.target.closest('.flex')?.remove();
            updateFinalPrice();
            return;
        }

        if (event.target.closest('.js-add-charge')) {
            event.preventDefault();
            createMetaField('charge');
            return;
        }

        if (event.target.closest('.js-add-discount')) {
            event.preventDefault();
            createMetaField('discount');
        }
    });

    if (submitButton) {
        submitButton.addEventListener('click', function (event) {
            event.preventDefault();

            const invalidField = validateRequiredFields();
            if (invalidField) {
                showAlert({
                    icon: 'warning',
                    title: text.validationTitle,
                    text: `${text.field} "${resolveFieldLabel(invalidField)}" ${text.required}`,
                }).then(() => invalidField.focus());
                return;
            }

            showAlert({
                title: text.confirmTitle,
                text: text.confirmSubmit,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: text.submit,
                cancelButtonText: text.cancel,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }

    if (config.loadDealIdFromQuery) {
        const dealIdField = document.getElementById('deal_id_hidden');
        if (dealIdField) {
            const query = new URLSearchParams(window.location.search);
            const dealId = query.get('deal_id');
            if (dealId) {
                dealIdField.value = dealId;
            }
        }
    }

    initSelect2();
    initSummernote();
    initProductSearch();
    syncWholesalerSelection();
    ensureEmptyState();
    ensureEditableProductRows();
    updateFinalPrice();
    updateBuilderSummary();
})(window, document, window.jQuery);
