function getCrmUiText(key, fallback) {
    if (window.crmUiText && Object.prototype.hasOwnProperty.call(window.crmUiText, key)) {
        return window.crmUiText[key];
    }

    return fallback;
}

const crmUiText = {
    messageTypeUpdatedSuccessfully: getCrmUiText("messageTypeUpdatedSuccessfully", "Message type updated successfully!"),
    failedToUpdateMessageType: getCrmUiText("failedToUpdateMessageType", "Failed to update message type!"),
    pleaseSelectAtLeastOneMessage: getCrmUiText("pleaseSelectAtLeastOneMessage", "Please select at least one message!"),
    successTitle: getCrmUiText("successTitle", "Success!"),
    errorTitle: getCrmUiText("errorTitle", "Error!"),
    deletedTitle: getCrmUiText("deletedTitle", "Deleted!"),
    notConvertedTitle: getCrmUiText("notConvertedTitle", "Not Converted"),
    noInquiryConverted: getCrmUiText("noInquiryConverted", "No inquiry converted!"),
    somethingWentWrong: getCrmUiText("somethingWentWrong", "Something went wrong!"),
    areYouSure: getCrmUiText("areYouSure", "Are you sure?"),
    ignoreMessagePrompt: getCrmUiText("ignoreMessagePrompt", "Do you want to ignore this message?"),
    yesIgnoreIt: getCrmUiText("yesIgnoreIt", "Yes, Ignore it!"),
    cancel: getCrmUiText("cancel", "Cancel"),
    markSpamPrompt: getCrmUiText("markSpamPrompt", "Do you want marked spam this message?"),
    yesMarkIt: getCrmUiText("yesMarkIt", "Yes, marked it!"),
    selectSupervisor: getCrmUiText("selectSupervisor", "Select Supervisor"),
    loading: getCrmUiText("loading", "Loading..."),
    selectEmployee: getCrmUiText("selectEmployee", "Select Employee"),
    notAssigned: getCrmUiText("notAssigned", "Not Assigned"),
    deleteMessagePrompt: getCrmUiText("deleteMessagePrompt", "This message will be moved to trash!"),
    yesDeleteIt: getCrmUiText("yesDeleteIt", "Yes, delete it!"),
    selectReason: getCrmUiText("selectReason", "Select Reason"),
    selectSubType: getCrmUiText("selectSubType", "Select Sub-Type"),
    failedToLoadUserInfo: getCrmUiText("failedToLoadUserInfo", "Failed to load user info."),
    userConnectedSuccessfully: getCrmUiText("userConnectedSuccessfully", "User connected successfully!"),
    requestFailed: getCrmUiText("requestFailed", "Request failed!"),
    genericTryAgain: getCrmUiText("genericTryAgain", "Something went wrong!"),
    saveTask: getCrmUiText("saveTask", "Save Task"),
    updateTask: getCrmUiText("updateTask", "Update Task"),
    markTaskCompletePrompt: getCrmUiText("markTaskCompletePrompt", "Do you want to mark this task as complete?"),
    yesMarkComplete: getCrmUiText("yesMarkComplete", "Yes, mark complete"),
    nameLabel: getCrmUiText("nameLabel", "Name"),
    emailLabel: getCrmUiText("emailLabel", "Email"),
    phoneLabel: getCrmUiText("phoneLabel", "Phone"),
    statusLabels: getCrmUiText("statusLabels", {
        new: "New",
        processing: "Processing",
        converted: "Converted",
        ignored: "Ignored",
        spam: "Spam",
    }),
    leadSubTypes: getCrmUiText("leadSubTypes", [
        { value: "retail", label: "Retail" },
        { value: "wholesale", label: "Wholesale" },
    ]),
    ticketSubTypes: getCrmUiText("ticketSubTypes", [
        { value: "support", label: "Support" },
        { value: "complaint", label: "Complaint" },
        { value: "career", label: "Career" },
        { value: "service", label: "Service" },
        { value: "retail", label: "Retail" },
        { value: "wholesale", label: "Wholesale" },
    ]),
    inquiryReasons: getCrmUiText("inquiryReasons", [
        { value: "complaint", label: "Complaint" },
        { value: "delivery_issue", label: "Delivery Issue" },
        { value: "return_rma", label: "Return/RMA" },
        { value: "billing_refund", label: "Billing/Refund" },
        { value: "product_issue_defect", label: "Product Issue/Defect" },
        { value: "setup_how_to", label: "Setup/How-to" },
        { value: "general_inquiry", label: "General Inquiry" },
    ]),
};

