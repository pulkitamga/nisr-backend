@extends('layouts.back-end.app')

@section('title', translate('shipping_method'))
<style type="text/css">
    #map {
        height: 50vh;
        width: 100%;
        position: relative;
    }

    .map-controls {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background-color: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    #searchInput {
        width: 250px;
        padding: 5px;
    }
</style>
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" alt="">
            {{translate('business_setup')}}
        </h2>
        <div class="btn-group">
            <div class="ripple-animation" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"
                    class="svg replaced-svg">
                    <path
                        d="M9.00033 9.83268C9.23644 9.83268 9.43449 9.75268 9.59449 9.59268C9.75449 9.43268 9.83421 9.2349 9.83366 8.99935V5.64518C9.83366 5.40907 9.75366 5.21463 9.59366 5.06185C9.43366 4.90907 9.23588 4.83268 9.00033 4.83268C8.76421 4.83268 8.56616 4.91268 8.40616 5.07268C8.24616 5.23268 8.16644 5.43046 8.16699 5.66602V9.02018C8.16699 9.25629 8.24699 9.45074 8.40699 9.60352C8.56699 9.75629 8.76477 9.83268 9.00033 9.83268ZM9.00033 13.166C9.23644 13.166 9.43449 13.086 9.59449 12.926C9.75449 12.766 9.83421 12.5682 9.83366 12.3327C9.83366 12.0966 9.75366 11.8985 9.59366 11.7385C9.43366 11.5785 9.23588 11.4988 9.00033 11.4993C8.76421 11.4993 8.56616 11.5793 8.40616 11.7393C8.24616 11.8993 8.16644 12.0971 8.16699 12.3327C8.16699 12.5688 8.24699 12.7668 8.40699 12.9268C8.56699 13.0868 8.76477 13.1666 9.00033 13.166ZM9.00033 17.3327C7.84755 17.3327 6.76421 17.1138 5.75033 16.676C4.73644 16.2382 3.85449 15.6446 3.10449 14.8952C2.35449 14.1452 1.76088 13.2632 1.32366 12.2493C0.886437 11.2355 0.667548 10.1521 0.666992 8.99935C0.666992 7.84657 0.885881 6.76324 1.32366 5.74935C1.76144 4.73546 2.35505 3.85352 3.10449 3.10352C3.85449 2.35352 4.73644 1.7599 5.75033 1.32268C6.76421 0.88546 7.84755 0.666571 9.00033 0.666016C10.1531 0.666016 11.2364 0.884905 12.2503 1.32268C13.2642 1.76046 14.1462 2.35407 14.8962 3.10352C15.6462 3.85352 16.24 4.73546 16.6778 5.74935C17.1156 6.76324 17.3342 7.84657 17.3337 8.99935C17.3337 10.1521 17.1148 11.2355 16.677 12.2493C16.2392 13.2632 15.6456 14.1452 14.8962 14.8952C14.1462 15.6452 13.2642 16.2391 12.2503 16.6768C11.2364 17.1146 10.1531 17.3332 9.00033 17.3327ZM9.00033 15.666C10.8475 15.666 12.4206 15.0168 13.7195 13.7185C15.0184 12.4202 15.6675 10.8471 15.667 8.99935C15.667 7.15213 15.0178 5.57907 13.7195 4.28018C12.4212 2.98129 10.8481 2.33213 9.00033 2.33268C7.1531 2.33268 5.58005 2.98185 4.28116 4.28018C2.98227 5.57852 2.3331 7.15157 2.33366 8.99935C2.33366 10.8466 2.98283 12.4196 4.28116 13.7185C5.57949 15.0174 7.15255 15.6666 9.00033 15.666Z"
                        fill="currentColor"></path>
                </svg>
            </div>
            <div
                class="dropdown-menu dropdown-menu-end bg-aliceblue border border-color-primary-light p-4 dropdown-w-lg">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/note.png')}}" alt="">
                    <h5 class="text-primary mb-0">{{translate('note')}}</h5>
                </div>
                <p class="title-color font-weight-medium mb-0">{{
                    translate('please_click_the_Save_button_below_to_save_all_the_changes') }}</p>
            </div>
        </div>
    </div>
    @include('admin-views.business-settings.business-setup-inline-menu')
    <div class="card">
        <div class="card-header">
            <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                {{translate('shipping')}}
            </h5>
        </div>
        @php
            $shippingMethod = getWebConfig('shipping_method');
        @endphp
        <div class="card-body">
            <form action="{{ route('admin.business-settings.shipping-method.update-shipping-responsibility') }}"
                method="post">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <label class="title-color d-flex">{{translate('shipping_responsibility')}}</label>
                            <div class="form-control min-form-control-height h-auto form-group d-flex flex-wrap gap-2">
                                <div class="custom-control custom-radio flex-grow-1">
                                    <input type="radio" class="custom-control-input toggle-switch-message"
                                        value="inhouse_shipping" name="shipping_method" id="inhouse-shipping" {{
                                        $shippingMethod=='inhouse_shipping' ?'checked':'' }}
                                        data-modal-id="toggle-modal" data-toggle-id="inhouse-shipping"
                                        data-on-image="seller-wise-shipping.png" data-off-image="inhouse-shipping.png"
                                        data-on-title="{{translate('want_to_change_the_shipping_responsibility_to_Inhouse').'?'}}"
                                        data-off-title="{{translate('want_to_change_the_shipping_responsibility_to_Third_Party_Delivery').'?'}}"
                                        data-on-message="<p>{{translate('admin_will_handle_the_shipping_responsibilities_when_you_choose_inhouse_shipping_method').'.'}}</p>"
                                        data-off-message="<p>{{translate('admin_will_handle_the_shipping_responsibilities_when_you_choose_inhouse_shipping_method').'.'}}</p>">
                                    <label class="custom-control-label"
                                        for="inhouse-shipping">{{translate('inhouse_shipping')}}</label>
                                </div>
                                <div class="custom-control custom-radio flex-grow-1">
                                    <input type="radio" class="custom-control-input toggle-switch-message"
                                        value="sellerwise_shipping" name="shipping_method" id="seller-wise-shipping" {{
                                        $shippingMethod=='sellerwise_shipping' ?'checked':'' }}
                                        data-modal-id="toggle-modal" data-toggle-id="seller-wise-shipping"
                                        data-on-image="inhouse-shipping.png" data-off-image="seller-wise-shipping.png"
                                        data-on-title="{{translate('want_to_change_the_shipping_responsibility_to_Third_Party').'?'}}"
                                        data-off-title="{{translate('Want_to_change_the_shipping_responsibility_to_Inhouse').'?'}}"
                                        data-on-message="<p>{{translate('third_party_will_handle_the_shipping_responsibilities_when_you_choose_third_party_shipping_method').'.'}}</p>"
                                        data-off-message="<p>{{translate('third_party_will_handle_the_shipping_responsibilities_when_you_choose_vendor_wise_shipping_method').'.'}}</p>">
                                    <label class="custom-control-label"
                                        for="seller-wise-shipping">{{translate('third_party_delivery')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $shippingType = isset($adminShipping) ? $adminShipping['shipping_type'] : 'order_wise';
                    @endphp

                    <div class="col-md-6">
                        <div class="">

                            <label class="title-color" id="for_inhouse_deliver"
                                style="{{ $shippingMethod != 'sellerwise_shipping' ? 'display:none':'' }}">{{translate('shipping_method')}}</label>
                            <label class="title-color" id="for_seller_deliver"
                                style="{{ $shippingMethod == 'sellerwise_shipping' ? 'display:none':'' }}">{{translate('shipping_method_for_In-house_deliver')}}</label>
                            <select class="form-control text-capitalize w-100 shipping-type" name="shippingCategory">
                                <option value="0" selected disabled>{{'---'.translate('select').'---'}}</option>
                                <option value="order_wise" {{$shippingType=='order_wise' ?'selected':'' }}>
                                    {{translate('order_wise')}} </option>
                                <option value="category_wise" {{$shippingType=='category_wise' ?'selected':'' }}>
                                    {{translate('category_wise')}}</option>
                                <option value="product_wise" {{$shippingType=='product_wise' ?'selected':'' }}>
                                    {{translate('product_wise')}}</option>
                                <option value="area_wise" {{$shippingType=='area_wise' ?'selected':'' }}>
                                    {{translate('area_wise')}}</option>
                            </select>
                            <div class="mt-2" id="product_wise_note">
                                <p>
                                    <img width="16" class="mt-n1"
                                        src="{{dynamicAsset(path: 'public/assets/back-end/img/danger-info.png')}}"
                                        alt="">
                                    <strong>{{translate('note').' '.':'}}</strong>
                                    {{translate('when_adding_a_product_a_product_specific_shipping_charge_is_added_Verify_that_all_of_the_products_delivery_costs_are_up_to_date').'.'}}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="d-flex justify-content-end gap-10">
                            <button type="submit" class="btn btn--primary px-5">{{translate('save')}}</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div id="update_category_shipping_cost">
        <div class="card mt-3">
            <div class="px-3 pt-4">
                <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                    {{translate('category_wise_shipping_cost')}}
                </h5>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <form action="{{route('admin.business-settings.category-shipping-cost.store')}}" method="POST">
                        @csrf
                        <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start"
>
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('category_name')}}</th>
                                    <th>{{translate('cost_per_product')}}</th>
                                    <th class="text-center">{{translate('status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sl = 0;
                                @endphp
                                @foreach ($allCategoryShippingCost as $key=>$item)
                                @if($item->category)
                                <tr>
                                    <td>
                                        {{++$sl}}
                                    </td>
                                    <td>
                                        <img class="rounded" width="64"
                                            src="{{ getStorageImages(path: $item->category->icon_full_url, type: 'backend-category') }}"
                                            alt="">
                                    </td>
                                    <td>
                                        {{$item->category->name}}
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control w-auto" name="ids[]"
                                            value="{{$item->id}}">
                                        <input type="hidden" class="form-control w-auto" name="category_ids[]"
                                            value="{{$item->category->id}}">
                                        <input type="number" class="form-control w-auto" min="0" step="0.01"
                                            name="cost[]" value="{{usdToDefaultCurrency(amount: $item->cost)}}">
                                    </td>
                                    <td>
                                        <label class="mx-auto switcher">
                                            <input type="checkbox" class="status switcher_input" name="multiplyQTY[]"
                                                id="" value="{{$item->id}}" {{$item->multiply_qty == 1?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                <tr>
                                    <td colspan="5">
                                        <div class="d-flex flex-wrap justify-content-end gap-10">
                                            <button type="submit"
                                                class="btn btn--primary px-5">{{translate('save')}}</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="order_wise_shipping">
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                    {{translate('add_order_wise_shipping')}}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.shipping-method.index')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-xl-4 col-md-6">
                            @php
                                $activeLanguage = $defaultLanguage;
                                $_la = is_array($language ?? null) ? $language : [];
                                if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                            @endphp
                            <ul class="nav nav-tabs w-fit-content mb-2">
                                @foreach($language as $lang)
                                    <li class="nav-item text-capitalize">
                                        <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}" id="{{$lang}}-link">
                                            {{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            @foreach($language as $lang)
                                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{$lang}}-form">
                                    <label class="title-color d-flex">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                                    <input type="text" name="title[]" class="form-control"
                                        placeholder="{{translate('title')}}" required>
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                            @endforeach
                        </div>
                        <div class="col-xl-4 col-md-6">
                            @foreach($language as $lang)
                                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{$lang}}-form">
                                    <label class="title-color d-flex">{{ translate('duration') }} ({{ strtoupper($lang) }})</label>
                                    <input type="text" name="duration[]" class="form-control"
                                        placeholder="{{translate('ex')}} : {{translate('4_to_6_days')}}" required>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="cost">{{translate('cost')}}</label>
                                        <input type="number" min="0" step="0.01" max="1000000" name="cost"
                                            class="form-control" placeholder="{{translate('ex')}} :" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-10">
                        <button type="submit" class="btn btn--primary px-5">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="px-3 py-4">
                <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                    {{translate('order_wise_shipping_method')}}
                    <span class="badge badge-soft-dark radius-50 fz-12">{{ $shippingMethods->count() }}</span>
                </h5>
            </div>
            <div class="table-responsive pb-3">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table text-start"
>
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('title')}}</th>
                            <th>{{translate('duration')}}</th>
                            <th>{{translate('cost')}}</th>
                            <th class="text-center">{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shippingMethods as $key=>$method)
                        <tr>
                            <th>{{$key+1}}</th>
                            <td>{{$method['title']}}</td>
                            <td>
                                {{$method['duration']}}
                            </td>
                            <td>
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $method['cost']), currencyCode:
                                getCurrencyCode(type: 'default'))}}
                            </td>
                            <td>
                                <form action="{{route('admin.business-settings.shipping-method.update-status')}}"
                                    method="post" id="shipping-methods{{$method['id']}}-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$method['id']}}">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input toggle-switch-message"
                                            id="shipping-methods{{$method['id']}}" name="status" value="1"
                                            {{$method->status == 1 ? 'checked' : ''}}
                                        data-modal-id = "toggle-status-modal"
                                        data-toggle-id = "shipping-methods{{$method['id']}}"
                                        data-on-image = "category-status-on.png"
                                        data-off-image = "category-status-off.png"
                                        data-on-title = "{{translate('want_to_Turn_ON_This_Shipping_Method').'?'}}"
                                        data-off-title = "{{translate('want_to_Turn_OFF_This_Shipping_Method').'?'}}"
                                        data-on-message = "<p>
                                            {{translate('if_you_enable_this_shipping_method_will_be_shown_in_the_user_app_and_website_for_customer_checkout')}}
                                        </p>"
                                        data-off-message = "<p>
                                            {{translate('if_you_disable_this_shipping_method_will_not_be_shown_in_the_user_app_and_website_for_customer_checkout')}}
                                        </p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-10">
                                    <a class="btn btn-outline--primary btn-sm edit" title="{{ translate('edit')}}"
                                        href="{{route('admin.business-settings.shipping-method.update',[$method['id']])}}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a title="{{translate('delete')}}"
                                        class="btn btn-outline-danger btn-sm delete-data-without-form"
                                        data-action="{{route('admin.business-settings.shipping-method.delete')}}"
                                        data-id="{{ $method['id'] }}">
                                        <i class="tio-delete"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($shippingMethods)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
            @else
            <div class="d-flex justify-content-end my-2 mx-2">
                {!! $shippingMethods->links() !!}
            </div>
            @endif
        </div>
    </div>
    <div id="area_wise_shipping">
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                    {{translate('add_area_wise_shipping')}}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.shipping-method.add-area')}}" method="post">
                    @csrf
                    <div class="row">
                        @php $countryCode = getWebConfig(name: 'country_code'); @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="title">{{translate('country')}}</label>
                                        <select id="country" name="country" class="form-control js-select2-custom">
                                            @foreach(COUNTRIES as $country)
                                            <option value="{{$country['code']}}" {{
                                                $countryCode?($countryCode==$country['code']?'selected':''):'' }}>
                                                {{$country['name']}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="state">{{translate('state')}}</label>
                                        <select id="state" name="state" class="form-control js-select2-custom"
                                            data-state-id="">
                                            <option value="0" selected="" disabled="">---Select---</option>
                                            @foreach($states as $state)
                                            <option value="{{$state['id']}}">
                                                {{$state['name']}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="title">{{translate('city')}}</label>
                                        <select id="city" name="city" class="form-control js-select2-custom"
                                            data-city-id="">
                                            <option value="0" selected="" disabled="">---Select---</option>
                                            @foreach($cities as $city)
                                            <option value="{{$city['id']}}">
                                                {{$city['name']}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="title">{{translate('area')}}</label>
                                          <select id="area" name="area" class="form-control js-select2-custom"
                                            data-city-id="">
                                            <option value="0" selected="" disabled="">---Select---</option>
                                         
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex"
                                            for="duration">{{translate('duration')}}</label>
                                        <input type="text" name="duration" class="form-control"
                                            placeholder="{{translate('ex')}} : {{translate('4_to_6_days')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <label class="title-color d-flex" for="cost">{{translate('cost')}}</label>
                                        <input type="number" min="0" step="0.01" max="1000000" name="cost"
                                            class="form-control" placeholder="{{translate('ex')}} :">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3 d-none">
                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Coordinates') }}<span
                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{translate('messages.draw_your_zone_on_the_map')}}">{{translate('messages.draw_your_zone_on_the_map')}}</span></label>
                        <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control"
                            readonly></textarea>
                    </div>
                    <!-- <input id="searchInput" type="text" placeholder="Search for a place">
                        <div id="map" style="height: 200px; width: 100%;"></div> -->
                    <!-- <div id="map" style="width: 100%; height: 400px;"></div>
                        <input id="searchInput" type="text" placeholder="Search here" style="margin-top: 10px; width: 100%; padding: 10px;"> -->
                    <input id="map-pac-input" class="form-control rounded __map-input mt-1"
                        title="{{translate('search_your_location_here')}}" type="text"
                        placeholder="{{translate('search_here')}}" />
                    <div class="rounded w-100 __h-400px mb-5" id="location-map-canvas"></div>
                    <!-- <button id="resetMap" style="margin-top: 10px; padding: 10px;">Reset</button> -->
                    <!-- <div id="map">
                          <div class="map-controls">
                            <input
                              id="searchInput"
                              class="controls"
                              type="text"
                              placeholder="Search for a location"
                            />
                          </div>
                        </div> -->
                    <div class="d-flex justify-content-end gap-10 mt-5">
                        <button type="submit" class="btn btn--primary px-5">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="px-3 py-4">
                <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                    {{translate('area_wise_shipping_method')}}
                    <span class="badge badge-soft-dark radius-50 fz-12">{{ $shippingMethodsArea->count() }}</span>
                </h5>
            </div>
            <div class="table-responsive pb-3">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table text-start"
>
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Country')}}</th>
                            <th>{{translate('State')}}</th>
                            <th>{{translate('City')}}</th>
                            <th>{{translate('Area')}}</th>
                            <th>{{translate('duration')}}</th>
                            <th>{{translate('cost')}}</th>
                            <th class="text-center">{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shippingMethodsArea as $key=>$method)
                        <tr>
                            <th>{{$key+1}}</th>
                            <td>{{$method['country']}}</td>
                            <td>{{$method['state'] ? $method['state']['name'] : 'null' }}</td>
                            <td>{{$method['city'] ? $method['city']['name'] : 'null' }}</td>
                            <td>{{$method['area']}}</td>
                            <td>
                                {{$method['duration']}}
                            </td>
                            <td>
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $method['cost']), currencyCode:
                                getCurrencyCode(type: 'default'))}}
                            </td>
                            <td>
                                <form action="{{route('admin.business-settings.shipping-method.update-area-status')}}"
                                    method="post" id="shipping-methods{{$method['id']}}-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$method['id']}}">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input toggle-switch-message"
                                            id="shipping-methods{{$method['id']}}" name="status" value="1"
                                            {{$method->status == 1 ? 'checked' : ''}}
                                        data-modal-id = "toggle-status-modal"
                                        data-toggle-id = "shipping-methods{{$method['id']}}"
                                        data-on-image = "category-status-on.png"
                                        data-off-image = "category-status-off.png"
                                        data-on-title = "{{translate('want_to_Turn_ON_This_Area_Shipping_Method').'?'}}"
                                        data-off-title =
                                        "{{translate('want_to_Turn_OFF_This_Area_Shipping_Method').'?'}}"
                                        data-on-message = "<p>
                                            {{translate('if_you_enable_this_area_shipping_method_will_be_shown_in_the_user_app_and_website_for_customer_checkout')}}
                                        </p>"
                                        data-off-message = "<p>
                                            {{translate('if_you_disable_this_area_shipping_method_will_not_be_shown_in_the_user_app_and_website_for_customer_checkout')}}
                                        </p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-10">
                                    <a class="btn btn-outline--primary btn-sm edit" title="{{ translate('edit')}}"
                                        href="{{route('admin.business-settings.shipping-method.update-area',[$method['id']])}}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a title="{{translate('delete')}}"
                                        class="btn btn-outline-danger btn-sm delete-data-without-form"
                                        data-action="{{route('admin.business-settings.shipping-method.delete-area')}}"
                                        data-id="{{ $method['id'] }}">
                                        <i class="tio-delete"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($shippingMethods)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
            @else
            <div class="d-flex justify-content-end my-2 mx-2">
                {!! $shippingMethods->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
<span id="get-shipping-type-data" data-action="{{route('admin.business-settings.shipping-type.index')}}"
    data-success="{{translate('shipping_method_updated_successfully').'!!'}}"></span>
<span id="route-get-country-state" data-url="{{route('admin.business-settings.shipping-method.getStates')}}"></span>
<span id="route-get-state-cities" data-url="{{route('admin.business-settings.shipping-method.getCities')}}"></span>
<span id="route-get-city-areas" data-url="{{route('admin.business-settings.shipping-method.getAreas')}}"></span>
<span id="get-shipping-type-value" data-value="{{$shippingType}}"></span>
@php
    $default_location = getWebConfig(name: 'default_location');
@endphp
@endsection

@push('script')
<script
    src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=initAutocomplete&loading=async&libraries=drawing,places&v=3.56">
</script>
<script>
    "use strict";

        function initAutocomplete() {
            const defaultLocation = {
                lat: {{ $default_location['lat'] ?? 0 }}, // Fallback to 0 if not defined
                lng: {{ $default_location['lng'] ?? 0 }}  // Fallback to 0 if not defined
            };
            // console.log(defaultLocation);

            const map = new google.maps.Map(document.getElementById("location-map-canvas"), {
                center: defaultLocation,
                zoom: 12,
            });

            const input = document.getElementById("map-pac-input");
            const autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo("bounds", map);

            const markers = [];  // Array to store markers

            // Create the marker used for autocomplete place change
            const marker = new google.maps.Marker({
                map,
                anchorPoint: new google.maps.Point(0, -29),
            });

            autocomplete.addListener("place_changed", () => {
                marker.setVisible(false);
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    alert(@json(__('No details available for input:')) + " '" + place.name + "'");
                    return;
                }

                map.setCenter(place.geometry.location);
                map.setZoom(17);
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                // Remove all previous markers
                markers.forEach(function(existingMarker) {
                    existingMarker.setMap(null);
                });

                // Clear markers array
                markers.length = 0;

                // Add the new marker to the markers array
                markers.push(marker);
            });

            // Request user's geolocation if available
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    // console.log("User Location:", userLocation);

                    // Set map to the user's current location
                    map.setCenter(userLocation);
                    map.setZoom(13); // Optionally, zoom in closer

                    // Add a marker for the user's location
                   /* const userMarker = new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: "Your Location",
                    });*/

                    // Remove all previous markers
                    markers.forEach(function(existingMarker) {
                        existingMarker.setMap(null);
                    });

                    // Clear markers array
                    markers.length = 0;

                    // Add the user's location marker to the markers array
                    // markers.push(userMarker);

                }, function(error) {
                    // If geolocation access is denied or fails, log the error and keep the default location
                    console.error("Geolocation error:", error);
                    alert(@json(__('Unable to retrieve your location. Using default location.')));
                });
            } else {
                // If geolocation is not supported, log a message and use the default location
                console.log("Geolocation not supported. Using default location.");
            }

            // Initialize the drawing manager
            const drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [
                        google.maps.drawing.OverlayType.POLYGON,  // Polygon mode only
                    ],
                },
                markerOptions: {
                    draggable: true,
                },
                circleOptions: {
                    fillColor: "#ffff00",
                    fillOpacity: 0.5,
                    strokeWeight: 1,
                    clickable: true,
                    editable: true,
                    zIndex: 1,
                },
            });

            // Attach Drawing Manager to the Map
            drawingManager.setMap(map);

            // Add Event Listeners for Drawing Manager
            google.maps.event.addListener(drawingManager, "overlaycomplete", (event) => {
                // If the overlay is a polygon
                if (event.type === google.maps.drawing.OverlayType.POLYGON) {
                    console.log("Polygon created:", event.overlay.getPath().getArray());

                    // Remove all previous markers and polygons
                    markers.forEach(function(existingMarker) {
                        existingMarker.setMap(null);
                    });

                    // Clear markers array
                    markers.length = 0;

                    // Add the new polygon to the markers array (though it's a polygon, you can still track it)
                    markers.push(event.overlay);

                    const polygon = event.overlay;

                    // Get coordinates as an array of lat/lng objects
                    const path = polygon.getPath();
                    const coordinates = [];
                    path.forEach((point) => {
                        coordinates.push({ lat: point.lat(), lng: point.lng() });
                    });

                    // Update the hidden input with coordinates in JSON format
                    document.getElementById("coordinates").value = JSON.stringify(coordinates);
                    console.log("Polygon coordinates:", coordinates);
                }
            });
        }
</script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/shipping-method.js')}}"></script>
@endpush
