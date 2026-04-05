"use strict";

$(document).ready(function () {
    let activeId = $('.select_shipping_address.active').attr('id');
    if (activeId) {
        let shipping_value = $('.selected_' + activeId).val();
        shipping_method_select(shipping_value)
    }

    let billingsActiveId = $('.select_billing_address.active').attr('id');
    if (billingsActiveId) {
        let billing_value = $('.selected_' + billingsActiveId).val();
        billing_method_select(billing_value)
    }

    try {
        initializePhoneInput(".phone-input-with-country-picker-3", ".country-picker-phone-number-3");
    } catch (error) { }

    try {
        initializePhoneInput(".phone-input-with-country-picker-2", ".country-picker-phone-number-2");
    } catch (error) { }

    syncCheckoutRequiredStates();
})

let messageUpdateThisAddress = $('#message-update-this-address').data('text');
let messagePleaseFillOutThisField = $('#message-please-fill-out-this-field').data('text');

const checkoutFieldSelectors = {
    contact_person_name: '#name',
    phone: '#phone',
    email: '#email',
    delivery_type: '.delivery-radio-btn',
    pickup_branch_id: '#pickup_branch_id',
    address_type: '#address_type',
    country: '#country',
    state: '#state_id',
    state_id: '#state_id',
    city: '#city_id',
    city_id: '#city_id',
    area: '#area',
    zip: '[name="zip"]',
    address: '#address',
    billing_contact_person_name: '#billing_contact_person_name',
    billing_phone: '#billing_phone',
    billing_contact_email: '#billing_contact_email',
    billing_address_type: '#billing_address_type',
    billing_country: '#billing_country',
    billing_state: '#billing_state_id',
    billing_state_id: '#billing_state_id',
    billing_city: '#billing_city_id',
    billing_city_id: '#billing_city_id',
    billing_area: '#billing_area',
    billing_zip: '[name="billing_zip"]',
    billing_address: '#billing_address',
    customer_password: '#customer_password',
    customer_confirm_password: '#customer_confirm_password'
};

function getCheckoutFieldTarget(fieldName) {
    const selector = checkoutFieldSelectors[fieldName];
    return selector ? $(selector).first() : $();
}

function getCheckoutFieldContainer($target) {
    if (!$target || !$target.length) {
        return $();
    }

    if ($target.hasClass('delivery-radio-btn')) {
        return $target.closest('.form-group');
    }

    const $formGroup = $target.closest('.form-group');
    return $formGroup.length ? $formGroup : $target.parent();
}

function clearCheckoutValidationState() {
    $('.checkout-field-error').remove();
    $('.delivery-radio-btn').removeClass('is-invalid');
    $('#address-form .is-invalid, #billing-address-form .is-invalid, .is_check_create_account_password_group .is-invalid')
        .removeClass('is-invalid')
        .removeAttr('aria-invalid');
}

function setCheckoutFieldInvalid(fieldName, message) {
    const $target = getCheckoutFieldTarget(fieldName);
    if (!$target.length) {
        return;
    }

    if ($target.hasClass('delivery-radio-btn')) {
        $target.addClass('is-invalid');
    } else {
        $target.addClass('is-invalid').attr('aria-invalid', 'true');
    }

    const $container = getCheckoutFieldContainer($target);
    if (!$container.length) {
        return;
    }

    $container.find('.checkout-field-error').remove();
    $('<div/>', {
        class: 'invalid-feedback d-block checkout-field-error',
        text: message
    }).appendTo($container);
}

function clearCheckoutFieldValidationByElement(element) {
    const $element = $(element);
    const $container = getCheckoutFieldContainer($element);
    $element.removeClass('is-invalid').removeAttr('aria-invalid');
    $container.find('.checkout-field-error').remove();
}

function toggleBillingAddressVisibility(showBillingAddress) {
    const $sameAsShipping = $('#same_as_shipping_address');
    const $billingWrapper = $('#hide_billing_address');

    if ($sameAsShipping.length) {
        $sameAsShipping.prop('checked', !showBillingAddress);
    }

    if ($billingWrapper.length) {
        $billingWrapper.toggle(showBillingAddress);
    }
}

function focusCheckoutField(fieldName) {
    const $target = getCheckoutFieldTarget(fieldName);
    if (!$target.length) {
        return;
    }

    if (String(fieldName).startsWith('billing_')) {
        toggleBillingAddressVisibility(true);
    }

    if (fieldName === 'pickup_branch_id') {
        $('#pickup_radio').prop('checked', true);
        togglePickupBranchVisibility();
    }

    if (['address_type', 'country', 'state', 'state_id', 'city', 'city_id', 'area', 'zip', 'address'].includes(fieldName)) {
        $('#delivery_radio').prop('checked', true);
        togglePickupBranchVisibility();
    }

    const scrollToTarget = () => {
        const offsetTop = Math.max(($target.offset()?.top || 0) - 140, 0);
        $('html, body').animate({ scrollTop: offsetTop }, 250);
        $target.trigger('focus');
    };

    window.setTimeout(scrollToTarget, 50);
}

