'use strict';

function getDataText(id, fallback = '') {
    const node = document.getElementById(id);
    return node?.dataset?.text?.trim() || fallback;
}

const uiText = {
    areYouSure: getDataText('message-are-you-sure', 'Are you sure?'),
    yes: getDataText('message-yes-word', 'Yes'),
    no: getDataText('message-no-word', 'No'),
    cancel: getDataText('message-cancel-word', 'Cancel'),
    somethingWentWrong: getDataText('support-ticket-something-went-wrong', 'Something went wrong'),
    escalateWarning: getDataText('support-ticket-escalate-warning', 'This will notify the department and owner.'),
    yesEscalate: getDataText('support-ticket-yes-escalate', 'Yes, Escalate'),
};

function normalizeStatusName(rawStatusName) {
    return String(rawStatusName || '').trim().toLowerCase().replace(/[\s-]+/g, '_');
}

function isInProgressStatusSelected(selectSelector) {
    const $statusField = $(selectSelector);
    if (!$statusField.length) {
        return false;
    }

    const selectedStatusId = Number($statusField.val() || 0);
    const configuredInProgressId = Number($statusField.data('in-progress-id') || 0);
    if (configuredInProgressId > 0 && selectedStatusId === configuredInProgressId) {
        return true;
    }

    const $selectedOption = $statusField.find('option:selected');
    const statusName = normalizeStatusName(
        $selectedOption.data('status-name') || $selectedOption.text(),
    );

    return statusName === 'in_progress' || statusName === 'inprogress';
}

function syncSupportFollowUpDateVisibility() {
    const shouldShowDate = isInProgressStatusSelected('#support-follow-up-status');
    const $dateRow = $('#support-ticket-next-follow-up-date-row');

    if (!$dateRow.length) {
        return shouldShowDate;
    }

    $dateRow.removeClass('d-none');
    if (!shouldShowDate) {
        $dateRow.addClass('d-none');
    }

    return shouldShowDate;
}

function setWholesaleFollowUpContext(ticketId, departmentId, employeeId, statusId, statusName) {
    const normalizedTicketId = Number(ticketId || 0);
    const $modal = $('#showWholesaleFollowUpModal');

    $('#wholesale-follow-up-ticket-id').val(normalizedTicketId > 0 ? String(normalizedTicketId) : '');
    $('#wholesale-follow-up-id').val(normalizedTicketId > 0 ? String(normalizedTicketId) : '');
    $('#wholesale-follow-up-support-ticket-id').val(normalizedTicketId > 0 ? String(normalizedTicketId) : '');
    $('#wholesale-follow-up-department-id').val(departmentId || '');
    $('#wholesale-follow-up-employee-id').val(employeeId || '');
    prefillFollowUpStatus('#wholesale-follow-up-status', statusId, statusName);

    if ($modal.length) {
        $modal.data('ticket-id', normalizedTicketId > 0 ? String(normalizedTicketId) : '');
        $modal.data('department-id', departmentId || '');
        $modal.data('employee-id', employeeId || '');
        $modal.data('status-id', statusId || '');
        $modal.data('status-name', statusName || '');
    }
}

function applyTicketFilters($controls) {
    const url = new URL(window.location.href);
    const $filterInputs = $controls.find('.filter-tickets');

    $filterInputs.each(function () {
        const $input = $(this);
        const param = $input.data('value') || $input.attr('name');
        if (!param) {
            return;
        }

        const value = $input.val();
        if (value === undefined || value === null || value === '') {
            url.searchParams.delete(param);
            return;
        }

        url.searchParams.set(param, value);
    });

    url.searchParams.delete('page');
    window.location.href = url.toString();
}

$('.filter-tickets').on('change', function () {
    const $controls = $(this).closest('.ticket-filter-controls');
    if ($controls.find('.apply-ticket-filters').length) {
        return;
    }

    const param = $(this).data('value') || $(this).attr('name');
    if (!param) {
        return;
    }

    const value = $(this).val();
    const text = window.location;
    let redirectTo = '';
    const polished = removeURLParameter(text.toString(), param);
    if (polished.includes('?')) {
        redirectTo = polished + '&' + param + '=' + value;
    } else {
        redirectTo = polished + '?' + param + '=' + value;
    }
    location.href = redirectTo;
});

$(document).on('click', '.apply-ticket-filters', function (event) {
    event.preventDefault();
    const $controls = $(this).closest('.ticket-filter-controls');
    applyTicketFilters($controls);
});

function removeURLParameter(url, parameter) {
    let urlParts = url.split('?');
    if (urlParts.length >= 2) {
        let prefix = encodeURIComponent(parameter) + '=';
        let pars = urlParts[1].split(/[&;]/g);
        for (let i = pars.length; i-- > 0;) {
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }
        return urlParts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
    }
    return url;
}

