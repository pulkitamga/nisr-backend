@if(auth()->guard('customer')->check() && auth()->guard('customer')->user()->user_type == 0)
<aside class="col-lg-4 pt-4 pt-lg-2 px-max-md-0 order-summery-aside">
    <div class="__cart-total __cart-total_sticky" id="cart-summary">
        <div class="cart_total p-0">
            @php($cart=\App\Utils\CartManager::getCartListQuery(type: 'checked'))
            @php($cartSummary=\App\Utils\CartManager::getCartPriceSummary(type: 'checked'))
            @php($subTotal=$cartSummary['subTotal'])
            @php($netBeforeVat=$cartSummary['netBeforeVat'])
            @php($totalTax=$cartSummary['vatTotal'])
            @php($installtionCharges=$cartSummary['installationCharge'])
            @php($ExchangeCharges=$cartSummary['exchangeCharge'])
            @php($totalShippingCost=$cartSummary['shippingTotal'])
            @php($totalDiscountOnProduct=$cartSummary['productDiscount'])
            @php($totalSavedAmount=$cartSummary['totalSavedAmount'])
            @php($couponDiscountOnProduct=$cartSummary['couponDiscountOnProduct'])
            @php($totalAmount=$cartSummary['totalAmount'])
            @php($isPickupDelivery=$cartSummary['isPickupDelivery'] ?? false)
            @php($isAreaWiseShippingPending=$cartSummary['isAreaWiseShippingPending'] ?? false)
            @php($shippingNotice=$cartSummary['shippingNotice'] ?? null)

            @if($totalSavedAmount > 0)
            <h6 class="text-center text-primary mb-4 d-flex align-items-center justify-content-center gap-2">
                <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/offer.svg')}}" alt="">
                {{translate('you_have_Saved')}}
                <strong>{{ webCurrencyConverter(amount: $totalSavedAmount) }}!</strong>
            </h6>
            @endif


            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('item_price')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $cartSummary['itemPrice']) }}
                </span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('discount_on_product')}}</span>
                <span class="cart_value">
                    - {{ webCurrencyConverter(amount: $totalDiscountOnProduct) }}
                </span>
            </div>
            @if($couponDiscountOnProduct > 0)
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('coupon_discount')}}</span>
                <span class="cart_value">
                    - {{ webCurrencyConverter(amount: $couponDiscountOnProduct) }}
                </span>
            </div>
            @endif
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{ translate('net_before_vat') }}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $netBeforeVat) }}
                </span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('tax')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $totalTax) }}
                </span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('sub_total')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $subTotal) }}
                </span>
            </div>
            @if ($isAreaWiseShippingPending)
            <div class="d-flex justify-content-between align-items-start">
                <span class="cart_title">{{translate('shipping')}}</span>
                <span class="cart_value text-muted text-right" style="max-width: 65%;">
                    {{ $shippingNotice }}
                </span>
            </div>
            @elseif($isPickupDelivery || $totalShippingCost > 0)
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('shipping')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $totalShippingCost) }}
                </span>
            </div>
            @endif

            @if($installtionCharges > 0)
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('Installation_Service')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $installtionCharges) }}
                </span>
            </div>
            @endif
            @if($ExchangeCharges > 0)
            <div class="d-flex justify-content-between">
                <span class="cart_title">{{translate('Exchange_Service')}}</span>
                <span class="cart_value">
                    - {{ webCurrencyConverter(amount: $ExchangeCharges) }}
                </span>
            </div>
            @endif
            @if(auth('customer')->check())

            @if(session()->has('coupon_discount'))
            @php($couponDiscount = session()->has('coupon_discount')?session('coupon_discount'):0)

            <div class="pt-2">
                <div class="d-flex align-items-center form-control rounded-pill pl-3 p-1">
                    <img width="24" src="{{asset('public/assets/front-end/img/icons/coupon.svg')}}" alt="">
                    <div class="px-2 d-flex justify-content-between w-100">
                        <div>
                            {{ session('coupon_code') }}
                            <span class="text-primary small">( -{{ webCurrencyConverter(amount: $couponDiscount) }}
                                )</span>
                        </div>
                        <div class="bg-transparent text-danger cursor-pointer px-2 get-view-by-onclick"
                            data-link="{{ route('coupon.remove') }}">x</div>
                    </div>
                </div>
            </div>
            @else
            <div class="pt-2">
                <form class="needs-validation coupon-code-form" action="javascript:" method="post" novalidate
                    id="coupon-code-ajax">
                    <div class="d-flex form-control rounded-pill ps-3 p-1">
                        <img width="24" src="{{theme_asset(path: 'public/assets/front-end/img/icons/coupon.svg')}}"
                            alt="">
                        <input class="input_code border-0 px-2 text-dark bg-transparent outline-0 w-100" type="text"
                            name="code" placeholder="{{translate('coupon_code')}}" required>
                        <button class="btn btn--primary rounded-pill text-uppercase py-1 fs-12" type="button"
                            id="apply-coupon-code">
                            {{translate('apply')}}
                        </button>
                    </div>
                    <div class="invalid-feedback">{{translate('please_provide_coupon_code')}}</div>
                </form>
            </div>
            @endif
            @endif

            <hr class="my-2">
            <div class="d-flex justify-content-between">
                <span class="cart_title text-primary font-weight-bold">{{translate('total')}}</span>
                <span class="cart_value">
                    {{ webCurrencyConverter(amount: $totalAmount) }}
                </span>
            </div>
        </div>
        @php($company_reliability = getWebConfig(name: 'company_reliability'))
        @if($company_reliability != null)
        <div class="pt-5">
            <div class="footer-slider owl-theme owl-carousel">
                @foreach ($company_reliability as $key=>$value)
                @if ($value['status'] == 1 && !empty($value['title']))
                <div class="">
                    <img class="order-summery-footer-image" alt=""
                        src="{{ getStorageImages(path: imagePathProcessing(imageData: $value['image'],path:'company-reliability'), type: 'source', source: theme_asset(path: 'public/assets/front-end/img').'/'.$value['item'].'.png') }}">
                    <div class="deal-title">{{translate($value['title'])}}</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="pt-4">
            <a
                class="btn btn--primary btn-block proceed_to_next_button {{$cart->count() <= 0 ? 'custom-disabled' : ''}} action-checkout-function">{{translate('proceed_to_Checkout')}}</a>
        </div>

        <div class="d-flex justify-content-center mt-3">
            <a href="{{route('store')}}" class="d-flex align-items-center gap-2 text-primary font-weight-bold">
                <i class="tio-back-ui fs-12"></i> {{translate('continue_Shopping')}}
            </a>
        </div>

    </div>
