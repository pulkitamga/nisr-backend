"use strict";

$(window).on('load', function () {
    $('.js-select2-custom').siblings('.select2-container').addClass('border-0');
    $('.js-select2-custom').siblings('.select2-container').find('.border-0').removeClass('border-0').addClass('border');
});
$('input[name=deliveryman_charge]').mousewheel(function (event) {
    event.preventDefault();
});

$("#filter").change(function () {
    let val = $(this).val();
    if (val === 'admin') {
        $('#seller_id_area').fadeOut();
        $('#seller_id').val('all');
    } else {
        $('#seller_id_area').fadeIn();
        $('#seller_id').val('all');
    }
    if (val === 'seller') {
        $('#seller_id_inhouse').fadeOut();
    } else {
        $('#seller_id_inhouse').fadeIn();
    }
});

$("#date_type").change(function () {
    let val = $(this).val();
    $('#from_div').toggle(val === 'custom_date');
    $('#to_div').toggle(val === 'custom_date');

    if (val === 'custom_date') {
        $('#from_date').attr('required', 'required');
        $('#to_date').attr('required', 'required');
        $('.filter-btn').attr('class', 'filter-btn col-12 text-right');
    } else {
        $('#from_date').val(null).removeAttr('required')
        $('#to_date').val(null).removeAttr('required')
        $('.filter-btn').attr('class', 'col-sm-6 col-md-3 filter-btn');
    }
}).change();

$('#from_date,#to_date').change(function () {
    let fr = $('#from_date').val();
    let to = $('#to_date').val();
    if (fr != '') {
        $('#to_date').attr('required', 'required');
    }
    if (to != '') {
        $('#from_date').attr('required', 'required');
    }
    if (fr != '' && to != '') {
        if (fr > to) {
            $('#from_date').val('');
            $('#to_date').val('');
            toastr.error('{{translate("invalid_date_range")}}!', Error, {
                CloseButton: true,
                ProgressBar: true
            });
        }
    }

})

$("#customer_id_value").on('change', function () {
    $('#customer_id').empty().val($(this).val());
});

var data_example_url = $('#js-data-example-ajax-url').data('url');
var characterTriggerLimit = $('#character-trigger-limit').data('limit');
$('.js-data-example-ajax').select2({
    data: [{ id: '', text: 'Select your option', disabled: true, selected: true }],
    ajax: {
        url: data_example_url,
        delay: 300, // Reduce excessive requests
        data: function (params) {
            let query = {
                q: params.term || '', // Search term
                page: params.page || 1
            };
            return query;
        },
        processResults: function (data) {
            return {
                results: data
            };
        }
    },
    minimumInputLength: characterTriggerLimit, // Apply only when searching
    allowClear: true,
    placeholder: "Select an option",
    initSelection: function (element, callback) {
        $.ajax({
            url: data_example_url,
            data: { default_list: true }, // Fetch default options
            success: function (data) {
                callback(data);
            }
        });
    }
});

$(document).ready(function () {
    $('#dataTable').DataTable();

    let delivery_type = $("#delivery-type").data('type');
    if (delivery_type === 'self_delivery') {
        $('.choose_delivery_man').show();
        $('#by_third_party_delivery_service_info').hide();
    } else if (delivery_type === 'third_party_delivery') {
        $('.choose_delivery_man').hide();
        $('#by_third_party_delivery_service_info').show();
    } else {
        $('.choose_delivery_man').hide();
        $('#by_third_party_delivery_service_info').hide();
    }
});

$(document).ready(function () {
    $('.select2-container--default').addClass('form-control').addClass('p-0');
    $('.select2-selection').addClass('border-0');
    initializePhoneInput(".phone-input-with-country-picker-2", ".country-picker-phone-number-2");

});
$('.payment-status-alert').on('click', function () {
    toastr.info($('#payment-status-alert-message').data('message'));
})
$('.exchange-status-alert').on('click', function () {
    toastr.info($('#exchange-status-alert-message').data('message'));
})

