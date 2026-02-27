'use strict';
$('.filter-tickets').on('change', function () {
    let param = $(this).data('value');
    let value = $(this).val();
    let text = window.location;
    let redirectTo = '';
    let polished = removeURLParameter(text.toString(), param);
    if (polished.includes('?')) {
        redirectTo = polished + '&' + param + '=' + value;
    } else {
        redirectTo = polished + '?' + param + '=' + value;
    }
    location.href = redirectTo;
})
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

$(document).on('change', '.support-ticket-status-select', function () {
    const select = $(this);
    const ticketId = select.data('ticket-id');
    const selectedStatusId = select.val();
    const previousStatusId = select.data('current-status');
    const selectedStatusName = select.find('option:selected').text().trim();
    const updateRoute = $('#support-ticket-status-route').data('url');
    const confirmTextSource = $('#get-confirm-and-cancel-button-text');

    if (!updateRoute || !ticketId || !selectedStatusId) {
        return;
    }

    if (String(previousStatusId) === String(selectedStatusId)) {
        return;
    }

    Swal.fire({
        title: confirmTextSource.data('sure') || 'Are you sure?',
        text: selectedStatusName,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmTextSource.data('confirm') || 'Yes',
        cancelButtonText: confirmTextSource.data('cancel') || 'No',
    }).then((result) => {
        if (!result.isConfirmed) {
            select.val(previousStatusId);
            return;
        }

        select.prop('disabled', true);
        $.ajax({
            url: updateRoute,
            method: 'POST',
            data: {
                id: ticketId,
                status: selectedStatusId,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                toastr.success(response?.message || 'Status updated successfully.');
                select.data('current-status', selectedStatusId);
            },
            error: function (jqXHR) {
                select.val(previousStatusId);
                toastr.error(jqXHR.responseJSON?.message || 'Request failed.');
            },
            complete: function () {
                select.prop('disabled', false);
            }
        });
    });
});

$(document).on('change', '.support-ticket-priority-select', function () {
    const select = $(this);
    const ticketId = select.data('ticket-id');
    const selectedPriority = String(select.val() || '').toLowerCase();
    const previousPriority = String(select.data('current-priority') || '').toLowerCase();
    const selectedPriorityName = select.find('option:selected').text().trim();
    const updateRoute = $('#support-ticket-priority-route').data('url');
    const confirmTextSource = $('#get-confirm-and-cancel-button-text');

    if (!updateRoute || !ticketId || !selectedPriority) {
        return;
    }

    if (selectedPriority === previousPriority) {
        return;
    }

    Swal.fire({
        title: confirmTextSource.data('sure') || 'Are you sure?',
        text: selectedPriorityName,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmTextSource.data('confirm') || 'Yes',
        cancelButtonText: confirmTextSource.data('cancel') || 'No',
    }).then((result) => {
        if (!result.isConfirmed) {
            select.val(previousPriority);
            return;
        }

        select.prop('disabled', true);
        $.ajax({
            url: updateRoute,
            method: 'POST',
            data: {
                id: ticketId,
                priority: selectedPriority,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                toastr.success(response?.message || 'Priority updated successfully.');
                select.data('current-priority', selectedPriority);
            },
            error: function (jqXHR) {
                select.val(previousPriority);
                toastr.error(jqXHR.responseJSON?.message || 'Request failed.');
            },
            complete: function () {
                select.prop('disabled', false);
            }
        });
    });
});


document.querySelectorAll('.statusForm').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault(); // prevent normal submit

        let btn = this.querySelector('button');
        let actionText = btn.textContent.trim(); // Close / Open
        Swal.fire({
            title: `Are you sure you want to ${actionText.toLowerCase()} this ticket?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX submit
                let formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(res => res.json())
                    .then(res => {
                        Swal.fire({
                            title: 'Success',
                            text: res.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload(); // reload to update status button
                        });
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    });
            }
        });
    });
});
$(document).on('click', '[data-target="#showSupportFollowUpModal"]', function () {
    let ticketId = $(this).data('ticket-id');
    let departmentId = $(this).data('department-id');
    let employeeId = $(this).data('employee-id');

    // set values inside modal hidden fields
    $('#support-follow-up-ticket-id').val(ticketId);
    $('#support-follow-up-department-id').val(departmentId);
    $('#support-follow-up-employee-id').val(employeeId);
});

$(document).on("ready", function () {
    $('#showSupportFollowUpModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const ticketId = button.data('ticket-id');
        const departmentId = button.data('department-id');
        const employeeId = button.data('employee-id');
        $('#support-follow-up-ticket-id').val(ticketId);
        $('#support-follow-up-department-id').val(departmentId);
        $('#support-follow-up-employee-id').val(employeeId);
        $('#support-task-name').val('');
        $('#support-task-description').val('');
        $('#support-task-due-date').val('');
        $('#support-task-status').val('pending');
        $('#support-add-to-calendar').prop('checked', false);
    });

    $('#updateSupportTicketFollowUpForm').on('submit', function (event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message || 'Follow-up updated successfully!');
                    $('#showSupportFollowUpModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function (jqXHR) {
                toastr.error(jqXHR.responseJSON?.message || 'Request failed.');
            }
        });
    });
});

$(document).on("ready", function () {
    $('#complain-follow-up-status').on('change', function () {
        var status = $(this).val();
        $('#complain-ticket-next-follow-up-date-row').removeClass().addClass('row d-none');
        if (status == 5 || status == 39) { // In Progress
            $('#complain-ticket-next-follow-up-date-row').removeClass().addClass('row');
        }
    });

    $('#showComplainFollowUpModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const ticketId = button.data('ticket-id');
        const departmentId = button.data('department-id');
        const employeeId = button.data('employee-id');
        $('#support-follow-up-ticket-id').val(ticketId);
        $('#support-follow-up-department-id').val(departmentId);
        $('#support-follow-up-employee-id').val(employeeId);
    });

    $('#updateComplainTicketFollowUpForm').on('submit', function (event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message || 'Follow-up updated successfully!');
                    $('#showComplainFollowUpModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function (jqXHR) {
                toastr.error(jqXHR.responseJSON?.message || 'Request failed.');
            }
        });
    });
});


$(document).on('click', '[data-target="#showWholesaleFollowUpModal"]', function () {
    let button = $(this);
    $('#wholesale-follow-up-ticket-id').val(button.data('ticket-id'));
    $('#wholesale-follow-up-department-id').val(button.data('department-id'));
    $('#wholesale-follow-up-employee-id').val(button.data('employee-id'));
});

$('#wholesale-follow-up-status').on('change', function () {
    let status = $(this).val();
    $('#wholesale-ticket-next-follow-up-date-row').addClass('d-none');
    if (status == 59) { // In Progress
        $('#wholesale-ticket-next-follow-up-date-row').removeClass('d-none');
    }
});

$('#updateWholesaleFollowUpForm').on('submit', function (e) {
    e.preventDefault();
    let form = $(this);

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function (response) {
            if (response.success) {
                toastr.success('Follow-up updated successfully!');
                $('#showWholesaleFollowUpModal').modal('hide');
                location.reload();
            } else {
                toastr.error(response.message || 'Something went wrong.');
            }
        },
        error: function (jqXHR) {
            toastr.error(jqXHR.responseJSON?.message || 'Request failed.');
        }
    });
});


$(document).on('click', '.escalate-btn', function () {
    let ticketId = $(this).data('ticket-id');
    $('#escalateTicketId').val(ticketId);
    $('#escalateTicketModal').modal('show');
});

// Form submission with confirmation
$('#escalateTicketForm').submit(function (e) {
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
