'use strict';

$(document).ready(function () {
    const form = $('#submit-create-role');
    const warningMessage = $('#select-minimum-one-box-message').data('warning') || 'Select at least one permission.';
    function initPermissionTooltips() {
        const selector = '.permission-tip';

        if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
            document.querySelectorAll(selector).forEach(function (element) {
                const tipText = (
                    element.getAttribute('data-tip') ||
                    element.getAttribute('title') ||
                    element.getAttribute('aria-label') ||
                    ''
                ).trim();

                if (tipText === '') {
                    return;
                }

                element.setAttribute('title', tipText);
                element.setAttribute('data-original-title', tipText);

                const instance = window.bootstrap.Tooltip.getInstance(element);
                if (instance) {
                    instance.dispose();
                }

                new window.bootstrap.Tooltip(element, {
                    container: 'body',
                    trigger: 'hover focus',
                    customClass: 'permission-tip-tooltip',
                    placement: element.getAttribute('data-placement') || 'right',
                });
            });
            return;
        }

        if (typeof $.fn.tooltip === 'function') {
            $(selector).each(function () {
                const tipText = (
                    $(this).attr('data-tip') ||
                    $(this).attr('title') ||
                    $(this).attr('aria-label') ||
                    ''
                ).trim();
                if (tipText !== '') {
                    $(this).attr('title', tipText).attr('data-original-title', tipText);
                }
            });

            $(selector).tooltip('dispose').tooltip({
                container: 'body',
                trigger: 'hover focus',
                template: '<div class="tooltip permission-tip-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
            });
        }
    }

    function moduleCheckboxes(moduleKey) {
        return $(`.permission-checkbox[data-module="${moduleKey}"]`);
    }

    function groupCheckboxes(moduleKey, groupKey) {
        return $(`.permission-checkbox[data-module="${moduleKey}"][data-group="${groupKey}"]`);
    }

    function syncGroupMasterCheckbox(moduleKey, groupKey) {
        const allGroupCheckboxes = groupCheckboxes(moduleKey, groupKey);
        const groupMaster = $(`.group-master-checkbox[data-module="${moduleKey}"][data-group="${groupKey}"]`);

        if (!allGroupCheckboxes.length) {
            groupMaster.prop('checked', false);
            return;
        }

        groupMaster.prop('checked', allGroupCheckboxes.length === allGroupCheckboxes.filter(':checked').length);
    }

    function syncModuleMasterCheckbox(moduleKey) {
        const allModuleCheckboxes = moduleCheckboxes(moduleKey);
        const moduleMaster = $(`#master_${moduleKey}`);

        if (!allModuleCheckboxes.length) {
            moduleMaster.prop('checked', false);
            return;
        }

        moduleMaster.prop('checked', allModuleCheckboxes.length === allModuleCheckboxes.filter(':checked').length);
    }

    function syncModuleGroups(moduleKey) {
        $(`.group-master-checkbox[data-module="${moduleKey}"]`).each(function () {
            syncGroupMasterCheckbox(moduleKey, $(this).data('group'));
        });
    }

    $('.module-master-checkbox').on('change', function () {
        const moduleKey = $(this).data('module');
        const isChecked = $(this).is(':checked');

        moduleCheckboxes(moduleKey).prop('checked', isChecked);
        syncModuleGroups(moduleKey);
    });

    $('.group-master-checkbox').on('change', function () {
        const moduleKey = $(this).data('module');
        const groupKey = $(this).data('group');
        const isChecked = $(this).is(':checked');

        groupCheckboxes(moduleKey, groupKey).prop('checked', isChecked);
        syncModuleMasterCheckbox(moduleKey);
    });

    $('.permission-checkbox').on('change', function () {
        const moduleKey = $(this).data('module');
        const groupKey = $(this).data('group');

        syncGroupMasterCheckbox(moduleKey, groupKey);
        syncModuleMasterCheckbox(moduleKey);
    });

    $('.group-master-checkbox').each(function () {
        syncGroupMasterCheckbox($(this).data('module'), $(this).data('group'));
    });

    $('.module-master-checkbox').each(function () {
        syncModuleMasterCheckbox($(this).data('module'));
    });

    initPermissionTooltips();

    form.on('submit', function (e) {
        const selected = $("input[name='permissions[]']:checked");
        if (!selected.length) {
            toastr.warning(warningMessage, {
                closeButton: true,
                progressBar: true,
            });
            e.preventDefault();
            return false;
        }

        return true;
    });
});
