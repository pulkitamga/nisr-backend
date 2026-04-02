'use strict';

function getServiceTicketText(id, fallback = '') {
    const node = document.getElementById(id);
    return node?.dataset?.text?.trim() || fallback;
}

const serviceTicketUiText = {
    areYouSure: getServiceTicketText('service-ticket-are-you-sure', 'Are you sure?'),
    actionCannotBeUndone: getServiceTicketText('service-ticket-action-cannot-be-undone', 'This action cannot be undone.'),
    yes: getServiceTicketText('service-ticket-yes', 'Yes'),
    no: getServiceTicketText('service-ticket-no', 'No'),
    invalidAction: getServiceTicketText('service-ticket-invalid-action', 'Invalid action'),
    noJobAssociated: getServiceTicketText('service-ticket-no-job-associated', 'No job associated'),
    partLabel: getServiceTicketText('service-ticket-part-label', 'Part'),
    laborLabel: getServiceTicketText('service-ticket-labor-label', 'Labor'),
    itemName: getServiceTicketText('service-ticket-item-name', 'Item Name'),
    quantity: getServiceTicketText('service-ticket-quantity', 'Quantity'),
    rate: getServiceTicketText('service-ticket-rate', 'Rate'),
    remove: getServiceTicketText('service-ticket-remove', 'Remove'),
    forceCloseTitle: getServiceTicketText('service-ticket-force-close-title', 'Payment is not paid!'),
    forceCloseText: getServiceTicketText('service-ticket-force-close-text', 'If you agree, you can force close this ticket.'),
    forceCloseConfirm: getServiceTicketText('service-ticket-force-close-confirm', 'Force Close'),
    forceCloseCancel: getServiceTicketText('service-ticket-force-close-cancel', 'Cancel'),
    forceCloseNote: getServiceTicketText('service-ticket-force-close-note', 'Force closed manually without payment'),
};

function applyLockedService(selector, serviceId) {
    if (!serviceId) {
        return;
    }

    const normalizedServiceId = String(serviceId);
    if ($(`${selector} option[value="${normalizedServiceId}"]`).length) {
        $(selector).val(normalizedServiceId);
    }
}

function handleServiceTicketAction(action, route, ticketId, jobId, serviceId) {
    switch (action) {
        case 'assign':
            $('#assignTicketId').val(ticketId);
            $('#assignTicketForm').attr('action', route);
            applyLockedService('#service_id', serviceId);
            $('#assignTicketModal').modal('show');
            break;
        case 'estimate':
            $('#estimateTicketId').val(ticketId);
            $('#estimateTicketForm').attr('action', route);
            applyLockedService('#estimate_service_id', serviceId);
            applyEstimateDefaults();
            recalculateEstimateTotals();
            $('#estimateTicketModal').modal('show');
            break;
        case 'schedule':
            if (!jobId) {
                toastr.error(serviceTicketUiText.noJobAssociated);
                return;
            }
            $('#scheduleTicketId').val(ticketId);
            $('#scheduleJobId').val(jobId);
            $('#scheduleTicketForm').attr('action', route);
            $('#scheduleTicketModal').modal('show');
            break;
        case 'start-job':
            if (!jobId) {
                toastr.error(serviceTicketUiText.noJobAssociated);
                return;
            }
            $('#startTicketId').val(ticketId);
            $('#startJobId').val(jobId);
            $('#startJobForm').attr('action', route);
            $('#startJobModal').modal('show');
            break;
        case 'complete-job':
            if (!jobId) {
                toastr.error(serviceTicketUiText.noJobAssociated);
                return;
            }
            $('#completeTicketId').val(ticketId);
            $('#completeJobId').val(jobId);
            $('#completeJobForm').attr('action', route);
            $('#completeJobModal').modal('show');
            break;
        case 'change-order':
            if (!jobId) {
                toastr.error(serviceTicketUiText.noJobAssociated);
                return;
            }
            $('#changeOrderTicketId').val(ticketId);
            $('#changeOrderJobId').val(jobId);
            $('#changeOrderForm').attr('action', route);
            $('#changeOrderModal').modal('show');
            break;
        case 'qa':
            if (!jobId) {
                toastr.error(serviceTicketUiText.noJobAssociated);
                return;
            }
            $('#qaTicketId').val(ticketId);
            $('#qaJobId').val(jobId);
            $('#qaTicketForm').attr('action', route);
            $('#qaTicketModal').modal('show');
            break;
        case 'close-ticket':
            $('#closeTicketId').val(ticketId);
            $('#closeTicketForm').attr('action', route);
            $('#closeTicketModal').modal('show');
            break;
        case 'cancel-ticket':
            $('#cancelTicketId').val(ticketId);
            $('#cancelJobId').val(jobId);
            $('#cancelTicketForm').attr('action', route);
            $('#cancelTicketModal').modal('show');
            break;
        default:
            toastr.error(serviceTicketUiText.invalidAction);
            break;
    }
}

