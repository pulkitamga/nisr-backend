
$(document).on('click', '.assign-owner-btn', function () {
    let ticketId = $(this).data('id');
    let form = $('#updateTicketOwnerForm');
    form.find('#owner_ticket_id').val(ticketId);
    $('#showOwnerModal').modal('show');
});






$('#updateTicketOwnerForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this);
    let actionUrl = $('#assignOwnerRoute').data('url');
    let formData = form.serialize();

    console.log("Form Data:", formData);
    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showOwnerModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            location.reload();
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

    console.log("Form Data:", formData);

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showEmployeeModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            location.reload();
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

    console.log("Setting dept ticket_id:", ticketId, "current dept:", deptId);
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

    console.log("Dept Form Data:", formData);

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
            location.reload(); // page reload for update
        },
        error: function (xhr) {
            Swal.fire(
                'Error!',
                xhr.responseJSON?.message ?? 'Something went wrong',
                'error'
            );
        }
    });
});




$(document).ready(function () {
    $(document).on("click", ".assign-employee-btn", function () {
        let ticketId = $(this).data("id");
        let deptId = $(this).data("department-id") || "";
        let headId = $(this).data("head-id") || "";

        $("#modal_ticket_id").val(ticketId);

        if ($("#ticket-department-id").length) {
            if (deptId) {
                $("#ticket-department-id").val(deptId).trigger("change", [headId]);
            }
        }
        else {
            let fixedDeptId = $("#fixed-department-id").val() || deptId; // 👈 fallback bhi de diya
            if (fixedDeptId) {
                loadEmployees(fixedDeptId, headId);
            }
        }

        $("#showEmployeeModal").modal("show");
    });
});

let routeUrl = $('#getEmployeeRoute').data('url');

function loadEmployees(deptId, headId = null) {
    $("#ticket-employee-id").html('<option value="">Loading...</option>');

    $.ajax({
        url: routeUrl,
        type: "GET",
        data: { department_id: deptId, head_id: headId },
        success: function (res) {
            $("#ticket-employee-id").html('<option value="">Select Employee</option>');
            $.each(res, function (key, emp) {
                $("#ticket-employee-id").append(`<option value="${emp.id}">${emp.name}</option>`);
            });
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
        title: 'Are you sure?"',
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




