'use strict';

(function () {
    const $ = window.jQuery;
    if (!$) {
        return;
    }

    const config = window.wholesaleListConfig || {};
    const routes = config.routes || {};
    const uiText = config.text || {};

    const getText = (key, fallback = '') => uiText[key] || fallback;

    const showError = (message) => {
        if (window.toastr) {
            window.toastr.error(message);
            return;
        }

        window.alert(message);
    };

    const showSuccess = (message) => {
        if (window.toastr) {
            window.toastr.success(message);
            return;
        }

        window.alert(message);
    };

    const showConfirm = (options, onConfirm) => {
        if (window.Swal) {
            window.Swal.fire({
                title: options.title,
                text: options.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: options.confirmButtonText,
                cancelButtonText: options.cancelButtonText,
            }).then((result) => {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
            return;
        }

        if (window.confirm(options.text || options.title)) {
            onConfirm();
        }
    };

    const submitPostForm = (action) => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = config.csrfToken || '';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    };

    const openModal = (selector) => {
        const modalElement = document.querySelector(selector);
        if (!modalElement) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        $(modalElement).modal('show');
    };

    const setAvailability = ($message, $submitButton, exists, existsText, availableText) => {
        $message
            .text(exists ? existsText : availableText)
            .toggleClass('text-danger', exists)
            .toggleClass('text-success', !exists);

        $submitButton.prop('disabled', exists);
    };

    const bindAvailabilityCheck = (inputSelector, routeType, messageSelector, submitSelector, existsTextKey, availableTextKey) => {
        $(document).on('keyup change', inputSelector, function () {
            const value = $(this).val();
            const $message = $(messageSelector);
            const $submitButton = $(submitSelector);

            if (!value || !routes.confirmInvoiceCheck) {
                $message.text('').removeClass('text-danger text-success');
                $submitButton.prop('disabled', true);
                return;
            }

            $.get(routes.confirmInvoiceCheck, {
                type: routeType,
                number: value,
            }).done((response) => {
                setAvailability(
                    $message,
                    $submitButton,
                    !!response.exists,
                    getText(existsTextKey),
                    getText(availableTextKey)
                );
            }).fail(() => {
                $message.text('').removeClass('text-danger text-success');
                $submitButton.prop('disabled', true);
                showError(getText('somethingWentWrong', 'Something went wrong!'));
            });
        });
    };

    $(document).on('change', '.moq-toggle', function () {
        const wholesalerId = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: routes.toggleMoq,
            type: 'POST',
            data: {
                _token: config.csrfToken,
                id: wholesalerId,
                status: status,
            },
        }).done((response) => {
            showSuccess(response.message || '');
        }).fail(() => {
            showError(getText('somethingWentWrong', 'Something went wrong!'));
        });
    });

    $(document).on('change', '.auto-submit-toggle', function () {
        const $form = $(this).closest('form');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
        }).done((response) => {
            showSuccess(response.message || '');
        }).fail(() => {
            showError(getText('somethingWentWrong', 'Something went wrong!'));
        });
    });

    $(document).on('click', '.wholesale-delete-action', function (event) {
        event.preventDefault();
        const deleteUrl = $(this).data('deleteUrl');

        if (!deleteUrl) {
            return;
        }

        showConfirm({
            title: getText('confirmDeletionTitle', 'Confirm Deletion'),
            text: getText('confirmDeletionText', 'Are you sure you want to delete this order?'),
            confirmButtonText: getText('yesDeleteIt', 'Yes, delete it!'),
            cancelButtonText: getText('cancel', 'Cancel'),
        }, () => submitPostForm(deleteUrl));
    });

    $(document).on('click', '.wholesale-order-status-history', function () {
        if (!routes.historyTemplate) {
            return;
        }

        let url = routes.historyTemplate;
        const id = $(this).data('id');
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            method: 'GET',
        }).done((data) => {
            $('.load-with-ajax').empty().append(data);
        });
    });

    $(document).on('click', '.wholesale-open-po-modal', function () {
        $('#modal_order_id').val($(this).data('orderId'));
        $('#purchase_order_no').val('');
        $('#availabilityMessage').text('').removeClass('text-danger text-success');
        $('#submitOrderNo').prop('disabled', true);
        openModal('#purchaseOrderModal');
    });

    $(document).on('keyup change', '#purchase_order_no', function () {
        const orderNumber = $(this).val();
        const $message = $('#availabilityMessage');
        const $submitButton = $('#submitOrderNo');

        if (!orderNumber || !routes.orderNumberCheck) {
            $message.text('').removeClass('text-danger text-success');
            $submitButton.prop('disabled', true);
            return;
        }

        $.get(routes.orderNumberCheck, {
            number: orderNumber,
        }).done((response) => {
            setAvailability(
                $message,
                $submitButton,
                !!response.exists,
                getText('orderNumberExists'),
                getText('orderNumberAvailable')
            );
        }).fail(() => {
            $message.text('').removeClass('text-danger text-success');
            $submitButton.prop('disabled', true);
            showError(getText('somethingWentWrong', 'Something went wrong!'));
        });
    });

    $(document).on('click', '.wholesale-open-invoice-modal', function () {
        $('#invoice_order_id').val($(this).data('orderId'));
        $('#invoice_no').val('');
        $('#invoiceAvailability').text('').removeClass('text-danger text-success');
        $('#submitInvoice').prop('disabled', true);
        openModal('#invoiceModal');
    });

    $(document).on('click', '.wholesale-open-confirm-order-modal', function () {
        $('#confirm_order_id').val($(this).data('orderId'));
        $('#confirm_order_no').val('');
        $('#confirmOrderAvailability').text('').removeClass('text-danger text-success');
        $('#submitConfirmOrder').prop('disabled', true);
        openModal('#confirmOrderModal');
    });

    bindAvailabilityCheck(
        '#invoice_no',
        'invoice_no',
        '#invoiceAvailability',
        '#submitInvoice',
        'invoiceNumberExists',
        'invoiceNumberAvailable'
    );

    bindAvailabilityCheck(
        '#confirm_order_no',
        'confirm_order_no',
        '#confirmOrderAvailability',
        '#submitConfirmOrder',
        'confirmOrderNumberExists',
        'confirmOrderNumberAvailable'
    );

    $(document).on('change', '.product-status-toggle', function () {
        const productId = $(this).data('id');
        const toggleUrl = (routes.productToggleTemplate || '').replace('__id__', productId);

        $.ajax({
            url: toggleUrl,
            type: 'POST',
            data: {
                _token: config.csrfToken,
            },
        }).done((response) => {
            showSuccess(response.message || '');
        }).fail(() => {
            showError(getText('somethingWentWrong', 'Something went wrong!'));
        });
    });

    $(document).on('click', '.confirm-delete-btn', function (event) {
        event.preventDefault();
        const form = $(this).closest('form');

        showConfirm({
            title: getText('confirmDeletionTitle', 'Confirm Deletion'),
            text: getText('confirmDeletionText', 'Are you sure you want to delete this order?'),
            confirmButtonText: getText('yesDeleteIt', 'Yes, delete it!'),
            cancelButtonText: getText('cancel', 'Cancel'),
        }, () => form.trigger('submit'));
    });

    $(function () {
        const reopenModalId = config.reopenApprovalModalId;
        if (!reopenModalId) {
            return;
        }

        openModal(`#approvalReviewModal${reopenModalId}`);
    });
})();
