'use strict';

function getLeadText(key, fallback = '') {
    return window.crmUiText?.[key] || fallback;
}

const leadUiText = {
    successTitle: getLeadText('successTitle', 'Success!'),
    errorTitle: getLeadText('errorTitle', 'Error!'),
    somethingWentWrong: getLeadText('somethingWentWrong', 'Something went wrong!'),
    areYouSure: getLeadText('areYouSure', 'Are you sure?'),
    cancel: getLeadText('cancel', 'Cancel'),
    yesEscalate: getLeadText('yesEscalate', 'Yes, Escalate'),
    escalateWarning: getLeadText('escalateWarning', 'This will notify the department and owner.'),
    selectSupervisor: getLeadText('selectSupervisor', 'Select Supervisor'),
    selectEmployee: getLeadText('selectEmployee', 'Select Employee'),
    loading: getLeadText('loading', 'Loading...'),
    convertButtonText: getLeadText('convertButtonText', 'Convert'),
    convertingText: getLeadText('convertingText', 'Converting...'),
    convertLeadMissingMessage: getLeadText(
        'convertLeadMissingMessage',
        'Lead id is missing. Please close and reopen the convert form',
    ),
    convertSelectPartyMessage: getLeadText(
        'convertSelectPartyMessage',
        'Please select a party from search results before converting',
    ),
    selectOrder: getLeadText('selectOrder', 'Select Order'),
    noOrdersFound: getLeadText('noOrdersFound', 'No Orders Found'),
    errorLoadingOrders: getLeadText('errorLoadingOrders', 'Error loading orders'),
    leadDisqualifyPrompt: getLeadText('leadDisqualifyPrompt', 'You want to disqualify this lead!'),
    yesDisqualify: getLeadText('yesDisqualify', 'Yes, Disqualify'),
    disqualifiedLabel: getLeadText('disqualifiedLabel', 'Disqualified'),
};

function mapEmployeeResponse(res) {
    if (Array.isArray(res)) {
        return res;
    }
    if (res && Array.isArray(res.employee)) {
        return res.employee;
    }
    if (res && Array.isArray(res.data)) {
        return res.data;
    }
    return [];
}

function getLeadRoute(selector) {
    return $(selector).data('url') || '';
}

function loadOwners(deptId, selectedOwnerId = null) {
    const employeeRouteUrl = getLeadRoute('#getEmployeeRoute');
    const $ownerSelect = $('#owner-employee-id');

    $ownerSelect.html(`<option value="">${leadUiText.loading}</option>`);

    $.ajax({
        url: employeeRouteUrl,
        type: 'GET',
        data: { department_id: deptId || '', assignment: 'owner' },
        success: function (res) {
            const owners = mapEmployeeResponse(res);
            $ownerSelect.html(`<option value="">${leadUiText.selectSupervisor}</option>`);

            $.each(owners, function (_, owner) {
                const selected = selectedOwnerId && String(selectedOwnerId) === String(owner.id) ? 'selected' : '';
                $ownerSelect.append(`<option value="${owner.id}" ${selected}>${owner.name}</option>`);
            });
        },
        error: function () {
            $ownerSelect.html(`<option value="">${leadUiText.selectSupervisor}</option>`);
        },
    });
}

function loadEmployees(deptId, headId = null) {
    const $employeeSelect = $('#ticket-employee-id');

    if (!deptId) {
        $employeeSelect.html(`<option value="">${leadUiText.selectEmployee}</option>`);
        return;
    }

    $employeeSelect.html(`<option value="">${leadUiText.loading}</option>`);

    $.ajax({
        url: getLeadRoute('#getEmployeeRoute'),
        type: 'GET',
        data: { department_id: deptId, head_id: headId, assignment: 'employee' },
        success: function (res) {
            const employees = mapEmployeeResponse(res);
            $employeeSelect.html(`<option value="">${leadUiText.selectEmployee}</option>`);

            $.each(employees, function (_, employee) {
                $employeeSelect.append(`<option value="${employee.id}">${employee.name}</option>`);
            });
        },
        error: function () {
            $employeeSelect.html(`<option value="">${leadUiText.selectEmployee}</option>`);
        },
    });
}

function resetLeadConvertForm() {
    const $form = $('#convertForm');

    $form.find('button[type="submit"]').prop('disabled', false).text(leadUiText.convertButtonText);
    $('#party_search_results').hide().empty();
    $('#party_search_input').val('');
    $('#party_id').val('');
    $('#order-section').hide();
    $('#order_id').empty().append(`<option value="">${leadUiText.selectOrder}</option>`);
}

