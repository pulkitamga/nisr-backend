$(document).ready(function () {

    // जब edit icon click हो
    $('.edit-message-type').click(function () {
        var messageId = $(this).data('id');
        var currentType = $(this).data('current-type');

        $('#message_id').val(messageId); // hidden input में डालो
        $('#type-id').val(currentType); // dropdown में current value select करो
    });

    // AJAX submit
    $('#updateTypeForm').on('submit', function (e) {
        e.preventDefault();
        let actionUrl = $(this).attr('action');
        let formData = $(this).serialize();

        $.ajax({
            url: actionUrl,
            type: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message || 'Message type updated successfully!');

                    $('div.edit-message-type[data-id="' + $('#message_id').val() + '"]').closest('td').find('.text-success').first().text($('#type-id').val());
                    $('#showTypeModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Failed to update message type!');
                }
            },
            error: function (xhr) {
                toastr.error('Something went wrong!');
            }
        });
    });

});

function getStatusClass(status) {
    const map = {
        new: 'text-primary bg-soft-primary',
        processing: 'text-warning bg-soft-warning',
        converted: 'text-success bg-soft-success',
        ignored: 'text-secondary bg-soft-secondary',
        spam: 'text-danger bg-soft-danger'
    };
    return map[status] || 'text-dark bg-soft-light';
}

function updateRowStatus(messageId, status) {
    const row = $('#row-' + messageId);
    if (!row.length) {
        return;
    }

    const badge = row.find('span.fz-12').first();
    if (!badge.length) {
        return;
    }

    badge
        .attr('class', 'btn ' + getStatusClass(status) + ' font-weight-bold px-3 py-1 mb-0 fz-12')
        .text(status.charAt(0).toUpperCase() + status.slice(1));
}

function clearActionButtons(messageId) {
    const row = $('#row-' + messageId);
    if (!row.length) {
        return;
    }

    row.find('.ignore-btn, .mark-spam-btn, a[data-bs-target="#convertModal"]').remove();
    row.find('.message-checkbox').prop('checked', false);
}

function updateOwnerCell(messageId, ownerName) {
    const row = $('#row-' + messageId);
    if (!row.length) {
        return;
    }

    const ownerCell = row.children('td').eq(7);
    if (!ownerCell.length) {
        return;
    }

    ownerCell.text(ownerName || 'Not Assigned');
}

const bulkConvertButton = document.querySelector(".bulk-convert-btn");
if (bulkConvertButton) {
    bulkConvertButton.addEventListener("click", function () {
        let ids = Array.from(document.querySelectorAll(".message-checkbox:checked"))
            .map(cb => cb.value);

        if (ids.length === 0) {
            Swal.fire("Please select at least one message!");
            return false;
        }
        document.getElementById("convertMessageIds").value = ids.join(",");

        let convertModal = new bootstrap.Modal(document.getElementById("convertBulkModal"));
        convertModal.show();
    });

    document.getElementById("bulkConvertForm").addEventListener("submit", function (e) {
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);

        if (formData.get("message_ids").includes(",")) {
            formData.set("message_ids", formData.get("message_ids").split(","));
        }

        fetch(form.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
            }
        }).then(res => res.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Converted!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const convertedIds = Array.isArray(data.converted) ? data.converted : [];
                    convertedIds.forEach(id => {
                        updateRowStatus(id, 'converted');
                        clearActionButtons(id);
                    });

                    const bulkModalEl = document.getElementById("convertBulkModal");
                    if (bulkModalEl) {
                        const bulkModal = bootstrap.Modal.getInstance(bulkModalEl);
                        if (bulkModal) {
                            bulkModal.hide();
                        }
                    }
                    $('#select-all').prop('checked', false);
                } else {
                    Swal.fire('Not Converted', data.message || 'No inquiry converted!', 'warning');
                }
            }).catch(err => {
                console.error(err);
                Swal.fire('Server Error', 'Please try again later!', 'error');
            });
    });
}



