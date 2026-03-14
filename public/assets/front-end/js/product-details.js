"use strict";

$(document).ready(function () {
    const $stickyElement = $(".bottom-sticky");
    const $offsetElement = $(".product-details-shipping-details");

    $(window).on("scroll", function () {
        const elementOffset = $offsetElement?.offset()?.top;
        const scrollTop = $(window).scrollTop();

        if (scrollTop >= elementOffset) {
            $stickyElement.addClass("stick");
            $(".floating-btn-grp").removeClass("style-2");
        } else {
            $stickyElement.removeClass("stick");
            $(".floating-btn-grp").addClass("style-2");
        }
    });
});

$(document).ready(function () {
    // Constants
    const DESKTOP_BREAKPOINT = 767;
    const ANIMATION_DELAY = 150;

    // Cache selectors
    const $window = $(window);
    const $stickyTop = $('.product-details-sticky-top');
    const $stickySection = $('.product-details-sticky');

    function bindStickyHover() {
        if ($stickySection.hasClass('multi-variation-product')) {
            $stickySection.hover(
                function () {
                    $stickyTop.stop(true, true).delay(ANIMATION_DELAY).slideDown();
                },
                function () {
                    $stickyTop.stop(true, true).delay(ANIMATION_DELAY).slideUp();
                }
            );
        }
    }

    function unbindStickyHover() {
        $stickySection.off('mouseenter mouseleave');
        $stickyTop.stop(true, true).hide();
    }

    function handleBreakpoint() {
        const windowWidth = $window.width();

        if (windowWidth > DESKTOP_BREAKPOINT) {
            bindStickyHover();
        } else {
            unbindStickyHover();
        }
    }

    let resizeTimeout;
    $window.on('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleBreakpoint, 100);
    });

    handleBreakpoint();
});


// Select the element
const targetElement = document.querySelector('.product-add-and-buy-section-parent');

// Define the action to take when the element is in the viewport
function handleIntersect(entries) {
    let getHeight = $('.product-details-sticky-bottom').height();
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            $('.product-details-sticky').removeClass('active');
            $('.floating-btn-grp').removeClass('has-product-details-sticky');
            $('body').css('padding-bottom', "0px");
        } else {
            $('.product-details-sticky').addClass('active');
            $('.floating-btn-grp').addClass('has-product-details-sticky');
            $('body').css('padding-bottom', `calc(${getHeight}px + 2rem)`);
        }
    });
}

// Create an intersection observer
const observer = new IntersectionObserver(handleIntersect, {
    root: null, // Use the viewport as the root
    threshold: 0.1 // Trigger when 10% of the element is visible
});

// Start observing the target element
if (targetElement) {
    observer.observe(targetElement);
}

cartQuantityInitialize();
getVariantPrice(".add-to-cart-details-form");
getVariantPrice(".add-to-cart-sticky-form");

$(".view_more_button").on("click", function () {
    loadReviewOnDetailsPage();
});

let loadReviewCount = 1;

function loadReviewOnDetailsPage() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });
    $.ajax({
        type: "post",
        url: $("#route-review-list-product").data("url"),
        data: {
            product_id: $("#products-details-page-data").data("id"),
            offset: loadReviewCount,
        },
        success: function (data) {
            $("#product-review-list").append(data.productReview);
            if (data.checkReviews == 0) {
                $(".view_more_button").removeClass("d-none").addClass("d-none");
            } else {
                $(".view_more_button").addClass("d-none").removeClass("d-none");
            }

            $(".show-instant-image").on("click", function () {
                let link = $(this).data("link");
                showInstantImage(link);
            });
        },
    });
    loadReviewCount++;
}

$("#chat-form").on("submit", function (e) {
    e.preventDefault();

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    $.ajax({
        type: "post",
        url: $("#route-messages-store").data("url"),
        data: $("#chat-form").serialize(),
        success: function (respons) {
            toastr.success($("#message-send-successfully").data("text"), {
                CloseButton: true,
                ProgressBar: true,
            });
            $("#chat-form").trigger("reset");
        },
    });
});