document.querySelectorAll('.statusForm').forEach((form) => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const actionText = btn ? btn.textContent.trim() : '';

        Swal.fire({
            title: uiText.areYouSure,
            text: actionText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: uiText.yes,
            cancelButtonText: uiText.no,
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    Accept: 'application/json',
                },
                body: formData,
            })
                .then(async (response) => {
                    let payload = {};
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = {};
                    }

                    if (!response.ok) {
                        throw new Error(payload.message || uiText.somethingWentWrong);
                    }

                    return payload;
                })
                .then((response) => {
                    const successMessage = response.message || '';
                    Swal.fire({
                        text: successMessage,
                        icon: 'success',
                        confirmButtonText: uiText.yes,
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        text: error.message || uiText.somethingWentWrong,
                        icon: 'error',
                        confirmButtonText: uiText.yes,
                    });
                });
        });
    });
});

function handleFollowUpSubmit(formSelector, modalSelector) {
    const $form = $(formSelector);
    if (!$form.length) {
        return;
    }

    $form.off('submit.supportTickets').on('submit.supportTickets', function (event) {
        event.preventDefault();

        if (formSelector === '#updateSupportTicketFollowUpForm') {
            const isInProgressStatus = syncSupportFollowUpDateVisibility();
            if (isInProgressStatus && !$('#support-ticket-next-follow-up-date').val()) {
                toastr.error(getDataText(
                    'support-ticket-follow-up-date-required',
                    'Follow-up date is required for In Progress',
                ));
                $('#support-ticket-next-follow-up-date').trigger('focus');
                return;
            }
        }

        if (formSelector === '#updateWholesaleFollowUpForm') {
            const $ticketIdInput = $('#wholesale-follow-up-ticket-id');
            let ticketId = Number($ticketIdInput.val() || 0);

            if (ticketId <= 0) {
                ticketId = Number($('#showWholesaleFollowUpModal').data('ticket-id') || 0);
                if (ticketId > 0) {
                    $ticketIdInput.val(String(ticketId));
                    $('#wholesale-follow-up-id').val(String(ticketId));
                    $('#wholesale-follow-up-support-ticket-id').val(String(ticketId));
                }
            }

            if (ticketId <= 0) {
                toastr.error(getDataText('support-ticket-ticket-id-required', 'Ticket ID is required.'));
                return;
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message || '');
                    $(modalSelector).modal('hide');
                    location.reload();
                    return;
                }

                toastr.error(response.message || uiText.somethingWentWrong);
            },
            error: function (jqXHR) {
                toastr.error(jqXHR.responseJSON?.message || uiText.somethingWentWrong);
            },
        });
    });
}

function prefillFollowUpStatus(selectSelector, statusId, statusName = '') {
    const $statusField = $(selectSelector);
    if (!$statusField.length) {
        return;
    }

    const normalizedStatusId = Number(statusId || 0);
    if (
        normalizedStatusId > 0 &&
        $statusField.find('option[value="' + normalizedStatusId + '"]').length
    ) {
        $statusField.val(String(normalizedStatusId));
        $statusField.trigger('change');
        return;
    }

    const normalizedStatusName = String(statusName || '').trim().toLowerCase();
    if (normalizedStatusName !== '') {
        const matchedOption = $statusField.find('option').filter(function () {
            const optionRawStatusName = String($(this).data('status-name') || '').trim().toLowerCase();
            if (optionRawStatusName !== '') {
                return optionRawStatusName === normalizedStatusName;
            }

            return $(this).text().trim().toLowerCase() === normalizedStatusName;
        }).first();

        if (matchedOption.length) {
            $statusField.val(String(matchedOption.val()));
            $statusField.trigger('change');
            return;
        }
    }

    $statusField.prop('selectedIndex', 0);
    $statusField.trigger('change');
}

$(document).on('click', '[data-target="#showSupportFollowUpModal"], [data-bs-target="#showSupportFollowUpModal"]', function () {
    let ticketId = $(this).data('ticket-id');
    let departmentId = $(this).data('department-id');
    let employeeId = $(this).data('employee-id');
    let statusId = $(this).data('status-id');
    let statusName = $(this).data('status-name');

    $('#support-follow-up-ticket-id').val(ticketId || '');
    $('#support-follow-up-department-id').val(departmentId || '');
    $('#support-follow-up-employee-id').val(employeeId || '');
    prefillFollowUpStatus('#support-follow-up-status', statusId, statusName);
    syncSupportFollowUpDateVisibility();
});

