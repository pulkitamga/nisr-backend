'use strict';
$(document).ready(function () {
    let shippingTypeValue = $('#get-shipping-type-value').data('value');
    let sCountryCode = $('#country').val();
    shippingType(shippingTypeValue);
    fFetchCountryStates(sCountryCode);
    let iStateId = $('#state').val();
    let iCityId = $('#city').data();
    if(iStateId != 0){
        fFetchStatesCities(iStateId, iCityId)
    }

    const shippingMethodCreateForm = document.getElementById('shipping-method-create-form');
    if (shippingMethodCreateForm) {
        const multilingualRequiredFields = shippingMethodCreateForm.querySelectorAll(
            '.form-system-language-form input[required], .form-system-language-form textarea[required], .form-system-language-form select[required]'
        );

        multilingualRequiredFields.forEach((field) => {
            field.addEventListener('invalid', () => {
                const languageForm = field.closest('.form-system-language-form');
                if (!languageForm || !languageForm.classList.contains('d-none')) {
                    return;
                }

                const language = languageForm.dataset.language;
                const languageTab = shippingMethodCreateForm.querySelector(`.form-system-language-tab[data-language="${language}"]`);
                languageTab?.click();
            }, true);
        });
    }
});
function shippingType(shippingTypeValue){
    if (shippingTypeValue === 'category_wise') {
        $('#product_wise_note').hide();
        $('#order_wise_shipping').hide();
        $('#area_wise_shipping').hide();
        $('#update_category_shipping_cost').show();

    } else if (shippingTypeValue === 'order_wise') {
        $('#product_wise_note').hide();
        $('#update_category_shipping_cost').hide();
        $('#area_wise_shipping').hide();
        $('#order_wise_shipping').show();
    } else if (shippingTypeValue === 'product_wise') {
        $('#product_wise_note').hide();
        $('#area_wise_shipping').hide();
        $('#update_category_shipping_cost').hide();
        $('#order_wise_shipping').show();
    } else {
        $('#update_category_shipping_cost').hide();
        $('#order_wise_shipping').hide();
        $('#product_wise_note').hide();
        $('#area_wise_shipping').show();
    }
}
$('.shipping-type').on('change',function (){
    let shippingTypeValue = $(this).val();
    shippingType(shippingTypeValue);
    let shippingTypeData = $('#get-shipping-type-data');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        url: shippingTypeData.data('action'),
        method: 'POST',
        data: {
            shippingType: shippingTypeValue
        },
        success: function () {
            toastr.success(shippingTypeData.data('success'));
        }
    });
})

$('#country').on('change',function (){
    let sCountryCode = $(this).val();
    fFetchCountryStates(sCountryCode);
});

$('#state').on('change',function (){
    let iStateId = $(this).val();
    fFetchStatesCities(iStateId);
});
$('#city').on('change', function () {
    let iCityId = $(this).val();
    fFetchCityAreas(iCityId);
});


function fFetchCountryStates(sCountryCode = 'EG'){
    $.ajax({
        url: $("#route-get-country-state").data("url"), // Update with your route name
        method: "GET",
        data: { sCountryCode },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#state').empty();
            $('#state').append(`<option value="0" selected="" disabled="">---Select---</option>`);
            // Populate modal table with new data
            response.data.forEach((state, index) => {
                $('#state').append(`
                    <option value='${state.id}'>${state.name}</option>
                `);
            });

            // If a state is pre-selected, set the selected option
            let iStateId = $('#state').data('state-id');  // Assuming you have this value on your page
            if (iStateId) {
                $('#state').val(iStateId).change();  // Trigger change event to load cities
            }
        },
        complete: function () {
            $("#loading").fadeOut();
        },
        error: function () {
            $("#loading").fadeOut();
            toastr.error("Failed to fetch states. Please try again.");
        }
    });
}

function fFetchStatesCities(iStateId = 0, iCityId = 0){
    $.ajax({
        url: $("#route-get-state-cities").data("url"), // Update with your route name
        method: "GET",
        data: { iStateId },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#city').empty();
            $('#city').append(`<option value="0" selected="" disabled="">---Select---</option>`);
            // Populate modal table with new data
            response.data.forEach((city, index) => {
                $('#city').append(`
                    <option value='${city.id}'>${city.name}</option>
                `);
            });

            let iCityId = $('#city').data('city-id'); 
            if (iCityId) {
                $('#city').val(iCityId);
            }
        },
        complete: function () {
            $("#loading").fadeOut();
        },
        error: function () {
            $("#loading").fadeOut();
            toastr.error("Failed to fetch cities. Please try again.");
        }
    });
}


function fFetchCityAreas(iCityId = 0){
    $.ajax({
        url: $("#route-get-city-areas").data("url"),
        method: "GET",
        data: { iCityId },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#area').empty();
            $('#area').append(`<option value="0" selected disabled>---Select---</option>`);

            if(response.data.length > 0){
                response.data.forEach((area) => {
                    $('#area').append(`
                        <option value="${area.name}">${area.name}</option>
                    `);
                });
            }
        },
        complete: function () {
            $("#loading").fadeOut();
        },
        error: function () {
            $("#loading").fadeOut();
            toastr.error("Failed to fetch areas.");
        }
    });
}