$(".payment-status").on('click', function (e) {
    e.preventDefault();
    let id = $(this).data('id');
    let value = $(this).val();
    Swal.fire({
        title: $("#payment-status-message").data('title'),
        text: $("#payment-status-message").data('message'),
        showCancelButton: true,
        confirmButtonColor: '#377dff',
        cancelButtonColor: 'secondary',
        confirmButtonText: $("#message-status-confirm-text").data('text'),
        cancelButtonText: $("#message-status-cancel-text").data('text'),
    }).then((result) => {
        if (value == 'paid') {
            value = 'unpaid'
        } else {
            value = 'paid'
        }
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: $("#payment-status-url").data('url'),
                method: 'POST',
                data: {
                    "id": id,
                    "payment_status": value
                },
                success: function (data) {
                    if (data.customer_status == 0) {
                        location.reload();
                        toastr.warning($("#message-status-warning-text").data('text'));
                    } else if (data.error) {
                        toastr.warning(data.error);
                    }
                    else {
                        location.reload();
                        toastr.success($("#message-status-success-text").data('text'));
                    }
                }
            });
        }
    })
});

$(".exchange-status").on('click', function (e) {
    e.preventDefault();
    let id = $(this).data('id');
    let value = $(this).val();
    console.log(value);
    Swal.fire({
        title: $("#exchange-status-message").data('title'),
        text: $("#exchange-status-message").data('message'),
        showCancelButton: true,
        confirmButtonColor: '#377dff',
        cancelButtonColor: 'secondary',
        confirmButtonText: $("#message-status-confirm-text").data('text'),
        cancelButtonText: $("#message-status-cancel-text").data('text'),
    }).then((result) => {
        console.log(result.value);
        if (value == '1') {
            $('#exchage_product_modal').modal("hide");
        } else {
            $('#exchage_product_modal').modal("show");
        }
    })
});
$("#order_status").on('change', function (e) {
    const $statusSelect = $(this);
    const previousStatus = $statusSelect.data('current-status');
    let value = $statusSelect.val();
    const $branchSelect = $("#order_delivered_from_branch");
    const requiresDeliveredBranch = ['out_for_delivery', 'delivered'].includes(value);

    if (requiresDeliveredBranch && $branchSelect.length && !$branchSelect.val()) {
        const branchRequiredMessage = $("#message-branch-required-before-delivery-status-text").data('text') || "Branch is required!";
        toastr.warning(branchRequiredMessage);
        if (previousStatus) {
            $statusSelect.val(previousStatus);
        }
        return;
    }

    Swal.fire({
        title: $("#message-status-title-text").data('text'),
        text: $("#message-status-subtitle-text").data('text'),
        showCancelButton: true,
        confirmButtonColor: '#377dff',
        cancelButtonColor: 'secondary',
        confirmButtonText: $("#message-status-confirm-text").data('text'),
        cancelButtonText: $("#message-status-cancel-text").data('text'),
    }).then((result) => {
        if (result.value) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: $("#order-status-url").data('url'),
                method: 'POST',
                data: {
                    "id": $statusSelect.data('id'),
                    "order_status": value
                },

                success: function (data) {
                    if (data.success === 0) {
                        toastr.error(data.message);
                        location.reload();
                        return;
                    }
                    if (data.payment_status === 0) {
                        toastr.warning($("#message-order-status-paid-first-text").data('text'));
                        location.reload();
                        return;
                    }
                    if (data.customer_status === 0) {
                        toastr.warning($("#message-status-warning-text").data('text'));
                        location.reload();
                        return;
                    }
                    toastr.success($("#message-status-success-text").data('text'));
                    location.reload();
                },

                error: function (xhr, status, error) {
                    if (previousStatus) {
                        $statusSelect.val(previousStatus);
                    }
                    toastr.error("Something went wrong!");
                }
            });
        } else if (previousStatus) {
            $statusSelect.val(previousStatus);
        }
    });
});