$(document).ready(function () {
    $('.action-btn').click(function () {
        let menu = $(this).next('.dropdown-menu');
        $('.dropdown-menu').not(menu).removeClass('show');
        menu.toggleClass('show');
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
});


$(document).on('click', '.ignore-btn', function () {
    let id = $(this).data('id');
    let routeUrl = $('#ignoreRoute').data('url'); // 👈 span se route nikala
    let token = $('meta[name="csrf-token"]').attr('content'); // 👈 meta se token liya

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to ignore this message?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Ignore it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: routeUrl,
                type: "POST",
                data: {
                    _token: token, // 👈 meta token use kiya
                    message_id: id
                },
                success: function (response) {
                    Swal.fire(
                        'Ignored!',
                        response.message,
                        'success'
                    )
                    updateRowStatus(id, 'ignored');
                    clearActionButtons(id);
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error!',
                        xhr.responseJSON?.message ?? 'Something went wrong',
                        'error'
                    )
                }
            });
        }
    });
});
$(document).on('click', '.mark-spam-btn', function () {
    let id = $(this).data('id');
    let routeUrl = $('#spamRoute').data('url'); // 👈 span se route nikala
    let token = $('meta[name="csrf-token"]').attr('content'); // 👈 meta se token liya

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want marked spam this message?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, marked it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: routeUrl,
                type: "POST",
                data: {
                    _token: token, // 👈 meta token use kiya
                    message_id: id
                },
                success: function (response) {
                    Swal.fire(
                        'Marked Spam!',
                        response.message,
                        'success'
                    )
                    updateRowStatus(id, 'spam');
                    clearActionButtons(id);
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error!',
                        xhr.responseJSON?.message ?? 'Something went wrong',
                        'error'
                    )
                }
            });
        }
    });
});


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

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showOwnerModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            const ticketId = form.find('#owner_ticket_id').val();
            const ownerName = form.find('#owner-employee-id option:selected').text().trim();
            updateOwnerCell(ticketId, ownerName);
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

    $.ajax({
        url: actionUrl,
        type: "POST",
        data: formData,
        success: function (response) {
            $('#showEmployeeModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
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



$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).on('click', '.delete-btn', function () {
    let id = $(this).data('id');
    let url = $(".delete-route[data-id='" + id + "']").data('url');

    Swal.fire({
        title: 'Are you sure?',
        text: "This message will be moved to trash!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: "DELETE",
                success: function (response) {
                    Swal.fire('Deleted!', response.message, 'success');
                    $("#row-" + id).fadeOut();
                },
                error: function (xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                }
            });
        }
    });
});


$('#select-all').on('change', function () {
    $('.message-checkbox').prop('checked', $(this).prop('checked'));
});



document.querySelectorAll("[data-bs-target='#convertModal']").forEach(btn => {
    btn.addEventListener("click", function () {
        document.getElementById("convertMessageId").value = this.dataset.id;
    });
});