function loadLeadOrders(userId) {
    const routeUrl = getLeadRoute('#getUserOrdersRoute');
    const $orderSection = $('#order-section');
    const $orderSelect = $('#order_id');

    $orderSection.show();
    $orderSelect.html(`<option value="">${leadUiText.loading}</option>`);

    $.ajax({
        url: routeUrl,
        type: 'GET',
        data: { user_id: userId },
        success: function (data) {
            $orderSelect.empty();

            if (data && data.length > 0) {
                $orderSelect.append(`<option value="">${leadUiText.selectOrder}</option>`);
                data.forEach((order) => {
                    $orderSelect.append(`<option value="${order.id}">${order.order_no}</option>`);
                });
                return;
            }

            $orderSelect.append(`<option value="">${leadUiText.noOrdersFound}</option>`);
        },
        error: function () {
            $orderSelect.html(`<option value="">${leadUiText.errorLoadingOrders}</option>`);
        },
    });
}

function initializeLeadAssignments() {
    $(document).on('click.leadModule', '.assign-owner-btn', function () {
        const ticketId = $(this).data('id');
        const ownerId = $(this).data('owner-id') || $(this).closest('tr').data('owner-id') || '';
        const departmentId = $(this).data('department-id') || '';
        const $form = $('#updateTicketOwnerForm');

        $form.find('#owner_ticket_id').val(ticketId);
        $form.find('#owner-employee-id').empty().append(`<option value="">${leadUiText.selectSupervisor}</option>`);
        loadOwners(departmentId, ownerId);

        $('#showOwnerModal').modal('show');
    });

    $('#updateTicketOwnerForm').off('submit.leadModule').on('submit.leadModule', function (event) {
        event.preventDefault();

        const $form = $(this);

        $.ajax({
            url: getLeadRoute('#assignOwnerRoute'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                $('#showOwnerModal').modal('hide');
                Swal.fire(leadUiText.successTitle, response.message, 'success');
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            },
            error: function (xhr) {
                Swal.fire(
                    leadUiText.errorTitle,
                    xhr.responseJSON?.message ?? leadUiText.somethingWentWrong,
                    'error',
                );
            },
        });
    });

    $(document).on('click.leadModule', '.assign-employee-btn', function () {
        const ticketId = $(this).data('id');
        const departmentId = $(this).data('department-id') || '';
        const headId = $(this).data('head-id') || null;
        const $form = $('#updateTicketEmployeeForm');

        $form.find('#employee_ticket_id').val(ticketId);
        $('#ticket-department-id').val(departmentId).trigger('change', [headId]);
        $('#showEmployeeModal').modal('show');
    });

    $('#updateTicketEmployeeForm').off('submit.leadModule').on('submit.leadModule', function (event) {
        event.preventDefault();

        const $form = $(this);

        $.ajax({
            url: getLeadRoute('#assignEmployeeRoute'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                $('#showEmployeeModal').modal('hide');
                Swal.fire(leadUiText.successTitle, response.message, 'success');
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            },
            error: function (xhr) {
                Swal.fire(
                    leadUiText.errorTitle,
                    xhr.responseJSON?.message ?? leadUiText.somethingWentWrong,
                    'error',
                );
            },
        });
    });

    $(document).on('click.leadModule', '.assign-dept-btn', function () {
        const ticketId = $(this).data('id');
        const deptId = $(this).data('department-id') || '';

        $('#depart_ticket_id').val(ticketId);
        $('#department-id').val(deptId).trigger('change');
        $('#priority').val('');
        $('#admin-message').val('');

        $('#showDepartmentsModal').modal('show');
    });

    $('#updateTicketDepartmentForm').off('submit.leadModule').on('submit.leadModule', function (event) {
        event.preventDefault();

        const $form = $(this);

        $.ajax({
            url: getLeadRoute('#assignDepartmentRoute'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                $('#showDepartmentsModal').modal('hide');
                Swal.fire(leadUiText.successTitle, response.message, 'success');
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            },
            error: function (xhr) {
                Swal.fire(
                    leadUiText.errorTitle,
                    xhr.responseJSON?.message ?? leadUiText.somethingWentWrong,
                    'error',
                );
            },
        });
    });

    $('#ticket-department-id').off('change.leadModule').on('change.leadModule', function (_event, headId = null) {
        loadEmployees($(this).val(), headId);
    });
}