function applyEstimateDefaults() {
    const $selectedOption = $('#estimate_service_id option:selected');

    if (!$selectedOption.length) {
        return;
    }

    const baseInshop = parseFloat($selectedOption.data('price-inshop')) || 0;
    const baseMobile = parseFloat($selectedOption.data('price-mobile')) || 0;
    const travelFee = parseFloat($selectedOption.data('travel-fee')) || 0;
    const includedKm = parseFloat($selectedOption.data('included-km')) || 0;
    const laborCharge = parseFloat($selectedOption.data('labour-charge')) || 0;
    const partsCost = parseFloat($selectedOption.data('parts-cost')) || 0;

    $('#base_price_inshop').val(baseInshop.toFixed(2));
    $('#base_price_mobile').val(baseMobile.toFixed(2));
    $('#travel_fee_per_km').val(travelFee.toFixed(2));
    $('#included_km').val(includedKm.toFixed(2));
    $('#labor_charge').val(laborCharge.toFixed(2));
    $('#parts_cost').val(partsCost.toFixed(2));
    $('#labor_charge_mobile').val(laborCharge.toFixed(2));
    $('#parts_cost_mobile').val(partsCost.toFixed(2));

    if ($('#estimate_is_mobile').val() === '1') {
        $('.mobile-fields').show();
        $('.inshop-fields').hide();
        return;
    }

    $('.inshop-fields').show();
    $('.mobile-fields').hide();
}

function recalculateEstimateTotals() {
    const mode = $('#estimate_is_mobile').val();
    const baseInshop = parseFloat($('#base_price_inshop').val()) || 0;
    const baseMobile = parseFloat($('#base_price_mobile').val()) || 0;
    const travelFee = parseFloat($('#travel_fee_per_km').val()) || 0;
    const includedKm = parseFloat($('#included_km').val()) || 0;
    const extraCharge = parseFloat($('#extra_charge').val()) || 0;
    let subtotal = 0;

    if (mode === '1') {
        const enteredKm = parseFloat($('#entered_km').val()) || 0;
        const laborMobile = parseFloat($('#labor_charge_mobile').val()) || 0;
        const partsMobile = parseFloat($('#parts_cost_mobile').val()) || 0;
        const extraKm = Math.max(0, enteredKm - includedKm);
        const travelCharge = extraKm * travelFee;

        subtotal = baseMobile + laborMobile + partsMobile + travelCharge;
        $('#subtotal_mobile').val(subtotal.toFixed(2));
        $('#labor_charge').val(laborMobile.toFixed(2));
        $('#parts_cost').val(partsMobile.toFixed(2));
    } else {
        const laborInshop = parseFloat($('#labor_charge').val()) || 0;
        const partsInshop = parseFloat($('#parts_cost').val()) || 0;
        subtotal = baseInshop + laborInshop + partsInshop;
        $('#subtotal_inshop').val(subtotal.toFixed(2));
    }

    const tax = 0;
    const total = subtotal + extraCharge + tax;
    $('#subtotal').val(subtotal.toFixed(2));
    $('#tax').val(tax.toFixed(2));
    $('#total').val(total.toFixed(2));
}

function buildPartLaborRow(index) {
    return `
        <div class="parts-labor-row mt-2">
            <select name="items[${index}][item_type]" class="form-control" required>
                <option value="part">${serviceTicketUiText.partLabel}</option>
                <option value="labor">${serviceTicketUiText.laborLabel}</option>
            </select>
            <input type="text" name="items[${index}][item_name]" class="form-control my-1" placeholder="${serviceTicketUiText.itemName}" required>
            <input type="number" step="0.01" name="items[${index}][quantity]" class="form-control my-1" placeholder="${serviceTicketUiText.quantity}" required>
            <input type="number" step="0.01" name="items[${index}][rate]" class="form-control my-1" placeholder="${serviceTicketUiText.rate}" required>
            <button type="button" class="btn btn-sm btn-danger remove-part-labor my-1">${serviceTicketUiText.remove}</button>
        </div>
    `;
}

function wireLanguageTabs(tabSelector, formSelector, prefix) {
    const tabs = document.querySelectorAll(tabSelector);

    if (!tabs.length) {
        return;
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', function () {
            const lang = this.id.replace(prefix + '-', '').replace('-link', '');

            tabs.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll(formSelector).forEach((form) => form.classList.add('d-none'));

            const selectedForm = document.getElementById(prefix + '-' + lang + '-form');
            if (selectedForm) {
                selectedForm.classList.remove('d-none');
            }
        });
    });
}