function renderCheckoutErrors(responseData) {
    clearCheckoutValidationState();

    const fieldErrors = responseData?.field_errors ?? {};
    const fieldNames = Object.keys(fieldErrors);

    fieldNames.forEach(function (fieldName) {
        setCheckoutFieldInvalid(fieldName, fieldErrors[fieldName]);
    });

    const focusField = responseData?.focus_field || fieldNames[0];
    if (focusField) {
        focusCheckoutField(focusField);
    }

    const summaryMessage = responseData?.message || responseData?.errors;
    if (summaryMessage) {
        toastr.error(summaryMessage, {
            CloseButton: true,
            ProgressBar: true
        });
    }
}

function setFieldRequired(selector, isRequired, indicatorField) {
    const $elements = $(selector);
    if ($elements.length) {
        $elements.prop('required', !!isRequired);
    }

    if (indicatorField) {
        $(`[data-required-indicator="${indicatorField}"]`).toggleClass('d-none', !isRequired);
    }
}

function syncCheckoutRequiredStates() {
    const physicalProduct = $('#physical_product').val();
    const deliveryType = $('input[name="delivery_type"]:checked').val();
    const isDelivery = physicalProduct === 'yes' && deliveryType !== 'pickup';
    const isGuestShipping = $('#email').length > 0;
    const zipRestrictionEnabled = Number($('#system-zip-restrict-status').data('value') || 0) === 1;
    const billingVisible = $('#billing-address-form').length > 0 && $('#hide_billing_address').is(':visible');
    const guestBilling = $('#billing_contact_email').length > 0;
    const createAccountEnabled = $('#is_check_create_account').is(':checked');

    setFieldRequired('#name', isDelivery, 'contact_person_name');
    setFieldRequired('#phone', isDelivery, 'phone');
    setFieldRequired('#email', isDelivery && isGuestShipping, 'email');
    setFieldRequired('#address_type', isDelivery, 'address_type');
    setFieldRequired('#country', isDelivery, 'country');
    setFieldRequired('#state_id', isDelivery, 'state');
    setFieldRequired('#city_id', isDelivery, 'city');
    setFieldRequired('#area', isDelivery, 'area');
    setFieldRequired('[name="zip"]', isDelivery && zipRestrictionEnabled, 'zip');
    setFieldRequired('#address', isDelivery, 'address');
    setFieldRequired('#pickup_branch_id', deliveryType === 'pickup', 'pickup_branch_id');
    setFieldRequired('#latitude', false);
    setFieldRequired('#longitude', false);

    setFieldRequired('#billing_contact_person_name', billingVisible, 'billing_contact_person_name');
    setFieldRequired('#billing_phone', billingVisible, 'billing_phone');
    setFieldRequired('#billing_contact_email', billingVisible && guestBilling, 'billing_contact_email');
    setFieldRequired('#billing_address_type', billingVisible, 'billing_address_type');
    setFieldRequired('#billing_country', billingVisible, 'billing_country');
    setFieldRequired('#billing_state_id', billingVisible, 'billing_state');
    setFieldRequired('#billing_city_id', billingVisible, 'billing_city');
    setFieldRequired('#billing_area', false, 'billing_area');
    setFieldRequired('[name="billing_zip"]', false, 'billing_zip');
    setFieldRequired('#billing_address', billingVisible, 'billing_address');
    setFieldRequired('#billing_latitude', false);
    setFieldRequired('#billing_longitude', false);
    setFieldRequired('#customer_password', createAccountEnabled, 'customer_password');
    setFieldRequired('#customer_confirm_password', createAccountEnabled, 'customer_confirm_password');
}

function getClientValidationFieldName($field) {
    const fieldId = $field.attr('id');
    const fieldName = $field.attr('name');

    if (fieldId === 'phone') {
        return 'phone';
    }

    if (fieldId === 'billing_phone') {
        return 'billing_phone';
    }

    return fieldName || fieldId || null;
}

function collectClientValidationErrors(formSelector) {
    const errors = {};

    $(formSelector).find('[required]').filter(function () {
        return $(this).is(':visible') && !$(this).is(':disabled');
    }).each(function () {
        const $field = $(this);
        const fieldName = getClientValidationFieldName($field);

        if (!fieldName || errors[fieldName]) {
            return;
        }

        if (!$field.val()) {
            errors[fieldName] = messagePleaseFillOutThisField;
        }
    });

    return errors;
}

function normalizeApiCollection(response, key) {
    if (response && Array.isArray(response[key])) {
        return response[key];
    }
    if (Array.isArray(response)) {
        return response;
    }
    return [];
}

function areaOptionValue(areaItem) {
    if (typeof areaItem === 'object' && areaItem !== null) {
        return areaItem.id ?? areaItem.name ?? '';
    }
    return areaItem ?? '';
}

function areaOptionText(areaItem) {
    if (typeof areaItem === 'object' && areaItem !== null) {
        return areaItem.name ?? areaItem.id ?? '';
    }
    return areaItem ?? '';
}

