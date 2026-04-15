

<h3 class="mt-4 mb-3 text-center text-lg-left mobile-fs-20 fs-18 font-bold">{{ translate('shopping_cart')}}</h3>

@php($shippingMethod=getWebConfig(name: 'shipping_method'))
@php($businessMode=getWebConfig(name: 'business_mode'))
@php($cart=\App\Utils\CartManager::getCartListGroupQuery())
@php($stockCheckStatus=getWebConfig(name: 'stock_check'))

<style type="text/css">
    .exchange_qty_plus,
    .exchange_qty_minus {
        display: flex;
        align-items: center;
        color: #1455ac;
        cursor: pointer;
    }

    .exchange_qty_input {
        width: 40px;
        height: 30px;
    }

    .d-none_exchange_qty {
        display: none !important;
    }

    .checkout-shipping-card {
        border: 1px solid #bfe7e2;
        border-radius: 14px;
        background: linear-gradient(180deg, #f5fcfb 0%, #ffffff 100%);
        box-shadow: 0 8px 20px rgba(48, 146, 136, 0.08);
    }

    .checkout-shipping-card__label {
        color: #0f766e;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .checkout-shipping-trigger {
        width: 100%;
        border: 1px solid #7fd0c7;
        border-radius: 12px;
        background-color: #fff;
        padding: 12px 14px;
        text-decoration: none;
        box-shadow: inset 0 0 0 1px rgba(127, 208, 199, 0.18);
    }

    .checkout-shipping-trigger:hover,
    .checkout-shipping-trigger:focus {
        text-decoration: none;
        border-color: #30a39a;
    }

    .checkout-shipping-trigger__icon {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        background: rgba(48, 163, 154, 0.12);
        color: #14958a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex: 0 0 42px;
    }

    .checkout-shipping-trigger__title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.4;
    }

    .checkout-shipping-trigger__meta {
        font-size: 12px;
        color: #5f6c7b;
        line-height: 1.4;
    }

    .checkout-shipping-select {
        min-height: 48px;
        border: 1px solid #7fd0c7;
        border-radius: 12px;
        background-color: #fff;
        font-weight: 700;
        color: #1f2937;
        box-shadow: inset 0 0 0 1px rgba(127, 208, 199, 0.18);
    }