function getLocalizedCrmStatus(status) {
    if (crmUiText.statusLabels && crmUiText.statusLabels[status]) {
        return crmUiText.statusLabels[status];
    }

    return status.charAt(0).toUpperCase() + status.slice(1);
}

function buildCrmPlaceholderOption(label) {
    return `<option value="">-- ${label} --</option>`;
}

function appendCrmSelectOptions(selectElement, options) {
    if (!selectElement || !Array.isArray(options)) {
        return;
    }

    options.forEach((option) => {
        selectElement.innerHTML += `<option value="${option.value}">${option.label}</option>`;
    });
}

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
                    toastr.success(response.message || crmUiText.messageTypeUpdatedSuccessfully);

                    $('div.edit-message-type[data-id="' + $('#message_id').val() + '"]').closest('td').find('.text-success').first().text($('#type-id').val());
                    $('#showTypeModal').modal('hide');
                } else {
                    toastr.error(response.message || crmUiText.failedToUpdateMessageType);
                }
            },
            error: function (xhr) {
                toastr.error(crmUiText.somethingWentWrong);
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
        .text(getLocalizedCrmStatus(status));
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

    ownerCell.text(ownerName || crmUiText.notAssigned);
}

const bulkConvertButton = document.querySelector(".bulk-convert-btn");
if (bulkConvertButton) {
    bulkConvertButton.addEventListener("click", function () {
        let ids = Array.from(document.querySelectorAll(".message-checkbox:checked"))
            .map(cb => cb.value);

        if (ids.length === 0) {
            Swal.fire(crmUiText.pleaseSelectAtLeastOneMessage);
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
                        title: crmUiText.successTitle,
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
                    Swal.fire(crmUiText.notConvertedTitle, data.message || crmUiText.noInquiryConverted, 'warning');
                }
            }).catch(err => {
                console.error(err);
                Swal.fire(crmUiText.errorTitle, crmUiText.somethingWentWrong, 'error');
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
        title: crmUiText.areYouSure,
        text: crmUiText.ignoreMessagePrompt,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: crmUiText.yesIgnoreIt,
        cancelButtonText: crmUiText.cancel
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
                        crmUiText.successTitle,
                        response.message,
                        'success'
                    )
                    updateRowStatus(id, 'ignored');
                    clearActionButtons(id);
                },
                error: function (xhr) {
                    Swal.fire(
                        crmUiText.errorTitle,
                        xhr.responseJSON?.message ?? crmUiText.somethingWentWrong,
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
        title: crmUiText.areYouSure,
        text: crmUiText.markSpamPrompt,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: crmUiText.yesMarkIt,
        cancelButtonText: crmUiText.cancel
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
                        crmUiText.successTitle,
                        response.message,
                        'success'
                    )
                    updateRowStatus(id, 'spam');
                    clearActionButtons(id);
                },
                error: function (xhr) {
                    Swal.fire(
                        crmUiText.errorTitle,
                        xhr.responseJSON?.message ?? crmUiText.somethingWentWrong,
                        'error'
                    )
                }
            });
        }
    });
});