function initializeSignaturePad() {
    const canvas = document.getElementById('signatureCanvas');
    const signatureInput = document.getElementById('customer_signature');

    if (!canvas || !signatureInput) {
        return;
    }

    const ctx = canvas.getContext('2d');
    let drawing = false;

    function resizeCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
    }

    function setSignatureValue() {
        signatureInput.value = canvas.toDataURL();
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    canvas.addEventListener('mousedown', (event) => {
        drawing = true;
        ctx.beginPath();
        ctx.moveTo(event.offsetX, event.offsetY);
    });

    canvas.addEventListener('mousemove', (event) => {
        if (!drawing) {
            return;
        }

        ctx.lineTo(event.offsetX, event.offsetY);
        ctx.stroke();
    });

    canvas.addEventListener('mouseup', () => {
        drawing = false;
        setSignatureValue();
    });

    canvas.addEventListener('mouseleave', () => {
        drawing = false;
        setSignatureValue();
    });

    canvas.addEventListener('touchstart', (event) => {
        event.preventDefault();
        drawing = true;
        const rect = canvas.getBoundingClientRect();
        const touch = event.touches[0];
        ctx.beginPath();
        ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    });

    canvas.addEventListener('touchmove', (event) => {
        event.preventDefault();
        if (!drawing) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        const touch = event.touches[0];
        ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
        ctx.stroke();
    });

    canvas.addEventListener('touchend', (event) => {
        event.preventDefault();
        drawing = false;
        setSignatureValue();
    });

    $('#clearSignature').off('click.serviceTicket').on('click.serviceTicket', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        signatureInput.value = '';
    });
}

function initializeForceClosePrompt() {
    const node = document.getElementById('service-ticket-force-close');
    const ticketId = node?.dataset?.ticketId?.trim();

    if (!ticketId) {
        return;
    }

    Swal.fire({
        title: serviceTicketUiText.forceCloseTitle,
        text: serviceTicketUiText.forceCloseText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: serviceTicketUiText.forceCloseConfirm,
        cancelButtonText: serviceTicketUiText.forceCloseCancel,
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = node.dataset.route || '';

        const fields = {
            _token: node.dataset.csrf || '',
            ticket_id: ticketId,
            force_close: '1',
            qa_notes: serviceTicketUiText.forceCloseNote,
        };

        Object.entries(fields).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });
}

function initializeServiceTicketActions() {
    $(document).on('click.serviceTicket', '.action-btn', function (event) {
        event.preventDefault();

        const action = $(this).data('action');
        const route = $(this).data('route');
        const ticketId = $(this).data('ticket-id');
        const jobId = $(this).data('job-id');
        const serviceId = $(this).data('service-id');
        const actionsWithConfirmation = ['start-job', 'complete-job', 'close-ticket', 'cancel-ticket'];

        if (!actionsWithConfirmation.includes(action)) {
            handleServiceTicketAction(action, route, ticketId, jobId, serviceId);
            return;
        }

        Swal.fire({
            title: serviceTicketUiText.areYouSure,
            text: serviceTicketUiText.actionCannotBeUndone,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: serviceTicketUiText.yes,
            cancelButtonText: serviceTicketUiText.no,
        }).then((result) => {
            if (result.isConfirmed) {
                handleServiceTicketAction(action, route, ticketId, jobId, serviceId);
            }
        });
    });
}

function initializeServiceTicketEstimateCalculator() {
    $('#estimate_service_id, #estimate_is_mobile')
        .off('change.serviceTicket')
        .on('change.serviceTicket', function () {
            applyEstimateDefaults();
            recalculateEstimateTotals();
        });

    $('#entered_km, #parts_cost, #labor_charge, #parts_cost_mobile, #labor_charge_mobile, #extra_charge')
        .off('input.serviceTicket')
        .on('input.serviceTicket', function () {
            recalculateEstimateTotals();
        });

    applyEstimateDefaults();
    recalculateEstimateTotals();
}

function initializeServiceTicketItems() {
    let itemIndex = $('#parts-labor-container .parts-labor-row').length;

    $('#add-part-labor').off('click.serviceTicket').on('click.serviceTicket', function () {
        $('#parts-labor-container').append(buildPartLaborRow(itemIndex));
        itemIndex += 1;
    });

    $(document).off('click.serviceTicket', '.remove-part-labor').on('click.serviceTicket', '.remove-part-labor', function () {
        $(this).closest('.parts-labor-row').remove();
    });
}

$(function () {
    initializeServiceTicketActions();
    initializeServiceTicketEstimateCalculator();
    initializeServiceTicketItems();
    initializeSignaturePad();
    wireLanguageTabs('.estimate-language-tab', '.estimate-language-form', 'esti');
    wireLanguageTabs('.job-language-tab', '.job-language-form', 'job');
    wireLanguageTabs('.order-language-tab', '.order-language-form', 'order');
    initializeForceClosePrompt();
});
