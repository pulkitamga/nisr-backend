"use strict";

function updateCartQuantityList(minimum_order_qty, key, incr, e) {
    let quantity_id = 'cart_quantity_web';
    updateCartCommon(minimum_order_qty, key, incr, e, quantity_id);
}

function updateCartQuantityListMobile(minimum_order_qty, key, incr, e) {
    let quantityId = 'cart_quantity_mobile';
    updateCartCommon(minimum_order_qty, key, incr, e, quantityId);
}

function updateCartCommon(minimum_order_qty, key, incr, e, quantity_id) {

    let quantity = parseInt($("#" + quantity_id + key).val()) + parseInt(incr);
    let exQuantity = $("#" + quantity_id + key);
    const currentStock = parseInt(exQuantity.data('current-stock')) || 0;

    if (currentStock > 0 && quantity > currentStock && e !== 'delete') {
        toastr.error($('#message-sorry-stock-limit-exceeded').data('text'));
        exQuantity.val(currentStock);
        return false;
    }

    if (minimum_order_qty > quantity && e != 'delete') {
        toastr.error($('#message-minimum-order-quantity-cannot-less-than').data('text') + minimum_order_qty);
        $(".cartQuantity" + key).val(minimum_order_qty);
        return false;
    }
    if (exQuantity.val() == exQuantity.data('min') && e == 'delete') {
        removeProductFromCartList(key)
    } else {
        $.post($('#route-cart-updateQuantity').data('url'), {
            _token: $('meta[name="_token"]').attr('content'),
            key,
            quantity
        }, function (response) {
            if (response.status == 0) {
                toastr.error(response.message, {
                    CloseButton: true,
                    ProgressBar: true
                });
                $(".cartQuantity" + key).val(response['qty']);
            } else {
                updateNavCart();
                $('#cart-summary').empty().html(response);
                $('[data-toggle="tooltip"]').tooltip()
                actionCheckoutFunctionInit()
                couponCode()
                setShippingIdFunctionCartDetails()
            }
        });
    }
}

function removeProductFromCartList(key) {
    $.post($('#route-cart-remove').data('url'), {
            _token: $('meta[name="_token"]').attr('content'),
            key: key
        },
        function (response) {
            updateNavCart();
            toastr.info($('#message-item-has-been-removed-from-cart').data('text'), {
                CloseButton: true,
                ProgressBar: true
            });
            let segmentArray = window.location.pathname.split('/');
            let segment = segmentArray[segmentArray.length - 1];
            if (segment === 'checkout-payment' || segment === 'checkout-details') {
                location.reload();
            }
            $('#cart-summary').empty().html(response.data)
            $('[data-toggle="tooltip"]').tooltip()
            actionCheckoutFunctionInit()
            couponCode()
            setShippingIdFunctionCartDetails();
        });
}

$('.qty_plus').on('click', function () {
    var $qty = $(this).parent().find('input');
    var currentVal = parseInt($qty.val());
    if (!isNaN(currentVal)) {
        $qty.val(currentVal + 1);
    }
    quantityListener();
});


$('.qty_minus').on('click', function () {
    var $qty = $(this).parent().find('input');
    var currentVal = parseInt($qty.val());
    if (!isNaN(currentVal) && currentVal > 1) {
        $qty.val(currentVal - 1);
    }
    quantityListener();
});


function quantityListener() {
    $('.cart_qty_input').each(function () {
        var qty = $(this);
        var minimumOrderQuantity = $(this).data('minimum-order') ?? 1;
        var currentStockQuantity = $(this).data('current-stock') ?? 1000;
        if (qty.val() == 1 || qty.val() == minimumOrderQuantity ) {
            qty.siblings('.qty_minus').html('<i class="tio-delete text-danger fs-12"></i>')
        } else {
            qty.siblings('.qty_minus').html('<i class="tio-remove"></i>')
        }

        try {
            if (qty.val() > currentStockQuantity) {
                qty.siblings('.qty_minus').html('<i class="tio-delete text-danger fs-12"></i>')
            }
        }catch (e) {
        }

    });
}

quantityListener();

cartQuantityInitialize();


function setShippingId(id, cartGroupId) {
    $.get({
        url: $('#route-customer-set-shipping-method').data('url'),
        dataType: 'json',
        data: {
            id: id,
            cart_group_id: cartGroupId
        },
        beforeSend: function () {
            $('#loading').show();
        },
        success: function () {
            location.reload();
        },
        complete: function () {
            $('#loading').hide();
        },
    });
}