$(document).on('click', '.assign-owner-btn', function () {
    let ticketId = $(this).data('id');
    let ownerId = $(this).data('owner-id') || $(this).closest('tr').data('owner-id') || '';
    let form = $('#updateTicketOwnerForm');

    form.find('#owner_ticket_id').val(ticketId);
    form.find('#owner-employee-id').empty().append(`<option value="">${crmUiText.selectSupervisor}</option>`);
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
            Swal.fire(crmUiText.successTitle, response.message, 'success');
            const ticketId = form.find('#owner_ticket_id').val();
            const ownerName = form.find('#owner-employee-id option:selected').text().trim();
            updateOwnerCell(ticketId, ownerName);
        },
        error: function (xhr) {
            Swal.fire(
                crmUiText.errorTitle,
                xhr.responseJSON?.message ?? crmUiText.somethingWentWrong,
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
    $('#owner-employee-id').html(`<option value="">${crmUiText.loading}</option>`);

    $.ajax({
        url: employeeRouteUrl,
        type: "GET",
        data: { department_id: deptId || '', assignment: 'owner' },
        success: function (res) {
            const owners = mapEmployeeResponse(res);
            $('#owner-employee-id').html(`<option value="">${crmUiText.selectSupervisor}</option>`);

            $.each(owners, function (key, owner) {
                const selected = selectedOwnerId && String(selectedOwnerId) === String(owner.id) ? 'selected' : '';
                $('#owner-employee-id').append(`<option value="${owner.id}" ${selected}>${owner.name}</option>`);
            });
        },
        error: function () {
            $('#owner-employee-id').html(`<option value="">${crmUiText.selectSupervisor}</option>`);
        }
    });
}

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
            Swal.fire(crmUiText.successTitle, response.message, 'success');
        },
        error: function (xhr) {
            Swal.fire(
                crmUiText.errorTitle,
                xhr.responseJSON?.message ?? crmUiText.somethingWentWrong,
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
                crmUiText.successTitle,
                response.message,
                'success'
            );
        },
        error: function (xhr) {
            Swal.fire(
                crmUiText.errorTitle,
                xhr.responseJSON?.message ?? crmUiText.somethingWentWrong,
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
        title: crmUiText.areYouSure,
        text: crmUiText.deleteMessagePrompt,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: crmUiText.yesDeleteIt,
        cancelButtonText: crmUiText.cancel
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: "DELETE",
                success: function (response) {
                    Swal.fire(crmUiText.deletedTitle, response.message, 'success');
                    $("#row-" + id).fadeOut();
                },
                error: function (xhr) {
                    Swal.fire(crmUiText.errorTitle, xhr.responseJSON?.message || crmUiText.somethingWentWrong, 'error');
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
                        title: crmUiText.successTitle,
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
                        title: crmUiText.notConvertedTitle,
                        text: data.message || crmUiText.somethingWentWrong,
                    });
                }
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: crmUiText.errorTitle,
                    text: crmUiText.somethingWentWrong
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

    const toggleBulkReasonByTypeAndSubtype = () => {
        const type = typeSelect.value;
        const subType = subTypeSelect.value;

        if (type === "ticket" && subType === "retail") {
            bulkReasonWrapper.style.display = "block";
            bulkReasonSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectReason);
            appendCrmSelectOptions(bulkReasonSelect, crmUiText.inquiryReasons);
            return;
        }

        bulkReasonWrapper.style.display = "none";
        bulkReasonSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectReason);
    };

    // Type change
    typeSelect.addEventListener("change", function () {
        const type = this.value;

        subTypeSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectSubType);
        bulkReasonWrapper.style.display = "none";
        bulkReasonSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectReason);

        if (type === "lead") {
            subTypeWrapper.style.display = "block";
            appendCrmSelectOptions(subTypeSelect, crmUiText.leadSubTypes);
        } else if (type === "ticket") {
            subTypeWrapper.style.display = "block";
            appendCrmSelectOptions(subTypeSelect, crmUiText.ticketSubTypes);
        } else {
            subTypeWrapper.style.display = "none";
        }

        toggleBulkReasonByTypeAndSubtype();
    });

    // Sub-type change using event delegation
    document.addEventListener("change", function (e) {
        if (e.target && e.target.id === "bulkSubTypeSelect") {
            toggleBulkReasonByTypeAndSubtype();
        }
    });
});


const typeSelect = document.getElementById("typeSelect");
const subTypeWrapper = document.getElementById("subTypeWrapper");
const subTypeSelect = document.getElementById("subTypeSelect");
const reasonWrapper = document.getElementById("reasonWrapper");
const reasonSelect = document.getElementById("reasonSelect");

