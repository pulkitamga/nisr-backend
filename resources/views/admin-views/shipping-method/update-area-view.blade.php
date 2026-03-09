@extends('layouts.back-end.app')

@section('title', translate('are_shipping_method'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" alt="">
            {{translate('area_shipping_method_update')}}
        </h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.business-settings.shipping-method.update-area',[$method['id']])}}"
                          class="text-start"
                          method="post">
                        @csrf
                        <div class="row ">
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <label class="title-color" for="country">{{translate('Country')}}</label>
                                            <select id="country" name="country" class="form-control js-select2-custom">
                                            @foreach(COUNTRIES as $country)
                                                <option value="{{$country['code']}}" {{ $method['country']?($method['country']==$country['code']?'selected':''):'' }} >
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
                                            <select id="state" name="state" class="form-control js-select2-custom" data-state-id="{{ $method->state_id }}">
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
                                            <select id="city" name="city" class="form-control js-select2-custom" data-city-id="{{ $method->city_id }}">
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
                                            <label class="title-color" for="area">{{translate('area')}}</label>
                                            <input type="text" name="area" value="{{$method['area']}}" class="form-control" placeholder="{{translate('area')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <label class="title-color" for="duration">{{translate('duration')}}</label>
                                            <input type="text" name="duration" value="{{$method['duration']}}"
                                                   class="form-control"
                                                   placeholder="{{translate('ex').' '.':'.' '.translate('4_to_6_days')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <label class="title-color" for="cost">{{translate('cost')}}</label>
                                            <input type="number" min="0" max="1000000" name="cost"
                                                value="{{usdToDefaultCurrency(amount: $method['cost'])}}"
                                                class="form-control"
                                                placeholder="{{translate('ex').' '.':'.' '.setCurrencySymbol(amount: usdToDefaultCurrency(amount: 10), currencyCode: getCurrencyCode())}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3 d-none">
                            <label class="input-label"
                                for="exampleFormControlInput1">{{ translate('Coordinates') }}<span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{translate('messages.draw_your_zone_on_the_map')}}">{{translate('messages.draw_your_zone_on_the_map')}}</span></label>
                                <textarea type="text" rows="8" name="coordinates"  id="coordinates" class="form-control" readonly>{{ $method->coordinates ?? '' }}</textarea>
                        </div>
                        <div id="map" style="width: 100%; height: 400px;"></div>
                        <input id="searchInput" type="text" placeholder="Search here" style="margin-top: 10px; width: 100%; padding: 10px;">
                        <div class="d-flex gap-10 mt-10 flex-wrap justify-content-end">
                            <button type="submit" class="btn btn--primary px-4">{{translate('update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <span id="route-get-country-state" data-url="{{route('admin.business-settings.shipping-method.getStates')}}"></span>
    <span id="route-get-state-cities" data-url="{{route('admin.business-settings.shipping-method.getCities')}}"></span>
    @php($default_location = getWebConfig(name: 'default_location'))
@endsection


@push('script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=initAutocomplete&loading=async&libraries=drawing,places&v=3.56"></script>
    <script>
        "use strict";
        function initAutocomplete() {
            const defaultLocation = {
                lat: {{ $default_location['lat'] ?? 0 }}, // Fallback to 0 if not defined
                lng: {{ $default_location['lng'] ?? 0 }}  // Fallback to 0 if not defined
            };
            // console.log(defaultLocation);

            const map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLocation,
                zoom: 12,
            });

            const input = document.getElementById("searchInput");
            const autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo("bounds", map);

            const markers = [];  // Array to store markers

             // Predefined coordinates (from the hidden input)
            const coordinatesInput = document.getElementById("coordinates").value;
            if (coordinatesInput) {
                try {
                    const parsedCoordinates = JSON.parse(coordinatesInput);
                    if (Array.isArray(parsedCoordinates) && parsedCoordinates.length > 0) {
                        const polygon = new google.maps.Polygon({
                            paths: parsedCoordinates,
                            strokeColor: "#000000",
                            strokeOpacity: 0.8,
                            strokeWeight: 1,
                            fillColor: "#000000",
                            fillOpacity: 0.35,
                        });
                        polygon.setMap(map);

                        // Adjust the map to fit the polygon bounds
                        const bounds = new google.maps.LatLngBounds();
                        parsedCoordinates.forEach((coord) => {
                            bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
                        });
                        map.fitBounds(bounds);
                    }
                } catch (error) {
                    console.error("Invalid coordinates format:", error);
                }
            }

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
                map.setZoom(13);
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
                }
            });
        }
    </script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/shipping-method.js')}}"></script>
@endpush
