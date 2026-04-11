@extends('layouts.front-end.app')

@section('title',translate('shipping_Address'))

@push('css_or_js')
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')
@include('layouts.front-end.partials._store-header')
@php($cart=\App\Utils\CartManager::getCartListQuery(type: 'checked'))

@php($billingInputByCustomer=getWebConfig(name: 'billing_input_by_customer'))
@php($shippingRestrictionSetup = $delivery_restriction_setup['setup'] ?? [])
@php($singleShippingCountryMode = (bool)($delivery_restriction_setup['single_country_mode'] ?? false))
@php($shippingCountryVisible = (bool)($shippingRestrictionSetup['country']['visible'] ?? false))
@php($shippingStateVisible = (bool)($shippingRestrictionSetup['state']['visible'] ?? false) && ($shippingCountryVisible || $singleShippingCountryMode))
@php($shippingCityVisible = (bool)($shippingRestrictionSetup['city']['visible'] ?? false) && $shippingStateVisible)
@php($shippingAreaVisible = (bool)($shippingRestrictionSetup['area']['visible'] ?? false) && $shippingCityVisible)
@php($shippingZipVisible = (bool)($shippingRestrictionSetup['zip']['visible'] ?? false))

<style>
    .delivery-radio-btn {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .875rem;
        margin-top: .75rem;
    }

    .delivery-radio-btn.is-invalid {
        padding: .75rem;
        border: 1px solid #e74c3c;
        border-radius: 1rem;
        background: #fff7f6;
    }

    .delivery-choice {
        position: relative;
        display: block;
        margin: 0;
        cursor: pointer;
    }

    .delivery-choice__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .delivery-choice__content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        min-height: 100%;
        padding: 1rem 1.125rem;
        border: 1px solid #d9e4e8;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, background-color .2s ease;
    }

    .delivery-choice__content::after {
        content: "";
        flex: 0 0 1.1rem;
        width: 1.1rem;
        height: 1.1rem;
        margin-top: .15rem;
        border: 2px solid #b8c9cf;
        border-radius: 50%;
        background: #fff;
        box-shadow: inset 0 0 0 3px #fff;
        transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
    }

    .delivery-choice__title {
        display: block;
        color: #16353d;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .delivery-choice__hint {
        display: block;
        margin-top: .35rem;
        color: #607d86;
        font-size: .82rem;
        line-height: 1.45;
    }

    .delivery-choice__input:checked + .delivery-choice__content {
        border-color: #1f9e97;
        background: linear-gradient(180deg, #f4fffd 0%, #ebfbf8 100%);
        box-shadow: 0 .75rem 1.75rem rgba(31, 158, 151, 0.14);
        transform: translateY(-1px);
    }

    .delivery-choice__input:checked + .delivery-choice__content::after {
        border-color: #1f9e97;
        background: #1f9e97;
        box-shadow: inset 0 0 0 3px #ebfbf8;
    }

    .delivery-choice__input:focus-visible + .delivery-choice__content {
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(31, 158, 151, 0.22);
        border-color: #1f9e97;
    }

    .delivery-type-selected-div {
        border: 1px solid #dde8eb;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-inline-start: 3px;
        margin-bottom: 1rem;
        background: #fff;
    }

    .checkout-section-block {
        padding: 1.1rem 1.2rem;
        border: 1px solid #e3ecef;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
    }

    .checkout-section-block + .checkout-section-block {
        margin-top: 1rem;
    }

    .checkout-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .checkout-section-title {
        margin: 0;
        color: #123740;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .checkout-section-copy {
        margin: .3rem 0 0;
        color: #6b848c;
        font-size: .82rem;
        line-height: 1.45;
    }

    @media (max-width: 575.98px) {
        .delivery-radio-btn {
            grid-template-columns: 1fr;
        }

        .delivery-choice__content {
            padding: .95rem 1rem;
        }

        .checkout-section-block {
            padding: 1rem;
        }
    }
</style>
<div class="container py-4 rtl __inline-56 px-0 px-md-3 text-align-direction">
    <div class="row mx-max-md-0">
        <div class="col-md-12 mb-3">
            <h3 class="font-weight-bold text-center text-lg-left">{{translate('checkout')}}</h3>
        </div>
    </div>
    <div class="mb-2 px-3 px-md-0">
        @include('web-views.partials._checkout-steps',['step'=>2])
    </div>
    <div class="row mx-max-md-0 align-items-start">
        <section class="col-md-8 col-lg-8 px-max-md-0">
            <div class="checkout_details">
                @php($defaultLocation = getWebConfig(name: 'default_location'))

                @if($physical_product_view)
                <input type="hidden" id="physical_product" name="physical_product" value="{{ $physical_product_view ? 'yes':'no'}}">
                <div class="px-3 px-md-0">
                    <h4 class="pb-2 mt-4 fs-18 text-capitalize">{{ translate('shipping_address')}}</h4>
                </div>

                @php($shippingAddresses= \App\Models\ShippingAddress::where(['customer_id'=>auth('customer')->id(), 'is_guest'=>0])->get())
                @php($loggedInCustomer = auth('customer')->user())
                @php($prefillShippingContactName = $loggedInCustomer && $shippingAddresses->isEmpty() ? trim(($loggedInCustomer->f_name ?? '').' '.($loggedInCustomer->l_name ?? '')) : '')
                @php($prefillShippingContactName = $prefillShippingContactName !== '' ? $prefillShippingContactName : ($loggedInCustomer && $shippingAddresses->isEmpty() ? ($loggedInCustomer->name ?? '') : ''))
                @php($prefillShippingPhone = $loggedInCustomer && $shippingAddresses->isEmpty() ? ($loggedInCustomer->phone ?? '') : '')
                <form method="post" class="card __card" id="address-form">
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <li class="list-group-item add-another-address">
                                @if ($shippingAddresses->count() >0)
                                <div class="d-flex align-items-center justify-content-end gap-3">
                                    <div class="dropdown">
                                        <button class="form-control dropdown-toggle text-capitalize" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            {{translate('saved_address')}}
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right saved-address-dropdown scroll-bar-saved-address" aria-labelledby="dropdownMenuButton">
                                            @foreach($shippingAddresses as $key => $address)
                                            <div class="dropdown-item select_shipping_address {{$key == 0 ? 'active' : ''}}" id="shippingAddress{{$key}}">
                                                <input type="hidden" class="selected_shippingAddress{{$key}}" value="{{$address}}">
                                                <input type="hidden" name="shipping_method_id" value="{{$address['id']}}">
                                                <div class="media gap-2">
                                                    <div class="">
                                                        <i class="tio-briefcase"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="mb-1 text-capitalize">{{$address->address_type}}</div>
                                                        <div class="text-muted fs-12 text-capitalize text-wrap">{{$address->address}}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div id="accordion">
                                    <div class="">
                                        <div class="mt-3">
                                            <div class="checkout-section-block">
                                                <div class="checkout-section-head">
                                                    <div>
                                                        <h5 class="checkout-section-title">{{ translate('Contact_Details') }}</h5>
                                                        <p class="checkout-section-copy">{{ translate('Enter_the_best_details_for_delivery_updates') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>{{ translate('contact_person_name')}}
                                                            <span class="text-danger checkout-required-indicator" data-required-indicator="contact_person_name">*</span>
                                                        </label>
                                                        <input type="hidden" name="nearest_branch" id="nearest_branch" value="1">
                                                        <input type="text" class="form-control" name="contact_person_name" id="name" value="{{ $prefillShippingContactName }}">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>{{ translate('phone')}}
                                                            <span class="text-danger checkout-required-indicator" data-required-indicator="phone">*</span>
                                                        </label>
                                                        <input type="tel" class="form-control phone-input-with-country-picker-3" id="phone" value="{{ $prefillShippingPhone }}">
                                                        <input type="hidden" id="shipping_phone_view" class="country-picker-phone-number-3 w-50" name="phone" readonly>
                                                    </div>
                                                </div>
                                                @if(!auth('customer')->check())

                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1">
                                                            {{ translate('email')}}
                                                            <span class="text-danger checkout-required-indicator" data-required-indicator="email">*</span>
                                                        </label>
                                                        <input type="email" class="form-control" name="email" id="email">
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                                <!-- <div class="col-12">
                                                    <div class="form-group">
                                                        <label>{{ translate('Delivery_Type') }}</label>
                                                        <div class="d-flex mt-lg-1 delivery-radio-btn">
                                                            <div class="form-control-sm {{Session::get('direction') === "rtl" ? '' : 'ps-5' }}">
                                                                <input class="form-check-input show" type="radio"
                                                                    name="delivery_type"
                                                                    id="delivery_radio"
                                                                    value="delivery"
                                                                    onchange="togglePickupBranchVisibility()" >
                                                                <label class="form-check-label text-nowrap delivery-radio-btn-label" for="delivery_radio">
                                                                    {{ translate('delivery') }}
                                                                </label>
                                                            </div>

                                                            <div class="form-control-sm {{Session::get('direction') === "rtl" ? '' : 'ps-5' }}">
                                                                <input class="form-check-input show" type="radio"
                                                                    name="delivery_type"
                                                                    id="pickup_radio"
                                                                    value="pickup"
                                                                    onchange="togglePickupBranchVisibility()">
                                                                <label class="form-check-label text-nowrap delivery-radio-btn-label" for="pickup_radio">
                                                                    {{ translate('pickup') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->



                                                <div class="checkout-section-block">
                                                    <div class="checkout-section-head">
                                                        <div>
                                                            <h5 class="checkout-section-title">{{ translate('Delivery_Choice') }}</h5>
                                                            <p class="checkout-section-copy">{{ translate('Choose_the_option_that_matches_how_you_want_to_receive_this_order') }}</p>
                                                        </div>
                                                    </div>
                                                <div class="col-12 px-0">
                                                    <div class="form-group">
                                                        <label>{{ translate('Delivery_Type') }}</label>
                                                        <div class="fs-12 text-muted mt-1">{{ translate('Choose_how_you_want_to_receive_your_order') }}</div>
                                                        <div class="delivery-radio-btn" role="radiogroup" aria-label="{{ translate('Delivery_Type') }}">
                                                            <label class="delivery-choice" for="delivery_radio">
                                                                <input class="delivery-choice__input" type="radio"
                                                                    name="delivery_type"
                                                                    id="delivery_radio"
                                                                    value="delivery"
                                                                    onchange="togglePickupBranchVisibility()">
                                                                <span class="delivery-choice__content">
                                                                    <span>
                                                                        <span class="delivery-choice__title">{{ translate('delivery') }}</span>
                                                                        <span class="delivery-choice__hint">{{ translate('Deliver_to_my_address') }}</span>
                                                                    </span>
                                                                </span>
                                                            </label>

                                                            <label class="delivery-choice" for="pickup_radio">
                                                                <input class="delivery-choice__input" type="radio"
                                                                    name="delivery_type"
                                                                    id="pickup_radio"
                                                                    value="pickup"
                                                                    onchange="togglePickupBranchVisibility()">
                                                                <span class="delivery-choice__content">
                                                                    <span>
                                                                        <span class="delivery-choice__title">{{ translate('pickup') }}</span>
                                                                        <span class="delivery-choice__hint">{{ translate('Collect_from_branch') }}</span>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="row col-12 delivery-type-selected-div d-none" id="deliver-address-type-div">
                                                    <div class="col-12 px-0">
                                                        <div class="checkout-section-head mb-4">
                                                            <div>
                                                                <h5 class="checkout-section-title">{{ translate('Address_Details') }}</h5>
                                                                <p class="checkout-section-copy">{{ translate('Complete_only_the_fields_needed_for_your_selected_delivery_option') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 d-none" id="deliver-address-type">
                                                        <div class="form-group">
                                                            <label>{{ translate('address_type')}} <span class="text-danger checkout-required-indicator" data-required-indicator="address_type">*</span></label>
                                                            <select class="form-control" name="address_type" id="address_type">
                                                                <option value="permanent">{{ translate('permanent')}}</option>
                                                                <option value="home">{{ translate('home')}}</option>
                                                                <option value="office">{{ translate('office')}}</option>
                                                                <option value="others">{{ translate('others')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-none" id="deliver-pickup-branch">
                                                        <div class="form-group">
                                                            <label>{{ translate('Pickup_branch')}} <span class="text-danger checkout-required-indicator d-none" data-required-indicator="pickup_branch_id">*</span></label>
                                                            <select class="form-control  js-select2-custom" name="pickup_branch_id" id="pickup_branch_id">
                                                                <option value="">{{ translate('please_select')}}</option>
                                                                @foreach($branches as $branch)
                                                                <option value="{{ $branch['id'] }}"
                                                                    data-address="{{ $branch['branch_address'] }}">
                                                                    {{ $branch['id'] == 1 ? translate('main_Branch') : translate($branch['branch_name']) }}
                                                                </option>
                                                                @endforeach

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-none" id="deliver-pickup-branch-address">
                                                        <div class="form-group">
                                                            <label>{{ translate('Pickup_branch_address')}}</label>
                                                            <textarea class="form-control" id="nearest_branch_textarea" rows="4" cols="50" readonly></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 d-none @if(!$shippingCountryVisible || $singleShippingCountryMode) single-country-hidden @endif" id="deliver-country" @if($singleShippingCountryMode) data-single-country @endif data-field-enabled="{{ $shippingCountryVisible ? 1 : 0 }}">
                                                        <div class="form-group">
                                                            <label @if(!$shippingCountryVisible || $singleShippingCountryMode) class="d-none" @endif>{{ translate('country') }} <span class="text-danger checkout-required-indicator" data-required-indicator="country">*</span></label>
                                                            <select name="country" id="country" class="form-control">
                                                                @if(!$singleShippingCountryMode)
                                                                <option value="">{{ translate('select_country') }}</option>
                                                                @endif
                                                                @foreach($shippingCountries as $sc)
                                                                <option value="{{ $sc['code'] }}" {{ $loop->first && $singleShippingCountryMode ? 'selected' : '' }}>{{ $sc['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 d-none" id="deliver-state" data-field-enabled="{{ $shippingStateVisible ? 1 : 0 }}">
                                                        <div class="form-group">
                                                            <label>{{ translate('state') }} <span class="text-danger checkout-required-indicator" data-required-indicator="state">*</span></label>
                                                            <select name="state_id" id="state_id" class="form-control">
                                                                <option value="">{{ translate('select_state') }}</option>
                                                            </select>

                                                            <input type="hidden" name="state" id="state_name">

                                                        </div>
                                                    </div>

                                                    <div class="col-6 d-none" id="deliver-city" data-field-enabled="{{ $shippingCityVisible ? 1 : 0 }}">
                                                        <div class="form-group">
                                                            <label>{{ translate('city') }} <span class="text-danger checkout-required-indicator" data-required-indicator="city">*</span></label>
                                                            <select name="city_id" id="city_id" class="form-control">
                                                                <option value="">{{ translate('select_city') }}</option>
                                                            </select>
                                                            <input type="hidden" name="city" id="city_name">

                                                        </div>
                                                    </div>

                                                    <div class="col-6 d-none" id="deliver-area" data-field-enabled="{{ $shippingAreaVisible ? 1 : 0 }}">
                                                        <div class="form-group">
                                                            <label>{{ translate('area') }} <span class="text-danger checkout-required-indicator" data-required-indicator="area">*</span></label>
                                                            <select name="area_id" id="area" class="form-control">
                                                                <option value="">{{ translate('select_area') }}</option>
                                                            </select>
                                                            <input type="hidden" name="area" id="area_name">
                                                        </div>
                                                    </div>
                                                    <div class="col-6 d-none" id="deliver-zip" data-field-enabled="{{ $shippingZipVisible ? 1 : 0 }}">
                                                        <div class="form-group">
                                                            <label>{{ translate('zip_code')}}
                                                                <span class="text-danger checkout-required-indicator {{ $zip_restrict_status == 1 ? '' : 'd-none' }}" data-required-indicator="zip">*</span>
                                                            </label>
                                                            @if($zip_restrict_status == 1)
                                                            <select name="zip" class="form-control selectpicker" data-live-search="true" id="select2-zip-container">
                                                                @forelse($zip_codes as $code)
                                                                <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                                @empty
                                                                <option value="">{{ translate('no_zip_to_deliver') }}</option>
                                                                @endforelse
                                                            </select>
                                                            @else
                                                            <input type="text" class="form-control"
                                                                name="zip" id="zip">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-6 d-none" id="deliver-address">
                                                        <div class="form-group mb-1">
                                                            <label>{{ translate('address')}}<span class="text-danger checkout-required-indicator" data-required-indicator="address">*</span></label>
                                                            <textarea class="form-control" id="address" type="text" name="address"></textarea>
                                                            <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                                                {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(getWebConfig('map_api_status') ==1 )
                                            <div class="form-group location-map-canvas-area map-area-alert-border d-none" id="location_map_canvas_area">
                                                <!-- <input id="pac-input" class="controls rounded __inline-46 location-search-input-field" title="{{translate('search_your_location_here')}}" type="text" placeholder="{{translate('search_here')}}"/> -->
                                                <div class="__h-200px" id="location_map_canvas"></div>
                                            </div>
                                            @endif
                                            <div class="d-flex gap-3 align-items-center">
                                                <label class="form-check-label d-flex gap-2 align-items-center d-none" id="save_address_label">
                                                    <input type="hidden" name="shipping_method_id" id="shipping_method_id" value="0">
                                                    @if(auth('customer')->check())
                                                    <input type="checkbox" name="save_address" id="save_address">
                                                    {{ translate('save_this_Address') }}
                                                    @endif
                                                </label>
                                            </div>

                                            <input type="hidden" id="latitude"
                                                name="latitude" class="form-control d-inline"
                                                placeholder="{{ translate('ex')}} : -94.22213"
                                                value="{{$defaultLocation?$defaultLocation['lat']:0}}"
                                                readonly>
                                            <input type="hidden"
                                                name="longitude" class="form-control"
                                                placeholder="{{ translate('ex')}} : 103.344322" id="longitude"
                                                value="{{$defaultLocation?$defaultLocation['lng']:0}}"
                                                readonly>

                                            <button type="submit" class="btn btn--primary d--none" id="address_submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </form>

                @if(!Auth::guard('customer')->check() && $web_config['guest_checkout_status'])
                <div class="card __card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap justify-content-between gap-3">
                            <div class="min-h-45 form-check d-flex gap-3 align-items-center cursor-pointer user-select-none">
                                <input type="checkbox" id="is_check_create_account" name="is_check_create_account" class="form-check-input mt-0" value="1">
                                <label class="form-check-label font-weight-bold fs-13 create-account-info-label" for="is_check_create_account">
                                    {{translate('Create_an_account_with_the_above_info')}}
                                </label>
                            </div>

                            <div class="is_check_create_account_password_group d--none">
                                <div class="d-flex gap-3 flex-wrap flex-sm-nowrap">
                                    <div class="w-100">
                                        <div class="password-toggle rtl">
                                            <input class="form-control text-align-direction" name="customer_password" type="password" id="customer_password" placeholder="{{ translate('new_Password') }}">
                                            <label class="password-toggle-btn">
                                                <input class="custom-control-input" type="checkbox">
                                                <i class="tio-hidden password-toggle-indicator"></i>
                                                <span class="sr-only">{{ translate('show_password') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="w-100">
                                        <div class="password-toggle rtl">
                                            <input class="form-control text-align-direction w-100" name="customer_confirm_password" type="password" id="customer_confirm_password" placeholder="{{ translate('confirm_Password') }}">
                                            <label class="password-toggle-btn">
                                                <input class="custom-control-input" type="checkbox">
                                                <i class="tio-hidden password-toggle-indicator"></i>
                                                <span class="sr-only">{{ translate('show_password') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif

                @if($billingInputByCustomer)
                <div>
                    <div class="billing-methods_label d-flex flex-wrap justify-content-between gap-2 mt-4 pb-3 px-3 px-md-0">
                        <h4 class="mb-0 fs-18 text-capitalize">{{ translate('billing_address')}}</h4>

                        @php($billingAddresses=\App\Models\ShippingAddress::where(['customer_id'=>auth('customer')->id(), 'is_guest'=>'0'])->get())
                        @php($prefillBillingContactName = $loggedInCustomer && $billingAddresses->isEmpty() ? trim(($loggedInCustomer->f_name ?? '').' '.($loggedInCustomer->l_name ?? '')) : '')
                        @php($prefillBillingContactName = $prefillBillingContactName !== '' ? $prefillBillingContactName : ($loggedInCustomer && $billingAddresses->isEmpty() ? ($loggedInCustomer->name ?? '') : ''))
                        @php($prefillBillingPhone = $loggedInCustomer && $billingAddresses->isEmpty() ? ($loggedInCustomer->phone ?? '') : '')
                        @if($physical_product_view)
                        <div class="form-check d-flex gap-3 align-items-center" id="same_as_shipping_address_wrapper">
                            <input type="checkbox" id="same_as_shipping_address" name="same_as_shipping_address"
                                class="form-check-input action-hide-billing-address mt-0" {{$billingInputByCustomer==1?'':'checked'}}>
                            <label class="form-check-label user-select-none" for="same_as_shipping_address">
                                {{ translate('same_as_shipping_address')}}
                            </label>
                        </div>
                        @endif
                    </div>

                    @if(!$physical_product_view)
                    <div class="mb-3 alert--info">
                        <div class="d-flex align-items-center gap-2">
                            <img class="mb-1" src="{{ theme_asset('public/assets/front-end/img/icons/info-light.svg') }}" alt="Info">
                            <span>{{ translate('When_you_input_all_the_required_information_for_this_billing_address_it_will_be_stored_for_future_purchases') }}</span>
                        </div>
                    </div>
                    @endif

                    <form method="post" class="card __card" id="billing-address-form">
                        <div id="hide_billing_address" class="">
                            <ul class="list-group">

                                <li class="list-group-item action-billing-address-hide">
                                    @if ($billingAddresses->count() >0)
                                    <div class="d-flex align-items-center justify-content-end gap-3">

                                        <div class="dropdown">
                                            <button class="form-control dropdown-toggle text-capitalize" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{translate('saved_address')}}
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right saved-address-dropdown scroll-bar-saved-address" aria-labelledby="dropdownMenuButton">
                                                @foreach($billingAddresses as $key=>$address)
                                                <div class="dropdown-item select_billing_address {{$key == 0 ? 'active' : ''}}" id="billingAddress{{$key}}">
                                                    <input type="hidden" class="selected_billingAddress{{$key}}" value="{{$address}}">
                                                    <input type="hidden" name="billing_method_id" value="{{$address['id']}}">
                                                    <div class="media gap-2">
                                                        <div class="">
                                                            <i class="tio-briefcase"></i>
                                                        </div>
                                                        <div class="media-body">
                                                            <div class="mb-1 text-capitalize">{{$address->address_type}}</div>
                                                            <div class="text-muted fs-12 text-capitalize text-wrap">{{$address->address}}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div id="accordion">
                                        <div class="">
                                            <div class="">
                                                <div class="checkout-section-block">
                                                    <div class="checkout-section-head">
                                                        <div>
                                                            <h5 class="checkout-section-title">{{ translate('Billing_Contact_Details') }}</h5>
                                                            <p class="checkout-section-copy">{{ translate('Use_details_for_invoices_and_billing_updates') }}</p>
                                                        </div>
                                                    </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('contact_person_name')}}<span class="text-danger checkout-required-indicator" data-required-indicator="billing_contact_person_name">*</span></label>
                                                            <input type="text" class="form-control"
                                                                name="billing_contact_person_name" id="billing_contact_person_name" value="{{ $prefillBillingContactName }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('phone')}}
                                                                <span class="text-danger checkout-required-indicator" data-required-indicator="billing_phone">*</span>
                                                            </label>
                                                            <input type="text" class="form-control phone-input-with-country-picker-2"
                                                                id="billing_phone" value="{{ $prefillBillingPhone }}">
                                                            <input type="hidden" class="country-picker-phone-number-2 w-50" name="billing_phone" readonly>
                                                        </div>
                                                    </div>
                                                    @if(!auth('customer')->check())
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label
                                                                for="exampleInputEmail1">{{ translate('email')}}
                                                                <span class="text-danger checkout-required-indicator" data-required-indicator="billing_contact_email">*</span></label>
                                                            <input type="text" class="form-control"
                                                                name="billing_contact_email" id="billing_contact_email">
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>{{ translate('address_type')}} <span class="text-danger checkout-required-indicator" data-required-indicator="billing_address_type">*</span></label>
                                                            <select class="form-control" name="billing_address_type" id="billing_address_type">
                                                                <option value="permanent">{{ translate('permanent')}}</option>
                                                                <option value="home">{{ translate('home')}}</option>
                                                                <option value="office">{{ translate('office')}}</option>
                                                                <option value="others">{{ translate('others')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="checkout-section-block">
                                                    <div class="checkout-section-head">
                                                        <div>
                                                            <h5 class="checkout-section-title">{{ translate('Billing_Address_Details') }}</h5>
                                                            <p class="checkout-section-copy">{{ translate('Add_the_address_where_your_invoice_should_be_registered') }}</p>
                                                        </div>
                                                    </div>
                                                <div class="row">
                                                    @php($singleBillingCountry = count($billingCountries) === 1)
                                                    <div class="col-6 @if($singleBillingCountry) d-none single-country-hidden @endif" id="billing-country-wrapper" @if($singleBillingCountry) data-single-country @endif>
                                                        <div class="form-group">
                                                            <label @if($singleBillingCountry) class="d-none" @endif>{{ translate('country') }} <span class="text-danger checkout-required-indicator" data-required-indicator="billing_country">*</span></label>
                                                            <select name="billing_country" id="billing_country" class="form-control">
                                                                @if(!$singleBillingCountry)
                                                                <option value="">{{ translate('select_country') }}</option>
                                                                @endif
                                                                @foreach($billingCountries as $bc)
                                                                <option value="{{ $bc['code'] }}" {{ $loop->first && $singleBillingCountry ? 'selected' : '' }}>{{ $bc['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('state') }} <span class="text-danger checkout-required-indicator" data-required-indicator="billing_state">*</span></label>
                                                            <select name="billing_state_id" id="billing_state_id" class="form-control">
                                                                <option value="">{{ translate('select_state') }}</option>
                                                            </select>
                                                            <input type="hidden" name="billing_state" id="billing_state_name">

                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('city') }} <span class="text-danger checkout-required-indicator" data-required-indicator="billing_city">*</span></label>
                                                            <select name="billing_city_id" id="billing_city_id" class="form-control">
                                                                <option value="">{{ translate('select_city') }}</option>
                                                            </select>
                                                            <input type="hidden" name="billing_city" id="billing_city_name">

                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('area') }}<!--  <span class="text-danger">*</span>--></label>
                                                            <select name="billing_area_id" id="billing_area" class="form-control">
                                                                <option value="">{{ translate('select_area') }}</option>
                                                            </select>
                                                            <input type="hidden" name="billing_area" id="billing_area_name">
                                                        </div>
                                                    </div>

                                                    <!-- hide input toggel is dissable else select box -->

                                                    <!-- <div class="col-6">
                                                        <div class="form-group">
                                                            @if($zip_restrict_status)
                                                            <label>{{ translate('zip_code') }}</label>
                                                                <select name="billing_zip" class="form-control selectpicker" data-live-search="true" id="select_billing_zip">
                                                                @foreach($zip_codes as $code)
                                                                <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                                @endforeach
                                                            </select>
                                                            @endif
                                                            {{-- If $zip_restrict_status is false, nothing will be shown --}}
                                                        </div>
                                                    </div> -->



                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label>{{ translate('zip_code')}}</label>
                                                            @if($zip_restrict_status)
                                                                <select name="billing_zip" class="form-control selectpicker" data-live-search="true" id="select_billing_zip">
                                                                    @foreach($zip_codes as $code)
                                                                    <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="text" class="form-control" id="billing_zip"
                                                                    name="billing_zip">
                                                                @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="checkout-section-block">
                                                    <div class="checkout-section-head">
                                                        <div>
                                                            <h5 class="checkout-section-title">{{ translate('Street_Address') }}</h5>
                                                            <p class="checkout-section-copy">{{ translate('Provide_the_full_billing_address_for_documents_and_invoices') }}</p>
                                                        </div>
                                                    </div>
                                                <div class="form-group mb-1">
                                                    <label>{{ translate('address')}}<span class="text-danger checkout-required-indicator" data-required-indicator="billing_address">*</span></label>
                                                    <textarea class="form-control" id="billing_address" type="billing_text" name="billing_address" id="billing_address"></textarea>

                                                    <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                                        {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                                    </span>
                                                </div>
                                                @if(getWebConfig('map_api_status') ==1 )
                                                <div class="form-group map-area-alert-border location-map-billing-canvas-area">
                                                    <input id="pac-input-billing" class="controls rounded __inline-46 location-search-input-field"
                                                        title="{{translate('search_your_location_here')}}"
                                                        type="text"
                                                        placeholder="{{translate('search_here')}}" />
                                                    <div class="__h-200px" id="location_map_canvas_billing"></div>
                                                </div>
                                                @endif

                                                <input type="hidden" name="billing_method_id" id="billing_method_id" value="0">
                                                @if(auth('customer')->check())
                                                <div class=" d-flex gap-3 align-items-center">
                                                    <label class="form-check-label d-flex gap-2 align-items-center" id="save-billing-address-label">
                                                        <input type="checkbox" name="save_address_billing" id="save_address_billing">
                                                        {{ translate('save_this_Address') }}
                                                    </label>
                                                </div>
                                                @endif

                                                <input type="hidden" id="billing_latitude"
                                                    name="billing_latitude" class="form-control d-inline"
                                                    placeholder="{{ translate('ex')}} : -94.22213"
                                                    value="{{$defaultLocation?$defaultLocation['lat']:0}}"
                                                    readonly>
                                                <input type="hidden"
                                                    name="billing_longitude" class="form-control"
                                                    placeholder="{{ translate('ex')}} : 103.344322" id="billing_longitude"
                                                    value="{{$defaultLocation?$defaultLocation['lng']:0}}"
                                                    readonly>

                                                <button type="submit" class="btn btn--primary d--none" id="address_submit"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>

                @if(!Auth::guard('customer')->check() && $web_config['guest_checkout_status'] && !$physical_product_view)
                <div class="card __card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap justify-content-between gap-3">
                            <div class="min-h-45 form-check d-flex gap-3 align-items-center cursor-pointer user-select-none">
                                <input type="checkbox" id="is_check_create_account" name="is_check_create_account" class="form-check-input mt-0" value="1">
                                <label class="form-check-label font-weight-bold fs-13 create-account-info-label" for="is_check_create_account">
                                    {{translate('Create_an_account_with_the_above_info')}}
                                </label>
                            </div>

                            <div class="is_check_create_account_password_group d--none">
                                <div class="d-flex gap-3 flex-wrap flex-sm-nowrap">
                                    <div class="w-100">
                                        <div class="password-toggle rtl">
                                            <input class="form-control text-align-direction" name="customer_password" type="password" id="customer_password" placeholder="{{ translate('new_Password')}}">
                                            <label class="password-toggle-btn">
                                                <input class="custom-control-input" type="checkbox">
                                                <i class="tio-hidden password-toggle-indicator"></i>
                                                <span class="sr-only">{{ translate('show_password') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="w-100">
                                        <div class="password-toggle rtl">
                                            <input class="form-control text-align-direction" name="customer_confirm_password" type="password" id="customer_confirm_password" placeholder="{{ translate('confirm_Password')}}">
                                            <label class="password-toggle-btn">
                                                <input class="custom-control-input" type="checkbox">
                                                <i class="tio-hidden password-toggle-indicator"></i>
                                                <span class="sr-only">{{ translate('show_password') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif
        </section>
        @include('web-views.partials._order-summary')
    </div>
</div>

<span id="message-update-this-address" data-text="{{ translate('Update_this_Address') }}"></span>
<span id="message-create-account-above-info" data-text="{{ translate('Create_an_account_with_the_above_info') }}"></span>
<span id="message-create-account-below-info" data-text="{{ translate('Create_an_account_with_the_below_info') }}"></span>
<span id="message-please-fill-out-this-field" data-text="{{ translate('Please_fill_out_this_field') }}"></span>
<span id="route-fetch-area-branch" data-url="fetch-area"></span>
<span id="route-customer-choose-shipping-address-other" data-url="{{ route('customer.choose-shipping-address-other') }}"></span>
<span id="default-latitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lat']:'26.774645719165914' }}"></span>
<span id="default-longitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lng']:'29.311165295285434' }}"></span>
<span id="route-action-checkout-function" data-route="checkout-details"></span>
<span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
<span id="system-zip-restrict-status" data-value="{{ $zip_restrict_status }}"></span>
<span id="system-delivery-restriction-setup"
      data-country-visible="{{ $shippingCountryVisible ? 1 : 0 }}"
      data-state-visible="{{ $shippingStateVisible ? 1 : 0 }}"
      data-city-visible="{{ $shippingCityVisible ? 1 : 0 }}"
      data-area-visible="{{ $shippingAreaVisible ? 1 : 0 }}"
      data-zip-visible="{{ $shippingZipVisible ? 1 : 0 }}"
      data-single-country-mode="{{ $singleShippingCountryMode ? 1 : 0 }}"></span>
@endsection

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
<script>
    "use strict";

    // 1. Define URLs
    let getStatesURL = "{{ route('checkout.get.states') }}";
    let getCitiesURL = "{{ route('checkout.get.cities') }}";
    let getAreasURL = "{{ route('checkout.get.areas') }}";
    let getBillingStatesURL = "{{ route('checkout.get.billing.states') }}";
    let getBillingCitiesURL = "{{ route('checkout.get.billing.cities') }}";
    let getBillingAreasURL = "{{ route('checkout.get.billing.areas') }}";

    const billingCountries = @json($billingCountriesName);
    const deliveryRestrictedCountries = @json($shippingCountriesName);


    function deliveryRestrictedCountriesCheck(countryOrCode, elementSelector, inputElement) {
        const foundIndex = deliveryRestrictedCountries.findIndex(country => country.toLowerCase() === countryOrCode.toLowerCase());
        if (foundIndex !== -1) {
            $(elementSelector).removeClass('map-area-alert-danger');
            $(inputElement).parent().find('.map-address-alert').removeClass('opacity-100').addClass('opacity-0')
        } else {
            $(elementSelector).addClass('map-area-alert-danger');
            $(inputElement).val('')
            $(inputElement).parent().find('.map-address-alert').removeClass('opacity-0').addClass('opacity-100')
        }
    }

    $('#is_check_create_account').on('change', function() {
        if ($(this).is(':checked')) {
            $('.is_check_create_account_password_group').fadeIn();
        } else {
            $('.is_check_create_account_password_group').fadeOut();
        }
    });
</script>
<script>
    // let updateShippingCostRoute = "{{ route('cart.update-shipping-cost') }}";
    let updateShippingCostRoute = "{{ route('update-shipping-cost') }}";
</script>

<script src="{{ theme_asset(path: 'public/assets/front-end/js/bootstrap-select.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/shipping.js') }}"></script>
<script>
    const pickupBranchElement = document.getElementById('pickup_branch_id');
    if (pickupBranchElement) {
        pickupBranchElement.addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            let branchAddress = selectedOption.getAttribute('data-address') || '';

            document.getElementById('nearest_branch_textarea').value = branchAddress;
        });
    }
</script>

<script>
    $(document).ready(function() {
        // 1. Initialize Request Trackers
        let stateReq = null,
            cityReq = null,
            areaReq = null;
        let bStateReq = null,
            bCityReq = null,
            bAreaReq = null;

        // 2. Optimized Helper Function
        function renderOptions(selector, data, placeholder) {
            let html = `<option value="">${placeholder}</option>`;
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(function(item) {
                    if (item.id && item.name) {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    }
                });
            } else {
                html = `<option value="">{{ __('No available locations') }}</option>`;
            }
            $(selector).html(html);
        }

        // --- SHIPPING SECTION ---
        $('#country').on('change', function() {
            if (stateReq) stateReq.abort();
            $('#state_id').html('<option value="">{{ __('Loading...') }}</option>');
            stateReq = $.get(getStatesURL, {
                country: this.value
            }, function(res) {
                renderOptions('#state_id', res.states, "{{ __('Select State') }}");
            });
        });

        $('#state_id').on('change', function() {
            if (cityReq) cityReq.abort();
            $('#city_id').html('<option value="">{{ __('Loading...') }}</option>');
            cityReq = $.get(getCitiesURL, {
                state_id: this.value
            }, function(res) {
                renderOptions('#city_id', res.cities, "{{ __('Select City') }}");
            });
        });

        $('#city_id').on('change', function() {
            if (areaReq) areaReq.abort();
            $('#area').html('<option value="">{{ __('Loading...') }}</option>');
            areaReq = $.get(getAreasURL, {
                city_id: this.value
            }, function(res) {
                renderOptions('#area', res.areas, "{{ __('Select Area') }}");
            });
        });

        // --- SHIPPING COST UPDATE LOGIC ---
        $(document).on('change', '#area', function() {
            let country = $('#country').val();
            let state_id = $('#state_id').val();
            let city_id = $('#city_id').val();
            let area_name = $(this).find('option:selected').text();

            if (area_name && area_name !== "{{ __('Select Area') }}") {
                $.ajax({
                    url: "{{ route('cart.update-shipping-cost') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        country: country,
                        state_id: state_id,
                        city_id: city_id,
                        area_name: area_name
                    },
                    beforeSend: function() {
                        // Dim the summary to show it's calculating
                        $('.cart_title').css('opacity', '0.6');
                    },
                    success: function(data) {
                        if (data.status === 1) {
                            // Update the HTML table with new shipping and grand total
                            // $('.cart_title').html(data.view);
                            // $('#cart-summary').replaceWith(data.view);
                            // toastr.success("{{ translate('Shipping_cost_updated') }}");

                            let tempDom = $('<div/>').append(data.view);

                            // 2. Extract ONLY the inner content of #cart-summary from the response
                            let innerContent = tempDom.find('#cart-summary').html();

                            // 3. Put that inner content into your existing page's #cart-summary
                            // This prevents the <aside> and the ID from duplicating
                            $('#cart-summary').html(innerContent);

                            // 4. Re-run your sticky sidebar function so it doesn't break
                            if (typeof orderSummaryStickyFunction === "function") {
                                orderSummaryStickyFunction();
                            }
                        }
                    },
                    complete: function() {
                        $('.cart_title').css('opacity', '1');
                    }
                });
            }
        });

        // --- BILLING SECTION ---
        $('#billing_country').on('change', function() {
            if (bStateReq) bStateReq.abort();
            $('#billing_state_id').html('<option value="">{{ __('Loading...') }}</option>');
            bStateReq = $.get(getBillingStatesURL, {
                billing_country: this.value
            }, function(res) {
                renderOptions('#billing_state_id', res.states, "{{ __('Select State') }}");
            });
        });

        $('#billing_state_id').on('change', function() {
            if (bCityReq) bCityReq.abort();
            $('#billing_city_id').html('<option value="">{{ __('Loading...') }}</option>');
            bCityReq = $.get(getBillingCitiesURL, {
                billing_state_id: this.value
            }, function(res) {
                renderOptions('#billing_city_id', res.cities, "{{ __('Select City') }}");
            });
        });

        $('#billing_city_id').on('change', function() {
            if (bAreaReq) bAreaReq.abort();
            $('#billing_area').html('<option value="">{{ __('Loading...') }}</option>');
            bAreaReq = $.get(getBillingAreasURL, {
                billing_city_id: this.value
            }, function(res) {
                renderOptions('#billing_area', res.areas, "{{ __('Select Area') }}");
            });
        });

        // --- NAME STORAGE LOGIC (Hidden Inputs) ---
        // Shipping
        $(document).on('change', '#state_id', function() {
            $('#state_name').val($(this).find('option:selected').text());
        });
        $(document).on('change', '#city_id', function() {
            $('#city_name').val($(this).find('option:selected').text());
        });
        $(document).on('change', '#area', function() {
            $('#area_name').val($(this).find('option:selected').text());
        });

        // Billing
        $(document).on('change', '#billing_state_id', function() {
            $('#billing_state_name').val($(this).find('option:selected').text());
        });
        $(document).on('change', '#billing_city_id', function() {
            $('#billing_city_name').val($(this).find('option:selected').text());
        });
        $(document).on('change', '#billing_area', function() {
            $('#billing_area_name').val($(this).find('option:selected').text());
        });
    });
</script>


@if(getWebConfig('map_api_status') ==1 )
<script
    src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=mapsShopping&loading=async&libraries=places&v=3.56"
    defer>
</script>

@endif
@endpush