$(document).on('click', '[data-target="#showComplainFollowUpModal"], [data-bs-target="#showComplainFollowUpModal"]', function () {
    let ticketId = $(this).data('ticket-id');
    let departmentId = $(this).data('department-id');
    let employeeId = $(this).data('employee-id');
    let statusId = $(this).data('status-id');
    let statusName = $(this).data('status-name');

    $('#support-follow-up-ticket-id').val(ticketId || '');
    $('#support-follow-up-department-id').val(departmentId || '');
    $('#support-follow-up-employee-id').val(employeeId || '');
    prefillFollowUpStatus('#complain-follow-up-status', statusId, statusName);
});

$(document).on('click', '[data-target="#showWholesaleFollowUpModal"], [data-bs-target="#showWholesaleFollowUpModal"]', function () {
    const $button = $(this);
    setWholesaleFollowUpContext(
        $button.data('ticket-id'),
        $button.data('department-id'),
        $button.data('employee-id'),
        $button.data('status-id'),
        $button.data('status-name'),
    );
});

$(function () {
    $('#support-follow-up-status').on('change select2:select', function () {
        syncSupportFollowUpDateVisibility();
    });

    $('#showSupportFollowUpModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const ticketId = button.data('ticket-id');
        const departmentId = button.data('department-id');
        const employeeId = button.data('employee-id');
        const statusId = button.data('status-id');
        const statusName = button.data('status-name');
        $('#support-follow-up-ticket-id').val(ticketId || '');
        $('#support-follow-up-department-id').val(departmentId || '');
        $('#support-follow-up-employee-id').val(employeeId || '');
        prefillFollowUpStatus('#support-follow-up-status', statusId, statusName);
        syncSupportFollowUpDateVisibility();
    });

    $('#showSupportFollowUpModal').on('shown.bs.modal', function () {
        syncSupportFollowUpDateVisibility();
    });

    $('#complain-follow-up-status').on('change', function () {
        let status = Number($(this).val() || 0);
        $('#complain-ticket-next-follow-up-date-row').removeClass().addClass('row d-none');
        if (status === 39) {
            $('#complain-ticket-next-follow-up-date-row').removeClass().addClass('row');
        }
    });

    $('#showComplainFollowUpModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const ticketId = button.data('ticket-id');
        const departmentId = button.data('department-id');
        const employeeId = button.data('employee-id');
        const statusId = button.data('status-id');
        const statusName = button.data('status-name');
        $('#support-follow-up-ticket-id').val(ticketId || '');
        $('#support-follow-up-department-id').val(departmentId || '');
        $('#support-follow-up-employee-id').val(employeeId || '');
        prefillFollowUpStatus('#complain-follow-up-status', statusId, statusName);
    });

    $('#showWholesaleFollowUpModal').on('show.bs.modal', function (event) {
        const $button = $(event.relatedTarget);
        const $modal = $(this);
        const ticketId = $button.data('ticket-id') || $modal.data('ticket-id') || '';
        const departmentId = $button.data('department-id') || $modal.data('department-id') || '';
        const employeeId = $button.data('employee-id') || $modal.data('employee-id') || '';
        const statusId = $button.data('status-id') || $modal.data('status-id') || '';
        const statusName = $button.data('status-name') || $modal.data('status-name') || '';

        setWholesaleFollowUpContext(ticketId, departmentId, employeeId, statusId, statusName);
    });

    $('#wholesale-follow-up-status').on('change', function () {
        let status = Number($(this).val() || 0);
        $('#wholesale-ticket-next-follow-up-date-row').addClass('d-none');
        if (status === 59) {
            $('#wholesale-ticket-next-follow-up-date-row').removeClass('d-none');
        }
    });

    handleFollowUpSubmit('#updateSupportTicketFollowUpForm', '#showSupportFollowUpModal');
    handleFollowUpSubmit('#updateComplainTicketFollowUpForm', '#showComplainFollowUpModal');
    handleFollowUpSubmit('#updateWholesaleFollowUpForm', '#showWholesaleFollowUpModal');
});

$(document).on('click', '.escalate-btn', function () {
    let ticketId = $(this).data('ticket-id');
    $('#escalateTicketId').val(ticketId);
    $('#escalateTicketModal').modal('show');
});

$('#escalateTicketForm').off('submit.supportTickets').on('submit.supportTickets', function (e) {
    e.preventDefault();
    const form = $(this);

    Swal.fire({
        title: uiText.areYouSure,
        text: uiText.escalateWarning,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: uiText.yesEscalate,
        cancelButtonText: uiText.cancel,
    }).then((result) => {
        if (result.isConfirmed) {
            form.off('submit').submit();
        }
    });
});