</style>
<div class="row g-3 mx-max-md-0 mb-3">
    <section class="col-lg-8 px-max-md-0">
        @if(count($cart)==0)
        @php($isPhysicalProductExist = false)
        @endif

        <div class="table-responsive d-none d-lg-block">
            <table
                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table __cart-table">
                <thead class="thead-light">
                    <tr class="">
                        <th class="font-weight-bold __w-45">
                            <div class="ps-3">
                                {{ translate('product')}}
                            </div>
                        </th>
                        <th class="font-weight-bold ps-0 __w-15p text-capitalize text-center">{{
                            translate('unit_price')}}</th>
                        <th class="font-weight-bold __w-15p text-center">
                            <span class="ps-3">{{ translate('qty')}}</span>
                        </th>
                        <th class="font-weight-bold __w-15p text-end">
                            <div class="pe-3">
                                {{ translate('total')}}
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
            @foreach($cart as $group_key=>$group)
            <div class="table table-light cart_information mb-0">
                <?php
                $isPhysicalProductExist = false;
                $total_shipping_cost = 0;

                foreach ($group as $row) {
                    if ($row->product_type == 'physical' && $row->is_checked) {
                        $isPhysicalProductExist = true;
                    }

                    if (
                        auth('customer')->check() &&
                        auth('customer')->user()->user_type == 0 &&
                        $row->product_type == 'physical' &&
                        $row->is_checked &&
                        $row->shipping_type != "order_wise"
                    ) {
                        $total_shipping_cost += $row->shipping_cost;
                    }
                }
                ?>


                @foreach($group as $cart_key => $cartItem)

                @if ($shippingMethod=='inhouse_shipping')
                <?php
                $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                ?>
                @else
                <?php
                if ($cartItem->seller_is == 'admin') {
                    $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                } else {
                    $seller_shipping = \App\Models\ShippingType::where('seller_id', $cartItem->seller_id)->first();
                    $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                }
                ?>
                @endif

                @if(auth('customer')->check() &&
                auth('customer')->user()->user_type == 0 && $cart_key==0)
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 px-12">
                    @php($verify_status = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request,
                    $group_key))
                    @if($cartItem->seller_is=='admin')
                    <div class="d-flex gap-2">
                        <div class="d-flex gap-3 align-items-center">
                            @if($businessMode == 'multi')
                            <input type="checkbox" class="shop-head-check shop-head-check-desktop">
                            @endif
                            <a href="{{route('shopView',['id'=>0])}}"
                                class="text-primary d-flex align-items-center gap-2 fs-16">
                                <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                {{getWebConfig(name: 'company_name')}}
                            </a>
                        </div>
                        @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
                        <span class="ps-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                            data-placement="right"
                            data-title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                            title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                            <i class="czi-security-announcement"></i>
                        </span>
                        @endif
                    </div>
                    @else
                    <?php
                    $shopIdentity = \App\Models\Shop::where(['seller_id' => $cartItem['seller_id']])->first();
                    ?>
                    <div class="d-flex gap-2">
                        @if($shopIdentity)
                        <div class="d-flex gap-3 align-items-center">
                            @if($businessMode == 'multi')
                            <input type="checkbox" class="shop-head-check shop-head-check-desktop">
                            @endif
                            <a href="{{ route('shopView',['id' => $shopIdentity->id]) }}"
                                class="text-primary d-flex align-items-center gap-2 fs-16">
                                <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                {{ $shopIdentity->name }}
                            </a>
                        </div>

                        @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
                        <span class="ps-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                            data-placement="right"
                            data-title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                            title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                            <i class="czi-security-announcement"></i>
                        </span>
                        @endif
                        @else
                        <a href="javascript:" class="text-primary d-flex align-items-center gap-2 fs-16">
                            <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                            <span class="text-danger">{{ translate('vendor_not_available') }}</span>
                        </a>
                        @endif
                    </div>
                    @endif
                    @if(auth('customer')->check() && auth('customer')->user()->user_type == 0)
                    @php($chosenShipping=\App\Models\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())

                    <div class=" bg-white select-method-border rounded ">
                        @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type ==
                        'order_wise')
                        @if(isset($chosenShipping)==false)
                        @php($chosenShipping['shipping_method_id']=0)
                        @endif
                        @php($shippings=\App\Utils\Helpers::getShippingMethods($cartItem['seller_id'],
                        $cartItem['seller_is']))
                        @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type ==
                        'order_wise')

                        <div class="d-sm-flex">
                            @isset($chosenShipping['shipping_cost'])
                            @if(auth('customer')->check() && auth('customer')->user()->user_type == 0)
                            <div class="text-sm-nowrap mx-sm-2 mt-sm-2 mb-1">
                                <span class="font-weight-bold">
                                    {{ translate('shipping_cost') }}
                                </span>:
                                <span>
                                    {{ webCurrencyConverter($chosenShipping['shipping_cost']) }}
                                </span>
                            </div>
                            @endif
                            @endisset


                            @if(auth('customer')->check() &&
                            auth('customer')->user()->user_type == 0 && count($shippings) > 0)
                            <div class="">
                                @php($selectedShipping = $shippings->firstWhere('id', data_get($chosenShipping, 'shipping_method_id')) ?? $shippings->first())
                                @php($selectedShippingCost = data_get($chosenShipping, 'shipping_cost', data_get($selectedShipping, 'cost')))
                                <div class="dropdown">
                                    <a class="checkout-shipping-trigger d-flex align-items-center gap-3"
                                        href="javascript:" data-toggle="dropdown">
                                        <span class="checkout-shipping-trigger__icon">
                                            <i class="fa fa-truck"></i>
                                        </span>
                                        <span class="d-flex flex-column min-width-0">
                                            <span class="checkout-shipping-card__label">{{ translate('shipping_method') }}</span>
                                            <span class="checkout-shipping-trigger__title text-capitalize text-truncate">
                                                {{ $selectedShipping ? $selectedShipping->getTranslatedField('title') : translate('choose_shipping_method') }}
                                            </span>
                                            @if($selectedShipping)
                                            <span class="checkout-shipping-trigger__meta">
                                                {{ $selectedShipping->getTranslatedField('duration') }} . {{ webCurrencyConverter(amount: $selectedShippingCost) }}
                                            </span>
                                            @endif
                                        </span>
                                    </a>
                                    <div class="dropdown-menu m-0 pb-0 w-100">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($shippings as $shipping)
                                            <li class="cursor-pointer text-dark px-3 py-1 setShippingIdFunctionCartDetails font-semi-bold fs-14"
                                                data-id="{{$shipping['id']}}"
                                                data-cart-group="{{$cartItem['cart_group_id']}}">
                                                {{$shipping->getTranslatedField('title').' ( '.$shipping->getTranslatedField('duration').' )
                                                '.webCurrencyConverter($shipping['cost'])}}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            @else
                            <span
                                class="text-danger d-flex align-items-center gap-1 fs-14 font-semi-bold user-select-none"
                                data-toggle="tooltip" data-placement="top"
                                title="{{ translate('No_shipping_options_available_at_this_shop') }}, {{ translate('please_remove_all_items_from_this_shop') }}">
                                <i class="czi-security-announcement"></i> {{ translate('shipping_Not_Available') }}
                            </span>
                            @endif

                        </div>
                        @endif
                        @else
                        @if ($isPhysicalProductExist && $shipping_type != 'order_wise' && $shipping_type != 'area_wise')
                        <div class="">
                            <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span>
                            :
                            <span>{{ webCurrencyConverter(amount: $total_shipping_cost)}}</span>
                        </div>
                        @elseif($isPhysicalProductExist && $shipping_type == 'order_wise' && $chosenShipping)
                        <div class="">
                            <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span>
                            :
                            <span>{{ webCurrencyConverter(amount: $chosenShipping->shipping_cost)}}</span>
                        </div>
                        @endif
                        @endif

                    </div>
                    @endif

                </div>
                @endif
                @endforeach
                <table
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table __cart-table">
                    <tbody>
                        <?php
                        $isPhysicalProductExist = false;
                        foreach ($group as $row) {
                            if ($row->product_type == 'physical' && $row->is_checked) {
                                $isPhysicalProductExist = true;
                            }
                        }
                        ?>
                        @foreach($group as $cart_key=>$cartItem)
                        <!-- <?php echo "<pre>";
                                print_r($cartItem); ?> -->
                        @php($product = $cartItem->allProducts)

                        <?php
                        $getProductCurrentStock = $stockCheckStatus == 1 ? $product->current_stock : DEFAULT_STOCK;
                        if (!empty($product->variation)) {
                            foreach (json_decode($product->variation, true) as $productVariantSingle) {
                                if ($productVariantSingle['type'] == $cartItem->variant) {
                                    $getProductCurrentStock = $stockCheckStatus == 1 ? $productVariantSingle['qty'] : DEFAULT_STOCK;
                                }
                            }
                        }
                        ?>

                        <?php
                        $checkProductStatus = $cartItem->allProducts?->status ?? 0;
                        if ($cartItem->seller_is == 'admin') {
                            $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
                            $inhouseVacation = getWebConfig(name: 'vacation_add');
                            $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
                            $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
                            $vacationStatus = $inhouseVacation['status'] ?? 0;
                            if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
                                $checkProductStatus = 0;
                            }
                        } else {
                            if (!isset($cartItem->allProducts->seller) || (isset($cartItem->allProducts->seller) && $cartItem->allProducts->seller->status != 'approved')) {
                                $checkProductStatus = 0;
                            }
                            if (!isset($cartItem->allProducts->seller->shop) || $cartItem->allProducts->seller->shop->temporary_close) {
                                $checkProductStatus = 0;
                            }
                            if (isset($cartItem->allProducts->seller->shop) && ($cartItem->allProducts->seller->shop->vacation_status && (date('Y-m-d') >= $cartItem->allProducts->seller->shop->vacation_start_date) && (date('Y-m-d') <= $cartItem->allProducts->seller->shop->vacation_end_date))) {
                                $checkProductStatus = 0;
                            }
                        }
                        ?>

                        <tr>
                            <td class="__w-45">
                                <div class="d-flex gap-3 align-items-center">
                                    <input type="checkbox" class="shop-item-check shop-item-check-desktop"
                                        value="{{ $cartItem['id'] }}" {{ $cartItem['is_checked'] ? 'checked' : '' }}>

                                    <div class="d-flex gap-3">
                                        <div class="">
                                            <a href="{{ $checkProductStatus == 1 ? route('product', $cartItem['slug']) : 'javascript:'}}"
                                                class="position-relative overflow-hidden">
                                                <img class="rounded __img-62 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}"
                                                    src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}"
                                                    alt="{{ translate('product') }}">
                                                @if ($checkProductStatus == 0)
                                                <span class="temporary-closed position-absolute text-center p-2">
                                                    <span class="fs-12 font-weight-bolder">{{ translate('N/A') }}</span>
                                                </span>
                                                @endif
                                            </a>
                                        </div>
                                        <div class="d-flex flex-column gap-1 justify-content-around">
                                            <div
                                                class="text-break __line-2 __w-18rem {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                <a href="">
                                                    {{$cartItem['name']}}

                                                </a>
                                                @if(!empty($cartItem['variant']))
                                                <div>
                                                    <span class="__text-12px">{{translate('variant')}} :
                                                        {{$cartItem['variant']}}</span>
                                                </div>
                                                @endif
                                            </div>

                                            @if (auth('customer')->check() && auth('customer')->user()->user_type == 0
                                            && $product->product_type == 'physical'  && $shipping_type != 'order_wise' && $shipping_type != 'area_wise')
                                            <div
                                                class="d-flex flex-wrap gap-2 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                <span class="fw-semibold">
                                                    {{ translate('shipping_cost')}}
                                                </span>:
                                                <span>
                                                    {{ webCurrencyConverter(amount: $cartItem['shipping_cost']) }}
                                                </span>
                                            </div>
                                            @endif

                                            @if(auth('customer')->check() && auth('customer')->user()->user_type == 0 &&
                                            $stockCheckStatus == 1 && $product->product_type == 'physical' &&
                                            $getProductCurrentStock < $cartItem['quantity']) <div
                                                class="d-flex text-danger font-bold">
                                                <span>{{ translate('Out_Of_Stock') }}</span>
                                        </div>
                                        @endif

                                    </div>
                                </div>
            </div>
            </td>
            <td class="{{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }} __w-15p">
                <div class="text-center">
                    <div class="fw-semibold">
                        {{ webCurrencyConverter(amount: $cartItem['price']) }}
                    </div>
                    <span class="text-nowrap fs-10">
                        @if ($cartItem->tax_model === "exclude")
                        ({{ translate('tax')}}
                        : {{ webCurrencyConverter(amount: $cartItem['tax']*$cartItem['quantity'])}}
                        )
                        @else
                        ({{ translate('tax_included')}})
                        @endif
                    </span>
                </div>

            </td>
            @if (auth('customer')->check() && auth('customer')->user()->user_type == 0 ) <td
                class="__w-15p text-center">

                @php($minimum_order=\App\Utils\ProductManager::get_product($cartItem['product_id']))
                @if ($checkProductStatus == 1)
                <div class="qty d-flex justify-content-center align-items-center gap-3">
                    <span class="qty_minus action-update-cart-quantity-list"
                        data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                        data-increment="{{ '-1' }}"
                        data-event="{{ $cartItem['quantity'] == $product->minimum_order_qty ? 'delete':'minus' }}">

                        @if($getProductCurrentStock < $cartItem['quantity'] || $cartItem['quantity']==(isset($cartItem->
                            product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1 ))
                            <i class="tio-delete text-danger"></i>
                            @else
                            <i class="tio-remove"></i>
                            @endif

                    </span>
                    <input type="text"
                        class="qty_input cart_qty_input cartQuantity{{ $cartItem['id'] }} action-change-update-cart-quantity-list"
                        value="{{$cartItem['quantity']}}" name="quantity[{{ $cartItem['id'] }}]"
                        id="cart_quantity_web{{$cartItem['id']}}" data-current-stock="{{ $getProductCurrentStock }}"
                        data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                        data-increment="{{ '0' }}"
                        data-min="{{ isset($cartItem->product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1 }}"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <span class="qty_plus action-update-cart-quantity-list"
                        data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                        data-increment="{{ '1' }}">
                        <i class="tio-add"></i>
                    </span>
                </div>
                @else
                <div class="qty d-flex justify-content-center align-items-center gap-3">
                    <span class="action-update-cart-quantity-list cursor-pointer"
                        data-minimum-order="{{ $product?->minimum_order_qty ?? 1 }}"
                        data-cart-id="{{ $cartItem['id'] }}" data-increment="-{{ $cartItem['quantity'] }}"
                        data-event="delete">
                        <i class="tio-delete text-danger" data-toggle="tooltip"
                            data-title="{{ translate('product_not_available_right_now')}}"></i>
                    </span>
                </div>
                @endif
            </td>

            @else

            <td class="__w-15p text-center">

                @php($minimum_order=\App\Utils\ProductManager::get_product($cartItem['product_id']))
                @if ($checkProductStatus == 1)
                <div class="qty d-flex justify-content-center align-items-center gap-3">
                    {{-- <span class="qty_minus action-update-cart-quantity-list"
                        data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                    data-increment="{{ '-1' }}"
                    data-event="{{ $cartItem['quantity'] == $product->minimum_order_qty ? 'delete':'minus' }}">

                    @if($getProductCurrentStock < $cartItem['quantity'] || $cartItem['quantity']==(isset($cartItem->
                        product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1 ))
                        <i class="tio-delete text-danger"></i>
                        @else
                        <i class="tio-remove"></i>
                        @endif

                        </span> --}}
                        <input type="text"
                            class="qty_input cart_qty_input cartQuantity{{ $cartItem['id'] }} action-change-update-cart-quantity-list"
                            value="{{$cartItem['quantity']}}" name="quantity[{{ $cartItem['id'] }}]"
                            id="cart_quantity_web{{$cartItem['id']}}" data-current-stock="{{ $getProductCurrentStock }}"
                            data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                            data-increment="{{ '0' }}"
                            data-min="{{ isset($cartItem->product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1 }}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" readonly>
                        {{-- <span class="qty_plus action-update-cart-quantity-list"
                        data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                        data-increment="{{ '1' }}">
                        <i class="tio-add"></i>
                        </span> --}}
                </div>
                @else
                {{-- <div class="qty d-flex justify-content-center align-items-center gap-3">
                    <span class="action-update-cart-quantity-list cursor-pointer"
                        data-minimum-order="{{ $product?->minimum_order_qty ?? 1 }}"
                data-cart-id="{{ $cartItem['id'] }}" data-increment="-{{ $cartItem['quantity'] }}"
                data-event="delete">
                <i class="tio-delete text-danger" data-toggle="tooltip"
                    data-title="{{ translate('product_not_available_right_now')}}"></i>
                </span>
        </div> --}}
        @endif
        </td>

        @endif

        <td class="__w-15p text-end {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
            @if (auth('customer')->check() && auth('customer')->user()->user_type == 0 )

            <div>
                {{ webCurrencyConverter(amount: ($cartItem['price'])*$cartItem['quantity']) }}
            </div>

            @else

            <div>
                {{ webCurrencyConverter(amount: ($cartItem['price'])*$cartItem['quantity']) }}
            </div>
            @endif
        </td>
        </tr>
        @if(auth('customer')->check() && auth('customer')->user()->user_type == 0)
        <tr>
            @if($cartItem['installation_charge'] > 0)
            <td class="__w-45" style="border-bottom: 1px solid;">
                <div class="d-flex gap-3 align-items-center">
                    <div class="d-flex gap-3">
                        <div class="">
                        </div>


                         <div class="d-flex flex-column gap-1">
                                <div class="d-flex">
                                    <input type="checkbox" class="" id="installtion_charges_for_{{ $cartItem['id'] }}" data-cart-id="{{ $cartItem['id'] }}" {{ $cartItem['installtion_charges'] > 0 ? "checked" : ""}} data-installation-charges="{{$cartItem['installation_charge']}}"> &nbsp; {{ translate('add_installation_service_for_amount', ['amount' => webCurrencyConverter(amount: $cartItem['installation_charge'])]) }}
                                </div>
                            </div>
                    </div>
                </div>
            </td>
            @endif
            @if($cartItem['exchange_charge'] > 0)
            <td class="__w-50p" colspan="6" style="border-bottom: 1px solid;">
                <div class="d-flex flex-column gap-1">
                    <div class="d-flex">
                        <input type="checkbox" class="" id="exchange_charges_for_{{ $cartItem['id'] }}"
                            data-exchange-charges="{{ $cartItem['exchange_charge'] }}"
                            data-cart-id="{{ $cartItem['id'] }}" {{ $cartItem['exchange_charges']> 0 ? "checked" :
                            "" }}>
                        &nbsp; {{ translate('exchange_old_product_and_save_amount', ['amount' => webCurrencyConverter(amount: abs($cartItem['exchange_charge']))]) }}
                        <div class="{{ $cartItem['exchange_charges'] == 0 &&  $cartItem['exchange_qty'] == 0 ? 'd-none_exchange_qty' : '' }} exchange_qty d-flex justify-content-center align-items-center gap-3"
                            id="exchangeQTYDetails_{{ $cartItem['id'] }}">
                            <span class="exchange_qty_minus action-update-exchange-quantity-list"
                                data-minimum-order="1" data-cart-id="{{ $cartItem['id'] }}"
                                data-exchange-charges="{{ $cartItem['exchange_charge'] }}" data-increment="-1"
                                data-event="{{ $cartItem['exchange_qty'] == 1 ? 'delete' : 'minus' }}">
                                @if(1 == $cartItem['exchange_qty'])
                                <i class="tio-delete text-danger"></i>
                                @else
                                <i class="tio-remove"></i>
                                @endif
                            </span>
                            <input type="text"
                                class="qty_input exchange_qty_input exchangeQuantity{{ $cartItem['id'] }} action-change-update-exchange-quantity-list"
                                value="{{ $cartItem['exchange_qty'] > 0 ? $cartItem['exchange_qty'] : 0 }}"
                                name="exchange_quantity[{{ $cartItem['id'] }}]"
                                id="exchange_quantity_web{{ $cartItem['id'] }}" data-cart-id="{{ $cartItem['id'] }}"
                                data-exchange-charges="{{ $cartItem['exchange_charge'] }}" data-increment="0"
                                data-min="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <span class="exchange_qty_plus action-update-exchange-quantity-list"
                                data-cart-id="{{ $cartItem['id'] }}"
                                data-exchange-charges="{{ $cartItem['exchange_charge'] }}" data-increment="1">
                                <i class="tio-add"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </td>
            @endif
        </tr>
        @endif

        @endforeach
        </tbody>
        </table>

        @php($free_delivery_status =
        \App\Utils\OrderManager::getFreeDeliveryOrderAmountArray($group[0]->cart_group_id))
        @if ($free_delivery_status['status'] && (session()->missing('coupon_type') || session('coupon_type')
        !='free_delivery'))
        <div class="free-delivery-area px-3 mb-3 mb-lg-2">
            <div class="d-flex align-items-center gap-8">
                <img class="__w-30px"
                    src="{{ theme_asset(path: 'public/assets/front-end/img/icons/free-shipping.png') }}" alt="">
                @if ($free_delivery_status['amount_need'] <= 0) <span class="text-muted fs-12 mt-1">{{
                        translate('you_Get_Free_Delivery_Bonus') }}</span>
                    @else
                    <span class="need-for-free-delivery font-bold fs-12 mt-1 text-primary">{{
                            webCurrencyConverter(amount: $free_delivery_status['amount_need']) }}</span>
                    <span class="text-muted fs-12 mt-1">{{ translate('add_more_for_free_delivery') }}</span>
                    @endif
            </div>
            <div class="progress free-delivery-progress">
                <div class="progress-bar" role="progressbar"
                    style="width: {{ $free_delivery_status['percentage'] }}%"
                    aria-valuenow="{{ $free_delivery_status['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
        @endif

</div>
@endforeach
</div>

@foreach($cart as $group_key => $group)
<div class="cart_information mb-3 pb-3 w-100 d-lg-none">
    <?php
    $isPhysicalProductExist = false;
    $total_shipping_cost = 0;
    foreach ($group as $row) {
        if (
            auth('customer')->check() &&
            auth('customer')->user()->user_type == 0 && $row->product_type == 'physical' && $row->is_checked
        ) {
            $isPhysicalProductExist = true;
        }
        if ($row->product_type == 'physical' && $row->is_checked && $row->shipping_type != "order_wise") {
            $total_shipping_cost += $row->shipping_cost;
        }
    }

    ?>

    @foreach($group as $cart_key=>$cartItem)
    @if ($shippingMethod=='inhouse_shipping')
    <?php
    $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
    ?>
    @else
    <?php
    if ($cartItem->seller_is == 'admin') {
        $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
        $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
    } else {
        $seller_shipping = \App\Models\ShippingType::where('seller_id', $cartItem->seller_id)->first();
        $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
    }
    ?>
    @endif

    @if($cart_key==0)
    <div class="card-header d-flex flex-column gap-2 border-0 justify-content-between px-12">
        @php($verify_status = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request, $group_key))
        @if($cartItem->seller_is=='admin')
        <div class="d-flex gap-2">
            <div class="d-flex gap-3 align-items-center">
                @if($businessMode == 'multi')
                <input type="checkbox" class="shop-head-check shop-head-check-mobile">
                @endif
                <a href="{{route('shopView',['id'=>0])}}" class="text-primary d-flex align-items-center gap-2 fs-16">
                    <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                    {{getWebConfig(name: 'company_name')}}
                </a>
            </div>
            @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
            <span class="ps-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                data-placement="bottom"
                data-title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                <i class="czi-security-announcement"></i>
            </span>
            @endif
        </div>
        @else
        <?php
        $shopIdentity = \App\Models\Shop::where(['seller_id' => $cartItem['seller_id']])->first();
        ?>
        <div class="d-flex gap-2">
            @if($shopIdentity)
            <div class="d-flex gap-3 align-items-center">
                @if($businessMode == 'multi')
                <input type="checkbox" class="shop-head-check shop-head-check-mobile">
                @endif
                <a href="{{ route('shopView',['id' => $shopIdentity->id]) }}"
                    class="text-primary d-flex align-items-center gap-2 fs-16">
                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/cart-store.png') }}" alt="">
                    {{ $shopIdentity->name }}
                </a>
            </div>
            @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
            <span class="ps-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                data-placement="right"
                data-title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                title="{{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                <i class="czi-security-announcement"></i>
            </span>
            @endif
            @else
            <a href="javascript:" class="text-primary d-flex align-items-center gap-2 fs-16">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/cart-store.png') }}" alt="">
                <span class="text-danger">{{ translate('vendor_not_available') }}</span>
            </a>
            @endif

        </div>
        @endif

        <div class=" bg-white select-method-border rounded">
            @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')
            @php($chosenShipping=\App\Models\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())
            @if(isset($chosenShipping)==false)
            @php($chosenShipping['shipping_method_id']=0)
            @endif
            @php( $shippings=\App\Utils\Helpers::getShippingMethods($cartItem['seller_id'],$cartItem['seller_is']))
            @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')

            @if(count($shippings) > 0)
            @php($selectedShipping = $shippings->firstWhere('id', data_get($chosenShipping, 'shipping_method_id')) ?? $shippings->first())
            @php($selectedShippingCost = data_get($chosenShipping, 'shipping_cost', data_get($selectedShipping, 'cost')))
            <div class="checkout-shipping-card p-3">
                <div class="checkout-shipping-card__label mb-2">{{ translate('shipping_method') }}</div>
                <select
                    class="form-control fs-13 font-weight-bold text-capitalize border-aliceblue max-240px action-set-shipping-id checkout-shipping-select"
                    data-product-id="{{ $cartItem['cart_group_id'] }}">
                    @foreach($shippings as $shipping)
                    <option value="{{$shipping['id']}}"
                        {{ data_get($selectedShipping, 'id') == $shipping['id'] ? 'selected' : '' }}>
                        {{$shipping->getTranslatedField('title').' ( '.$shipping->getTranslatedField('duration').' ) '.webCurrencyConverter(amount: $shipping['cost'])}}
                    </option>
                    @endforeach
                </select>
                @if($selectedShipping)
                <div class="checkout-shipping-trigger__meta mt-2 text-center">
                    {{ $selectedShipping->getTranslatedField('duration') }} . {{ webCurrencyConverter(amount: $selectedShippingCost) }}
                </div>
                @endif
            </div>
            @else
            <span class="text-danger d-flex align-items-center gap-1 fs-14 font-semi-bold user-select-none"
                data-toggle="tooltip" data-placement="top"
                title="{{ translate('No_shipping_options_available_at_this_shop') }}, {{ translate('please_remove_all_items_from_this_shop') }}">
                <i class="czi-security-announcement"></i> {{ translate('shipping_Not_Available') }}
            </span>
            @endif

            @isset($chosenShipping['shipping_cost'])
            <div class="text-sm-nowrap mt-2 text-center fs-12">
                <span class="font-weight-bold">{{ translate('shipping_cost')}}</span>
                :<span>{{webCurrencyConverter($chosenShipping['shipping_cost'])}}</span>
            </div>
            @endisset
            @endif
            @else
            @if ($isPhysicalProductExist && $shipping_type != 'order_wise')
            <div class="text-sm-nowrap text-center fs-12">
                <span class="font-weight-bold">{{ translate('total_shipping_cost') }}</span> :
                <span>{{ webCurrencyConverter(amount: $total_shipping_cost) }}</span>
            </div>
            @elseif($isPhysicalProductExist && $shipping_type == 'order_wise' && $chosenShipping)
            <div class="text-sm-nowrap text-center fs-12">
                <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span> :
                <span>{{ webCurrencyConverter(amount: isset($chosenShipping['shipping_cost']) ?
                    $chosenShipping['shipping_cost'] : 0)}}</span>
            </div>
            @endif
            @endif
        </div>
    </div>
    @endif
    @endforeach

    <?php
    $isPhysicalProductExist = false;
    foreach ($group as $row) {
        if ($row->product_type == 'physical' && $row->is_checked) {
            $isPhysicalProductExist = true;
        }
    }
    ?>
    @foreach($group as $cart_key=>$cartItem)
    @php($product = $cartItem->allProducts)

    <?php
    $checkProductStatus = $cartItem->allProducts?->status ?? 0;
    if ($cartItem->seller_is == 'admin') {
        $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
        $inhouseVacation = getWebConfig(name: 'vacation_add');
        $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
        $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
        $vacationStatus = $inhouseVacation['status'] ?? 0;
        if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
            $checkProductStatus = 0;
        }
    } else {
        if (!isset($cartItem->allProducts->seller) || (isset($cartItem->allProducts->seller) && $cartItem->allProducts->seller->status != 'approved')) {
            $checkProductStatus = 0;
        }
        if (!isset($cartItem->allProducts->seller->shop) || $cartItem->allProducts->seller->shop->temporary_close) {
            $checkProductStatus = 0;
        }
        if (isset($cartItem->allProducts->seller->shop) && ($cartItem->allProducts->seller->shop->vacation_status && (date('Y-m-d') >= $cartItem->allProducts->seller->shop->vacation_start_date) && (date('Y-m-d') <= $cartItem->allProducts->seller->shop->vacation_end_date))) {
            $checkProductStatus = 0;
        }
    }
    ?>
    <div
        class="d-flex justify-content-between gap-3 p-3 fs-12  {{count($group)-1 == $cart_key ? '' :'border-bottom border-aliceblue'}}">
        <div class="d-flex gap-3 align-items-center">
            <input type="checkbox" class="shop-item-check shop-item-check-mobile" value="{{ $cartItem['id'] }}" {{
                $cartItem['is_checked'] ? 'checked' : '' }}>
            <div class="d-flex align-items-center gap-3">
                <div class="">
                    <a href="{{ $checkProductStatus == 1 ? route('product',$cartItem['slug']) : 'javascript:'}}"
                        class="position-relative overflow-hidden">
                        <img class="rounded __img-48 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}"
                            src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}"
                            alt="{{ translate('product') }}">
                        @if ($checkProductStatus == 0)
                        <span class="temporary-closed position-absolute text-center p-2">
                            <span class="fs-12 font-weight-bolder">{{ translate('N/A') }}</span>
                        </span>
                        @endif
                    </a>
                </div>
                <div class="d-flex flex-column gap-1 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                    <div class="text-break __line-2">
                        <a
                            href="{{ $checkProductStatus == 1 ? route('product',$cartItem['slug']) : 'javascript:'}}">{{$cartItem['name']}}</a>
                    </div>

                    @if(!empty($cartItem['variant']))
                    <div>
                        <span class="__text-12px">{{translate('variant')}} : {{$cartItem['variant']}}</span>
                    </div>
                    @endif

                    @if (auth('customer')->check() && auth('customer')->user()->user_type == 0)

                    <div class="d-flex flex-wrap column-gap-2">
                        <div class="text-nowrap text-muted">{{ translate('unit_price')}} :</div>
                        <div class="text-start d-flex gap-1 flex-wrap">
                            <div class="fw-semibold">{{ webCurrencyConverter(amount:
                                $cartItem['price']-$cartItem['discount']) }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <div class="text-nowrap text-muted">{{ translate('total')}} :</div>
                        <div class="font-semi-bold">
                            {{ webCurrencyConverter(amount:
                            ($cartItem['price']-$cartItem['discount'])*$cartItem['quantity']) }}

                        </div>
                        <span class="text-nowrap fs-10 mt-1px">
                            @if ($cartItem->tax_model === "exclude")
                            ({{ translate('tax')}}
                            : {{ webCurrencyConverter(amount: $cartItem['tax']*$cartItem['quantity'])}}
                            )
                            @else
                            ({{ translate('tax_included')}})
                            @endif
                        </span>
                    </div>

                    @else
                    <div class="d-flex flex-wrap column-gap-2">
                        <div class="text-nowrap text-muted">{{ translate('unit_price')}} :</div>
                        <div class="text-start d-flex gap-1 flex-wrap">
                            <div class="fw-semibold">{{ webCurrencyConverter(amount:
                                $cartItem['price']) }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <div class="text-nowrap text-muted">{{ translate('total')}} :</div>
                        <div class="font-semi-bold">
                            {{ webCurrencyConverter(amount:
                            ($cartItem['price'])*$cartItem['quantity']) }}

                        </div>

                    </div>
                    @endif

                    @if (auth('customer')->check() && auth('customer')->user()->user_type == 0 && $shipping_type !=
                    'order_wise')
                    <div class="d-flex flex-wrap gap-2 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                        <span class="text-muted"> {{ translate('shipping_cost')}}</span>:<span class="font-semi-bold">{{
                            webCurrencyConverter(amount: $cartItem['shipping_cost']) }}</span>
                    </div>
                    @endif

                    @if(auth('customer')->check() && auth('customer')->user()->user_type == 0 && $product->product_type
                    == 'physical' && $getProductCurrentStock < $cartItem['quantity']) <div
                        class="d-flex text-danger font-bold">
                        <span>{{ translate('Out_Of_Stock') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        @php($minimum_order=\App\Utils\ProductManager::get_product($cartItem['product_id']))
        @if ($minimum_order && $checkProductStatus)
        <div class="qty d-flex flex-column align-items-center gap-1">
            <span class="qty_plus action-update-cart-quantity-list-mobile p-2"
                data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                data-increment="1">
                <i class="tio-add"></i>
            </span>
            <input type="number"
                class="qty_input cartQuantity{{ $cartItem['id'] }} action-change-update-cart-quantity-list-mobile"
                value="{{$cartItem['quantity']}}" name="quantity[{{ $cartItem['id'] }}]"
                id="cart_quantity_mobile{{$cartItem['id']}}" data-minimum-order="{{ $product->minimum_order_qty }}"
                data-cart-id="{{ $cartItem['id'] }}" data-increment="0"
                data-current-stock="{{ $getProductCurrentStock }}"
                data-min="{{ isset($cartItem->product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1 }}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <span class="qty_minus action-update-cart-quantity-list-mobile p-2"
                data-minimum-order="{{ $product->minimum_order_qty }}" data-cart-id="{{ $cartItem['id'] }}"
                data-increment="-1"
                data-event="{{ $cartItem['quantity'] == $product->minimum_order_qty ? 'delete':'minus' }}">

                @if($getProductCurrentStock < $cartItem['quantity'] || $cartItem['quantity']==(isset($cartItem->
                    product->minimum_order_qty) ? $cartItem->product->minimum_order_qty : 1))
                    <i class="tio-delete text-danger"></i>
                    @else
                    <i class="tio-remove"></i>
                    @endif
            </span>
        </div>
        @else
        <div class="qty d-flex flex-column align-items-center gap-3">
            <span class="action-update-cart-quantity-list-mobile cursor-pointer"
                data-minimum-order="{{ $product?->minimum_order_qty ?? 1}}" data-cart-id="{{ $cartItem['id'] }}"
                data-increment="-{{ $cartItem['quantity'] }}" data-event="delete">
                <i class="tio-delete text-danger" data-toggle="tooltip"
                    data-title="{{ translate('product_not_available_right_now')}}"></i>
            </span>
        </div>
        @endif
    </div>
</div>
@endforeach

@php($free_delivery_status = \App\Utils\OrderManager::getFreeDeliveryOrderAmountArray($group[0]->cart_group_id))
@if ($free_delivery_status['status'] && (session()->missing('coupon_type') || session('coupon_type') !='free_delivery'))
<div class="free-delivery-area px-3 mb-3 mb-lg-2">
    <div class="d-flex align-items-center gap-8">
        <img class="__w-30px" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/free-shipping.png') }}"
            alt="">
        @if ($free_delivery_status['amount_need'] <= 0) <span class="text-muted fs-12 mt-1">{{
            translate('you_Get_Free_Delivery_Bonus') }}</span>
            @else
            <span class="need-for-free-delivery font-bold fs-12 mt-1 text-primary">{{ webCurrencyConverter(amount:
                $free_delivery_status['amount_need']) }}</span>
            <span class="text-muted fs-12 mt-1">{{ translate('add_more_for_free_delivery') }}</span>
            @endif
    </div>
    <div class="progress free-delivery-progress">
        <div class="progress-bar" role="progressbar" style="width: {{ $free_delivery_status['percentage'] }}%"
            aria-valuenow="{{ $free_delivery_status['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
</div>
@endif
</div>
@endforeach


@if($shippingMethod=='inhouse_shipping')
<?php
$isPhysicalProductExist = false;
foreach ($cart as $group_key => $group) {
    foreach ($group as $row) {
        if ($row->product_type == 'physical' && $row->is_checked) {
            $isPhysicalProductExist = true;
        }
    }
}
?>

<?php
$admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
$shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
?>
@if ($shipping_type == 'order_wise' && $isPhysicalProductExist)
@php($shippings=\App\Utils\Helpers::getShippingMethods(1,'admin'))
@php($chosenShipping=\App\Models\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())

@if(isset($chosenShipping)==false)
@php($chosenShipping['shipping_method_id']=0)
@endif

@if(auth('customer')->check() && auth('customer')->user()->user_type == 0 )
<div class="px-3 px-md-0 mb-3">
    <div class="row">
        <div class="col-12">
            @php($selectedShipping = $shippings->firstWhere('id', data_get($chosenShipping, 'shipping_method_id')) ?? $shippings->first())
            @php($selectedShippingCost = data_get($chosenShipping, 'shipping_cost', data_get($selectedShipping, 'cost')))
            <div class="checkout-shipping-card p-3">
                <div class="checkout-shipping-card__label mb-2">{{ translate('shipping_method') }}</div>
                <select class="form-control border-aliceblue action-set-shipping-id checkout-shipping-select" data-product-id="all_cart_group">
                @foreach($shippings as $shipping)
                <option value="{{$shipping['id']}}"
                    {{ data_get($selectedShipping, 'id') == $shipping['id'] ? 'selected' : '' }}>
                    {{$shipping->getTranslatedField('title').' ( '.$shipping->getTranslatedField('duration').' ) '.webCurrencyConverter(amount: $shipping['cost'])}}
                </option>
                @endforeach
                </select>
                @if($selectedShipping)
                <div class="checkout-shipping-trigger__meta mt-2">
                    {{ $selectedShipping->getTranslatedField('duration') }} . {{ webCurrencyConverter(amount: $selectedShippingCost) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endif
@endif
@if( $cart->count() == 0)
<div class="card mb-4">
    <div class="card-body py-5">
        <div class="py-md-4">
            <div class="text-center text-capitalize">
                <img class="mb-3 mw-100" src="{{theme_asset(path: 'public/assets/front-end/img/icons/empty-cart.svg')}}"
                    alt="">
                <p class="text-capitalize">{{translate('Your_Cart_is_Empty')}}!</p>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth('customer')->check() && auth('customer')->user()->user_type == 0 )

<div class="px-3 px-md-0 mt-3 mt-md-0">
    <form method="get">
        <div class="mb-lg-3">
            <div class="row">
                <div class="col-12">
                    <label for="phoneLabel" class="form-label input-label fs-14 font-semibold">
                        {{ translate('order_note') }}
                        <span class="input-label-secondary">({{ translate('optional') }})</span>
                    </label>
                    <textarea class="form-control w-100 border-aliceblue h-100-200" id="order_note"
                        name="order_note">{{ session('order_note')}}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
@endif
</section>

@include('web-views.partials._order-summary')

<span id="route-customer-set-shipping-method" data-url="{{ url('/customer/set-shipping-method') }}"></span>
<span id="route-action-checkout-function" data-route="shop-cart"></span>
</div>

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/js/cart-details.js') }}"></script>
@endpush