// Ajax form submit
const convertForm = document.getElementById("convertForm");
if (convertForm) {
    convertForm.addEventListener("submit", function (e) {
        e.preventDefault();
        let form = this;

        fetch(form.action, {
            method: "POST",
            body: new FormData(form),
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
            }
        }).then(res => res.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Converted!',
                        text: data.message, // ✅ Controller se aaya message
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const messageId = document.getElementById("convertMessageId")?.value;
                    if (messageId) {
                        updateRowStatus(messageId, 'converted');
                        clearActionButtons(messageId);
                    }

                    const convertModalEl = document.getElementById("convertModal");
                    if (convertModalEl) {
                        const convertModal = bootstrap.Modal.getInstance(convertModalEl);
                        if (convertModal) {
                            convertModal.hide();
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Not Converted',
                        text: data.message || 'Conversion failed!', // ✅ Exact reason dikhayega
                    });
                }
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Please try again later!'
                });
            });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const typeSelect = document.getElementById("bulkTypeSelect");
    const subTypeWrapper = document.getElementById("bulkSubTypeWrapper");
    const subTypeSelect = document.getElementById("bulkSubTypeSelect");
    const bulkReasonWrapper = document.getElementById("bulkReasonWrapper");
    const bulkReasonSelect = document.getElementById("bulkReasonSelect");

    if (!typeSelect || !subTypeWrapper || !subTypeSelect || !bulkReasonWrapper || !bulkReasonSelect) {
        return;
    }

    // Type change
    typeSelect.addEventListener("change", function () {
        const type = this.value;

        subTypeSelect.innerHTML = '<option value="">-- Select Sub-Type --</option>';
        bulkReasonWrapper.style.display = "none";
        bulkReasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';

        if (type === "lead") {
            subTypeWrapper.style.display = "block";
            ["Retail", "Wholesale"].forEach(opt => {
                subTypeSelect.innerHTML += `<option value="${opt.toLowerCase()}">${opt}</option>`;
            });
        } else if (type === "ticket") {
            subTypeWrapper.style.display = "block";
            ["Support", "Complaint", "Career", "Service", "Retail", "Wholesale"].forEach(opt => {
                subTypeSelect.innerHTML += `<option value="${opt.toLowerCase()}">${opt}</option>`;
            });
        } else {
            subTypeWrapper.style.display = "none";
        }
    });

    // Sub-type change using event delegation
    document.addEventListener("change", function (e) {
        if (e.target && e.target.id === "bulkSubTypeSelect") {
            const value = e.target.value;

            if (value === "retail") {
                bulkReasonWrapper.style.display = "block";
                const bulkreasons = [
                    "Complaint",
                    "Delivery Issue",
                    "Return/RMA",
                    "Billing/Refund",
                    "Product Issue/Defect",
                    "Setup/How-to",
                    "General Inquiry"
                ];
                bulkReasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';
                bulkreasons.forEach(opt => {
                    bulkReasonSelect.innerHTML += `<option value="${opt.toLowerCase().replace(/[^a-z0-9]/gi, '_')}">${opt}</option>`;
                });
            } else {
                bulkReasonWrapper.style.display = "none";
                bulkReasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';
            }
        }
    });
});