$("#order_delivered_from_branch").on('change', function (e) {
    const orderBranchSelect = document.getElementById('order_delivered_from_branch');
    let value = $(this).val();
    Swal.fire({
        title: $("#message-transfer-from-branch-title-text").data('text'),
        text: $("#message-transfer-from-branch-subtitle-text").data('text'),
        showCancelButton: true,
        confirmButtonColor: '#377dff',
        cancelButtonColor: 'secondary',
        confirmButtonText: $("#message-status-confirm-text").data('text'),
        cancelButtonText: $("#message-status-cancel-text").data('text'),
    }).then((result) => {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: $("#order-transfer-delivery-branch-url").data('url'),
                method: 'POST',
                data: {
                    "id": $(this).data('id'),
                    "branch_id": value
                },
                beforeSend: function () {
                    $("#loading").fadeIn();
                },
                success: function (data) {
                    if (data.success == 0) {
                        toastr.info($("#message-order-delivered-branch-transfered-text").data('text'));
                        location.reload();
                    } else if (data.success == 2) {
                        toastr.error($("#message-order-delivered-branch-transfered-outoff-stock-text").data('text'));
                        location.reload();
                        // $(orderBranchSelect).val(data.transfer_from_branch).trigger('change');
                        // orderBranchSelect.value = data.transfer_from_branch;
                    } else {
                        toastr.success($("#message-branch-deliverey-transfered-text").data('text'));
                    }
                },
                complete: function () {
                    $("#loading").fadeOut();
                },
                error: function () {
                    $("#loading").fadeOut();
                }
            });
        }
    })
});

$("#choose_delivery_type").on('change', function () {
    let value = $(this).val();
    if (value === 'self_delivery') {
        $('.choose_delivery_man').show();
        $('#by_third_party_delivery_service_info').hide();
    } else if (value === 'third_party_delivery') {
        $('.choose_delivery_man').hide();
        $('#by_third_party_delivery_service_info').show();
        $('#third_party_delivery_service_modal').modal("show");
    } else {
        $('.choose_delivery_man').hide();
        $('#by_third_party_delivery_service_info').hide();
    }

});

$("#addDeliveryMan").on('change', function () {
    let id = $(this).val();
    $.ajax({
        type: "GET",
        url: $("#add-delivery-man-url").data('url') + id,
        data: {
            'order_id': $(this).data('order-id'),
            'delivery_man_id': id
        },
        success: function (data) {
            if (data.status == true) {
                toastr.success($("#message-deliveryman-add-success-text").data('text'), {
                    CloseButton: true,
                    ProgressBar: true
                });
                location.reload();
            } else {
                toastr.error($("#message-deliveryman-add-error-text").data('text'), {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        },
        error: function () {
            toastr.error($("#message-deliveryman-add-invalid-text").data('text'), {
                CloseButton: true,
                ProgressBar: true
            });
        }
    });
});
$('input[name=deliveryman_charge]').on('keyup', function (event) {
    if (event.which === 13) {
        let value = $(this);
        amountDateUpdate(value);
    }
});

$(".deliveryman-charge").on('click', function () {
    let value = $('input[name=deliveryman_charge]');
    amountDateUpdate(value);
});
$('.deliveryman-charge-alert').on('click', function () {
    toastr.info($('#deliveryman-charge-alert-message').data('message'))
})

$("#expected_delivery_date").on('change', function () {
    amountDateUpdate(this);
});

function amountDateUpdate(t) {
    let field_name = $(t).attr('name');
    let field_val = $(t).val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        url: $("#add-date-update-url").data('url'),
        method: 'POST',
        data: {
            'order_id': $(t).data('order-id'),
            'field_name': field_name,
            'field_val': field_val
        },
        success: function (data) {
            if (data.status == true) {
                toastr.success(data.message, {
                    CloseButton: true,
                    ProgressBar: true
                });
                location.reload();
            } else {
                toastr.error($("#message-deliveryman-charge-error-text").data('text'), {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        },
        error: function () {
            toastr.error($("#message-deliveryman-charge-invalid-text").data('text'), {
                CloseButton: true,
                ProgressBar: true
            });
        }
    });
}

function parseMapCoordinate(value) {
    const parsedValue = parseFloat(value);
    return Number.isFinite(parsedValue) ? parsedValue : null;
}

function getMapCoordinates(latitudeSelector, longitudeSelector) {
    const latitude = parseMapCoordinate($(latitudeSelector).data('latitude'));
    const longitude = parseMapCoordinate($(longitudeSelector).data('longitude'));

    if (latitude === null || longitude === null) {
        return null;
    }

    return { lat: latitude, lng: longitude };
}

/** shipping address  map */
async function shippingAddressMap() {
    const canvas = document.getElementById("location_map_canvas_shipping");
    const coordinates = getMapCoordinates("#shipping-latitude", "#shipping-longitude");
    if (!canvas || !coordinates || typeof google === 'undefined' || !google.maps) {
        return null;
    }

    const myLatLng = {
        lat: coordinates.lat,
        lng: coordinates.lng
    };
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    const map = new google.maps.Map(canvas, {
        center: {
            lat: coordinates.lat,
            lng: coordinates.lng
        },
        zoom: 13,
        mapId: 'roadmap'
    });

    var marker = new AdvancedMarkerElement({
        map,
        position: myLatLng,

    });

    marker.setMap(map);
    var geocoder = geocoder = new google.maps.Geocoder();
    google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
        var coordinates = JSON.parse(coordinates);
        var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
        marker.position = { lat: coordinates['lat'], lng: coordinates['lng'] };
        map.panTo(latlng);

        document.getElementById('latitude').value = coordinates['lat'];
        document.getElementById('longitude').value = coordinates['lng'];

        geocoder.geocode({ 'latLng': latlng }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    document.getElementById('address').value = results[1].formatted_address;
                    console.log(results[1].formatted_address);
                }
            }
        });
    });

    const input = document.getElementById("pac-input");
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
                document.getElementById('latitude').value = this.position.lat();
                document.getElementById('longitude').value = this.position.lng();

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
};

