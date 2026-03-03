'use strict';

(function ($) {
    $(document).on('click', '.escalate-btn', function () {
        const leadId = $(this).data('lead-id');
        $('#escalateLeadId').val(leadId);
        $('#escalateLeadModal').modal('show');
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
})(jQuery);
