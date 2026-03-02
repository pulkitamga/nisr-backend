"use strict";

let messageAreYouSure = $("#message-are-you-sure").data("text");
let messageYesWord = $("#message-yes-word").data("text");
let messageNoWord = $("#message-no-word").data("text");
let messageWantAddOrUpdateThisProduct = $(
    "#message-want-to-add-or-update-this-request"
).data("text");

$(document).on("ready", function () {
    $('#ticket-follow-up-status').on('change', function () {
        var iStatus = $(this).val();
        $('#ticket-next-follow-up-date-row, #ticket-remainder-days-after-row, #ticket-remainder-interval-row, #ticket-remainder-cycle-row').removeClass().addClass('row d-none');
        if (iStatus == 46 || iStatus == 5 || iStatus == 39) {
            $('#ticket-next-follow-up-date-row').removeClass().addClass('row');
        } else if (iStatus == 54) {
            $('#ticket-remainder-days-after-row, #ticket-remainder-interval-row, #ticket-remainder-cycle-row').removeClass().addClass('row');
        }
    });

    $('#showDepartmentsModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const ticketId = button.data('ticket-id'); // Extract ticket ID
        const ticketDeprtmentId = button.data('department-id'); // Extract ticket ID
        const departmentIdSelect = document.getElementById('department-id');
        $('input[name="ticket_id"]').val(ticketId);
        $(departmentIdSelect).val(0).trigger('change');
        if (ticketDeprtmentId != 0) {
            $("input[name='department-id']").val(ticketDeprtmentId);
            $(departmentIdSelect).val(ticketDeprtmentId).trigger('change');
        }
    });



    $('#showFollowUpModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const ticketId = button.data('ticket-id');
        const ticketDeprtmentId = button.data('department-id');
        const ticketEmployeeId = button.data('employee-id');
        $('#follow-up-ticket-id').val(ticketId);
        $('#follow-up-department-id').val(ticketDeprtmentId);
        $('#follow-up-employee-id').val(ticketEmployeeId);
    });

    // Handling form submission
    $('#updateTicketDepartmentForm').on('submit', function (event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                    "content"
                ),
            },
        });

        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success('Successfully assigned the department to complaint!');
                    $('#showDepartmentsModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Something went wrong. Please try again.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.statusText) {
                    errorMessage = jqXHR.statusText;
                }

                toastr.error(errorMessage);
            }
        });
    });

    $('#updateTicketEmployeeForm').on('submit', function (event) {
        event.preventDefault();

        let actionUrl = $('#assignEmployeeRoute').data('url');
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                    "content"
                ),
            },
        });

        $.ajax({
            url: actionUrl,
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success('Successfully assigned the employee to complaint!');
                    $('#showEmployeeModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Something went wrong. Please try again.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.statusText) {
                    errorMessage = jqXHR.statusText;
                }

                toastr.error(errorMessage);
            }
        });
    });

    $('#updateTicketFollowUpForm').on('submit', function (event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                    "content"
                ),
            },
        });

        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success('Successfully follow up updated.');
                    $('#showFollowUpModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Something went wrong. Please try again.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.statusText) {
                    errorMessage = jqXHR.statusText;
                }

                toastr.error(errorMessage);
                // $('#showFollowUpModal').modal('hide');
            }
        });
    });
})

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

let employeeRouteUrl = $('#route-get-department-employee').data('url') || $('#getEmployeeRoute').data('url');

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
            let employees = mapEmployeeResponse(res);
            if (headId) {
                employees = employees.filter(emp => String(emp.id) !== String(headId));
            }
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
