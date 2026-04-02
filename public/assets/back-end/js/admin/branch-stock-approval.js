(function () {
    'use strict';

    const configNode = document.getElementById('branch-stock-approval-config');
    if (!configNode) {
        return;
    }

    const showConfirm = (options, onConfirm) => {
        if (window.Swal) {
            window.Swal.fire({
                title: options.title,
                text: options.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmColor || '#377dff',
                cancelButtonColor: '#a4aab3',
                confirmButtonText: options.confirmText,
                cancelButtonText: options.cancelText,
            }).then((result) => {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
            return;
        }

        if (window.confirm(options.text || options.title)) {
            onConfirm();
        }
    };

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.branch-stock-decision-form');
        if (!form) {
            return;
        }

        event.preventDefault();

        const isApprove = form.dataset.confirmType === 'approve';
        showConfirm({
            title: isApprove ? configNode.dataset.approveTitle : configNode.dataset.rejectTitle,
            text: isApprove ? configNode.dataset.approveText : configNode.dataset.rejectText,
            confirmText: isApprove ? configNode.dataset.approveConfirm : configNode.dataset.rejectConfirm,
            cancelText: configNode.dataset.cancel,
            confirmColor: isApprove ? '#29c17e' : '#ed4c78',
        }, () => form.submit());
    });
})();