document.getElementById("typeSelect").addEventListener("change", function () {
    let type = this.value;
    let subTypeWrapper = document.getElementById("subTypeWrapper");
    let subTypeSelect = document.getElementById("subTypeSelect");
    let reasonWrapper = document.getElementById("reasonWrapper");
    let reasonSelect = document.getElementById("reasonSelect");

    subTypeSelect.innerHTML = '<option value="">-- Select Sub-Type --</option>';
    reasonWrapper.style.display = "none";
    reasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';

    if (type === "lead") {
        subTypeWrapper.style.display = "block";
        ["Retail", "Wholesale"].forEach(opt => {
            subTypeSelect.innerHTML += `<option value="${opt.toLowerCase()}">${opt}</option>`;
        });
    } else if (type === "ticket") {
        subTypeWrapper.style.display = "block";
        ["Support", "Complaint", "Career", "Service", "Retail", "Wholesale"].forEach(opt => {
            subTypeSelect.innerHTML += `<option value="${opt.toLowerCase()}">${opt}</option>`;
        });

        subTypeSelect.addEventListener("change", function () {
            if (this.value === "retail") {
                reasonWrapper.style.display = "block";
                let reasons = [
                    "Complaint",
                    "Delivery Issue",
                    "Return/RMA",
                    "Billing/Refund",
                    "Product Issue/Defect",
                    "Setup/How-to",
                    "General Inquiry"
                ];
                reasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';
                reasons.forEach(opt => {
                    reasonSelect.innerHTML += `<option value="${opt.toLowerCase().replace(/[^a-z0-9]/gi, '_')}">${opt}</option>`;
                });
            } else {
                reasonWrapper.style.display = "none";
                reasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';
            }
        });
    } else {
        subTypeWrapper.style.display = "none";
    }
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

function initLeadDetails(leadId, translations) {
    $(document).ready(function () {
        // Ensure only one collapse is open at a time
        $('.action-btn').on('click', function () {
            var target = $(this).data('collapse-target');
            var collapses = ['activity', 'task', 'note', 'call', 'file'];

            // Pehle sabhi li se active hatao
            $('#actionTabs li').removeClass('active');

            // Jis button pe click hua uske parent li ko active karo
            $(this).closest('li').addClass('active');

            collapses.forEach(function (collapse) {
                if (collapse !== target) {
                    $('#collapse' + collapse.charAt(0).toUpperCase() + collapse.slice(1) + '-' + leadId).collapse('hide');
                }
            });
        });


        // Handle form submissions via AJAX
        function handleFormSubmission(formId, listId, successMessage, activityListId) {
            $(formId).on('submit', function (e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                var isUpdate = $('#task-id-' + leadId).val() !== '';
                var url = isUpdate
                    ? '/admin/crm/lead/' + leadId + '/task/' + $('#task-id-' + leadId).val()
                    : form.attr('action');


                $('#method-' + leadId).val(isUpdate ? 'PUT' : 'POST');


                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status) {
                            toastr.success(isUpdate ? translations.taskUpdated : successMessage);

                            $(listId).html(response.html);
                            if (activityListId && response.activity_html) {
                                $(activityListId).html(response.activity_html);
                            }
                            form[0].reset();
                            $('#task-id-' + leadId).val('');
                            $('#method-' + leadId).val('POST');
                            $('#task-submit-btn-' + leadId).text('Save Task');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = 'An error occurred. Please try again.';
                        if (Object.keys(errors).length > 0) {
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }
                        toastr.error(errorMessage);
                    }

                });
            });
        }

        $(document).on('click', '.task-edit-btn', function () {
            var taskId = $(this).data('task-id');
            var name = $(this).data('name');
            var description = $(this).data('description') || '';
            var dueDate = $(this).data('due-date');
            var status = $(this).data('status');

            // Populate form
            $('#task-id-' + leadId).val(taskId);
            $('#taskName-' + leadId).val(name);
            $('#taskDesc-' + leadId).val(description);
            $('#taskDue-' + leadId).val(dueDate);
            $('#taskStatus-' + leadId).val(status);
            $('#task-submit-btn-' + leadId).text('Update Task');

            // Set method to PUT for updates
            $('#method-' + leadId).val('PUT');

            // Open Task tab
            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to mark this task as complete?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark complete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/crm/lead/' + leadId + '/task/' + taskId + '/complete',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status) {
                                toastr.success(translations.taskCompleted);
                                $('#task-list-' + leadId).html(response.html);
                                $('#activity-list-' + leadId).html(response.activity_html);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function (xhr) {
                            var errors = xhr.responseJSON.errors || {};
                            var errorMessage = 'An error occurred. Please try again.';
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('<br>');
                            }
                            toastr.error(errorMessage);
                        }

                    });
                }
            });
        });

        handleFormSubmission('#activity-form-' + leadId, '#activity-list-' + leadId, translations.activitySaved, '#activity-list-' + leadId);
        handleFormSubmission('#note-form-' + leadId, '#note-list-' + leadId, translations.noteSaved, '#activity-list-' + leadId);
        handleFormSubmission('#task-form-' + leadId, '#task-list-' + leadId, translations.taskSaved, '#activity-list-' + leadId);
        handleFormSubmission('#call-form-' + leadId, '#call-list-' + leadId, translations.callSaved, '#activity-list-' + leadId);
        handleFormSubmission('#file-form-' + leadId, '#file-list-' + leadId, translations.fileUploaded, '#activity-list-' + leadId);
    });
}


