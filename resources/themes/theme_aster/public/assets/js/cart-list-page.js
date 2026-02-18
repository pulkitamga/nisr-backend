"use strict";

function proceedToNextAction(){
    $('#proceed-to-next-action')
        .off('click.cartNext')
        .on('click.cartNext', function () {
        let orderNote = $('#order_note').val();
        $.post({
            url: $('#order_note_url').data('url'),
            data: {
                _token: $('meta[name="_token"]').attr('content'),
                order_note: orderNote,

            },
            beforeSend: function () {
                $('#loading').addClass('d-grid');
            },
            success: function (response) {
                if(response.status === 0) {
                    response.message.map(function (message) {
                        toastr.error(message)
                    })
                }else{
                    location.href = response.redirect ? response.redirect : $('#route-checkout-details').data('url');
                }
            },
            complete: function () {
                $('#loading').removeClass('d-grid');
            },
        });
    });
}
proceedToNextAction();


function multipleCheckBoxFunctionsInit()
{
    $('.cart_information').each(function() {
        let allShopItemsInChecked = true;
        $(this).find('.shop-item-check').each(function() {
            if (!$(this).prop('checked')) {
                allShopItemsInChecked = false;
                return false;
            }
        });
        $(this).find('.shop-head-check').prop('checked', allShopItemsInChecked);
    });

    $(document)
        .off('change.cartSelection', '.shop-head-check')
        .on('change.cartSelection', '.shop-head-check', function () {
            $(this).parents('.cart_information').find('.shop-item-check').prop('checked', this.checked);
        });

    $(document)
        .off('change.cartSelection', '.shop-item-check')
        .on('change.cartSelection', '.shop-item-check', function () {
            var allChecked = true;

            $(this).parents('.cart_information').find('.shop-item-check').each(function () {
                if (!$(this).prop('checked')) {
                    allChecked = false;
                    return false;
                }
            });

            $(this).parents('.cart_information').find('.shop-head-check').prop('checked', allChecked);
        });

    $(document)
        .off('change.cartSelection', '.shop-head-check-desktop')
        .on('change.cartSelection', '.shop-head-check-desktop', function () {
            getCartSelectCartItemsCheckedValues('.cart_information input[type="checkbox"].shop-item-check-desktop')
        });

    $(document)
        .off('change.cartSelection', '.shop-head-check-mobile')
        .on('change.cartSelection', '.shop-head-check-mobile', function () {
            getCartSelectCartItemsCheckedValues('.cart_information input[type="checkbox"].shop-item-check-mobile')
        });

    $(document)
        .off('change.cartSelection', '.shop-item-check-desktop')
        .on('change.cartSelection', '.shop-item-check-desktop', function () {
            getCartSelectCartItemsCheckedValues('.cart_information input[type="checkbox"].shop-item-check-desktop')
        });

    $(document)
        .off('change.cartSelection', '.shop-item-check-mobile')
        .on('change.cartSelection', '.shop-item-check-mobile', function () {
            getCartSelectCartItemsCheckedValues('.cart_information input[type="checkbox"].shop-item-check-mobile')
        });

    function getCartSelectCartItemsCheckedValues(elementSelector)
    {
        let checkedValues = [];
        $(elementSelector).each(function() {
            if ($(this).prop('checked')) {
                checkedValues.push($(this).val());
            }
        });
        getCartSelectCartItemsRequest(checkedValues)
    }

    function getCartSelectCartItemsRequest(checkedValues)
    {
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')}
        });
        $.ajax({
            url: $('#get-cart-select-cart-items').data('route'),
            type: "POST",
            data: {
                ids: checkedValues
            },
            beforeSend: function () {
                $('#loading').addClass('d-grid');
            },
            success: function (response) {
                $('#cart-summary').empty().html(response.htmlView);
                toastr.success(response.message);
                initTooltip();
                proceedToNextAction();
                updateCartQuantityListCartData();
                setShippingIdFunction();
                updateCartQuantityListMobileCartData();
                renderCouponCodeApply()
                multipleCheckBoxFunctionsInit()
            },
            complete: function () {
                $('#loading').removeClass('d-grid');
            }
        });
    }
}

multipleCheckBoxFunctionsInit()