function resolveCountryCodeFromSelect(selectSelector, countryValue) {
    const normalizedInput = String(countryValue ?? '').trim().toLowerCase();
    let resolvedCode = '';

    if (normalizedInput !== '') {
        $(selectSelector + ' option').each(function () {
            const optionValue = String($(this).val() ?? '').trim();
            const optionText = String($(this).text() ?? '').trim().toLowerCase();

            if (!optionValue) {
                return;
            }

            if (optionValue.toLowerCase() === normalizedInput || optionText === normalizedInput) {
                resolvedCode = optionValue;
                return false;
            }
        });
    }

    if (!resolvedCode && normalizedInput.length === 2) {
        resolvedCode = normalizedInput.toUpperCase();
    }

    if (!resolvedCode) {
        const selectableOptions = $(selectSelector + ' option').filter(function () {
            return String($(this).val() ?? '').trim() !== '';
        });
        if (selectableOptions.length === 1) {
            resolvedCode = String(selectableOptions.first().val() ?? '').trim();
        }
    }

    return resolvedCode;
}

const addressItems = document.querySelectorAll('.select_shipping_address');
addressItems.forEach(item => {
    item.addEventListener('click', function () {
        const selectedAddressId = item.id;
        let shipping_value = $('.selected_' + selectedAddressId).val();
        $('.select_shipping_address').removeClass('active');
        $('#' + selectedAddressId).addClass('active')
        shipping_method_select(shipping_value)
    });
});

function shipping_method_select(get_value) {
    let shipping_method_id = $('.select_shipping_address.active input[name="shipping_method_id"]').val();
    let shipping_value = JSON.parse(get_value);
    console.log("Shipping value:", shipping_value);

    $('#name').val(shipping_value.contact_person_name);
    $('#phone').val(shipping_value.phone);
    $('#address').val(shipping_value.address);
    $('#zip').val(shipping_value.zip);
    $('#address_type').val(shipping_value.address_type);

    let countryCode = resolveCountryCodeFromSelect('#country', shipping_value.country);
    if (countryCode) {
        $('#country').val(countryCode);
    }

    $.get(getStatesURL, {
        country: countryCode
    }).done(function (response) {
        const states = normalizeApiCollection(response, 'states');
        $('#state_id').empty().append('<option value="">Select State</option>');
        $('#city_id').empty().append('<option value="">Select City</option>');
        $('#area').empty().append('<option value="">Select Area</option>');

        let selectedStateId = '';
        $.each(states, function (key, state) {
            const shippingStateName = String(shipping_value.state ?? '').trim().toLowerCase();
            let isMatch = String(state.name ?? '').trim().toLowerCase() === shippingStateName;
            if (isMatch) selectedStateId = state.id;

            $('#state_id').append(`<option value="${state.id}" data-name="${state.name}" ${isMatch ? 'selected' : ''}>${state.name}</option>`);
        });

        $('#state_name').val(shipping_value.state);
        $.get(getCitiesURL, {
            state_id: selectedStateId
        }).done(function (response) {
            const cities = normalizeApiCollection(response, 'cities');
            $('#city_id').empty().append('<option value="">Select City</option>');
            $('#area').empty().append('<option value="">Select Area</option>');

            let selectedCityId = '';
            $.each(cities, function (key, city) {
                const shippingCityName = String(shipping_value.city ?? '').trim().toLowerCase();
                let isMatch = String(city.name ?? '').trim().toLowerCase() === shippingCityName;
                if (isMatch) selectedCityId = city.id;

                $('#city_id').append(`<option value="${city.id}" data-name="${city.name}" ${isMatch ? 'selected' : ''}>${city.name}</option>`);
            });

            $('#city_name').val(shipping_value.city);
            $.get(getAreasURL, {
                city_id: selectedCityId
            }).done(function (response) {
                const areas = normalizeApiCollection(response, 'areas');
                $('#area').empty().append('<option value="">Select Area</option>');
                $.each(areas, function (key, area) {
                    const optionText = String(areaOptionText(area)).trim();
                    const optionValue = String(areaOptionValue(area)).trim();
                    const shippingAreaValue = String(shipping_value.area ?? '').trim();
                    let isMatch = optionText.toLowerCase() === shippingAreaValue.toLowerCase() || optionValue === shippingAreaValue;
                    $('#area').append(`<option value="${optionValue}" ${isMatch ? 'selected' : ''}>${optionText}</option>`);
                });
                $('#area').trigger('change');

            });
        });
    });
    let update_address = `
        <input type="hidden" name="shipping_method_id" id="shipping_method_id" value="${shipping_method_id}">
        <input type="checkbox" name="update_address" id="update_address"> ${messageUpdateThisAddress}`;
    $('#save_address_label').html(update_address);
    syncCheckoutRequiredStates();
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});

$(document).on('change', '#area', function () {
    let selectedOption = $(this).find('option:selected');
    let areaName = selectedOption.val();

    $.post(updateShippingCostRoute, {
        area_name: areaName,
    }).done(function (res) {
        if (res.shipping_cost !== undefined) {
            $('#cart-summary').load(location.href + ' #cart-summary > *');

        }
    }).fail(function (xhr) {
    });
});