if (typeSelect && subTypeWrapper && subTypeSelect && reasonWrapper && reasonSelect) {
    const renderReasonOptions = () => {
        reasonSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectReason);
        appendCrmSelectOptions(reasonSelect, crmUiText.inquiryReasons);
    };

    const toggleReasonByTypeAndSubtype = () => {
        if (typeSelect.value === "ticket" && subTypeSelect.value === "retail") {
            reasonWrapper.style.display = "block";
            renderReasonOptions();
            return;
        }

        reasonWrapper.style.display = "none";
        reasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';
    };

    typeSelect.addEventListener("change", function () {
        let type = this.value;

        subTypeSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectSubType);
        reasonWrapper.style.display = "none";
        reasonSelect.innerHTML = buildCrmPlaceholderOption(crmUiText.selectReason);

        if (type === "lead") {
            subTypeWrapper.style.display = "block";
            appendCrmSelectOptions(subTypeSelect, crmUiText.leadSubTypes);
        } else if (type === "ticket") {
            subTypeWrapper.style.display = "block";
            appendCrmSelectOptions(subTypeSelect, crmUiText.ticketSubTypes);
        } else {
            subTypeWrapper.style.display = "none";
        }

        toggleReasonByTypeAndSubtype();
    });

    subTypeSelect.addEventListener("change", function () {
        toggleReasonByTypeAndSubtype();
    });
}




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

let employeeRouteUrl = $('#getEmployeeRoute').data('url');

function loadEmployees(deptId, headId = null) {
    if (!deptId) {
        $("#ticket-employee-id").html(`<option value="">${crmUiText.selectEmployee}</option>`);
        return;
    }

    $("#ticket-employee-id").html(`<option value="">${crmUiText.loading}</option>`);

    $.ajax({
        url: employeeRouteUrl,
        type: "GET",
        data: { department_id: deptId, head_id: headId, assignment: 'employee' },
        success: function (res) {
            const employees = mapEmployeeResponse(res);
            $("#ticket-employee-id").html(`<option value="">${crmUiText.selectEmployee}</option>`);
            $.each(employees, function (key, emp) {
                $("#ticket-employee-id").append(`<option value="${emp.id}">${emp.name}</option>`);
            });
        },
        error: function () {
            $("#ticket-employee-id").html(`<option value="">${crmUiText.selectEmployee}</option>`);
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
                            $('#task-submit-btn-' + leadId).text(crmUiText.saveTask);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = crmUiText.genericTryAgain;
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
            $('#task-submit-btn-' + leadId).text(crmUiText.updateTask);

            // Set method to PUT for updates
            $('#method-' + leadId).val('PUT');

            // Open Task tab
            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: crmUiText.areYouSure,
                text: crmUiText.markTaskCompletePrompt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: crmUiText.yesMarkComplete,
                cancelButtonText: crmUiText.cancel
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
                            var errorMessage = crmUiText.genericTryAgain;
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
                            $('#task-submit-btn-' + leadId).text(crmUiText.saveTask);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = crmUiText.genericTryAgain;
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
            $('#task-submit-btn-' + leadId).text(crmUiText.updateTask);

            $('#method-' + leadId).val('PUT');

            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn-inbox', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: crmUiText.areYouSure,
                text: crmUiText.markTaskCompletePrompt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: crmUiText.yesMarkComplete,
                cancelButtonText: crmUiText.cancel
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
                            var errorMessage = crmUiText.genericTryAgain;
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
                            $('#task-submit-btn-' + leadId).text(crmUiText.saveTask);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var errorMessage = crmUiText.genericTryAgain;
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
            $('#task-submit-btn-' + leadId).text(crmUiText.updateTask);

            $('#method-' + leadId).val('PUT');

            $('.action-btn[data-collapse-target="task"]').trigger('click');
        });

        // Handle task complete
        $(document).on('click', '.task-complete-btn-inbox', function () {
            var taskId = $(this).data('task-id');

            Swal.fire({
                title: crmUiText.areYouSure,
                text: crmUiText.markTaskCompletePrompt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: crmUiText.yesMarkComplete,
                cancelButtonText: crmUiText.cancel
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
                            var errorMessage = crmUiText.genericTryAgain;
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
                <strong>${crmUiText.nameLabel}:</strong> ${data.name ?? '-'}<br>
                <strong>${crmUiText.emailLabel}:</strong> ${data.email ?? '-'}<br>
                <strong>${crmUiText.phoneLabel}:</strong> ${data.phone ?? '-'}
            `);
        }).fail(function () {
            toastr.error(crmUiText.failedToLoadUserInfo);
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
                toastr.success(crmUiText.userConnectedSuccessfully);
            } else {
                toastr.error(crmUiText.somethingWentWrong);
            }
        }).fail(function () {
            toastr.error(crmUiText.requestFailed);
        });
    });
});