function setShippingIdFunctionCartDetails() {
    $(document)
        .off('click.cartShipping', '.setShippingIdFunctionCartDetails')
        .on('click.cartShipping', '.setShippingIdFunctionCartDetails', function(){
            let id = $(this).data('id');
            let cartGroupId = $(this).data('cart-group');
            setShippingIdCartDetails(id, cartGroupId);
        });

    $(document)
        .off('change.cartShipping', '.set_shipping_onchange')
        .on('change.cartShipping', '.set_shipping_onchange', function(){
            let id = $(this).val();
            setShippingIdCartDetails(id, 'all_cart_group');
        });

    function setShippingIdCartDetails(id, cart_group_id) {
        $.get({
            url: $('#route-set-shipping-id').data('url'),
            dataType: 'json',
            data: {
                id: id,
                cart_group_id: cart_group_id
            },
            beforeSend: function () {
                $('#loading').addClass('d-grid');
            },
            success: function (data) {
                location.reload();
            },
            complete: function () {
                $('#loading').removeClass('d-grid');
            },
        });
    }
}

setShippingIdFunctionCartDetails();

function handleInstallationChargeToggle(event, chargeType) {
    let checkbox = event.target;
    let charges = checkbox.checked ? parseFloat(checkbox.dataset[chargeType] || 0) : 0;
    let cart_id = checkbox.dataset['cartId'];

   $.post($('#route-cart-updateInstalltionCharges').data('url'), {
        _token: $('meta[name="_token"]').attr('content'),
        cart_id,
        charges
    }, function (response) {
        if (response.status == 0) {
            toastr.error(response.message, {
                CloseButton: true,
                ProgressBar: true
            });
        } else {
            updateNavCart();
            $('#cart-summary').empty().html(response);
            $('[data-toggle="tooltip"]').tooltip()
            actionCheckoutFunctionInit()
            couponCode()
            setShippingIdFunctionCartDetails()
        }
    });
}

let exchangeUpdateRequestSequence = 0;
const exchangeLatestRequestByCart = {};

function setExchangeControlsDisabled(cartId, disabled) {
    $(`#exchange_charges_for_${cartId}, #exchange_quantity_web${cartId}`).prop("disabled", disabled);
    const pointerEvents = disabled ? "none" : "";
    const opacity = disabled ? 0.5 : "";
    $(`.exchange_qty_plus[data-cart-id="${cartId}"], .exchange_qty_minus[data-cart-id="${cartId}"]`)
        .css({ "pointer-events": pointerEvents, "opacity": opacity });
}

function getNormalizedExchangePayload(cartId, qty, charges) {
    let normalizedQty = parseInt(qty) || 0;
    normalizedQty = Math.max(0, normalizedQty);

    let normalizedCharges = parseFloat(charges) || 0;
    if (normalizedQty <= 0) {
        normalizedQty = 0;
        normalizedCharges = 0;
    }

    return {
        qty: normalizedQty,
        charges: normalizedCharges
    };
}

function postExchangeUpdate(cartId, qty, charges, onError = null) {
    const payload = getNormalizedExchangePayload(cartId, qty, charges);
    const productQty = parseInt($(`#cart_quantity_web${cartId}`).val()) || 0;
    if (payload.charges > 0 && payload.qty < 1) {
        toastr.error('Exchange qty must be at least 1 when Replacement Discount is enabled.');
        if (typeof onError === "function") {
            onError();
        }
        return;
    }
    if (productQty > 0 && payload.qty > productQty) {
        toastr.error('Exchange qty cannot exceed product quantity.');
        if (typeof onError === "function") {
            onError();
        }
        return;
    }

    const requestId = ++exchangeUpdateRequestSequence;
    exchangeLatestRequestByCart[cartId] = requestId;
    setExchangeControlsDisabled(cartId, true);

    return $.post($('#route-cart-updateExchangeCharges').data('url'), {
        _token: $('meta[name="_token"]').attr('content'),
        cart_id: cartId,
        qty: payload.qty,
        charges: payload.charges
    }, function (response) {
        if (exchangeLatestRequestByCart[cartId] !== requestId) {
            return;
        }

        if (response.status == 0) {
            toastr.error(response.message);
            if (typeof onError === "function") {
                onError();
            }
            return;
        }

        updateNavCart();
        $('#cart-summary').empty().html(response);
        $('[data-toggle="tooltip"]').tooltip()
        actionCheckoutFunctionInit()
        couponCode()
        setShippingIdFunctionCartDetails()
    }).always(function () {
        if (exchangeLatestRequestByCart[cartId] === requestId) {
            setExchangeControlsDisabled(cartId, false);
        }
    });
}

