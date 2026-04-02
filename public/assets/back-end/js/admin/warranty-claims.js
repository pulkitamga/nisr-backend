(function () {
    'use strict';

    function getConfig() {
        const configNode = document.getElementById('warranty-claim-js-config');

        return {
            processing: configNode?.dataset.processing || 'Processing...',
            success: configNode?.dataset.success || 'Success!',
            error: configNode?.dataset.error || 'Something went wrong.',
        };
    }

    function attachModalAction(button) {
        const url = button.getAttribute('data-url');
        const modalId = button.getAttribute('data-target');

        if (!url || !modalId) {
            return;
        }

        const modal = document.querySelector(modalId);
        const form = modal?.querySelector('form');

        if (form) {
            form.setAttribute('action', url);
        }
    }

    function bindClaimActionButtons() {
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-toggle="modal"][data-url][data-target]');

            if (!button) {
                return;
            }

            attachModalAction(button);
        });
    }

    function bindClaimModalSubmissions() {
        const i18n = getConfig();

        $(document).on('submit', '.claim-modal-form', function (event) {
            event.preventDefault();

            const form = $(this);
            const button = form.find('button[type=submit]');
            const originalLabel = button.html();

            button.prop('disabled', true).html('<i class="tio-loading"></i> ' + i18n.processing);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (response) {
                    const successMessage = response?.payment_link
                        ? (response.message || i18n.success) + ' ' + response.payment_link
                        : (response?.message || i18n.success);

                    toastr.success(successMessage);
                    location.reload();
                },
                error: function (xhr) {
                    const validationErrors = xhr.responseJSON?.errors || {};
                    const firstValidationError = Object.values(validationErrors)[0]?.[0];

                    toastr.error(xhr.responseJSON?.message || firstValidationError || i18n.error);
                    button.prop('disabled', false).html(originalLabel);
                }
            });
        });
    }

    function bindDecideModalReset() {
        const modal = document.getElementById('decideModal');

        if (!modal) {
            return;
        }

        modal.addEventListener('hidden.bs.modal', function () {
            modal.querySelector('form')?.reset();
        });
    }

    function bindDiagnoseModal() {
        const modal = document.getElementById('diagnoseModal');

        if (!modal) {
            return;
        }

        const actionSelect = modal.querySelector('#actionSelect');
        const tamperCheckbox = modal.querySelector('#tamperCheckbox');
        const inspectionGroup = modal.querySelector('#inspectionFeeGroup');
        const repairFeeGroup = modal.querySelector('#repairFeeGroup');
        const replaceOptions = modal.querySelector('#replaceOptions');
        const feeOptionSelect = modal.querySelector('#replacementFeeOption');
        const replacementFeeGroup = modal.querySelector('#replacementFeeGroup');
        const inspectionFeeInput = modal.querySelector('#inspectionFeeInput');
        const repairFeeInput = modal.querySelector('#repairFeeInput');
        const replacementFeeInput = modal.querySelector('#replacementFeeInput');
        const replacementModeSelect = modal.querySelector('#replacementModeSelect');

        if (!actionSelect || !tamperCheckbox || !feeOptionSelect || !replacementModeSelect) {
            return;
        }

        function toggleFields() {
            const action = actionSelect.value;
            const isTamper = tamperCheckbox.checked;
            const isRepair = action === 'repair';
            const isReplace = action === 'replace';
            const isReject = action === 'reject';
            const feeOption = feeOptionSelect.value;
            const isReplacementFeeRequired = isReplace && feeOption === 'fee_required';
            const showInspectionFee = isTamper && !isReject;

            if (inspectionGroup && inspectionFeeInput) {
                inspectionGroup.style.display = showInspectionFee ? 'block' : 'none';
                inspectionFeeInput.disabled = !showInspectionFee;
                if (!showInspectionFee) {
                    inspectionFeeInput.value = '';
                }
            }

            if (repairFeeGroup && repairFeeInput) {
                repairFeeGroup.style.display = isRepair ? 'block' : 'none';
                repairFeeInput.disabled = !isRepair;
                if (!isRepair) {
                    repairFeeInput.value = '';
                }
            }

            if (replaceOptions) {
                replaceOptions.style.display = isReplace ? 'block' : 'none';
            }

            feeOptionSelect.disabled = !isReplace;
            feeOptionSelect.required = isReplace;
            replacementModeSelect.disabled = !isReplace;
            replacementModeSelect.required = isReplace;

            if (replacementFeeGroup && replacementFeeInput) {
                replacementFeeGroup.style.display = isReplacementFeeRequired ? 'block' : 'none';
                replacementFeeInput.disabled = !isReplacementFeeRequired;
                if (!isReplacementFeeRequired) {
                    replacementFeeInput.value = '0';
                }
            }
        }

        actionSelect.addEventListener('change', toggleFields);
        tamperCheckbox.addEventListener('change', toggleFields);
        feeOptionSelect.addEventListener('change', toggleFields);

        modal.addEventListener('shown.bs.modal', toggleFields);
        modal.addEventListener('hidden.bs.modal', function () {
            modal.querySelector('form')?.reset();
            toggleFields();
        });
    }

    function bindPaymentHandlingModal() {
        const modal = document.getElementById('paymentHandlingModal');

        if (!modal) {
            return;
        }

        const actionSelect = modal.querySelector('#paymentAction');
        const chargeCheckboxes = modal.querySelectorAll('input[name="charge_ids[]"]');
        const pendingWrapper = modal.querySelector('#pendingChargesWrapper');
        const paymentReferenceWrapper = modal.querySelector('#paymentReferenceWrapper');
        const paymentReferenceInput = modal.querySelector('#paymentReference');
        const linkExpiryWrapper = modal.querySelector('#linkExpiryWrapper');
        const linkExpireHoursInput = modal.querySelector('#linkExpireHours');

        if (!actionSelect) {
            return;
        }

        function toggleChargeView() {
            const action = actionSelect.value;
            const chargeActions = ['pos', 'cod', 'cod_collect', 'online_link'];
            const referenceActions = ['pos', 'cod_collect'];
            const linkActions = ['online_link'];

            const chargeRequired = chargeActions.includes(action);
            const referenceRequired = referenceActions.includes(action);
            const linkRequired = linkActions.includes(action);

            if (pendingWrapper) {
                pendingWrapper.style.display = chargeRequired ? 'block' : 'none';
            }

            chargeCheckboxes.forEach(function (checkbox) {
                if (!chargeRequired) {
                    checkbox.checked = false;
                }
            });

            if (paymentReferenceWrapper && paymentReferenceInput) {
                paymentReferenceWrapper.style.display = referenceRequired ? 'block' : 'none';
                paymentReferenceInput.required = referenceRequired;
                if (!referenceRequired) {
                    paymentReferenceInput.value = '';
                }
            }

            if (linkExpiryWrapper && linkExpireHoursInput) {
                linkExpiryWrapper.style.display = linkRequired ? 'block' : 'none';
                linkExpireHoursInput.required = linkRequired;
                if (!linkRequired) {
                    linkExpireHoursInput.value = '24';
                }
            }
        }

        actionSelect.addEventListener('change', toggleChargeView);
        modal.addEventListener('shown.bs.modal', toggleChargeView);
    }

    function bindDispatchModal() {
        const modal = document.getElementById('dispatchModal');

        if (!modal) {
            return;
        }

        const dispatchSelect = modal.querySelector('select[name="dispatch_mode"]');
        const trackingInput = modal.querySelector('input[name="tracking_number"]');

        if (!dispatchSelect || !trackingInput) {
            return;
        }

        function toggleTracking() {
            const formGroup = trackingInput.closest('.form-group');
            const showTracking = dispatchSelect.value === 'ship';

            trackingInput.required = showTracking;
            if (!showTracking) {
                trackingInput.value = '';
            }

            if (formGroup) {
                formGroup.style.display = showTracking ? 'block' : 'none';
            }
        }

        dispatchSelect.addEventListener('change', toggleTracking);
        modal.addEventListener('shown.bs.modal', toggleTracking);
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindClaimActionButtons();
        bindClaimModalSubmissions();
        bindDecideModalReset();
        bindDiagnoseModal();
        bindPaymentHandlingModal();
        bindDispatchModal();
    });
})();
