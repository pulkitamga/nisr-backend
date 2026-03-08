"use strict";

let elementViewAllHoldOrdersSearch = $(".view_all_hold_orders_search");
let getYesWord = $("#message-yes-word").data("text");
let getNoWord = $("#message-no-word").data("text");
let messageAreYouSure = $("#message-are-you-sure").data("text");
let isPosOrderPlacing = false;
let isPosAddToCartRunning = false;
let lastQuickViewRequestToken = 0;

function normalizePosNumber(value) {
    const source = (value ?? "").toString();
    // Support Arabic and Persian numerals in RTL input.
    return source
        .replace(/[٠-٩]/g, (d) => String(d.charCodeAt(0) - 1632))
        .replace(/[۰-۹]/g, (d) => String(d.charCodeAt(0) - 1776))
        .replace(/[^\d.-]/g, "");
}

function parsePosInt(value, fallback = 0) {
    const parsed = parseInt(normalizePosNumber(value), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function syncVisibleQtyMinusButtonState() {
    const inCartSection = $(".in-cart-quantity-system");
    const inCartVisible = inCartSection.length > 0
        && !inCartSection.hasClass("d--none")
        && !inCartSection.hasClass("d-none");

    const input = inCartVisible ? $(".in-cart-quantity-field").first() : $(".cart-qty-field").first();
    const minusBtn = inCartVisible ? $(".in-cart-quantity-minus").first() : $(".btn-number[data-type='minus'][data-field='quantity']").first();

    if (!input.length || !minusBtn.length) {
        return;
    }

    const minValue = Math.max(parsePosInt(input.attr("min"), 1), 1);
    const valueCurrent = parsePosInt(input.val(), minValue);
    if (valueCurrent > minValue) {
        minusBtn.removeAttr("disabled");
    } else {
        minusBtn.attr("disabled", true);
    }
}

function generatePosIdempotencyKey(action) {
    const prefix = (action || "pos").toString().trim().toLowerCase().replace(/[^a-z0-9_-]/g, "-");
    const stamp = Date.now().toString(36);
    if (window.crypto && typeof window.crypto.randomUUID === "function") {
        return `${prefix}-${stamp}-${window.crypto.randomUUID()}`;
    }
    const fallback = Math.random().toString(36).slice(2, 12);
    return `${prefix}-${stamp}-${fallback}`;
}

function getPosActiveCartId() {
    const byHiddenInput = ($("#order-place input[name='cart_id']").val() || "").toString().trim();
    if (byHiddenInput.length > 0) {
        return byHiddenInput;
    }

    const cartIdElement = $("#cart_id_primary");
    const byData = (cartIdElement.data("cart-id") || "").toString().trim();
    if (byData.length > 0) {
        return byData;
    }

    const byText = (cartIdElement.text() || "").toString().trim();
    return byText;
}

function setQuickViewLineKey(lineKey = "") {
    const normalizedLineKey = (lineKey || "").toString().trim();
    $("#line-key").val(normalizedLineKey);
}

function getQuickViewSelectedQuantity() {
    const inCartSection = $(".in-cart-quantity-system");
    const inCartVisible = inCartSection.length > 0
        && !inCartSection.hasClass("d--none")
        && !inCartSection.hasClass("d-none");

    let qty = parsePosInt((inCartVisible ? $(".in-cart-quantity-field").val() : $(".cart-qty-field").val()), 0);
    if (!Number.isFinite(qty) || qty < 1) {
        qty = parsePosInt($(".cart-qty-field").val(), 0);
    }
    if (!Number.isFinite(qty) || qty < 1) {
        qty = 1;
    }
    return qty;
}

function getQuickViewMaxExchangeQuantity() {
    return Math.max(getQuickViewSelectedQuantity(), 0);
}

function syncExchangeQuantityState() {
    const exchangeCheckbox = $("#exchange-charge-checkbox");
    const exchangeQtyWrapper = $("#exchange-qty-wrapper");
    const exchangeQtyInput = $("#exchange-quantity");
    const exchangeMinusBtn = $(".exchange-qty-field-minus");
    const exchangePlusBtn = $(".exchange-qty-field-plus");

    if (!exchangeCheckbox.length || !exchangeQtyInput.length) {
        return;
    }

    const maxExchangeQty = getQuickViewMaxExchangeQuantity();
    const canUseExchange = maxExchangeQty > 0;
    if (!canUseExchange) {
        exchangeCheckbox.prop("checked", false);
    }
    exchangeCheckbox.prop("disabled", !canUseExchange);

    const exchangeEnabled = exchangeCheckbox.is(":checked") && canUseExchange;
    let exchangeQty = parsePosInt(exchangeQtyInput.val(), 0);
    if (!Number.isFinite(exchangeQty) || exchangeQty < 0) {
        exchangeQty = 0;
    }
    // Keep user-entered exchange qty unchanged when product qty changes.
    // Final relationship validation (exchange <= product qty) is enforced on submit.
    if (exchangeEnabled && exchangeQty < 1) {
        exchangeQty = 1;
    }
    if (!exchangeEnabled) {
        exchangeQty = 0;
    }

    exchangeQtyInput.val(exchangeQty);
    exchangeQtyInput.attr("min", exchangeEnabled ? 1 : 0);
    exchangeQtyInput.attr("max", maxExchangeQty);

    exchangeQtyWrapper.toggleClass("d-none", !exchangeEnabled);
    exchangeQtyInput.prop("disabled", !exchangeEnabled || !canUseExchange);
    exchangeMinusBtn.prop("disabled", !exchangeEnabled || exchangeQty <= 1);
    exchangePlusBtn.prop("disabled", !exchangeEnabled || exchangeQty >= maxExchangeQty);
}

document.addEventListener("keydown", function (event) {
    if (event.altKey && event.code === "KeyO") {
        $("#submit_order").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyZ") {
        $("#payment_close").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyS") {
        $("#order_complete").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyC") {
        emptyCart();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyA") {
        $("#add_new_customer").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyN") {
        $("#submit_new_customer").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyK") {
        $("#short-cut").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyP") {
        $("#print_invoice").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyQ") {
        $("#search").focus();
        $("#-pos-search-box").css("display", "none");
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyE") {
        $("#pos-search-box").css("display", "none");
        $("#extra_discount").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyD") {
        $("#pos-search-box").css("display", "none");
        $("#coupon_discount").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyB") {
        $("#invoice_close").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyX") {
        $(".action-clear-cart").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyR") {
        $(".action-new-order").click();
        event.preventDefault();
    }
});

$(".search-bar-input").on("keyup", function () {
    $(".pos-search-card").removeClass("d-none").show();
    let name = $(".search-bar-input").val();
    const branch_id = $('#branch_id').data('branch') || '';
    let elementSearchResultBox = $(".search-result-box");
    if (name.length > 0) {
        $("#pos-search-box").removeClass("d-none").show();
        $.get({
            url: $("#route-admin-products-search-product").data("url"),
            dataType: "json",
            data: {
                name: name,
                branch_id: branch_id,
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                elementSearchResultBox.empty().html(data.result);
                renderSelectProduct();
                renderQuickViewSearchFunctionality();
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    } else {
        elementSearchResultBox.empty().hide();
    }
});

$(".action-category-filter").on("change", (event) => {
    let getUrl = new URL(window.location.href);
    getUrl.searchParams.set("category_id", $(event.target).val());
    window.location.href = getUrl.toString();
});

function renderCustomerAmountForPay() {
    if (parseFloat($('.customer-wallet-balance').val()) < parseFloat($('.total-amount').val())) {
        disableOrderPlaceButton();
        $('.wallet-balance-input').addClass('border-danger');
    } else {
        $('.wallet-balance-input').removeClass('border-danger');
    }
}

function disableOrderPlaceButton() {
    var selectedPaymentType = $('input[name="type"]:checked').val();
    if (selectedPaymentType === 'wallet') {
        $('.action-form-submit').attr('disabled', true);
    } else {
        $('.action-form-submit').attr('disabled', false);
    }
}
$(".action-customer-change").on("change", function () {
    const branch_id = $('#branch_id').data('branch') || '';
    $.post({
        url: $("#route-admin-pos-change-customer").data("url"),
        data: {
            _token: $('meta[name="_token"]').attr("content"),
            user_id: $(this).val(),
            branch_id: branch_id,
            cart_id: getPosActiveCartId(),
        },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            $("#cart-summary").empty().html(data.view);
            viewAllHoldOrders("keyup");
            posUpdateQuantityFunctionality();
            basicFunctionalityForCartSummary();
            removeFromCart();
            renderCustomerAmountForPay();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
});

$(".action-view-all-hold-orders").on("click", () => viewAllHoldOrders());
elementViewAllHoldOrdersSearch.on("keyup", () => viewAllHoldOrders("keyup"));

function viewAllHoldOrders(action = null) {
    const branchId = $('#branch_id').data('branch') || '';
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });
    $.post({
        url: $("#route-admin-pos-view-hold-orders").data("url"),
        data: {
            customer: elementViewAllHoldOrdersSearch.val(),
            branch_id: branchId,
        },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            $("#hold-orders-modal-content").empty().html(data.view);
            if (action !== "keyup") {
                $("#hold-orders-modal-btn").click();
            }
            $(".total_hold_orders").text(data.totalHoldOrders);
            renderViewHoldOrdersFunctionality();
            basicFunctionalityForCartSummary();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
}

function renderSelectProduct() {
    $(".action-get-variant-for-already-in-cart").off("click").on("click", function () {
        getVariantForAlreadyInCart($(this).data("action"));
    });

    $(".action-add-to-cart").off("click").on("click", function (e) {
        e.preventDefault();
        addToCart();
    });

    $(".action-color-change").off("click").on("click", function () {
        let val = $(this).val();
        $(".color-border").removeClass("border-add");
        $("#label-" + val.id).addClass("border-add");
    });

    cartQuantityInitialize();
    setQuickViewLineKey("");
    getVariantPrice();
    $(".variant-change input , .cart-qty-field").off("change").on("change", function () {
        getVariantPrice();
        syncExchangeQuantityState();
        syncVisibleQtyMinusButtonState();
    });
    $("#add-to-cart-form .in-cart-quantity-field").off("change").on("change", function () {
        getVariantPrice("already_in_cart");
        syncExchangeQuantityState();
        syncVisibleQtyMinusButtonState();
    });
    $("#exchange-charge-checkbox").off("change").on("change", function () {
        syncExchangeQuantityState();
    });
    $("#exchange-quantity").off("input change").on("input change", function () {
        syncExchangeQuantityState();
    });
    $(".cart-qty-field, .in-cart-quantity-field").off("input").on("input", function () {
        syncExchangeQuantityState();
        syncVisibleQtyMinusButtonState();
    });

    $(".cart-qty-field").off("focus").on("focus", function () {
        $(this).closest(".product-quantity-group").addClass("border-primary");
    });

    $(".cart-qty-field").off("blur").on("blur", function () {
        $(this)
            .closest(".product-quantity-group")
            .removeClass("border-primary");
    });

    $(".in-cart-quantity-field").off("focus").on("focus", function () {
        $(this).closest(".product-quantity-group").addClass("border-primary");
    });

    $(".in-cart-quantity-field").off("blur").on("blur", function () {
        $(this)
            .closest(".product-quantity-group")
            .removeClass("border-primary");
    });

    syncExchangeQuantityState();
    syncVisibleQtyMinusButtonState();
}

renderSelectProduct();
renderQuickViewFunctionality();

function renderQuickViewFunctionality() {
    $(".action-select-product").off("click").on("click", function () {
        quickView($(this).data("id"));
    });
}

function renderQuickViewSearchFunctionality() {
    $(".action-select-search-product").off("click").on("click", function () {
        quickView($(this).data("id"));
    });
}

function basicFunctionalityForCartSummary() {
    const branchId = $('#branch_id').data('branch') || '';
    $(".action-empty-alert-show").off("click").on("click", () => {
        toastr.warning($("#message-cart-is-empty").data("text"), {
            CloseButton: true,
            ProgressBar: true,
        });
    });
    $(".action-clear-cart").off("click").on("click", () => {
        let clearUrl = $("#route-admin-pos-clear-cart-ids").data("url");
        if (branchId) {
            clearUrl += (clearUrl.includes("?") ? "&" : "?") + "branch_id=" + encodeURIComponent(branchId);
        }
        const activeCartId = getPosActiveCartId();
        if (activeCartId) {
            clearUrl += (clearUrl.includes("?") ? "&" : "?") + "cart_id=" + encodeURIComponent(activeCartId);
        }
        document.location.href = clearUrl;
    });

    $(".action-new-order").off("click").on("click", () => {
        let newOrderUrl = $("#route-admin-pos-new-cart-id").data("url");
        if (branchId) {
            newOrderUrl += (newOrderUrl.includes("?") ? "&" : "?") + "branch_id=" + encodeURIComponent(branchId);
        }
        const activeCartId = getPosActiveCartId();
        if (activeCartId) {
            newOrderUrl += (newOrderUrl.includes("?") ? "&" : "?") + "cart_id=" + encodeURIComponent(activeCartId);
        }
        document.location.href = newOrderUrl;
    });

    $(".action-cart-change").off("click").on("click", function () {
        let value = $(this).data("cart");
        let dynamicUrl = $("#route-admin-pos-change-cart-editable").data("url");
        dynamicUrl = dynamicUrl.replace(":value", `${value}`);
        if (branchId) {
            dynamicUrl += (dynamicUrl.includes("?") ? "&" : "?") + "branch_id=" + encodeURIComponent(branchId);
        }
        window.location.href = dynamicUrl;
    });

    $(".action-empty-cart").off("click").on("click", function () {
        Swal.fire({
            title: messageAreYouSure,
            text: $("#message-you-want-to-remove-all-items-from-cart").data(
                "text"
            ),
            type: "warning",
            showCancelButton: true,
            cancelButtonColor: "default",
            confirmButtonColor: "#161853",
            cancelButtonText: getNoWord,
            confirmButtonText: getYesWord,
            reverseButtons: true,
        }).then((result) => {
            if (result.value) {
                $.post(
                    $("#route-admin-pos-empty-cart").data("url"),
                    {
                        _token: $('meta[name="_token"]').attr("content"),
                        branch_id: branchId,
                        cart_id: getPosActiveCartId(),
                    },
                    function (data) {
                        $("#cart-summary").empty().html(data.view);
                        toastr.info(
                            $("#message-item-has-been-removed-from-cart").data(
                                "text"
                            ),
                            {
                                CloseButton: true,
                                ProgressBar: true,
                            }
                        );
                    }
                );
            }
        });
    });

    $(".action-form-submit").off("click").on("click", function () {
        if (isPosOrderPlacing) {
            return;
        }

        if (checkedPaidAmount()) {
            Swal.fire({
                title: messageAreYouSure,
                type: "warning",
                text: $(this).data("message"),
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: getNoWord,
                confirmButtonText: getYesWord,
                reverseButtons: true,
            }).then(function (result) {
                if (result.value) {
                    if (isPosOrderPlacing) {
                        return;
                    }
                    isPosOrderPlacing = true;
                    $('.action-form-submit').attr('disabled', true);

                    let formData = new FormData(document.getElementById('order-place'));
                    formData.append('idempotency_key', generatePosIdempotencyKey('place-order'));
                    $.ajaxSetup({
                        headers: {
                            "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                    });
                    $.post({
                        url: $("#order-place").attr("action"),
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $("#loading").fadeIn();
                        },
                        success: function (response) {
                            if (Boolean(response.checkProductTypeForWalkingCustomer) === true) {
                                $('#add-customer').modal('show');
                                $('.alert--message-for-pos').addClass('active');
                                $('.alert--message-for-pos .warning-message').empty().html(response.message);
                                isPosOrderPlacing = false;
                                $('.action-form-submit').attr('disabled', false);
                            } else {
                                if (response && response.orderId) {
                                    let nextUrl = new URL(window.location.href);
                                    nextUrl.searchParams.set("last_order_id", response.orderId);
                                    if (response.cartId) {
                                        nextUrl.searchParams.set("cart_id", response.cartId);
                                    }
                                    window.location.href = nextUrl.toString();
                                } else {
                                    location.reload();
                                }
                            }
                        },
                        error: function () {
                            isPosOrderPlacing = false;
                            $('.action-form-submit').attr('disabled', false);
                        },
                        complete: function () {
                            $("#loading").fadeOut();
                            if (!document.hidden) {
                                isPosOrderPlacing = false;
                                $('.action-form-submit').attr('disabled', false);
                            }
                        },
                    });
                }
            });
        }
    });

    $('.option-buttons input').on('change', function () {
        renderCustomerAmountForPay();
        let type = $(this).val();
        if ($(this).is(':checked')) {
            $('.cash-change-section').hide();
            if (type === 'cash') {
                $('.cash-change-amount').show();
            } else if (type === 'card') {
                $('.cash-change-card').removeClass('d-none').show();
            } else if (type === 'wallet') {
                let insufficientBalanceMessage = $('#message-insufficient-balance');
                let cashChangeWallet = $('.cash-change-wallet');
                if (parseFloat($('.customer-wallet-balance').val()) < parseFloat($('.total-amount').val())) {
                    insufficientBalanceMessage.text(insufficientBalanceMessage.data('text'));
                }
                cashChangeWallet.show();
                cashChangeWallet.removeClass('d-none').show();
            }
        }
    });

    $('.option-buttons input').trigger('change');

    $('.pos-paid-amount-element').on("keyup change", function (event) {
        if (event.which < 48 || event.which > 57) {
            event.preventDefault();
        }
        let minimumAmount = $(this).attr('min');
        let GivenAmount = parseFloat($(this).val());
        let currencyPosition = $(this).data('currency-position');
        let currencySymbol = $(this).data('currency-symbol');

        if (GivenAmount >= minimumAmount) {
            $(this).removeClass('border-danger');
        } else {
            $(this).addClass('border-danger');
        }

        let amount = GivenAmount - minimumAmount;
        if (!$(this).val()) {
            amount = 0;
        }
        let result = '';
        if (currencyPosition?.toString() === 'left') {
            result = currencySymbol + amount;
        } else {
            result = amount + currencySymbol;
        }

        $('.pos-change-amount-element').text(result);
    });
}

basicFunctionalityForCartSummary();
posUpdateQuantityFunctionality();

function checkedPaidAmount() {
    let totalAmount = parseFloat($(".total-amount").val() || 0);
    if (!Number.isFinite(totalAmount) || totalAmount <= 0) {
        toastr.warning($("#message-cart-is-empty").data("text"), {
            CloseButton: true,
            ProgressBar: true,
        });
        return false;
    }

    let paidAmount = $(".pos-paid-amount-element");
    if ($('.paid-by-cash').prop('checked') && paidAmount.val() === '') {
        toastr.error($("#message-enter-valid-amount").data("text"), {
            CloseButton: true,
            ProgressBar: true,
        });
        return false;
    } else if ($('.paid-by-cash').prop('checked') && parseFloat(paidAmount.val()) < parseFloat(paidAmount.attr('min'))) {
        toastr.error($("#message-less-than-total-amount").data("text"), {
            CloseButton: true,
            ProgressBar: true,
        });
        return false;
    }
    return true;
}

function refreshPosCartAfterDiscount(viewHtml) {
    if (typeof viewHtml !== "string" || viewHtml.trim().length === 0) {
        return;
    }

    if ($("#order-place").length) {
        $("#order-place").replaceWith(viewHtml);
    } else {
        const fallbackOrderForm = $("#cart").closest("form");
        if (fallbackOrderForm.length) {
            fallbackOrderForm.replaceWith(viewHtml);
        }
    }

    basicFunctionalityForCartSummary();
    posUpdateQuantityFunctionality();
    viewAllHoldOrders("keyup");
    removeFromCart();
    renderCustomerAmountForPay();
    $("#search").focus();
}

function closePosDiscountModal(modalSelector) {
    const modalElement = $(modalSelector);
    if (!modalElement.length) {
        return;
    }

    if (typeof modalElement.modal === "function") {
        modalElement.modal("hide");
    }

    modalElement
        .removeClass("show")
        .attr("aria-hidden", "true")
        .css("display", "none");

    $("body").removeClass("modal-open").css("padding-right", "");
    $(".modal-backdrop").remove();
}

$(".action-coupon-discount").on("click", function (event) {
    event.preventDefault();

    let couponCode = ($("#coupon_code").val() || "").trim();
    if (couponCode.length === 0) {
        toastr.error($(this).data('error-message'));
        return;
    }

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });
    $.post({
        url: $("#route-admin-pos-coupon-discount").data("url"),
        data: {
            coupon_code: couponCode,
            cart_id: getPosActiveCartId(),
            branch_id: $('#branch_id').data('branch') || '',
        },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            if (data.coupon === "success") {
                toastr.success(
                    $("#message-coupon-added-successfully").data("text"),
                    {
                        CloseButton: true,
                        ProgressBar: true,
                    }
                );
            } else if (data.coupon === "amount_low") {
                toastr.warning(
                    $(
                        "#message-this-discount-is-not-applied-for-this-amount"
                    ).data("text"),
                    {
                        CloseButton: true,
                        ProgressBar: true,
                    }
                );
            } else if (data.coupon === "cart_empty") {
                toastr.warning($("#message-cart-is-empty").data("text"), {
                    CloseButton: true,
                    ProgressBar: true,
                });
            } else {
                toastr.warning($("#message-coupon-is-invalid").data("text"), {
                    CloseButton: true,
                    ProgressBar: true,
                });
            }

            closePosDiscountModal("#add-coupon-discount");
            refreshPosCartAfterDiscount(data.view);
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
});

$(".action-extra-discount").on("click", function (event) {
    event.preventDefault();

    let discount = ($("#dis_amount").val() || "").toString().trim();
    let type = $("#type_ext_dis").val();
    if (discount.length === 0) {
        toastr.error($(this).data('error-message'));
        return;
    }

    if (parseFloat(discount) > 0) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-update-discount").data("url"),
            data: {
                discount: discount,
                type: type,
                cart_id: getPosActiveCartId(),
                branch_id: $('#branch_id').data('branch') || '',
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                if (data.extraDiscount === "success") {
                    toastr.success(
                        $("#message-extra-discount-added-successfully").data(
                            "text"
                        ),
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                } else if (data.extraDiscount === "empty") {
                    toastr.warning($("#message-cart-is-empty").data("text"), {
                        CloseButton: true,
                        ProgressBar: true,
                    });
                } else {
                    toastr.warning(
                        $(
                            "#message-this-discount-is-not-applied-for-this-amount"
                        ).data("text"),
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                }

                closePosDiscountModal("#add-discount");
                refreshPosCartAfterDiscount(data.view);
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    } else {
        toastr.warning(
            $("#message-amount-can-not-be-negative-or-zero").data("text"),
            {
                CloseButton: true,
                ProgressBar: true,
            }
        );
    }
});

function posUpdateQuantityFunctionality() {
    $(".action-pos-update-quantity").off("change").on("change", function (event) {
        let getKey = $(this).data("product-key");
        let quantity = parsePosInt($(this).val(), 0);
        $(this).val(quantity > 0 ? quantity : 1);
        let variant = $(this).data("product-variant");
        let lineKey = $(this).data("line-key");
        getPOSUpdateQuantity(getKey, quantity, event, variant, lineKey);
    });
}

function getPOSUpdateQuantity(key, qty, e, variant = null, lineKey = null) {
    const branch_id = $('#branch_id').data('branch') || '';

    if (qty !== "") {
        $.post(
            $("#route-admin-pos-update-quantity").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                key: key,
                quantity: qty,
                variant: variant,
                line_key: lineKey,
                branch_id: branch_id,
                cart_id: getPosActiveCartId(),
            },
            function (data) {
                updateQuantityResponseProcess(data);
            }
        );
    } else {
        let element = $(e.target);
        let minValue = parsePosInt(element.attr("min"), 1);
        $.post(
            $("#route-admin-pos-update-quantity").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                key: key,
                quantity: minValue,
                variant: variant,
                line_key: lineKey,
                branch_id: branch_id,
                cart_id: getPosActiveCartId(),
            },
            function (data) {
                updateQuantityResponseProcess(data);
            }
        );
    }

    if (e.type == "keydown") {
        if (
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
            return;
        }
        if (
            (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }
    }
}

function updateQuantityResponseProcess(data) {
    if (data.exchangeQtyInvalid && data.message) {
        toastr.warning(data.message, {
            CloseButton: true,
            ProgressBar: true,
        });
    }
    if (data.productType === "physical" && data.qty < 0) {
        toastr.warning(
            $("#message-product-quantity-is-not-enough").data("text"),
            {
                CloseButton: true,
                ProgressBar: true,
            }
        );
    }
    if (data.upQty === "zeroNegative") {
        toastr.warning(
            $("#message-product-quantity-cannot-be-zero-in-cart").data("text"),
            {
                CloseButton: true,
                ProgressBar: true,
            }
        );
    }
    if (data.quantityUpdate == 1) {
        toastr.success($("#message-product-quantity-updated").data("text"), {
            CloseButton: true,
            ProgressBar: true,
        });
    }
    $("#order-place").replaceWith(data.view);
    basicFunctionalityForCartSummary();
    posUpdateQuantityFunctionality();
    viewAllHoldOrders("keyup");
    removeFromCart();
}

let dropdownSelect = $("#dropdown-order-select");
dropdownSelect.on(
    "click",
    ".dropdown-menu .dropdown-item:not(:last-child)",
    function () {
        let selectedText = $(this).text();
        dropdownSelect.find(".dropdown-toggle").text(selectedText);
    }
);

$("#order-place").submit(function (eventObj) {
    eventObj.preventDefault();
    let customerValue = $("#customer").val();
    if (customerValue) {
        $(this).append(
            '<input type="hidden" name="user_id" value="' +
            customerValue +
            '" /> '
        );
    }
    return true;
});

$(function () {
    $(document).on("click", "input[type=number]", function () {
        this.select();
    });
});

window.addEventListener("click", function (event) {
    let searchResultBoxes =
        document.getElementsByClassName("search-result-box");
    for (let i = 0; i < searchResultBoxes.length; i++) {
        let searchResultBox = searchResultBoxes[i];
        if (
            event.target !== searchResultBox &&
            !searchResultBox.contains(event.target)
        ) {
            searchResultBox.style.display = "none";
        }
    }
});

function renderViewHoldOrdersFunctionality() {
    $(".action-cancel-customer-order").on("click", function () {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-cancel-order").data("url"),
            data: {
                cart_id: $(this).data("cart-id"),
                branch_id: $('#branch_id').data('branch') || '',
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                $("#hold-orders-modal-content").empty().html(data.view);
                renderViewHoldOrdersFunctionality();
                toastr.info(data.message, {
                    CloseButton: true,
                    ProgressBar: true,
                });
                location.reload();
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    });
}

$(".action-print-pos-invoice").on("click", function () {
    const divName = $(this).data("print");
    printSpecificSectionWithPrintArea(divName)
});

function printSpecificSectionWithPrintArea(selector) {
    try {
        $(selector).printThis();
    } catch (e) {
    }
}

const renderRippleEffect = () => {
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement("span");
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.classList.add("ripple");
        const ripple = button.getElementsByClassName("ripple")[0];
        if (ripple) {
            ripple.remove();
        }
        button.appendChild(circle);
    }
    const buttons = document.getElementsByClassName("btn-number");
    for (const button of buttons) {
        button.addEventListener("click", createRipple);
    }
};

function quickView(product_id) {
    const branch_id = $('#branch_id').data('branch') || '';
    const requestToken = ++lastQuickViewRequestToken;
    $.ajax({
        url: $("#route-admin-pos-quick-view").data("url"),
        type: "GET",
        data: {
            product_id: product_id,
            branch_id: branch_id,
        },
        dataType: "json",
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            if (requestToken !== lastQuickViewRequestToken) {
                return;
            }
            if (!data || Number(data.success) !== 1) {
                if (data && data.message) {
                    toastr.error(data.message, {
                        CloseButton: true,
                        ProgressBar: true,
                    });
                }
                return;
            }
            $("#quick-view-modal").empty().html(data.view);
            renderSelectProduct();
            renderRippleEffect();
            closeAlertMessage();
            $("#quick-view").modal("show");
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
}

function getVariantForAlreadyInCart(event = null) {
    let current_val = parsePosInt($(".in-cart-quantity-field").val(), 0);
    if (current_val > 0) {
        $(".in-cart-quantity-minus").removeAttr("disabled");
        if (event == "plus") {
            $(".in-cart-quantity-field").val(current_val + 1);
        } else {
            $(".in-cart-quantity-field").val(current_val - 1);
            if (current_val <= 2) {
                $(".in-cart-quantity-minus").attr("disabled", true);
            }
        }
    } else {
        $(".in-cart-quantity-minus").attr("disabled", true);
    }
    syncExchangeQuantityState();
    syncVisibleQtyMinusButtonState();
    getVariantPrice("already_in_cart");
}

function checkAddToCartValidity() {
    var names = {};
    $("#add-to-cart-form input:radio").each(function () {
        names[$(this).attr("name")] = true;
    });
    var count = 0;
    $.each(names, function () {
        count++;
    });

    if ($("input:radio:checked").length - 1 == count) {
        return true;
    }
    return false;
}

function cartQuantityInitialize() {
    $(".btn-number").click(function (e) {
        e.preventDefault();
        let fieldName = $(this).attr("data-field");
        let type = $(this).attr("data-type");
        // Scope to the current quantity group first to avoid updating a wrong input.
        let input = $(this).closest(".product-quantity-group").find("input[name='" + fieldName + "']");
        if (!input.length) {
            input = $(this).siblings("input[name='" + fieldName + "']");
        }
        if (!input.length) {
            input = $("input[name='" + fieldName + "']").first();
        }
        let currentVal = parsePosInt(input.val(), 0);
        let minValue = parsePosInt(input.attr("min"), 0);
        let maxValue = parsePosInt(input.attr("max"), 999999);

        if (!isNaN(currentVal)) {
            if (type == "minus") {
                if (currentVal > minValue) {
                    input.val(currentVal - 1).change();
                }
                if (parsePosInt(input.val(), 0) <= minValue) {
                    $(this).attr("disabled", true);
                }
            } else if (type == "plus") {
                if (currentVal < maxValue) {
                    input.val(currentVal + 1).change();
                }
                if (parsePosInt(input.val(), 0) >= maxValue) {
                    $(this).attr("disabled", true);
                }
            }
        } else {
            input.val(minValue > 0 ? minValue : 1);
        }
        syncVisibleQtyMinusButtonState();
    });

    $(".input-number").focusin(function () {
        $(this).data("oldValue", $(this).val());
    });

    $(".input-number").change(function () {
        let minValue = parsePosInt($(this).attr("min"), 0);
        let maxValue = parsePosInt($(this).attr("max"), 0);
        let valueCurrent = parsePosInt($(this).val(), 0);
        let name = $(this).attr("name");
        const group = $(this).closest(".product-quantity-group");
        const minusBtn = group.find(".btn-number[data-type='minus'][data-field='" + name + "']");
        const plusBtn = group.find(".btn-number[data-type='plus'][data-field='" + name + "']");
        if (valueCurrent >= minValue) {
            minusBtn.removeAttr("disabled");
        } else {
            $(this).val($(this).data("oldValue"))
        }
        if (valueCurrent <= maxValue) {
            plusBtn.removeAttr("disabled");
        } else {
            $(this).val($(this).data("oldValue"))
        }
        syncVisibleQtyMinusButtonState();
    });
    $(".input-number").keydown(function (e) {
        if (
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
            return;
        }
        if (
            (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }
    });
}

function updateProductDetailsTopSection(response) {
    let formSelector = ".add-to-cart-details-form";
    $(formSelector).find(".discounted-unit-price").html(response?.discounted_unit_price);
    $(formSelector).find(".product-details-chosen-price-amount").html(response?.price);
    $(formSelector).find(".product-total-unit-price").html(response?.discount_amount > 0 ? response?.total_unit_price : "");

    if (response?.discount_amount > 0) {
        if (response?.discount_type === 'flat') {
            $(formSelector).find(".discounted_badge").html(`${response?.discount}`);
        } else {
            $(formSelector).find(".discounted_badge").html(`- ${response?.discount}`);
        }
        $(formSelector).find(".discounted-badge-element").removeClass('d-none');
    } else {
        $(formSelector).find(".discounted-badge-element").addClass('d-none');
    }
}

function getVariantPrice(type = null) {
    if (
        $("#add-to-cart-form input[name=quantity]").val() > 0 &&
        checkAddToCartValidity()
    ) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.ajax({
            type: "POST",
            url: $("#route-admin-pos-get-variant-price").data("url") +
                (type ? "?type=" + type : ""),
            data: $("#add-to-cart-form").serializeArray().concat([
                { name: 'branch_id', value: $('#branch_id').data('branch') || '' },
                { name: 'cart_id', value: getPosActiveCartId() }
            ]),
            success: function (response) {
                updateProductDetailsTopSection(response);

                let price;
                let tax;
                let discount;
                stockStatus(response.quantity, 'cart-qty-field-plus', 'cart-qty-field')
                if (response.inCartStatus == 0) {
                    $(".default-quantity-system").removeClass("d-none");
                    setQuickViewLineKey("");
                    $(".quick-view-modal-add-cart-button").text(
                        $("#message-add-to-cart").data("text")
                    );
                    $(".in-cart-quantity-system")
                        .addClass("d--none");
                    $(".default-quantity-system")
                        .removeClass("d--none");
                    price = response.price;
                    tax = response.tax;
                    discount = (response.discount * response.requestQuantity);
                } else {
                    $(".default-quantity-system")
                        .addClass("d--none");
                    $(".in-cart-quantity-system")
                        .removeClass("d--none");
                    $(".quick-view-modal-add-cart-button").text(
                        $("#message-update-to-cart").data("text")
                    );
                    setQuickViewLineKey(response?.inCartData?.line_key || "");
                    if (type == null) {
                        $(".in-cart-quantity-field").val(response.inCartData.quantity);
                        response.inCartData.quantity == 1
                            ? buttonDisableOrEnableFunction('in-cart-quantity-minus', true)
                            : "";
                        price = response.inCartData.price;
                        tax = response.inCartData.tax;
                        discount = (response.inCartData.discount * response.inCartData.quantity);
                    } else {
                        price = response.price;
                        tax = response.tax;
                        discount = (response.discount * response.requestQuantity);
                    }
                    stockStatus(response.quantity, 'in-cart-quantity-plus', 'in-cart-quantity-field')
                }
                setProductData('price-section', response.price, tax, response.discount_text);
                syncExchangeQuantityState();
                syncVisibleQtyMinusButtonState();
            },
        });
    }
}

function addToCart(form_id = "add-to-cart-form") {
    if (isPosAddToCartRunning) {
        return;
    }

    if (checkAddToCartValidity()) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });

        const exchangeQtyLimitMessage = "Exchange qty cannot exceed product quantity.";
        const exchangeQtyMinMessage = "Exchange qty must be at least 1 when Replacement Discount is enabled.";
        let exchangeCharge = 0;
        let installationCharge = 0;
        const branch_id = $('#branch_id').data('branch') || '';
        const selectedQty = getQuickViewSelectedQuantity();
        const isReplacementEnabled = $("#exchange-charge-checkbox").prop('checked');


        if (isReplacementEnabled) {
            let exchangeQuantity = parsePosInt($("#exchange-quantity").val(), 0);
            if (!Number.isFinite(exchangeQuantity) || exchangeQuantity < 1) {
                toastr.error(exchangeQtyMinMessage);
                return false;
            }
            if (exchangeQuantity > selectedQty) {
                toastr.error(exchangeQtyLimitMessage);
                return false;
            }
            $("#exchange-quantity").val(exchangeQuantity);
            let exchangePrice = $("#exchange-quantity").data("price");
            exchangeCharge = exchangeQuantity > 0 ? (exchangeQuantity * exchangePrice).toFixed(2) : 0;
        } else {
            $("#exchange-quantity").val(0);
        }

        if ($("#installation-charge-checkbox").prop('checked')) {
            installationCharge = $("#installation-charge-checkbox").data("price");
        }
        let finalData = $("#" + form_id).serializeArray().concat([
            { name: 'exchange_charge', value: exchangeCharge },
            { name: 'exchange_quantity', value: parsePosInt($("#exchange-quantity").val(), 0) || 0 },
            { name: 'replacement_discount_enabled', value: isReplacementEnabled ? 1 : 0 },
            { name: 'installation_charge', value: installationCharge },
            { name: 'branch_id', value: branch_id },
            { name: 'cart_id', value: getPosActiveCartId() },
            { name: 'idempotency_key', value: generatePosIdempotencyKey('add-to-cart') }
        ]);
        isPosAddToCartRunning = true;
        $('.quick-view-modal-add-cart-button').attr('disabled', true);
        $.post({
            url: $("#route-admin-pos-add-to-cart").data("url"),
            data: finalData,
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {

                if (data.data == 1) {
                    $("#cart-summary").empty().html(data.view);
                    toastr.success($("#message-cart-updated").data("text"), {
                        CloseButton: true,
                        ProgressBar: true,
                    });
                    data.inCartData && data.inCartData == 1
                        ? $(".in-cart-quantity-field").val(data.requestQuantity)
                        : "";
                    removeFromCart();
                    basicFunctionalityForCartSummary();
                    return false;
                } else if (data.data == 0) {
                    $('.product-stock-message').empty().html($('#get-product-stock-message').data('out-of-stock'));
                    $('.pos-alert-message').removeClass('d-none');
                    return false;
                } else {
                    $(".in-cart-quantity-field").val(data.quantity);
                    getVariantPrice();
                    setTimeout(function () {
                        $(".cart-qty-field").val(1);
                    }, 500);
                }
                $(".close-quick-view-modal").click();

                toastr.success(
                    $("#message-item-has-been-added-in-your-cart").data("text"),
                    {
                        CloseButton: true,
                        ProgressBar: true,
                    }
                );
                $("#order-place").replaceWith(data.view);
                viewAllHoldOrders("keyup");
                $(".search-result-box").empty().hide();
                $("#search").val("");
                basicFunctionalityForCartSummary();
                posUpdateQuantityFunctionality();
                removeFromCart();
            },
            error: function (xhr, status, error) {
                const firstFieldErrors = xhr?.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0]
                    : null;
                const message = (Array.isArray(firstFieldErrors) && firstFieldErrors.length > 0)
                    ? firstFieldErrors[0]
                    : xhr?.responseJSON?.message;
                if (message) {
                    toastr.error(message);
                }
            },
            complete: function () {
                $("#loading").fadeOut();
                isPosAddToCartRunning = false;
                $('.quick-view-modal-add-cart-button').attr('disabled', false);
            },
        });
    } else {
        Swal.fire({
            type: "info",
            title: $("#message-cart-word").data("text"),
            text: $("#message-please-choose-all-the-options").data("text"),
        });
    }
}


function removeFromCart() {
    $(".remove-from-cart").off("click").on("click", function () {
        let id = $(this).data("id");
        let variant = $(this).data("variant");
        let lineKey = $(this).data("line-key");
        $.post(
            $("#route-admin-pos-remove-cart").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                id: id,
                variant: variant,
                line_key: lineKey,
                branch_id: $('#branch_id').data('branch') || '',
                cart_id: getPosActiveCartId(),
            },
            function (data) {
                $("#order-place").replaceWith(data.view);
                if (data.errors) {
                    for (
                        let increment = 0;
                        increment < data.errors.length;
                        increment++
                    ) {
                        toastr.error(data.errors[increment].message, {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    }
                } else {
                    toastr.info(
                        $("#message-item-has-been-removed-from-cart").data(
                            "text"
                        ),
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                    viewAllHoldOrders("keyup");
                }
                basicFunctionalityForCartSummary();
                posUpdateQuantityFunctionality();
                removeFromCart();
            }
        );
    });
}
removeFromCart();

$(".js-example-matcher").select2({
    matcher: matchCustom,
});

function matchCustom(params, data) {
    if ($.trim(params.term) === "") {
        return data;
    }
    if (typeof data.text === "undefined") {
        return null;
    }
    if (data.text.indexOf(params.term) > -1) {
        let modifiedData = $.extend({}, data, true);
        modifiedData.text;
        return modifiedData;
    }
    return null;
}
function closeAlertMessage() {
    $('.close-alert-message').on('click', function () {
        $('.pos-alert-message').addClass('d-none');
    })
}

function productStockMessage(type,) {
    $('.product-stock-message').empty().html($('#get-product-stock-message').data(type))
    $('.pos-alert-message').removeClass('d-none');
}
function stockStatus(quantity, buttonDisableOrEnableClassName, inputQuantityClassName) {
    let stockOutMessage = $("#message-stock-out").data("text");
    let stockInMessage = $("#message-stock-id").data("text");
    let elementStockStatusInQuickView = $(".stock-status-in-quick-view");
    let inputQuantity = $('.' + inputQuantityClassName);
    if (quantity <= 0) {
        elementStockStatusInQuickView.removeClass("text-success").addClass("text-danger");
        elementStockStatusInQuickView.html(
            `<i class="tio-checkmark-circle-outlined"></i> ` +
            stockOutMessage
        );
        productStockMessage('out-of-stock')
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, true);
        inputQuantity.val(1);
        $(".btn-number[data-type='minus']").attr('disabled', true);
    } else if (inputQuantity.val() >= quantity) {
        productStockMessage('limited-stock');
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, true);
        inputQuantity.val(quantity);
    } else {
        $('.pos-alert-message').addClass('d-none');
        elementStockStatusInQuickView.removeClass("text-danger").addClass("text-success");
        elementStockStatusInQuickView.html(
            `<i class="tio-checkmark-circle-outlined"></i> ` +
            stockInMessage
        );
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, false);
    }
}

function setProductData(parentClass, price, tax, discount) {
    let updatedTax = tax.replace(/[^\d.,]/g, '');
    if (updatedTax <= 0) {
        $('.tax-container').empty()
    }
    $('.' + parentClass + ' ' + '.set-product-tax').html(tax);
    $('.' + parentClass + ' ' + '.set-discount-amount').html(discount);
}
$('.close-alert--message-for-pos').on('click', function () {
    $('.alert--message-for-pos').removeClass('active');
})