function initializeLeadConvertFlow() {
    $(document).on('shown.bs.modal.leadModule', '#convertLeadModal', function () {
        resetLeadConvertForm();
    });

    $(document).on('hidden.bs.modal.leadModule', '#convertLeadModal', function () {
        resetLeadConvertForm();
    });

    $('#party_search_input').off('keyup.leadModule').on('keyup.leadModule', function () {
        const query = $(this).val().trim();
        const partyType = $('#party_type').val();

        $('#party_id').val('');

        if (query.length < 1) {
            $('#party_search_results').hide().empty();
            return;
        }

        $.ajax({
            url: getLeadRoute('#partySearchRoute'),
            type: 'GET',
            data: {
                q: query,
                party_type: partyType,
            },
            success: function (data) {
                const $resultsContainer = $('#party_search_results');
                $resultsContainer.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    $resultsContainer.hide();
                    return;
                }

                data.forEach((item) => {
                    $resultsContainer.append(
                        `<li class="list-group-item list-group-item-action" data-id="${item.id}" style="cursor:pointer;">${item.text}</li>`,
                    );
                });
                $resultsContainer.show();
            },
            error: function () {
                $('#party_search_results').hide().empty();
            },
        });
    });

    $(document).off('click.leadModule', '#party_search_results li').on('click.leadModule', '#party_search_results li', function () {
        const selectedId = $(this).data('id');
        const selectedText = $(this).text();
        const partyType = $('#party_type').val();

        $('#party_id').val(selectedId);
        $('#party_search_input').val(selectedText);
        $('#party_search_results').hide().empty();
        $('#order-section').hide();

        if (partyType === 'contact') {
            loadLeadOrders(selectedId);
        }
    });

    $('#party_type').off('change.leadModule').on('change.leadModule', function () {
        $('#party_id').val('');
        $('#party_search_input').val('');
        $('#party_search_results').hide().empty();
        $('#order-section').hide();
        $('#order_id').empty().append(`<option value="">${leadUiText.selectOrder}</option>`);
    });

    $(document).on('click.leadModule', '.convert-btn', function () {
        $('#lead_id').val($(this).data('lead-id'));
    });

    $('#convertForm').off('submit.leadModule').on('submit.leadModule', function (event) {
        const leadId = $('#lead_id').val();
        const partyId = $('#party_id').val();
        const $submitButton = $(this).find('button[type="submit"]');

        if (!leadId) {
            event.preventDefault();
            Swal.fire(leadUiText.errorTitle, leadUiText.convertLeadMissingMessage, 'error');
            return;
        }

        if (!partyId) {
            event.preventDefault();
            Swal.fire(leadUiText.errorTitle, leadUiText.convertSelectPartyMessage, 'error');
            return;
        }

        $submitButton.prop('disabled', true).text(leadUiText.convertingText);
    });
}

function initializeLeadDisqualifyFlow() {
    $(document).on('click.leadModule', '.disqualify-btn', function () {
        const leadId = $(this).data('id');

        Swal.fire({
            title: leadUiText.areYouSure,
            text: leadUiText.leadDisqualifyPrompt,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: leadUiText.yesDisqualify,
            cancelButtonText: leadUiText.cancel,
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: getLeadRoute('#leadDisqualifyRoute'),
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    message_id: leadId,
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire(leadUiText.successTitle, response.message, 'success');
                        $('#row-' + leadId).find('span.btn').removeClass().addClass('btn text-danger bg-soft-danger').text(leadUiText.disqualifiedLabel);
                        return;
                    }

                    Swal.fire(leadUiText.errorTitle, response.message, 'error');
                },
                error: function (xhr) {
                    Swal.fire(
                        leadUiText.errorTitle,
                        xhr.responseJSON?.message || leadUiText.somethingWentWrong,
                        'error',
                    );
                },
            });
        });
    });
}

function initializeLeadEscalationFlow() {
    $(document).on('click.leadModule', '.escalate-btn', function () {
        $('#escalateLeadId').val($(this).data('lead-id'));
        $('#escalateLeadModal').modal('show');
    });

    $('#escalateLeadForm').off('submit.leadModule').on('submit.leadModule', function (event) {
        event.preventDefault();
        const $form = $(this);

        Swal.fire({
            title: leadUiText.areYouSure,
            text: leadUiText.escalateWarning,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: leadUiText.yesEscalate,
            cancelButtonText: leadUiText.cancel,
        }).then((result) => {
            if (result.isConfirmed) {
                $form.off('submit.leadModule');
                $form[0].submit();
            }
        });
    });
}

$(function () {
    initializeLeadAssignments();
    initializeLeadConvertFlow();
    initializeLeadDisqualifyFlow();
    initializeLeadEscalationFlow();
});
