'use strict';

$(document).on('click', '.assign-owner-btn', function () {
    let ticketId = $(this).data('id');
    let ownerId = $(this).data('owner-id') || $(this).closest('tr').data('owner-id') || '';
    let form = $('#updateTicketOwnerForm');

    form.find('#owner_ticket_id').val(ticketId);
    form.find('#owner-employee-id').empty().append('<option value="">Select Supervisor</option>');
    loadOwners('', ownerId);

    $('#showOwnerModal').modal('show');
});






$('#updateTicketOwnerForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this);
    let actionUrl = $('#assignOwnerRoute').data('url');
    let formData = form.serialize();

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showOwnerModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            setTimeout(function () {
                window.location.reload();
            }, 500);
        },
        error: function (xhr) {
            Swal.fire(
                'Error!',
                xhr.responseJSON?.message ?? 'Something went wrong',
                'error'
            )
        }
    });
});

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

function loadOwners(deptId, selectedOwnerId = null) {
    const employeeRouteUrl = $('#getEmployeeRoute').data('url');
    $('#owner-employee-id').html('<option value="">Loading...</option>');

    $.ajax({
        url: employeeRouteUrl,
        type: "GET",
        data: { department_id: deptId || '', assignment: 'owner' },
        success: function (res) {
            const owners = mapEmployeeResponse(res);
            $('#owner-employee-id').html('<option value="">Select Supervisor</option>');

            $.each(owners, function (key, owner) {
                const selected = selectedOwnerId && String(selectedOwnerId) === String(owner.id) ? 'selected' : '';
                $('#owner-employee-id').append(`<option value="${owner.id}" ${selected}>${owner.name}</option>`);
            });
        },
        error: function () {
            $('#owner-employee-id').html('<option value="">Select Supervisor</option>');
        }
    });
}

$(document).on('click', '.assign-employee-btn', function () {
    let ticketId = $(this).data('id');
    let form = $('#updateTicketEmployeeForm');
    form.find('#employee_ticket_id').val(ticketId);
    $('#showEmployeeModal').modal('show');
});




$('#updateTicketEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this);
    let actionUrl = $('#assignEmployeeRoute').data('url');
    let formData = form.serialize();

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showEmployeeModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            setTimeout(function () {
                window.location.reload();
            }, 500);
        },
        error: function (xhr) {
            Swal.fire(
                'Error!',
                xhr.responseJSON?.message ?? 'Something went wrong',
                'error'
            )
        }
    });
});




$(document).on('click', '.assign-dept-btn', function () {
    let ticketId = $(this).data('id');
    let deptId = $(this).data('department-id') || '';

    $('#depart_ticket_id').val(ticketId);
    $('#department-id').val(deptId).trigger('change');
    $('#priority').val('');
    $('#admin-message').val('');

    $('#showDepartmentsModal').modal('show');
});


$('#updateTicketDepartmentForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this);
    let actionUrl = $('#assignDepartmentRoute').data('url');
    let formData = form.serialize();

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showDepartmentsModal').modal('hide'); // modal band karo
            Swal.fire(
                'Success!',
                response.message,
                'success'
            );
            setTimeout(function () {
                window.location.reload();
            }, 500);
        },
        error: function (xhr) {
            Swal.fire(
                'Error!',
                xhr.responseJSON?.message ?? 'Something went wrong',
                'error'
            );
        }
    });

    $(document).on('submit', '#escalateLeadForm', function (e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will notify the department and owner.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Escalate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

let employeeRouteUrl = $('#getEmployeeRoute').data('url');

function loadEmployees(deptId, headId = null) {
    if (!deptId) {
        $("#ticket-employee-id").html('<option value="">Select Employee</option>');
        return;
    }

    $("#ticket-employee-id").html('<option value="">Loading...</option>');

    $.ajax({
        url: employeeRouteUrl,
        type: "GET",
        data: { department_id: deptId, head_id: headId, assignment: 'employee' },
        success: function (res) {
            const employees = mapEmployeeResponse(res);
            $("#ticket-employee-id").html('<option value="">Select Employee</option>');
            $.each(employees, function (key, emp) {
                $("#ticket-employee-id").append(`<option value="${emp.id}">${emp.name}</option>`);
            });
        },
        error: function () {
            $("#ticket-employee-id").html('<option value="">Select Employee</option>');
        }
    });
}

$("#ticket-department-id").on("change", function (e, headId = null) {
    loadEmployees($(this).val(), headId);
});

$(document).on('click', '.escalate-btn', function () {
    let leadId = $(this).data('lead-id');
    $('#escalateLeadId').val(leadId);
    $('#escalateLeadModal').modal('show');
});

// Form submission with confirmation
$('#escalateLeadForm').submit(function (e) {
    e.preventDefault();
    let form = $(this);
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will notify the department and owner.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Escalate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.off('submit').submit(); // Submit without further prevention
        }
    });
});