$(document).on("keydown", "input", function (e) {
    if (e.which == 13) e.preventDefault();
});

/** billing address  map */
async function billingAddressMap() {
    const canvas = document.getElementById("location_map_canvas_billing");
    const coordinates = getMapCoordinates("#billing-latitude", "#billing-longitude");
    if (!canvas || !coordinates || typeof google === 'undefined' || !google.maps) {
        return null;
    }

    var myLatLng = {
        lat: coordinates.lat,
        lng: coordinates.lng
    };
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    const map = new google.maps.Map(canvas, {
        center: { lat: coordinates.lat, lng: coordinates.lng },
        zoom: 13,
        mapId: 'roadmap'
    });

    var marker = new AdvancedMarkerElement({
        map,
        position: myLatLng,
    });

    marker.setMap(map);
    var geocoder = geocoder = new google.maps.Geocoder();
    google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
        var coordinates = JSON.parse(coordinates);
        var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
        marker.position = { lat: coordinates['lat'], lng: coordinates['lng'] };
        map.panTo(latlng);

        document.getElementById('billing_latitude').value = coordinates['lat'];
        document.getElementById('billing_longitude').value = coordinates['lng'];

        geocoder.geocode({ 'latLng': latlng }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    document.getElementById('billing_address').value = results[1].formatted_address;
                }
            }
        });
    });

    const input = document.getElementById("billing-pac-input");
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
                document.getElementById('latitude').value = this.position.lat();
                document.getElementById('longitude').value = this.position.lng();

            });

            markers.push(mrkr);

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
};

$(document).on("keydown", "input", function (e) {
    if (e.which == 13) e.preventDefault();
});

async function locationShowingMap() {
    const canvas = document.getElementById("location_map_canvas");
    const coordinates = getMapCoordinates("#shipping-latitude", "#shipping-longitude");
    if (!canvas || !coordinates || typeof google === 'undefined' || !google.maps) {
        return null;
    }

    let myLatLng = {
        lat: coordinates.lat,
        lng: coordinates.lng
    };
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    const map = new google.maps.Map(canvas, {
        center: {
            lat: coordinates.lat,
            lng: coordinates.lng
        },
        zoom: 13,
        mapId: 'roadmap'
    });

    var marker = new AdvancedMarkerElement({
        position: myLatLng,
        map: map,
    });

    marker.setMap(map);
    var geocoder = geocoder = new google.maps.Geocoder();
    google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
        var latlng = new google.maps.LatLng(coordinates.lat, coordinates.lng);
        marker.position = { lat: coordinates.lat, lng: coordinates.lng };
        map.panTo(latlng);
    });

    return map;
}

/*End Show location on map*/

async function mapCallBackFunction() {
    await shippingAddressMap();
    await billingAddressMap();
    await locationShowingMap();
}

$(document).on('shown.bs.modal', '#locationModal', function () {
    locationShowingMap();
});


$(".readUrl").on('change', function () {
    let input = $(this).val();
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
            let imgData = e.target.result;
            let imgName = input.files[0].name;
            input.setAttribute("data-title", imgName);
        }
        reader.readAsDataURL(input.files[0]);
    }
});
