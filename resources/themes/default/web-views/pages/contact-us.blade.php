@extends('layouts.front-end.app')

@section('title',translate('contact_us'))

@push('css_or_js')

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<link rel="stylesheet"
    href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">

<style>
    #map {
        height: 600px;
        width: 100%;
    }

    #search-box {
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')


<section>
    <div class="container">

        <div class="rounded-10 my-4 text-center d-sm-block position-relative blog-banner-container">
            <div class="text--primary w-100 position-absolute">
                <img class="blog-banner-svg svg" src="{{ theme_asset(path: 'public/assets/front-end/img/blogs/background.svg') }}" alt="">
            </div>
            <div class="py-5 px-3">
                <h1 class="mb-2 fw-semibold h2">
                    {{translate('Contact_us') }}
                </h1>
                <p class="fs-20 mb-0">
                    {{ translate('Contact_us') }}
                </p>
            </div>
        </div>
        <!-- <div class="d-block d-sm-none">
            <h2 class="fs-16 fw-semibold my-3 text-center">
                {{ translate('Contact_us') }}
            </h2>
             <p class="fs-20 mb-0">
                {{ translate('Contact_us') }}
                        </p>
        </div> -->
    </div>
</section>

<div class="__inline-58 py-lg-5 ">
    <style>
        .contact-card {
            border: 1px solid #e0e0e0;
            border-radius: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .contact-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 6px 20px rgba(18, 157, 145, 0.25);
            border-color: #129d91;
        }

        .contact-icon {
            width: 3.5rem;
            height: 3.5rem;
        }

        .contact-link:hover {
            text-decoration: underline;
            color: #129d91 !important;
        }
    </style>

    <section class="py-lg-5 mb-5" style="background-color: #f9fafb;">
        <div class="container px-3">
            <h1 class="text-center fw-bold mb-4 mobile-head" style="font-size: 2rem;">
                {{ translate('contact_us') }}
            </h1>

            <div class="row justify-content-center gy-4 gx-4">

                <!-- Phone -->
                <div class="col-12 col-sm-6 col-md  -4 d-flex justify-content-center mb-3 mb-md-0">
                    <div class="contact-card text-center p-4 py-5" style="max-width: 25rem; width: 100%;">
                        <img src="https://cdn-icons-png.flaticon.com/512/724/724664.png" alt="Phone"
                            class="contact-icon mb-3">
                        <h3 class="fw-semibold mb-2" style="font-size: 1.25rem;">{{ translate('Call Us') }}</h3>
                        <a href="tel:{{ getWebConfig(name: 'company_phone') }}"
                            class="text-decoration-none contact-link" style="font-size: 1rem;">
                            <i class="fa fa-phone me-2"></i>{{ getWebConfig(name: 'company_phone') }}
                        </a>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-12 col-sm-6 col-md  -4 d-flex justify-content-center mb-3 mb-md-0">
                    <div class="contact-card text-center p-4 py-5" style="max-width: 25rem; width: 100%;">
                        <img src="https://cdn-icons-png.flaticon.com/512/561/561188.png" alt="Email"
                            class="contact-icon mb-3">
                        <h3 class="fw-semibold mb-2" style="font-size: 1.25rem;">{{ translate('Email Us') }}</h3>
                        <a href="mailto:{{ getWebConfig(name: 'company_email') }}"
                            class="text-decoration-none contact-link" style="font-size: 1rem;">
                            <i class="fa fa-envelope me-2"></i>{{ getWebConfig(name: 'company_email') }}
                        </a>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-12 col-sm-6 col-md  -4 d-flex justify-content-center mb-3 mb-md-0">
                    <div class="contact-card text-center p-4 py-5" style="max-width: 25rem; width: 100%;">
                        <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png" alt="Address"
                            class="contact-icon mb-3">
                        <h3 class="fw-semibold mb-2" style="font-size: 1.25rem;">{{ translate('address') }}</h3>
                        <p class=" mb-0" style="font-size: 1rem;">
                            <i class="fa fa-map-marker me-2"></i>{{ getWebConfig(name: 'shop_address') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-0 mb-md-5 mobile-head">{{ translate('follow_us') }}</h2>

            @if(!empty($web_config['social_media']))
            <div class="row justify-content-center g-4">
                @foreach ($web_config['social_media'] as $item)
                @php
                $defaultIcons = [
                'facebook' => 'https://cdn-icons-png.flaticon.com/512/733/733547.png',
                'instagram' => 'https://cdn-icons-png.flaticon.com/512/174/174855.png',
                'twitter' => 'https://cdn-icons-png.flaticon.com/512/733/733579.png',
                'linkedin' => 'https://cdn-icons-png.flaticon.com/512/145/145807.png',
                'pinterest' => 'https://cdn-icons-png.flaticon.com/512/2111/2111498.png',
                'youtube' => 'https://cdn-icons-png.flaticon.com/512/1384/1384060.png',
                ];
                $icon = $defaultIcons[$item->name] ?? 'https://cdn-icons-png.flaticon.com/512/25/25694.png';
                @endphp

                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card text-center h-100 p-4 shadow border-0">
                        <div class="card-body">
                            <img src="{{ $icon }}" class="mb-4" width="60" height="60" alt="{{ ucfirst($item->name) }}">
                            <h5 class="card-title mb-3 fs-5">{{ ucfirst($item->name) }}</h5>
                            <a href="{{ $item->link }}" target="_blank" class="text-muted d-block small">
                                {{ '@' . parse_url($item->link, PHP_URL_HOST) }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>




    <div class="container rtl text-align-direction">
        <div class="row no-gutters py-md-5 pt-5 ">
            <div class="col-lg-6 iframe-full-height-wrap ">
                <img class="for-contact-image" src="{{theme_asset(path: "public/assets/front-end/png/contact.png")}}"
                    alt="">
            </div>
            <div class="col-lg-6">
                <div class="card px-md-5">
                    <div class="card-body for-send-message">
                        <h2 class="h4 mb-4 text-center font-semibold text-black">{{translate('send_us_a_message')}}</h2>
                        <form action="{{route('contact.store')}}" method="POST" id="getResponse">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{translate('your_name')}}</label>
                                        <input class="form-control name" name="name" type="text"
                                            value="{{ old('name') }}" placeholder="{{ translate('John_Doe') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-email">{{translate('email_address')}}</label>
                                        <input class="form-control email" name="email" type="email"
                                            value="{{ old('email') }}"
                                            placeholder="{{ translate('enter_email_address') }}" required>

                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-phone">{{translate('your_phone')}}</label>
                                        <input class="form-control mobile_number phone-input-with-country-picker"
                                            type="number" value="{{ old('mobile_number') }}"
                                            placeholder="{{translate('contact_number')}}" required>

                                        <div class="">
                                            <input type="hidden" class="country-picker-country-code w-50"
                                                name="country_code" readonly>
                                            <input type="hidden" class="country-picker-phone-number w-50"
                                                name="mobile_number" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-subject">{{translate('subject')}}:</label>
                                        <input class="form-control subject" type="text" name="subject"
                                            value="{{ old('subject') }}" placeholder="{{translate('short_title')}}"
                                            required>

                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cf-message">{{translate('message')}}</label>
                                        <textarea class="form-control message" name="message" rows="2"
                                            required>{{ old('subject') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            @php($recaptcha = getWebConfig(name: 'recaptcha'))
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha_element" class="w-100" data-type="image"></div>
                            <br />
                            @else
                            <div class="row mb-3 mt-1">
                                <div class="col-6 pr-0">
                                    <input type="text" class="form-control" name="default_captcha_value" value=""
                                        placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                                </div>
                                <div class="col-6 input-icons rounded">
                                    <a href="javascript:" class="get-contact-recaptcha-verify"
                                        data-link="{{ URL('/contact/code/captcha') }}">
                                        <img src="{{ URL('/contact/code/captcha/1') }}"
                                            class="input-field __h-44 rounded" id="default_recaptcha_id" alt="">
                                        <i class="tio-refresh icon"></i>
                                    </a>
                                </div>
                            </div>
                            @endif
                            <div class="d-flex justify-content-end mt-lg-5 ">
                                <button class="btn btn--primary btn-block" type="submit">{{translate('send')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="mt-4 mt-md-0">
    <h1 class="text-3xl font-bold text-center mb-8 mobile-head">{{ translate('Our_branches') }}</h1>

    <div class="container">
        <div class="card my-4 shadow-sm mb-5">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="text-capitalize text-nowrap mb-0">
                    {{ translate('Branches') }} <span class="badge badge-soft-dark radius-50 fz-12 px-1">({{ $branchesTable->total() }})</span>
                </h5>
                <form method="GET" action="{{ url()->current() }}" class="d-flex">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control form-control-sm me-2 input-group-text input-search-table"
                        placeholder="  {{translate('Search branch...')}}"
                        aria-label="Search branch">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i> {{translate('Search')}}
                </form>
            </div>


            <div class="card-body table-responsive table-mobile-responsive p-0">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-align-middle w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th class="text-nowrap">{{translate('SL')}}</th>
                            <th>{{translate('Branch')}}</th>
                            <th>{{translate('Phone')}}</th>
                            <th>{{translate('Address')}}</th>
                            <th>{{translate('Location')}}</th>
                            <th class="text-nowrap">{{translate('Status')}}</th>
                            <th>{{translate('Direction')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branchesTable as $index => $branch)

                        <tr>
                            <td>{{ $branchesTable->firstItem() + $index }}</td>
                            <td class="text-nowrap">{{ getTranslatedValue($branch, 'branch_name', $branch->branch_name) }}</td>
                            <td class="text-nowrap">{{ $branch->phone }}</td>
                            <td>{{getTranslatedValue($branch, 'branch_address', $branch->branch_address) }}</td>
                            <td class="text-nowrap">{{ $branch->branch_state ?? '' }}</td>
                            <td>
                                @php($open = $branch->isOpenNow())
                                <span class="text-white p-2 badge bg-{{ $open ? 'success' : 'danger' }}">
                                    {{ translate($open ? 'Open' : 'Closed') }}
                                </span>
                            </td>

                            <td class="">
                                <div class="d-flex justify-content-center">
                                    <a href="https://www.google.com/maps?q={{ $branch->branch_latitude ?? 0 }},{{ $branch->branch_longitude ?? 0 }}" target="_blank" class="btn btn-sm btn-outline-primary direction-btn" title="Get Direction">
                                        <svg width="18" height="18" style="margin-right: 4px;" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M256 0C167.6 0 96 71.6 96 160c0 114.9 139.8 266.7 145.2 272.7 6 6.5 15.6 6.5 21.6 0C276.2 426.7 416 274.9 416 160 416 71.6 344.4 0 256 0zm0 240c-44.2 0-80-35.8-80-80s35.8-80 80-80 80 35.8 80 80-35.8 80-80 80z" />
                                        </svg> {{translate('Direction')}}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center"> {{translate('No branches found.')}}</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="card-footer d-flex justify-content-center">
                {{ $branchesTable->withQueryString()->links() }}
            </div>
        </div>
    </div>
</section>

<section>


    <div class="container mb-4 mb-md-5">
        <div id="map"></div>
    </div>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=initMap&libraries=places"
        async defer>
    </script>
    <script>
        let map;
        let markers = [];
        let infoWindows = [];

        function initMap() {
            var centerCoordinates = {
                lat: 26.774645719165914,
                lng: 29.311165295285434
            };
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: centerCoordinates
            });

            var branches = @json($branches);

            branches.forEach(function(branch) {
                let position = {
                    lat: parseFloat(branch.branch_latitude),
                    lng: parseFloat(branch.branch_longitude)
                };

                // Marker icon color based on branch status
                let iconUrl = branch.status == 'active' ?
                    "http://maps.google.com/mapfiles/ms/icons/green-dot.png" :
                    "http://maps.google.com/mapfiles/ms/icons/red-dot.png";

                let marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: branch.branch_name,
                    icon: {
                        url: iconUrl
                    }
                });

                let infoWindow = new google.maps.InfoWindow({
                    content: `
                       <div class="card mb-4" style="max-width: 350px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <div class="card-body" style="background-color: #f8f9fa;">
                                <h5 class="text-center mb-4 text-[21px] text-capitalize text-primary font-weight-bold">${branch.branch_name}</h5>
                                <p><strong class="text-dark mb-2">Phone:</strong> <span class="text-muted">${branch.phone}</span></p>
                                <p><strong class="text-dark mb-2">Email:</strong> <span class="text-muted">${branch.email}</span></p>
                                <p><strong class="text-dark mb-2">Address:</strong> <span class="text-muted">${branch.branch_address}</span></p>
                            

                        
                                <h6 class="mt-4 text-primary font-weight-bold">Branch Timings:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Sunday:</strong> <span class="text-muted">${branch.sun_branch_hours_from && branch.sun_branch_hours_to ? branch.sun_branch_hours_from + ' - ' + branch.sun_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Monday:</strong> <span class="text-muted">${branch.mon_branch_hours_from && branch.mon_branch_hours_to ? branch.mon_branch_hours_from + ' - ' + branch.mon_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Tuesday:</strong> <span class="text-muted">${branch.tue_branch_hours_from && branch.tue_branch_hours_to ? branch.tue_branch_hours_from + ' - ' + branch.tue_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Wednesday:</strong> <span class="text-muted">${branch.wed_branch_hours_from && branch.wed_branch_hours_to ? branch.wed_branch_hours_from + ' - ' + branch.wed_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Thursday:</strong> <span class="text-muted">${branch.thu_branch_hours_from && branch.thu_branch_hours_to ? branch.thu_branch_hours_from + ' - ' + branch.thu_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Friday:</strong> <span class="text-muted">${branch.fri_branch_hours_from && branch.fri_branch_hours_to ? branch.fri_branch_hours_from + ' - ' + branch.fri_branch_hours_to : 'Closed'}</span></li>
                                    <li><strong>Saturday:</strong> <span class="text-muted">${branch.sat_branch_hours_from && branch.sat_branch_hours_to ? branch.sat_branch_hours_from + ' - ' + branch.sat_branch_hours_to : 'Closed'}</span></li>
                                </ul>
                                <div class="d-flex justify-content-center mt-3">
                                                    <a href="https://www.google.com/maps/dir/?api=1&destination=${position.lat},${position.lng}" target="_blank" class="btn btn--primary btn-sm">
                                                        Get Direction
                                                    </a>
                                                    </div>

                            </div>
                        </div>


                    `
                });

                marker.addListener('click', function() {
                    // Close all open InfoWindows first
                    infoWindows.forEach(function(iw) {
                        iw.close();
                    });
                    infoWindow.open(map, marker);
                });

                markers.push({
                    marker: marker,
                    branch: branch
                });
                infoWindows.push(infoWindow);
            });

            // Cluster markers
            const markerCluster = new markerClusterer.MarkerClusterer({
                map,
                markers: markers.map(m => m.marker)
            });

            // Search feature
            document.getElementById('branch-search').addEventListener('input', function(e) {
                var searchText = e.target.value.toLowerCase();
                let visibleBranches = [];

                markers.forEach(function(m) {
                    const isVisible = m.branch.branch_name.toLowerCase().includes(searchText);
                    m.marker.setVisible(isVisible);
                    if (isVisible) visibleBranches.push(m.branch);
                });

                updateBranchList(visibleBranches);
            });

        }
    </script>

    <script>
        function updateBranchList(filteredBranches) {
            const container = document.getElementById('branch-cards');
            container.innerHTML = ''; // clear old

            filteredBranches.forEach(branch => {
                const html = `
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">${branch.branch_name}</h5>
                        <p><strong>Phone:</strong> ${branch.phone}</p>
                        <p><strong>Email:</strong> ${branch.email}</p>
                        <p><strong>Address:</strong> ${branch.branch_address}</p>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${branch.branch_latitude},${branch.branch_longitude}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Get Direction
                        </a>
                    </div>
                </div>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', html);
            });
        }
    </script>

</section>
@endsection


@push('script')


@php($recaptcha = getWebConfig(name: 'recaptcha'))
@if(isset($recaptcha) && $recaptcha['status'] == 1 && !empty($recaptcha['site_key']))
<script type="text/javascript">
    "use strict";
    var onloadCallback = function() {
        grecaptcha.render('recaptcha_element', {'sitekey': '{{ $recaptcha['site_key'] }}'});
    };
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<script>
    "use strict";
    $("#getResponse").on('submit', function(e) {
        var response = grecaptcha.getResponse();
        if (response.length === 0) {
            e.preventDefault();
            toastr.error($('#message-please-check-recaptcha').data('text'));
        }
    });
</script>
@endif


<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@endpush