function initInboxDetails(leadId, translations) {
    $(document).ready(function () {
        $('.action-btn').on('click', function () {
            var target = $(this).data('collapse-target');
            var collapses = ['activity', 'task', 'note', 'call', 'file'];
            $('#actionTabs li').removeClass('active');
            $(this).closest('li').addClass('active');

            collapses.forEach(function (collapse) {
                if (collapse !== target) {
                    $('#collapse' + collapse.charAt(0).toUpperCase() + collapse.slice(1) + '-' + leadId).collapse('hide');
                }
            });
        });

        function handleFormSubmission(formId, listId, successMessage, activityListId) {
            $(formId).on('submit', function (e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                var isUpdate = $('#task-id-' + leadId).val() !== '';
                var url = isUpdate
                    ? '/admin/crm/inbox/' + leadId + '/task/' + $('#task-id-' + leadId).val()
                    : form.attr('action');


                $('#method-' + leadId).val(isUpdate ? 'PUT' : 'POST');


                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status) {
                            toastr.success(isUpdate ? translations.taskUpdated : successMessage);

                            $(listId).html(response.html);
                            if (activityListId && response.activity_html) {
                                $(activityListId).html(response.activity_html);
                            }
                            form[0].reset();
                            $('#task-id-' + leadId).val('');
                            $('#method-' + leadId).val('POST');
                            $('#task-submit-btn-' + leadId).text('Save Task');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = 'An error occurred. Please try again.';
                        if (Object.keys(errors).length > 0) {
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }
                        toastr.error(errorMessage);
                    }

                });
            });
        }

        $(document).on('click', '.task-edit-btn-inbox', function () {
            var taskId = $(this).data('task-id');
            var name = $(this).data('name');
            var description = $(this).data('description') || '';
            var dueDate = $(this).data('due-date');
            var status = $(this).data('status');

            // Populate form
            $('#task-id-' + leadId).val(taskId);
            $('#taskName-' + leadId).val(name);
            $('#taskDesc-' + leadId).val(description);
            $('#taskDue-' + leadId).val(dueDate);
            $('#taskStatus-' + leadId).val(status);
            $('#task-submit-btn-' + leadId).text('Update Task');

            $('#method-' + leadId).val('PUT');

            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn-inbox', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to mark this task as complete?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark complete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/crm/inbox/' + leadId + '/task/' + taskId + '/complete',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status) {
                                toastr.success(translations.taskCompleted);
                                $('#task-list-' + leadId).html(response.html);
                                $('#activity-list-' + leadId).html(response.activity_html);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function (xhr) {
                            var errors = xhr.responseJSON.errors || {};
                            var errorMessage = 'An error occurred. Please try again.';
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('<br>');
                            }
                            toastr.error(errorMessage);
                        }

                    });
                }
            });
        });

        handleFormSubmission('#activity-form-' + leadId, '#activity-list-' + leadId, translations.activitySaved, '#activity-list-' + leadId);
        handleFormSubmission('#note-form-' + leadId, '#note-list-' + leadId, translations.noteSaved, '#activity-list-' + leadId);
        handleFormSubmission('#task-form-' + leadId, '#task-list-' + leadId, translations.taskSaved, '#activity-list-' + leadId);
        handleFormSubmission('#call-form-' + leadId, '#call-list-' + leadId, translations.callSaved, '#activity-list-' + leadId);
        handleFormSubmission('#file-form-' + leadId, '#file-list-' + leadId, translations.fileUploaded, '#activity-list-' + leadId);
    });
}



