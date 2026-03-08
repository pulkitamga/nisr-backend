'use strict';

const restrictionLevels = ['country', 'state', 'city', 'area'];
const restrictionToggleIds = {
    country: '#country-area',
    state: '#state-area',
    city: '#city-area',
    area: '#area',
    zip: '#zip-area',
};

function getRestrictionMessage(id, fallback) {
    return String($(id).data('text') || fallback);
}

function getRestrictionStates() {
    return {
        country: $(restrictionToggleIds.country).is(':checked') ? 1 : 0,
        state: $(restrictionToggleIds.state).is(':checked') ? 1 : 0,
        city: $(restrictionToggleIds.city).is(':checked') ? 1 : 0,
        area: $(restrictionToggleIds.area).is(':checked') ? 1 : 0,
        zip: $(restrictionToggleIds.zip).is(':checked') ? 1 : 0,
    };
}

function getRestrictionStatesFromData() {
    return {
        country: Number($('#get-country-status').data('value') || 0),
        state: Number($('#get-state-status').data('value') || 0),
        city: Number($('#get-city-status').data('value') || 0),
        area: Number($('#get-area-status').data('value') || 0),
        zip: Number($('#get-zip-status').data('value') || 0),
    };
}

function validateRestrictionTransition(level, targetStatus, states) {
    const parentMessage = getRestrictionMessage('#delivery-restriction-parent-message', 'Please enable the parent level first.');
    const childMessage = getRestrictionMessage('#delivery-restriction-child-message', 'Please disable the child level first.');

    if (targetStatus === 1) {
        if (level === 'state' && states.country !== 1) {
            return parentMessage;
        }
        if (level === 'city' && (states.country !== 1 || states.state !== 1)) {
            return parentMessage;
        }
        if (level === 'area' && (states.country !== 1 || states.state !== 1 || states.city !== 1)) {
            return parentMessage;
        }
    } else {
        if (level === 'country' && (states.state === 1 || states.city === 1 || states.area === 1)) {
            return childMessage;
        }
        if (level === 'state' && (states.city === 1 || states.area === 1)) {
            return childMessage;
        }
        if (level === 'city' && states.area === 1) {
            return childMessage;
        }
    }

    return null;
}

function syncRestrictionPanels() {
    const states = getRestrictionStates();

    $('.country-disable').toggle(states.country === 1);
    $('.state-disable').toggle(states.country === 1 && states.state === 1);
    $('.city-disable').toggle(states.country === 1 && states.state === 1 && states.city === 1);
    $('.area-disable').toggle(states.country === 1 && states.state === 1 && states.city === 1 && states.area === 1);
    $('.zip-disable').toggle(states.zip === 1);
}

function openRestrictionToggleModal($toggle) {
    const rootPath = $('#get-root-path-for-toggle-modal-image').data('path');
    const modalId = $toggle.data('modal-id');
    const toggleId = $toggle.data('toggle-id');
    const onImage = rootPath + '/' + $toggle.data('on-image');
    const offImage = rootPath + '/' + $toggle.data('off-image');
    const onTitle = $toggle.data('on-title');
    const offTitle = $toggle.data('off-title');
    const onMessage = $toggle.data('on-message');
    const offMessage = $toggle.data('off-message');

    toggleModal(modalId, toggleId, onImage, offImage, onTitle, offTitle, onMessage, offMessage);
}

function initializeHierarchyToggleGuard() {
    const hierarchyToggleSelector = '#country-area, #state-area, #city-area, #area';

    // Remove the global generic click behavior for these specific toggles.
    $(hierarchyToggleSelector).off('click');

    $(document).on('click', hierarchyToggleSelector, function (event) {
        event.preventDefault();

        const $toggle = $(this);
        const level = String($toggle.data('level') || '');
        if (!restrictionLevels.includes(level)) {
            openRestrictionToggleModal($toggle);
            return;
        }

        const states = getRestrictionStates();
        const targetStatus = $toggle.is(':checked') ? 0 : 1;
        const validationMessage = validateRestrictionTransition(level, targetStatus, states);

        if (validationMessage) {
            toastr.error(validationMessage);
            return;
        }

        openRestrictionToggleModal($toggle);
    });
}

$('.zip_code').on('click', function () {
    if ($.trim($("input[name='zipcode']").val()) === '') {
        toastr.error($('#get-zip-code-text').data('error'));
    }
});

$(".js-example-responsive").select2({
    theme: "classic",
    placeholder: $('#get-select-country-text').data('text'),
    allowClear: true,
});

$(function () {
    const initialStates = getRestrictionStatesFromData();
    $(restrictionToggleIds.country).prop('checked', initialStates.country === 1);
    $(restrictionToggleIds.state).prop('checked', initialStates.state === 1);
    $(restrictionToggleIds.city).prop('checked', initialStates.city === 1);
    $(restrictionToggleIds.area).prop('checked', initialStates.area === 1);
    $(restrictionToggleIds.zip).prop('checked', initialStates.zip === 1);

    syncRestrictionPanels();
    initializeHierarchyToggleGuard();

    // Keep panel visibility in sync with current toggle states.
    $(document).on('change', '#country-area, #state-area, #city-area, #area, #zip-area', function () {
        syncRestrictionPanels();
    });
});