function handleExchangeChargeToggle(event, chargeType) {
    let checkbox = event.target;
    let isChecked = checkbox.checked;
    let charges = isChecked ? parseFloat(checkbox.dataset[chargeType] || 0) : 0;
    let cart_id = checkbox.dataset['cartId'];
    let qty = isChecked ? 1 : 0;
    $(`#exchange_quantity_web${cart_id}`).val(qty);
    const exchangeQTYDetails = document.getElementById(`exchangeQTYDetails_${cart_id}`);

    if (exchangeQTYDetails) {
        if (isChecked) {
            exchangeQTYDetails.classList.remove('d-none_exchange_qty');
        } else {
            exchangeQTYDetails.classList.add('d-none_exchange_qty');
        }
    }

    postExchangeUpdate(cart_id, qty, charges, function () {
        checkbox.checked = !isChecked;
        if (!checkbox.checked) {
            $(`#exchange_quantity_web${cart_id}`).val(0);
        }
        if (!exchangeQTYDetails) {
            return;
        }
        if (checkbox.checked) {
            exchangeQTYDetails.classList.remove('d-none_exchange_qty');
        } else {
            exchangeQTYDetails.classList.add('d-none_exchange_qty');
        }
    });
}

$(document).on("change", "input[id^='installtion_charges_for_']", function (event) {
    handleInstallationChargeToggle(event, "installationCharges");
});

$(document).on("change", "input[id^='exchange_charges_for_']", function (event) {
    handleExchangeChargeToggle(event, "exchangeCharges");
});

/*$('.exchange_qty_plus').on('click', function () {
    var $qty = $(this).parent().find('input');
    var currentVal = parseInt($qty.val());
    if (!isNaN(currentVal)) {
        $qty.val(currentVal + 1);
    }
    exchangeQuantityListener();
});


$('.exchange_qty_minus').on('click', function () {
    var $qty = $(this).parent().find('input');
    var currentVal = parseInt($qty.val());
    if (!isNaN(currentVal) && currentVal > 1) {
        $qty.val(currentVal - 1);
    }
    exchangeQuantityListener();
});*/

exchangeQuantityListener();

function exchangeQuantityListener() {
    $('.exchange_qty_input').each(function () {
        var qty = $(this);
        var minimumOrderQuantity = 1;
        var currentStockQuantity = $(this).data('current-stock') ?? 1000;
        var id = $(this).data('current-stock') ?? 1000;
        if (qty.val() == 1 || qty.val() == minimumOrderQuantity ) {
            qty.siblings('.exchange_qty_minus').html('<i class="tio-delete text-danger fs-12 "></i>')
        } else {
            qty.siblings('.exchange_qty_minus').html('<i class="tio-remove"></i>')
        }
    });
}

$(document).on("click", ".exchange_qty_plus", function () {
    let input = $(this).siblings(".exchange_qty_input");
    let cartId = input.data("cart-id");
    let charges = input.data("exchange-charges");
    let newQuantity = (parseInt(input.val()) || 0) + 1;
    const normalized = getNormalizedExchangePayload(cartId, newQuantity, charges);
    newQuantity = normalized.qty;
    input.val(newQuantity);
    updateExchangeQuantity(cartId, newQuantity, charges);
});

// Handle Minus (-) Button Click
$(document).on("click", ".exchange_qty_minus", function () {
    let input = $(this).siblings(".exchange_qty_input");
    let cartId = input.data("cart-id");
    let currentQuantity = parseInt(input.val());
    let charges = input.data("exchange-charges");
    let newQuantity = 1;
    if (currentQuantity > 1) {
        newQuantity = currentQuantity - 1;
    }
    const normalized = getNormalizedExchangePayload(cartId, newQuantity, charges);
    newQuantity = normalized.qty;
    input.val(newQuantity >= 1 ? newQuantity : 1);
    updateExchangeQuantity(cartId, newQuantity, charges);
});

// Handle Manual Quantity Input Change
$(document).on("change", ".exchange_qty_input", function () {
    let input = $(this);
    let cartId = input.data("cart-id");
    let charges = input.data("exchange-charges");
    let newQuantity = parseInt(input.val()) || 0; // Default to 0 if empty

    const isReplacementEnabled = $(`#exchange_charges_for_${cartId}`).is(":checked");
    if (isReplacementEnabled && newQuantity < 1) {
        newQuantity = 1;
        input.val(1);
    }

    updateExchangeQuantity(cartId, newQuantity, charges);
});

function updateExchangeQuantity(cartId, newQuantity, charges = 0) {
    postExchangeUpdate(cartId, newQuantity, charges);
}
