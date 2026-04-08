@extends('layouts.front-end.app')

@section('title', translate('my_Address'))

@push('css_or_js')
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/nouislider/distribute/nouislider.min.css')}}" />
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/address.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')
@include('layouts.front-end.partials._store-header')
@php($shippingRestrictionSetup = $deliveryRestriction['setup'] ?? [])
@php($singleCountryMode = (bool)($deliveryRestriction['single_country_mode'] ?? false))
@php($showCountryField = (bool)($shippingRestrictionSetup['country']['visible'] ?? false) && !$singleCountryMode)
@php($showStateField = (bool)($shippingRestrictionSetup['state']['visible'] ?? false) && ($showCountryField || $singleCountryMode))
@php($showCityField = (bool)($shippingRestrictionSetup['city']['visible'] ?? false) && $showStateField)
@php($showAreaField = (bool)($shippingRestrictionSetup['area']['visible'] ?? false) && $showCityField)
@php($showZipField = (bool)($shippingRestrictionSetup['zip']['visible'] ?? false))

<div class="container py-4 rtl __account-address text-align-direction">

    <div class="row g-3">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 col-md-8">

            <div class="card">
                <div class="card-body">
                    <h5 class="font-bold m-0 fs-16">{{translate('Update_Addresses')}}</h5>
                    <form action="{{route('address-update')}}" method="post">
                        @csrf
                        <div class="row pb-1">
                            <div class="col-md-6">
                                <input type="hidden" name="id" value="{{$shippingAddress->id}}">
                                <ul class="donate-now d-flex gap-2">
                                    <li class="address_type_li">
                                        <input type="radio" class="address_type" id="a25" name="addressAs" value="permanent" {{ $shippingAddress->address_type == 'permanent' ? 'checked' : ''}} />
                                        <label for="a25" class="component">{{translate('permanent')}}</label>
                                    </li>
                                    <li class="address_type_li">
                                        <input type="radio" class="address_type" id="a50" name="addressAs" value="home" {{ $shippingAddress->address_type == 'home' ? 'checked' : ''}} />
                                        <label for="a50" class="component">{{translate('home')}}</label>
                                    </li>
                                    <li class="address_type_li">
                                        <input type="radio" class="address_type" id="a75" name="addressAs" value="office" {{ $shippingAddress->address_type == 'office' ? 'checked' : ''}} />
                                        <label for="a75" class="component">{{translate('office')}}</label>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" id="is_billing" value="{{$shippingAddress->is_billing}}">
                                <ul class="donate-now d-flex gap-2">
                                    <li class="address_type_bl">
                                        <input type="radio" class="bill_type" id="b25" name="is_billing" value="0" {{ $shippingAddress->is_billing == '0' ? 'checked' : ''}} />
                                        <label for="b25" class="component">{{translate('shipping')}}</label>
                                    </li>
                                    <li class="address_type_bl">
                                        <input type="radio" class="bill_type" id="b50" name="is_billing" value="1" {{ $shippingAddress->is_billing == '1' ? 'checked' : ''}} />
                                        <label for="b50" class="component">{{translate('billing')}}</label>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12 @if(!$showCountryField) d-none @endif" id="country-wrapper" @if($singleCountryMode) data-single-country @endif>
                                <label for="country" @if(!$showCountryField) class="d-none" @endif>{{ translate('country') }}</label>
                                <input type="hidden" name="country" id="country_name" value="{{ $shippingAddress->country }}">

                                <select name="country_id" class="form-control selectpicker" data-live-search="true" id="country" required>
                                    @foreach($delivery_countries as $country)
                                    <option value="{{ $country['code'] }}" {{ ($country['name'] == $shippingAddress->country || ($singleCountryMode && ($deliveryRestriction['default_country_code'] ?? null) === $country['code'])) ? 'selected' : '' }}>
                                        {{ $country['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                               <div class="form-group col-md-6">
                                <label for="person_name">{{translate('contact_person_name')}}</label>
                                <input class="form-control" type="text" id="person_name"
                                    name="name"
                                    value="{{$shippingAddress->contact_person_name}}"
                                    required>
                                 </div>
                            <div class="form-group col-md-6">
                                <label for="own_phone">{{translate('phone')}}</label>
                                <input class="form-control phone-input-with-country-picker" type="text" id="own_phone" value="+{{$shippingAddress->phone}}" required="required">
                                <input type="hidden" class="country-picker-phone-number w-50" name="phone" value="{{ $shippingAddress->phone }}" readonly>
                            </div>
                            @if($showStateField)
                            <div class="form-group col-md-6">
                                <label for="state_id">{{ translate('state') }}</label>
                                <select name="state_id" class="form-control" id="state_id" required>
                                    <option value="">{{ translate('select_state') }}</option>
                                </select>
                                <input type="hidden" name="state" id="state_name" value="{{ $shippingAddress->state }}">
                            </div>
                            @endif

                            @if($showCityField)
                            <div class="form-group col-md-6">
                                <label for="city_id">{{ translate('city') }}</label>
                                <select name="city_id" class="form-control" id="city_id" required>
                                    <option value="">{{ translate('select_city') }}</option>
                                </select>
                                <input type="hidden" name="city" id="city_name" value="{{ $shippingAddress->city }}">
                            </div>
                            @endif

                            @if($showAreaField)
                            <div class="form-group col-md-6">
                                <label for="area">{{ translate('area') }}</label>
                                <select name="area_id" class="form-control" id="area" required>
                                    <option value="">{{ translate('select_area') }}</option>
                                </select>
                                <input type="hidden" name="area" id="area_name" value="{{ $shippingAddress->area }}">
                            </div>
                            @endif

                            @if($showZipField)
                            <div class="form-group col-md-6">
                                <label for="zip_code">{{translate('zip_code')}}</label>
                                @if($zip_restrict_status)
                                <select name="zip" class="form-control selectpicker" data-live-search="true" id="" >
                                    @foreach($delivery_zipcodes as $zip)
                                    <option value="{{ $zip->zipcode }}" {{ $zip->zipcode == $shippingAddress->zip? 'selected' : ''}}>{{ $zip->zipcode }}</option>
                                    @endforeach
                                </select>
                                @else
                                <input class="form-control" type="text" id="zip_code" name="zip" value="{{$shippingAddress->zip}}" >
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="form-row">
                            <div class=" col-md-12">
                                <div class="form-group mb-1">
                                    <label for="own_address">{{translate('address')}}</label>
                                    <textarea class="form-control" id="address"
                                        type="text" name="address" required>{{$shippingAddress->address}}</textarea>
                                    <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                        {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                    </span>
                                </div>
                            </div>
                            @if(getWebConfig('map_api_status') ==1 )
                            <div class="col-md-12">
                                <div class="form-group map-area-alert-border location-map-address-canvas-area">
                                    <input id="pac-input" class="controls rounded __inline-46 location-search-input-field" title="{{translate('search_your_location_here')}}" type="text" placeholder="{{translate('search_here')}}" />
                                    <div class="__h-200px" id="location_map_canvas"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @php($shipping_latitude=$shippingAddress->latitude)
                        @php($shipping_longitude=$shippingAddress->longitude)
                        <input type="hidden" id="latitude"
                            name="latitude" class="form-control d-inline"
                            placeholder="{{ translate('ex')}} : -94.22213" value="{{$shipping_latitude??0}}" required readonly>
                        <input type="hidden"
                            name="longitude" class="form-control"
                            placeholder="{{ translate('ex')}} : 103.344322" id="longitude" value="{{$shipping_longitude??0}}" required readonly>
                        <div class="modal-footer">
                            <a href="{{ route('account-address') }}" class="closeB btn btn-secondary fs-14 font-semi-bold py-2 px-4">{{translate('close')}}</a>
                            <button type="submit" class="btn btn--primary fs-14 font-semi-bold py-2 px-4">{{translate('update')}} </button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </div>
</div>
<span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
<span id="account-edit-delivery-restriction-setup"
      data-single-country-mode="{{ $singleCountryMode ? 1 : 0 }}"
      data-state-visible="{{ $showStateField ? 1 : 0 }}"
      data-city-visible="{{ $showCityField ? 1 : 0 }}"
      data-area-visible="{{ $showAreaField ? 1 : 0 }}"></span>
@endsection

@push('script')
<script>
    let getStatesURL = "{{ route('checkout.get.states') }}";
    let getCitiesURL = "{{ route('checkout.get.cities') }}";
    let getAreasURL = "{{ route('checkout.get.areas') }}";
</script>
<script>
    'use strict'
    const deliveryRestrictedCountries = @json($countriesName);
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
</script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/bootstrap-select.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@if(getWebConfig('map_api_status') ==1 )
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=callBackFunction&loading=async&libraries=places&v=3.56" defer>
    </script>
    <script>
        async function initAutocomplete() {
            var myLatLng = { lat: {{$shipping_latitude??'-33.8688'}}, lng: {{$shipping_longitude??'151.2195'}} };
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                center: { lat: {{$shipping_latitude??'-33.8688'}}, lng: {{$shipping_longitude??'151.2195'}} },
                zoom: 13,
                mapId: 'roadmap'
            });

            var marker = new AdvancedMarkerElement({
                map,
                position: myLatLng,
            });

            marker.setMap( map );
            var geocoder = geocoder = new google.maps.Geocoder();
            google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                var coordinates = JSON.parse(coordinates);
                var latlng = new google.maps.LatLng( coordinates['lat'], coordinates['lng'] ) ;
                marker.position={lat:coordinates['lat'], lng:coordinates['lng']};
                map.panTo( latlng );

                document.getElementById('latitude').value = coordinates['lat'];
                document.getElementById('longitude').value = coordinates['lng'];

                geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[1]) {
                            document.getElementById('address').value = results[1].formatted_address;

                            let systemCountryRestrictStatus = $('#system-country-restrict-status').data('value');
                            if (systemCountryRestrictStatus) {
                                const countryObject = findCountryObject(results[1].address_components);
                                deliveryRestrictedCountriesCheck(countryObject.long_name, '.location-map-address-canvas-area', '#address')
                            }
                        }
                    }
                });
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    var mrkr = new AdvancedMarkerElement({
                        map,
                        title: place.name,
                        position: place.geometry.location,
                    });

                    google.maps.event.addListener(mrkr, "click", function (event) {
                        document.getElementById('latitude').value = this.position.lat();
                        document.getElementById('longitude').value = this.position.lng();
                    });

                    markers.push(mrkr);

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }
        function callBackFunction(){
            initAutocomplete();
        }

        $(document).on("keydown", "input", function(e) {
            if (e.which==13) e.preventDefault();
        });
    </script>

    <script>
        $(document).ready(function () {
            const restrictionSetupElement = document.getElementById('account-edit-delivery-restriction-setup');
            const singleCountryMode = Number(restrictionSetupElement?.dataset?.singleCountryMode || 0) === 1;
            const stateVisible = Number(restrictionSetupElement?.dataset?.stateVisible || 0) === 1;
            const cityVisible = Number(restrictionSetupElement?.dataset?.cityVisible || 0) === 1;
            const areaVisible = Number(restrictionSetupElement?.dataset?.areaVisible || 0) === 1;
            let preSelectedState = $('#state_name').val();
            let preSelectedCity = $('#city_name').val();
            let preSelectedArea = "{{ $shippingAddress->area }}";

            function clearSelect(selectSelector, hiddenSelector, placeholder) {
                const $select = $(selectSelector);
                if ($select.length) {
                    $select.empty().append(`<option value="">${placeholder}</option>`);
                }
                $(hiddenSelector).val('');
            }

            $('#country').change(function () {
                let selectedCountryName = $(this).find('option:selected').text().trim();
                $('#country_name').val(selectedCountryName);

                if (!stateVisible) {
                    return;
                }

                const countryCode = $(this).val();
                clearSelect('#state_id', '#state_name', '{{ translate('select_state') }}');
                clearSelect('#city_id', '#city_name', '{{ translate('select_city') }}');
                clearSelect('#area', '#area_name', '{{ translate('select_area') }}');

                $.get(getStatesURL, { country: countryCode }, function (response) {
                    $.each(response.states ?? [], function (i, state) {
                        const selected = state.name.toLowerCase() === preSelectedState.toLowerCase() ? 'selected' : '';
                        $('#state_id').append(`<option value="${state.id}" data-name="${state.name}" ${selected}>${state.name}</option>`);
                    });

                    if (cityVisible) {
                        $('#state_id').trigger('change');
                    }
                });
            });

            $('#state_id').change(function () {
                const selected = $(this).find(':selected');
                $('#state_name').val(selected.data('name'));

                if (!cityVisible) {
                    return;
                }

                const stateId = $(this).val();
                clearSelect('#city_id', '#city_name', '{{ translate('select_city') }}');
                clearSelect('#area', '#area_name', '{{ translate('select_area') }}');

                $.get(getCitiesURL, { state_id: stateId }, function (response) {
                    $.each(response.cities ?? [], function (i, city) {
                        const selected = city.name.toLowerCase() === preSelectedCity.toLowerCase() ? 'selected' : '';
                        $('#city_id').append(`<option value="${city.id}" data-name="${city.name}" ${selected}>${city.name}</option>`);
                    });

                    if (areaVisible) {
                        $('#city_id').trigger('change');
                    }
                });
            });

            $('#city_id').change(function () {
                const selected = $(this).find(':selected');
                $('#city_name').val(selected.data('name'));

                if (!areaVisible) {
                    return;
                }

                const cityId = $(this).val();
                clearSelect('#area', '#area_name', '{{ translate('select_area') }}');

                $.get(getAreasURL, { city_id: cityId }, function (response) {
                    $.each(response.areas ?? [], function (i, area) {
                        const selected = area.name.toLowerCase() === preSelectedArea.toLowerCase() ? 'selected' : '';
                        $('#area').append(`<option value="${area.id}" data-name="${area.name}" ${selected}>${area.name}</option>`);
                    });
                });
            });

            $('#area').change(function () {
                const selected = $(this).find('option:selected');
                $('#area_name').val(selected.data('name') ?? selected.text());
            });

            if (singleCountryMode || $('#country').val()) {
                $('#country').trigger('change');
            }
        });

    </script>
@endif
@endpush