</aside>

<div class="bottom-sticky3 bg-white p-3 shadow-sm w-100 d-lg-none">
    <div class="d-flex justify-content-center align-items-center fs-14 mb-2">
        <div class="product-description-label fw-semibold text-capitalize">{{translate('total_price')}} :</div>
        &nbsp; <strong class="text-base">{{ webCurrencyConverter(amount:
            $totalAmount)
            }}</strong>
    </div>
    <a data-route="{{ Route::currentRouteName() }}"
        class="btn btn--primary btn-block proceed_to_next_button text-capitalize {{$cart->count() <= 0 ? 'custom-disabled' : ''}} action-checkout-function">{{translate('proceed_to_checkout')}}</a>
</div>

@elseif(auth()->guard('customer')->check() && auth()->guard('customer')->user()->user_type == 1)

    <aside class="col-lg-4 pt-4 pt-lg-2 px-max-md-0 order-summery-aside">
        <div class="__cart-total __cart-total_sticky">
            <div class="cart_total p-0">
                @php($shippingMethod=getWebConfig(name: 'shipping_method'))
                @php($subTotal=0)
                @php($totalTax=0)
                @php($cart=\App\Utils\CartManager::getCartListQuery(type: 'checked'))
                @php($cartGroupIds=\App\Utils\CartManager::get_cart_group_ids())
                @if($cart->count() > 0)
                @foreach($cart as $key => $cartItem)
                @php($subTotal+=$cartItem['price']*$cartItem['quantity'])
                @php($totalTax+=$cartItem['tax_model']=='exclude' ? ($cartItem['tax']*$cartItem['quantity']):0)


                <!-- <div class="d-flex justify-content-between">
                    <span class="cart_title">{{translate('unit_price')}}</span>
                    <span class="cart_value">
                        {{ webCurrencyConverter(amount: $cartItem['price']) }}
                    </span>
                </div> -->
                @endforeach
                @endif
                <div class="d-flex justify-content-between">
                    <span class="cart_title">{{translate('sub_total')}}</span>
                    <span class="cart_value">
                        {{ webCurrencyConverter(amount: $subTotal) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="cart_title">{{translate('tax')}}</span>
                    <span class="cart_value">
                        {{ webCurrencyConverter(amount: $totalTax) }}
                    </span>
                </div>


                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="cart_title text-primary font-weight-bold">{{translate('total')}}</span>
                    <span class="cart_value">
                        {{ webCurrencyConverter(amount: $subTotal+$totalTax) }}
                    </span>
                </div>
            </div>


            <div class="pt-4">
                <div class="d-flex mb-lg-3 justify-content-between ">
                    <form action="{{ route('cart.remove-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger square-btn">
                            <i class="tio-delete"></i> {{ translate('Clear_cart') }}
                        </button>
                    </form>
                    <a href="#" id="clear-selected-items" class="btn btn-outline-danger square-btn">
                        <i class="tio-delete"></i> {{ translate('Clear_selected_item') }}
                    </a>

                </div>
                <form action="{{ route('wholesale.createOrder') }}" method="POST">
                    @csrf


                    @foreach($cart as $cartItem)
                    @php(
                    $productTax = $cartItem['tax_model'] == 'exclude'
                    ? $cartItem['tax']
                    : 0
                    )
                    <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $cartItem['product_id'] }}">
                    <input type="hidden" name="products[{{ $loop->index }}][variation_type]" value="{{ $cartItem['variant'] }}">
                    <input type="hidden" name="products[{{ $loop->index }}][quantity]" value="{{ $cartItem['quantity'] }}">
                    <input type="hidden" name="products[{{ $loop->index }}][tax]" value="{{ $productTax}}">
                    @endforeach
                    <button type="submit"
                        class="btn btn--primary btn-block {{$cart->count() <= 0 ? 'custom-disabled' : ''}}">
                        {{ translate('purchase_order') }}
                    </button>
                </form>

            </div>

            <div class="d-flex justify-content-center mt-3">
                <a href="{{route('store')}}" class="d-flex align-items-center gap-2 text-primary font-weight-bold">
                    <i class="tio-back-ui fs-12"></i> {{translate('continue_Shopping')}}
                </a>
            </div>

        </div>
    </aside>



@endif

@push('script')
<script>
    "use strict";

    $(document).ready(function() {
        orderSummaryStickyFunction()
    });

    $(document).on('click', '#clear-selected-items', function(e) {
        e.preventDefault();
        let selectedKeys = [...new Set($('.shop-item-check:checked').map(function() {
            return $(this).val();
        }).get())];

        if (selectedKeys.length === 0) {
            toastr.warning('{{ __('Please select at least one item to clear.') }}');
            return;
        }

        selectedKeys.forEach(function(key) {
            removeProductFromCartList(key);
        });
    });
</script>
@endpush