function initDealDetails(leadId, translations) {
    $(document).ready(function () {
        $('.action-btn').on('click', function () {
            var target = $(this).data('collapse-target');
            var collapses = ['activity', 'task', 'note', 'call', 'file'];
            $('#actionTabs li').removeClass('active');
            $(this).closest('li').addClass('active');

            collapses.forEach(function (collapse) {
                if (collapse !== target) {
                    $('#collapse' + collapse.charAt(0).toUpperCase() + collapse.slice(1) + '-' + leadId).collapse('hide');
                }
            });
        });

        function handleFormSubmission(formId, listId, successMessage, activityListId) {
            $(formId).on('submit', function (e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                var isUpdate = $('#task-id-' + leadId).val() !== '';
                var url = isUpdate
                    ? '/admin/crm/deal/' + leadId + '/task/' + $('#task-id-' + leadId).val()
                    : form.attr('action');


                $('#method-' + leadId).val(isUpdate ? 'PUT' : 'POST');


                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status) {
                            toastr.success(isUpdate ? translations.taskUpdated : successMessage);

                            $(listId).html(response.html);
                            if (activityListId && response.activity_html) {
                                $(activityListId).html(response.activity_html);
                            }
                            form[0].reset();
                            $('#task-id-' + leadId).val('');
                            $('#method-' + leadId).val('POST');
                            $('#task-submit-btn-' + leadId).text('Save Task');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = 'An error occurred. Please try again.';
                        if (Object.keys(errors).length > 0) {
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }
                        toastr.error(errorMessage);
                    }

                });
            });
        }

        $(document).on('click', '.task-edit-btn-inbox', function () {
            var taskId = $(this).data('task-id');
            var name = $(this).data('name');
            var description = $(this).data('description') || '';
            var dueDate = $(this).data('due-date');
            var status = $(this).data('status');

            // Populate form
            $('#task-id-' + leadId).val(taskId);
            $('#taskName-' + leadId).val(name);
            $('#taskDesc-' + leadId).val(description);
            $('#taskDue-' + leadId).val(dueDate);
            $('#taskStatus-' + leadId).val(status);
            $('#task-submit-btn-' + leadId).text('Update Task');

            $('#method-' + leadId).val('PUT');

            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn-inbox', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to mark this task as complete?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark complete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/crm/deal/' + leadId + '/task/' + taskId + '/complete',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status) {
                                toastr.success(translations.taskCompleted);
                                $('#task-list-' + leadId).html(response.html);
                                $('#activity-list-' + leadId).html(response.activity_html);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function (xhr) {
                            var errors = xhr.responseJSON.errors || {};
                            var errorMessage = 'An error occurred. Please try again.';
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('<br>');
                            }
                            toastr.error(errorMessage);
                        }

                    });
                }
            });
        });

        handleFormSubmission('#activity-form-' + leadId, '#activity-list-' + leadId, translations.activitySaved, '#activity-list-' + leadId);
        handleFormSubmission('#note-form-' + leadId, '#note-list-' + leadId, translations.noteSaved, '#activity-list-' + leadId);
        handleFormSubmission('#task-form-' + leadId, '#task-list-' + leadId, translations.taskSaved, '#activity-list-' + leadId);
        handleFormSubmission('#call-form-' + leadId, '#call-list-' + leadId, translations.callSaved, '#activity-list-' + leadId);
        handleFormSubmission('#file-form-' + leadId, '#file-list-' + leadId, translations.fileUploaded, '#activity-list-' + leadId);
    });
}


$(function () {
    let selectedMessageId, selectedUserId;

    const getUserBase = $('#getUserRoute').data('route');
    const connectUserRoute = $('#connectUserRoute').data('route');
    const csrf = $('meta[name="csrf-token"]').attr('content');

    $(document).on('click', '.suggestion-btn', function () {
        selectedMessageId = $(this).data('message-id');
        selectedUserId = $(this).data('user-id');

        let getUserUrl = `${getUserBase}/${selectedUserId}`;

        $.get(getUserUrl, function (data) {
            $('#suggestion-user-info').html(`
                <strong>Name:</strong> ${data.name ?? '-'}<br>
                <strong>Email:</strong> ${data.email ?? '-'}<br>
                <strong>Phone:</strong> ${data.phone ?? '-'}
            `);
        }).fail(function () {
            toastr.error('Failed to load user info.');
        });
    });

    $('#connect-user-btn').on('click', function () {
        $.post(connectUserRoute, {
            _token: csrf,
            message_id: selectedMessageId,
            user_id: selectedUserId
        }, function (res) {
            if (res.success) {
                $('#row-' + selectedMessageId + ' .suggestion-btn').remove();
                $('#suggestionModal').modal('hide');
                toastr.success('User connected successfully!');
            } else {
                toastr.error('Something went wrong!');
            }
        }).fail(function () {
            toastr.error('Request failed!');
        });
    });
});



