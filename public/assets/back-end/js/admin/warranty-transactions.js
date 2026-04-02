(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('click', '.view-history-btn', function () {
            const button = $(this);
            const serial = button.data('serial');
            const modal = $('#historyModal');
            const serialNode = modal.find('#modalSerial');
            const bodyNode = modal.find('#modalBody');
            const urlTemplate = modal.data('historyUrlTemplate');

            if (!serial || !urlTemplate) {
                return;
            }

            serialNode.text(serial);
            bodyNode.html('');

            $.get(urlTemplate.replace('__SERIAL__', encodeURIComponent(serial)), function (data) {
                bodyNode.html(data);
            });
        });
    });
})();
