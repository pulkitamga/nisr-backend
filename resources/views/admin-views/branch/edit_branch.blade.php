@extends('layouts.back-end.app')

@section('title', translate('update_Branch'))
@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0]['code'] ?? 'en';
$translations = [];
foreach ($aBranchDetails->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
@endphp


<div class="content container-fluid main-card {{Session::get('direction')}}">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" class="mb-1" alt="">
            {{ translate('update_Branch') }}
        </h2>
    </div>
    <form class="user" action="{{route('admin.branch.update',[$aBranchDetails['id']])}}" method="post" enctype="multipart/form-data" id="update-branch-form">
        @csrf
        <div class="card">
            <div class="card-body">
                <input type="hidden" name="status" value="approved">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}" class="mb-1" alt="">
                    {{ translate('branch_information') }}
                </h5>
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="row">
                    @foreach($languages as $lang)
                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form col-lg-8"
                        id="{{ $lang }}-form">
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                        <div class="row"> 
                            <div class="col-sm-6 col-lg-6">
                                <div class="form-group">
                                    <label class="title-color d-flex">{{translate('Branch_Name')}}  ({{ strtoupper($lang) }})</label>
                                    <input class="form-control" type="text" name="branch_name[]" value="{{ $lang == $defaultLanguage ? $aBranchDetails['branch_name'] : ($translations[$lang]['branch_name'] ?? '') }}" placeholder="{{translate('enter_branch_name')}}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-6">
                                <div class="form-group">
                                    <label class="title-color d-flex">{{translate('Branch_address')}}  ({{ strtoupper($lang) }})</label>
                                    <input type="text" value="{{ $lang == $defaultLanguage ? $aBranchDetails['branch_address'] : ($translations[$lang]['branch_address'] ?? '')}}" name="branch_address[]" class="form-control" id="branch_address" placeholder="{{translate('your_branch_address')}}" required>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('phone')}}</label>
                            <input class="form-control" type="text" name="phone" value="{{$aBranchDetails['phone']}}" placeholder="{{translate('01xxxxxxxx')}}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('email')}}</label>
                            <input class="form-control" type="text" name="email" value="{{$aBranchDetails['email']}}" placeholder="{{translate('company@gmail.com')}}">
                        </div>
                    </div>
                    @php($countryCode = getWebConfig(name: 'country_code'))
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('country')}} </label>
                            <select id="branch_country" name="branch_country" class="form-control js-select2-custom">
                                @foreach(COUNTRIES as $country)
                                <option value="{{$country['code']}}" {{ $aBranchDetails['branch_country']?($aBranchDetails['branch_country']==$country['code']?'selected':''):'' }}>
                                    {{$country['name']}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('state')}} </label>
                            <select id="branch_state" name="branch_state" class="form-control js-select2-custom">
                                @foreach($states as $state)
                                <option value="{{$state['name']}}" {{ $aBranchDetails['branch_state']?($aBranchDetails['branch_state']==$state['name']?'selected':''):'' }}>
                                    {{$state['name']}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Zipcode')}}</label>
                            <input type="text" value="{{$aBranchDetails['branch_zipcode']}}" name="zipcode" class="form-control" id="zipcode" placeholder="{{translate('your_zipcode')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{ translate('Select_Manager') }}</label>
                            <select class="form-control" name="manager_id" id="manager_id" required>
                                <option value="">{{ translate('Select_Manager') }}</option>

                                @forelse($admins as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @empty
                                <option value="">{{ translate('No Managers') }}</option>
                                @endforelse
                            </select>

                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Status')}}</label>
                            <select class="form-control" name="status" id="status">
                                <option value="active" {{$aBranchDetails['status']?($aBranchDetails['status']=='active'?'selected':''):'' }}>Active</option>
                                <option value="inactive" {{$aBranchDetails['status']?($aBranchDetails['status']=='inactive'?'selected':''):'' }}>Block</option>
                            </select>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-body">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <i class="tio-time tio-lg fw-bold"></i>
                    {{ translate('Working Hours') }}
                </h5>
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <table class="table table-hover table-thead-bordered table-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle">No.</th>
                                    <th rowspan="2" class="text-center align-middle">Days</th>
                                    <th colspan="2" class="text-center align-middle">Working Hours</th>
                                </tr>
                                <tr>
                                    <th>Working From</th>
                                    <th>Working To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center align-middle">1</td>
                                    <td class="text-center align-middle font-weight-bold">Sunday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['sun_branch_hours_from']}}" name="sun_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['sun_branch_hours_to']}}" name="sun_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">2</td>
                                    <td class="text-center align-middle font-weight-bold">Monday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['mon_branch_hours_from']}}" name="mon_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['mon_branch_hours_to']}}" name="mon_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">3</td>
                                    <td class="text-center align-middle font-weight-bold">Tuesday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['tue_branch_hours_from']}}" name="tue_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['tue_branch_hours_to']}}" name="tue_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">4</td>
                                    <td class="text-center align-middle font-weight-bold">Wednesday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['wed_branch_hours_from']}}" name="wed_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['wed_branch_hours_to']}}" name="wed_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">5</td>
                                    <td class="text-center align-middle font-weight-bold">Thursday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['thu_branch_hours_from']}}" name="thu_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['thu_branch_hours_to']}}" name="thu_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">6</td>
                                    <td class="text-center align-middle font-weight-bold">Friday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['fri_branch_hours_from']}}" name="fri_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['fri_branch_hours_to']}}" name="fri_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">7</td>
                                    <td class="text-center align-middle font-weight-bold">Saturday</td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['sat_branch_hours_from']}}" name="sat_branch_hours_from" class="form-control border-0" id="operating-hours-from" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                    <td class="p-0">
                                        <input type="time" value="{{$aBranchDetails['sat_branch_hours_to']}}" name="sat_branch_hours_to" class="form-control border-0" id="operating-hours-to" placeholder="{{translate('your_shop_address')}}" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-body">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <i class="ti-share tio-lg fw-bold"></i>
                    {{ translate('Area_Covered') }}
                </h5>
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('City')}} </label>
                            <select id="city" name="shipping_method_city" class="form-control js-select2-custom">
                                <option value="0" selected="" disabled="">---Select---</option>
                                @foreach($aUniqueCities as $city)
                                <option value="{{$city['city_id']}}" {{ $aBranchDetails['shipping_method_city']?($aBranchDetails['shipping_method_city']==$city['city_id']?'selected':''):'' }}>
                                    {{$city['city_name']}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Shipping_Method_Area')}}</label>
                            <select class="js-example-basic-multiple js-states js-example-responsive form-control" name="shipping_methods_area[]" id="shipping_methods_area" multiple="multiple">
                                @foreach ($aShippingMethodArea as $key => $a)
                                <option value="{{ $a['id'] }}" {{ in_array($a['id'], $shipping_methods_area) ? 'selected' : '' }}>
                                    {{ $a['area'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4 d-none">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Delivery_restriction')}}</label>
                            <select class="js-example-basic-multiple js-states js-example-responsive form-control" name="delivery_restriction[]" id="delivery_restriction" multiple="multiple">
                                @foreach ($aDeliveryRestriction as $key => $a)
                                <option value="{{ $a['id'] }}" {{ in_array($a['id'], $delivery_restriction) ? 'selected' : '' }}>
                                    {{ $a['area'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-body">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/location.png')}}" class="mb-1" alt="">
                    {{ translate('MAP_Location') }}
                </h5>
                <div class="row">
                    @php($default_location = getWebConfig(name: 'default_location'))
                    @if(getWebConfig('map_api_status') ==1 )
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">
                                {{translate('latitude')}}
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="right" title="{{translate('copy_the_latitude_of_your_business_location_from_Google_Maps_and_paste_it_here')}}">
                                    <img width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" alt="">
                                </span>
                            </label>
                            <input class="form-control latitude disabled-input" type="text" name="branch_latitude" id="latitude" value="{{ $aBranchDetails['branch_latitude']??$default_location['lat'] }}" placeholder="{{translate('latitude')}}" readonly>

                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">
                                {{translate('longitude')}}
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="right" title="{{translate('copy_the_longitude_of_your_business_location_from_Google_Maps_and_paste_it_here')}}">
                                    <img width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" alt="">
                                </span>
                            </label>
                            <input class="form-control longitude disabled-input" type="text" name="branch_longitude" id="longitude" value="{{ $aBranchDetails['branch_longitude']??$default_location['lng'] }}" placeholder="{{translate('longitude')}}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="title-color d-flex justify-content-end">
                                <span class="badge badge--primary-2">
                                    {{translate('latitude').' : '}}
                                    <span id="showLatitude">
                                        {{($aBranchDetails['branch_latitude']??$default_location['lat'])}}
                                    </span>
                                </span>
                                <span class="mx-1 badge badge--primary-2" id="showLongitude">
                                    {{translate('longitude').' : '}}
                                    <span id="showLongitude">
                                        {{($aBranchDetails['branch_longitude']??$default_location['lng'])}}
                                    </span>
                                </span>
                            </label>
                            <input id="map-pac-input" class="form-control rounded __map-input mt-1" title="{{translate('search_your_location_here')}}" type="text" placeholder="{{translate('search_here')}}" />
                            <div class="rounded w-100 __h-200px mb-5" id="location-map-canvas"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-footer">
                <div class="d-flex align-items-center justify-content-end gap-10">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary reset-button">{{translate('reset')}} </button>
                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="update-branch-form" data-redirect-route="{{route('admin.branch.update',[$aBranchDetails['id']])}}" data-message="{{translate('want_to_update_this_branch').'?'}}">{{translate('update')}}</button>
                </div>
            </div>
        </div>
    </form>
    <span id="get-default-latitude" data-latitude="{{$aBranchDetails['branch_latitude']??$default_location['lat']}}"></span>
    <span id="get-default-longitude" data-longitude="{{$aBranchDetails['branch_longitude']??$default_location['lng']}}"></span>
    <span id="route-get-cities-area" data-url="{{route('admin.branch.getCitiesArea')}}"></span>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/branch.js')}}"></script>

@if(getWebConfig('map_api_status') ==1 )
<script src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=initAutocomplete&loading=async&libraries=places&v=3.56" defer>
</script>
@endif
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/maintenance-mode-setting.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/business-setting.js') }}"></script>
@endpush