const addressItemsBilling = document.querySelectorAll('.select_billing_address');
addressItemsBilling.forEach(item => {
    item.addEventListener('click', function () {
        const selectedBillingAddressId = item.id;
        let billing_value = $('.selected_' + selectedBillingAddressId).val();
        $('.select_billing_address').removeClass('active');
        $('#' + selectedBillingAddressId).addClass('active')
        billing_method_select(billing_value);
        console.log(billing_value)
    });
});


function billing_method_select(get_billing_value) {
    let billing_value = JSON.parse(get_billing_value);
    let billing_method_id = $('.select_billing_address.active input[name="billing_method_id"]').val();

    $('#billing_contact_person_name').val(billing_value.contact_person_name);
    $('#billing_phone').val(billing_value.phone);
    $('#billing_address').val(billing_value.address);
    $('#billing_zip').val(billing_value.zip);
    $('#billing_address_type').val(billing_value.address_type);

    let countryCode = resolveCountryCodeFromSelect('#billing_country', billing_value.country);
    if (countryCode) {
        $('#billing_country').val(countryCode);
    }

    $.get(getBillingStatesURL, {
        billing_country: countryCode
    }).done(function (response) {
        const states = normalizeApiCollection(response, 'states');
        $('#billing_state_id').empty().append('<option value="">Select State</option>');
        $('#billing_city_id').empty().append('<option value="">Select City</option>');
        $('#billing_area').empty().append('<option value="">Select Area</option>');

        let selectedStateId = '';
        $.each(states, function (key, state) {
            const billingStateName = String(billing_value.state ?? '').trim().toLowerCase();
            let isMatch = String(state.name ?? '').trim().toLowerCase() === billingStateName;
            if (isMatch) selectedStateId = state.id;

            $('#billing_state_id').append(`<option value="${state.id}" data-name="${state.name}" ${isMatch ? 'selected' : ''}>${state.name}</option>`);
        });

        $('#billing_state_name').val(billing_value.state);

        $.get(getBillingCitiesURL, {
            billing_state_id: selectedStateId
        }).done(function (response) {
            const cities = normalizeApiCollection(response, 'cities');
            $('#billing_city_id').empty().append('<option value="">Select City</option>');
            $('#billing_area').empty().append('<option value="">Select Area</option>');

            let selectedCityId = '';
            $.each(cities, function (key, city) {
                const billingCityName = String(billing_value.city ?? '').trim().toLowerCase();
                let isMatch = String(city.name ?? '').trim().toLowerCase() === billingCityName;
                if (isMatch) selectedCityId = city.id;

                $('#billing_city_id').append(`<option value="${city.id}" data-name="${city.name}" ${isMatch ? 'selected' : ''}>${city.name}</option>`);
            });

            $('#billing_city_name').val(billing_value.city);

            $.get(getBillingAreasURL, {
                billing_city_id: selectedCityId
            }).done(function (response) {
                const areas = normalizeApiCollection(response, 'areas');
                $('#billing_area').empty().append('<option value="">Select Area</option>');
                $.each(areas, function (key, area) {
                    const optionText = String(areaOptionText(area)).trim();
                    const optionValue = String(areaOptionValue(area)).trim();
                    const billingAreaValue = String(billing_value.area ?? '').trim();
                    let isMatch = optionText.toLowerCase() === billingAreaValue.toLowerCase() || optionValue === billingAreaValue;
                    $('#billing_area').append(`<option value="${optionValue}" ${isMatch ? 'selected' : ''}>${optionText}</option>`);
                });
            });
        });
    });

    let update_address_billing = `
        <input type="hidden" name="billing_method_id" id="billing_method_id" value="${billing_method_id}">
        <input type="checkbox" name="update_billing_address" id="update_billing_address"> ${messageUpdateThisAddress}`;
    $('#save-billing-address-label').html(update_address_billing);
    syncCheckoutRequiredStates();
}


$('.add-another-address').on('click', function () {
    $('#sh-0').prop('checked', true);
    $("#collapseThree").collapse();
})

let defaultLatitudeAddressValue = $('#default-latitude-address').data('value');
let defaultLongitudeAddressValue = $('#default-longitude-address').data('value');