function renderFocusPreviewImageByColor() {
    $(".focus-preview-image-by-color").on("click", function () {
        let id = $(this).data("colorid");
        $(`.color-variants-${id}`).click();
    });
}
renderFocusPreviewImageByColor();

let map;
let marker;
let geocoder;
let serviceLocationCenter;

function getServiceLocationDefaults() {
    return {
        lat: parseFloat(document.getElementById("default-latitude-address")?.dataset?.value || 28.6139),
        lng: parseFloat(document.getElementById("default-longitude-address")?.dataset?.value || 77.2090),
    };
}

function setServiceLocationInputs(lat, lng) {
    const latitudeInput = document.getElementById("latitude");
    const longitudeInput = document.getElementById("longitude");

    if (latitudeInput) {
        latitudeInput.value = lat;
    }

    if (longitudeInput) {
        longitudeInput.value = lng;
    }
}

function reverseGeocodeServiceLocation(lat, lng) {
    const addressInput = document.getElementById("address_details");

    if (!geocoder || !addressInput) {
        return;
    }

    geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function (results, status) {
        if (status !== "OK" || !results || !results[0]) {
            console.error("Geocode failed:", status);
            return;
        }

        addressInput.value = results[0].formatted_address;

        if (typeof window.syncServiceLocationFromCoordinates === "function") {
            window.syncServiceLocationFromCoordinates(results[0]);
        }
    });
}

function updateServiceLocation(lat, lng, options = {}) {
    const nextCenter = { lat: parseFloat(lat), lng: parseFloat(lng) };
    serviceLocationCenter = nextCenter;
    setServiceLocationInputs(nextCenter.lat, nextCenter.lng);

    if (!map || !marker) {
        return;
    }

    marker.setPosition(nextCenter);
    map.setCenter(nextCenter);

    if (options.zoom !== false) {
        map.setZoom(options.zoomLevel || 15);
    }

    google.maps.event.trigger(map, "resize");
    reverseGeocodeServiceLocation(nextCenter.lat, nextCenter.lng);
}

function fetchAndRenderLocation() {
    const fallback = getServiceLocationDefaults();
    const applyLocation = (lat, lng) => {
        serviceLocationCenter = { lat, lng };

        if (!map && window.google?.maps) {
            mapsShopping();
        }

        updateServiceLocation(lat, lng);
    };

    if (!navigator.geolocation) {
        applyLocation(fallback.lat, fallback.lng);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => applyLocation(position.coords.latitude, position.coords.longitude),
        () => applyLocation(fallback.lat, fallback.lng),
        {
            enableHighAccuracy: true,
            timeout: 8000,
            maximumAge: 300000,
        }
    );
}

function mapsShopping() {
    const mapCanvas = document.getElementById("location_map_canvas");
    if (!mapCanvas || !window.google?.maps) {
        return;
    }

    const defaultCenter = serviceLocationCenter || getServiceLocationDefaults();
    geocoder = new google.maps.Geocoder();

    map = new google.maps.Map(mapCanvas, {
        center: defaultCenter,
        zoom: 13,
    });

    marker = new google.maps.Marker({
        map: map,
        position: defaultCenter,
        draggable: true,
    });

    const input = document.getElementById("pac-input");
    if (input) {
        const searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });

        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();
            if (!places || places.length === 0) {
                return;
            }

            const place = places[0];
            if (!place.geometry || !place.geometry.location) {
                return;
            }

            updateServiceLocation(place.geometry.location.lat(), place.geometry.location.lng());
        });
    }

    marker.addListener("dragend", function () {
        const position = marker.getPosition();
        updateServiceLocation(position.lat(), position.lng(), { zoom: false });
    });

    map.addListener("click", function (mapsMouseEvent) {
        const clickedLatLng = mapsMouseEvent.latLng;
        updateServiceLocation(clickedLatLng.lat(), clickedLatLng.lng());
    });

    updateServiceLocation(defaultCenter.lat, defaultCenter.lng, { zoom: false });
}

window.fetchAndRenderLocation = fetchAndRenderLocation;
window.mapsShopping = mapsShopping;


