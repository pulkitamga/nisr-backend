@php
    use App\Utils\CartManager;
    use App\Utils\Helpers;
@endphp
<div class="col-lg-4">
    <div class="card text-dark sticky-top-80">
        <div class="card-body px-sm-4 d-flex flex-column gap-2">
            @php($current_url=request()->segment(count(request()->segments())))
            @php($shippingMethod=getWebConfig(name: 'shipping_method'))
            @php($cart=CartManager::getCartListQuery(type: 'checked'))
            @php($cartAll=CartManager::getCartListQuery())
            @php($cartSummary=CartManager::getCartPriceSummary(type: 'checked'))
            @php($productPriceTotal=$cartSummary['itemPrice'])
            @php($totalTax=$cartSummary['vatTotal'])
            @php($totalShippingCost=$cartSummary['shippingTotal'])
            @php($totalDiscountOnProduct=$cartSummary['productDiscount'])
            @php($couponDiscountOnProduct=$cartSummary['couponDiscountOnProduct'])
            @php($netBeforeVat=$cartSummary['netBeforeVat'])
            @php($subTotal=$cartSummary['subTotal'])
            @php($installtionCharges=$cartSummary['installationCharge'])
            @php($ExchangeCharges=$cartSummary['exchangeCharge'])
            @php($totalAmount=$cartSummary['totalAmount'])
            @php($isPickupDelivery=$cartSummary['isPickupDelivery'] ?? false)
            @php($isAreaWiseShippingPending=$cartSummary['isAreaWiseShippingPending'] ?? false)
            @php($shippingNotice=$cartSummary['shippingNotice'] ?? null)

            @if($cartAll->count() > 0 && $cart->count() == 0)
                <span>{{ translate('Please_checked_items_before_proceeding_to_checkout') }}</span>
            @elseif($cartAll->count() == 0)
                <span>{{ translate('empty_cart') }}</span>
            @endif

            <div class="d-flex mb-3">
                <h5 class="text-capitalize">{{ translate('order_summary') }}</h5>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>{{ translate('item_price') }}</div>
                <div>{{webCurrencyConverter($productPriceTotal)}}</div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-capitalize">{{ translate('product_discount') }}</div>
                <div>{{webCurrencyConverter($totalDiscountOnProduct)}}</div>
            </div>
            @if($couponDiscountOnProduct > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>{{ translate('coupon_discount') }}</div>
                    <div>-{{webCurrencyConverter($couponDiscountOnProduct)}}</div>
                </div>
            @endif
            @if(auth('customer')->check() && !session()->has('coupon_discount'))
                <form class="needs-validation" action="{{ route('coupon.apply') }}" method="post" id="submit-coupon-code">
                    @csrf
                    <div class="form-group my-3">
                        <label for="promo-code" class="fw-semibold">{{ translate('Promo_Code') }}</label>
                        <div class="form-control focus-border pe-1 rounded d-flex align-items-center">
                            <input type="text" name="code" id="promo-code"
                                   class="w-100 text-dark bg-transparent border-0 focus-input"
                                   placeholder="{{ translate('write_coupon_code_here') }}" required>
                            <button class="btn btn-primary text-nowrap" id="coupon-code-apply">{{ translate('apply') }}</button>
                        </div>
                    </div>
                </form>
            @endif

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>Net (before VAT)</div>
                <div>{{webCurrencyConverter($netBeforeVat)}}</div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>{{ translate('tax') }}</div>
                <div>{{webCurrencyConverter($totalTax)}}</div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>{{ translate('sub_total') }}</div>
                <div>{{webCurrencyConverter($subTotal)}}</div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>{{ translate('shipping') }}</div>
                @if($isAreaWiseShippingPending)
                    <div class="text-muted text-end" style="max-width: 68%;">{{ $shippingNotice }}</div>
                @elseif($isPickupDelivery || $totalShippingCost > 0)
                    <div class="text-primary">{{webCurrencyConverter($totalShippingCost)}}</div>
                @else
                    <div class="text-primary">{{webCurrencyConverter(0)}}</div>
                @endif
            </div>
            @if($installtionCharges > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>{{ translate('Installation_Service') }}</div>
                    <div class="text-primary">{{webCurrencyConverter($installtionCharges)}}</div>
                </div>
            @endif
            @if($ExchangeCharges > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>{{ translate('Exchange_Service') }}</div>
                    <div class="text-primary">-{{webCurrencyConverter($ExchangeCharges)}}</div>
                </div>
            @endif

            @if(auth('customer')->check() && session()->has('coupon_discount'))
                @php($couponDiscount = session()->has('coupon_discount')?session('coupon_discount'):0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>{{ translate('coupon_code') }}</div>
                    <div class="text-primary">
                        {{ session('coupon_code') }}
                        <span class="small">(-{{webCurrencyConverter($couponDiscount)}})</span>
                    </div>
                </div>
            @endif
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4>{{ translate('total') }}</h4>
                <h2 class="text-primary">{{webCurrencyConverter($totalAmount)}}</h2>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                <a href="{{ route('home') }}" class="btn-link text-primary text-capitalize"><i
                        class="bi bi-chevron-double-left fs-10"></i> {{ translate('continue_shopping') }}</a>
                <button
                    class="btn btn-primary text-capitalize {{$cart->count() <= 0 ? 'custom-disabled' : ''}} {{ str_contains(request()->url(), 'checkout-payment') ? 'd-none':''}}"
                    id="proceed-to-next-action" data-goto-checkout="{{route('customer.choose-shipping-address-other')}}"
                    data-checkout-payment="{{ route('checkout-payment') }}"
                    {{ (isset($product_null_status) && $product_null_status == 1) ? 'disabled':''}}
                    type="button">{{translate('proceed_to_next')}}</button>
            </div>
        </div>
    </div>
</div>