async function initAutocomplete() {

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                defaultLatitudeAddressValue = position.coords.latitude;
                defaultLongitudeAddressValue = position.coords.longitude;

                updateMapAndMarker(defaultLatitudeAddressValue, defaultLongitudeAddressValue);
            },
            function (error) {
                console.error("Error getting geolocation: ", error.message);
                defaultLatitudeAddressValue = 26.774645719165914;
                defaultLongitudeAddressValue = 29.311165295285434;
                updateMapAndMarker(defaultLatitudeAddressValue, defaultLongitudeAddressValue);
            }
        );
    }

    function updateMapAndMarker(latitude, longitude) {
        const myLatLng = { lat: latitude, lng: longitude };

        const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
            center: myLatLng,
            zoom: 4,
            mapId: "roadmap",
        });

        const marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            draggable: true,
        });

        const geocoder = new google.maps.Geocoder();

        // Fetch the area based on latitude and longitude
        fetchArea(latitude, longitude);

        // Reverse geocode to get the address
        geocoder.geocode({ location: myLatLng }, function (results, status) {
            if (status === "OK") {
                if (results[0]) {
                    document.getElementById("address").value = results[0].formatted_address;
                    document.getElementById("latitude").value = latitude;
                    document.getElementById("longitude").value = longitude;
                    fetchArea(latitude, longitude);
                } else {
                    console.error("No results found");
                }
            } else {
                console.error("Geocoder failed due to: " + status);
            }
        });

        // Update marker and address on map click
        map.addListener("click", function (mapsMouseEvent) {
            const clickedLatLng = mapsMouseEvent.latLng;
            marker.setPosition(clickedLatLng);
            map.panTo(clickedLatLng);

            const lat = clickedLatLng.lat();
            const lng = clickedLatLng.lng();

            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;
            fetchArea(lat, lng);

            geocoder.geocode({ location: clickedLatLng }, function (results, status) {
                if (status === "OK") {
                    if (results[0]) {
                        document.getElementById("address").value = results[0].formatted_address;
                    }
                } else {
                    console.error("Geocoder failed due to: " + status);
                }
            });
        });
    }

    // Initialize the search box
    const input = document.getElementById("pac-input");
    const searchBox = new google.maps.places.SearchBox(input);
    // map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

    // Bias the SearchBox results towards the current map's viewport
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });

    let markers = [];

    // Listen for the event when the user selects a place
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length === 0) {
            return;
        }

        // Clear out the old markers
        markers.forEach((marker) => marker.setMap(null));
        markers = [];

        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.error("Returned place contains no geometry");
                return;
            }

            // Create a marker for each place
            const placeMarker = new google.maps.Marker({
                map: map,
                title: place.name,
                position: place.geometry.location,
            });

            // Update address fields when clicking on the marker
            google.maps.event.addListener(placeMarker, "click", function () {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;
                document.getElementById("address").value = place.formatted_address || place.name;
                fetchArea(lat, lng);
            });

            markers.push(placeMarker);

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}

async function fetchArea(latitude, longitude) {
    // Perform an AJAX request to fetch branch data for the product
    const pickupBranchSelect = document.getElementById('pickup_branch_id');
    const nearestBranchAddress = document.getElementById('nearest_branch_textarea');
    $.ajax({
        url: $("#route-fetch-area-branch").data("url"), // Update with your route name
        method: "GET",
        data: { latitude: latitude, longitude: longitude },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#nearest_branch').val(response.branch[0].id)
            pickupBranchSelect.value = response.branch[0].id;
            nearestBranchAddress.value = `${response.branch[0].address}`;
            // console.log(response.branch[0].id);
        },
        complete: function () {
            $("#loading").fadeOut();
        },
        error: function () {
            $("#loading").fadeOut();
            // toastr.error("Failed to fetch branches. Please try again.");
        }
    });
}

$(document).on("keydown", "input", function (e) {
    if (e.which == 13) e.preventDefault();
})

async function initAutocompleteBilling() {
    var myLatLng = {
        lat: defaultLatitudeAddressValue,
        lng: defaultLongitudeAddressValue
    };
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    const map = new google.maps.Map(document.getElementById("location_map_canvas_billing"), {
        center: {
            lat: defaultLatitudeAddressValue,
            lng: defaultLongitudeAddressValue
        },
        zoom: 4,
        mapId: "roadmap",
    });

    var marker = new AdvancedMarkerElement({
        map,
        position: myLatLng,
    });

    marker.setMap(map);
    var geocoder = geocoder = new google.maps.Geocoder();
    google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
        coordinates = JSON.parse(coordinates);
        var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
        marker.position = { lat: coordinates['lat'], lng: coordinates['lng'] };
        map.panTo(latlng);

        document.getElementById('billing_latitude').value = coordinates['lat'];
        document.getElementById('billing_longitude').value = coordinates['lng'];

        geocoder.geocode({ 'latLng': latlng }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    document.getElementById('billing_address').value = results[1].formatted_address;

                    let systemCountryRestrictStatus = $('#system-country-restrict-status').data('value');
                    if (systemCountryRestrictStatus) {
                        const countryObject = findCountryObject(results[1].address_components);
                        deliveryRestrictedCountriesCheck(countryObject.long_name, '.location-map-billing-canvas-area', '#billing_address')
                    }
                }
            }
        });
    });


    const input = document.getElementById("pac-input-billing");
    const searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });
    let markers = [];

    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        markers.forEach((marker) => {
            marker.setMap(null);
        });
        markers = [];

        const bounds = new google.maps.LatLngBounds();
        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }
            var mrkr = new AdvancedMarkerElement({
                map,
                title: place.name,
                position: place.geometry.location,
            });

            google.maps.event.addListener(mrkr, "click", function (event) {
                document.getElementById('billing_latitude').value = this.position.lat();
                document.getElementById('billing_longitude').value = this.position.lng();

            });

            markers.push(mrkr);

            if (place.geometry.viewport) {

                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}

$(document).on("keydown", "input", function (e) {
    if (e.which == 13) e.preventDefault();
});

function checkoutFromShipping() {
    let physical_product = $('#physical_product').val();
    let billing_address_same_shipping;

    if (physical_product === 'yes') {
        let sameAsShippingCheckbox = $('#same_as_shipping_address');
        billing_address_same_shipping = sameAsShippingCheckbox ? sameAsShippingCheckbox.is(":checked") : false;
    } else {
        billing_address_same_shipping = false;
    }

    let isCheckCreateAccount = $('#is_check_create_account');
    let customerPassword = $('#customer_password');
    let customerConfirmPassword = $('#customer_confirm_password');

    syncCheckoutRequiredStates();
    clearCheckoutValidationState();

    const shippingErrors = physical_product === 'yes' ? collectClientValidationErrors('#address-form') : {};
    const billingErrors = billing_address_same_shipping !== true ? collectClientValidationErrors('#billing-address-form') : {};
    const passwordErrors = isCheckCreateAccount && isCheckCreateAccount.prop("checked")
        ? collectClientValidationErrors('.is_check_create_account_password_group')
        : {};
    const fieldErrors = Object.assign({}, shippingErrors, billingErrors, passwordErrors);
    const firstField = Object.keys(fieldErrors)[0];

    if (firstField) {
        renderCheckoutErrors({
            message: fieldErrors[firstField],
            field_errors: fieldErrors,
            focus_field: firstField,
        });
        return;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.post({
        url: $('#route-customer-choose-shipping-address-other').data('url'),
        data: {
            physical_product: physical_product,
            shipping: physical_product === 'yes' ? $('#address-form').serialize() : null,
            billing: $('#billing-address-form').serialize(),
            billing_addresss_same_shipping: billing_address_same_shipping,
            is_check_create_account: isCheckCreateAccount && isCheckCreateAccount.prop("checked") ? 1 : 0,
            customer_password: customerPassword ? customerPassword.val() : null,
            customer_confirm_password: customerConfirmPassword ? customerConfirmPassword.val() : null,
        },

        beforeSend: function () {
            $('#loading').show();
        },
        success: function () {
            location.href = $('#route-checkout-payment').data('url');
        },
        complete: function () {
            $('#loading').hide();
        },
        error: function (data) {
            renderCheckoutErrors(data.responseJSON || {});
        }
    });
}

function mapsShopping() {
    try {
        initAutocomplete();
    } catch (error) {
    }
    try {
        initAutocompleteBilling();
    } catch (error) {
    }
}



function togglePickupBranchVisibility() {
    const selectedValue = document.querySelector('input[name="delivery_type"]:checked')?.value;

    const pickupBranchDiv = document.getElementById('deliver-pickup-branch');
    const pickupBranchAddressDiv = document.getElementById('deliver-pickup-branch-address');
    const addressAddressDiv = document.getElementById('deliver-address');
    const zipAddressDiv = document.getElementById('deliver-zip');
    const cityAddressDiv = document.getElementById('deliver-city');
    const addressType = document.getElementById('deliver-address-type');
    const deliveryAddressTypeDiv = document.getElementById('deliver-address-type-div');
    const stateAddressDiv = document.getElementById('deliver-state');
    const areaAddressDiv = document.getElementById('deliver-area');
    const cAddressDiv = document.getElementById('deliver-country');
    const locationMap = document.getElementById('location_map_canvas');
    const locationMapArea = document.getElementById('location_map_canvas_area');
    const pickupBranchSelect = document.getElementById('pickup_branch_id');
    const sameAsShippingAddressWrapper = document.getElementById('same_as_shipping_address_wrapper');
    const saveAddressLabel = document.getElementById('save_address_label');
    const sameAsShippingAddress = document.getElementById('same_as_shipping_address') || document.getElementById('same-as-shipping-address');
    const hideBillingAddress = document.getElementById('hide_billing_address') || document.getElementById('hide-billing-address');
    const updateAddressCheckbox = document.getElementById('update_address');
    const saveAddressCheckbox = document.getElementById('save_address');
    const createAccountInfoLabels = document.querySelectorAll('.create-account-info-label');
    const createAccountAboveInfoText = document.getElementById('message-create-account-above-info')?.dataset?.text || '';
    const createAccountBelowInfoText = document.getElementById('message-create-account-below-info')?.dataset?.text || createAccountAboveInfoText;
    const setElementVisibility = (element, shouldShow) => {
        if (!element) return;
        if (shouldShow) {
            element.style.removeProperty('display');
        } else {
            element.style.setProperty('display', 'none', 'important');
        }
    };
    const setCreateAccountLabelText = (text) => {
        createAccountInfoLabels.forEach((label) => {
            label.textContent = text;
        });
    };
    const deliverySelectorGroup = document.querySelector('.delivery-radio-btn');

    if (!selectedValue) {
        deliverySelectorGroup?.classList.remove('delivery-choice--delivery', 'delivery-choice--pickup');
        pickupBranchDiv?.classList.add('d-none');
        pickupBranchAddressDiv?.classList.add('d-none');
        deliveryAddressTypeDiv?.classList.add('d-none');
        addressAddressDiv?.classList.add('d-none');
        addressType?.classList.add('d-none');
        cityAddressDiv?.classList.add('d-none');
        stateAddressDiv?.classList.add('d-none');
        areaAddressDiv?.classList.add('d-none');
        cAddressDiv?.classList.add('d-none');
        locationMap?.classList.add('d-none');
        locationMapArea?.classList.add('d-none');
        setElementVisibility(sameAsShippingAddressWrapper, false);
        setElementVisibility(saveAddressLabel, false);
        if (sameAsShippingAddress) {
            sameAsShippingAddress.checked = false;
        }
        if (hideBillingAddress) {
            hideBillingAddress.style.display = '';
        }
        setCreateAccountLabelText(createAccountAboveInfoText);
        syncCheckoutRequiredStates();
        return;
    }

    if (selectedValue === 'pickup') {
        deliverySelectorGroup?.classList.remove('delivery-choice--delivery');
        deliverySelectorGroup?.classList.add('delivery-choice--pickup');
        setCreateAccountLabelText(createAccountBelowInfoText);
        pickupBranchDiv?.classList.remove('d-none');
        pickupBranchAddressDiv?.classList.remove('d-none');
        deliveryAddressTypeDiv?.classList.remove('d-none');
        addressAddressDiv?.classList.add('d-none');
        addressType?.classList.add('d-none');
        cityAddressDiv?.classList.add('d-none');
        stateAddressDiv?.classList.add('d-none');
        areaAddressDiv?.classList.add('d-none');
        cAddressDiv?.classList.add('d-none');
        locationMap?.classList.add('d-none');
        locationMapArea?.classList.add('d-none');
        setElementVisibility(sameAsShippingAddressWrapper, false);
        setElementVisibility(saveAddressLabel, false);
        if (sameAsShippingAddress) {
            sameAsShippingAddress.checked = false;
        }
        if (hideBillingAddress) {
            hideBillingAddress.style.display = '';
        }
        if (updateAddressCheckbox) {
            updateAddressCheckbox.checked = false;
        }
        if (saveAddressCheckbox) {
            saveAddressCheckbox.checked = false;
        }
    } else {
        deliverySelectorGroup?.classList.remove('delivery-choice--pickup');
        deliverySelectorGroup?.classList.add('delivery-choice--delivery');
        setCreateAccountLabelText(createAccountAboveInfoText);
        pickupBranchDiv?.classList.add('d-none');
        pickupBranchAddressDiv?.classList.add('d-none');
        addressAddressDiv?.classList.remove('d-none');
        cityAddressDiv?.classList.remove('d-none');
        deliveryAddressTypeDiv?.classList.remove('d-none');
        stateAddressDiv?.classList.remove('d-none');
        areaAddressDiv?.classList.remove('d-none');
        cAddressDiv?.classList.remove('d-none');
        locationMap?.classList.remove('d-none');
        locationMapArea?.classList.remove('d-none');
        addressType?.classList.remove('d-none');
        setElementVisibility(sameAsShippingAddressWrapper, true);
        setElementVisibility(saveAddressLabel, true);
        if (sameAsShippingAddress) {
            sameAsShippingAddress.checked = true;
        }
        if (hideBillingAddress) {
            hideBillingAddress.style.display = 'none';
        }

    }

    syncCheckoutRequiredStates();
}

$(document).on('change', 'input[name="delivery_type"]', function () {
    togglePickupBranchVisibility();
});

$(document).on('change', '#same_as_shipping_address, #is_check_create_account', function () {
    syncCheckoutRequiredStates();
});

$(document).on('input change', '#address-form input, #address-form select, #address-form textarea, #billing-address-form input, #billing-address-form select, #billing-address-form textarea, .is_check_create_account_password_group input', function () {
    clearCheckoutFieldValidationByElement(this);
});

$(document).on('change', 'input[name="delivery_type"]', function () {
    const selectedValue = $('input[name="delivery_type"]:checked').val();

    if (selectedValue === 'pickup') {
        // Pickup selected → Set shipping cost 0
        $.post(updateShippingCostRoute, {
            area_name: null // or send a keyword like 'pickup'
        }).done(function (res) {
            $('#cart-summary').load(location.href + ' #cart-summary > *');
        });

    } else {
        $('#area').trigger('change');
    }

    // Also call visibility toggle
    togglePickupBranchVisibility();
});


$(document).ready(function () {
    togglePickupBranchVisibility();


    $('#country').change(function () {
        let country = $(this).val();
        $.get(getStatesURL, {
            country: country
        }, function (data) {
            const states = normalizeApiCollection(data, 'states');
            $('#state_id').empty().append('<option value="">Select State</option>');
            $('#city_id').empty().append('<option value="">Select City</option>');
            $('#area').empty().append('<option value="">Select Area</option>');
            $.each(states, function (key, state) {
                $('#state_id').append(
                    `<option value="${state.id}" data-name="${state.name}">${state.name}</option>`
                );
            });
        });
    });


    $('#state_id').change(function () {
        let selected = $(this).find('option:selected');
        $('#state_name').val(selected.data('name')); // 👈 update hidden input

        let state_id = $(this).val();
        $.get(getCitiesURL, {
            state_id: state_id
        }, function (data) {
            const cities = normalizeApiCollection(data, 'cities');
            $('#city_id').empty().append('<option value="">Select City</option>');
            $('#area').empty().append('<option value="">Select Area</option>');

            $.each(cities, function (key, city) {
                $('#city_id').append(
                    `<option value="${city.id}" data-name="${city.name}">${city.name}</option>`
                );
            });
        });
    });

    $('#city_id').change(function () {
        let selected = $(this).find('option:selected');
        $('#city_name').val(selected.data('name')); // 👈 update hidden input

        let city_id = $(this).val();
        $.get(getAreasURL, {
            city_id: city_id
        }, function (data) {
            const areas = normalizeApiCollection(data, 'areas');
            $('#area').empty().append('<option value="">Select Area</option>');
            $.each(areas, function (key, area) {
                const optionText = String(areaOptionText(area)).trim();
                const optionValue = String(areaOptionValue(area)).trim();
                $('#area').append(`<option value="${optionValue}">${optionText}</option>`);
            });
        });
    });
    // --- SHIPPING ---
    $('#shipping_country').change(function () {
        let country = $(this).val();
        $.get(getStatesURL, {
            country: country
        }, function (data) {
            const states = normalizeApiCollection(data, 'states');
            $('#shipping_state_id').empty().append('<option value="">Select State</option>');
            $('#shipping_city_id').empty().append('<option value="">Select City</option>');
            $('#shipping_area').empty().append('<option value="">Select Area</option>');
            $.each(states, function (key, state) {
                $('#shipping_state_id').append(`<option value="${state.id}" >${state.name}</option>`);
            });
        });
    });

    $('#shipping_state_id').change(function () {
        let state_id = $(this).val();
        $.get(getCitiesURL, {
            state_id: state_id
        }, function (data) {
            const cities = normalizeApiCollection(data, 'cities');
            $('#shipping_city_id').empty().append('<option value="">Select City</option>');
            $('#shipping_area').empty().append('<option value="">Select Area</option>');
            $.each(cities, function (key, city) {
                $('#shipping_city_id').append(`<option value="${city.id}">${city.name}</option>`);
            });
        });
    });

    $('#shipping_city_id').change(function () {
        let city_id = $(this).val();
        $.get(getAreasURL, {
            city_id: city_id
        }, function (data) {
            const areas = normalizeApiCollection(data, 'areas');
            $('#shipping_area').empty().append('<option value="">Select Area</option>');
            $.each(areas, function (key, area) {
                const optionText = String(areaOptionText(area)).trim();
                const optionValue = String(areaOptionValue(area)).trim();
                $('#shipping_area').append(`<option value="${optionValue}">${optionText}</option>`);
            });
        });
    });

    // --- BILLING ---
    $('#billing_country').change(function () {
        let country = $(this).val();
        $.get(getBillingStatesURL, {
            billing_country: country
        }, function (data) {
            const states = normalizeApiCollection(data, 'states');
            $('#billing_state_id').empty().append('<option value="">Select State</option>');
            $('#billing_city_id').empty().append('<option value="">Select City</option>');
            $('#billing_area').empty().append('<option value="">Select Area</option>');
            $.each(states, function (key, state) {
                $('#billing_state_id').append(`<option value="${state.id}" data-name="${state.name}">${state.name}</option>`);
            });
        });
    });

    $('#billing_state_id').change(function () {
        let selected = $(this).find('option:selected');

        $('#billing_state_name').val(selected.data('name'));
        let state_id = $(this).val();

        $.get(getBillingCitiesURL, {
            billing_state_id: state_id
        }, function (data) {
            const cities = normalizeApiCollection(data, 'cities');
            $('#billing_city_id').empty().append('<option value="">Select City</option>');
            $('#billing_area').empty().append('<option value="">Select Area</option>');
            $.each(cities, function (key, city) {
                $('#billing_city_id').append(`<option value="${city.id}" data-name="${city.name}">${city.name}</option>`);
            });
        });
    });

    $('#billing_city_id').change(function () {
        let selected = $(this).find('option:selected');

        $('#billing_city_name').val(selected.data('name'));
        let city_id = $(this).val();

        $.get(getBillingAreasURL, {
            billing_city_id: city_id
        }, function (data) {
            const areas = normalizeApiCollection(data, 'areas');
            $('#billing_area').empty().append('<option value="">Select Area</option>');
            $.each(areas, function (key, area) {
                const optionText = String(areaOptionText(area)).trim();
                const optionValue = String(areaOptionValue(area)).trim();
                $('#billing_area').append(`<option value="${optionValue}">${optionText}</option>`);
            });
        });
    });

    // Hydrate dependent dropdowns if country is already selected
    // (e.g. prefilled form values after refresh).
    if ($('#country').val() && $('#state_id option').length <= 1) {
        $('#country').trigger('change');
    }
    if ($('#billing_country').val() && $('#billing_state_id option').length <= 1) {
        $('#billing_country').trigger('change');
    }

});
