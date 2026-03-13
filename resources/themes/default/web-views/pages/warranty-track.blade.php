@extends('layouts.front-end.app')
@section('title', translate('warranty_Lookup'))

@push('css_or_js')
<meta property="og:image" content="{{ $web_config['web_logo']['path'] }}" />
<meta property="og:title" content="{{ $web_config['company_name'] }}" />
<meta property="og:url" content="{{ env('APP_URL') }}">
<meta property="og:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}" />
<meta property="twitter:title" content="{{ $web_config['company_name'] }}" />
<meta property="twitter:url" content="{{ env('APP_URL') }}">
<meta property="twitter:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<link rel="stylesheet" media="screen" href="{{ theme_asset(path: 'public/assets/front-end/vendor/nouislider/distribute/nouislider.min.css') }}" />
@endpush

@section('content')
<div class="container rtl pt-4 pb-5 text-align-direction tracking-page">
    <div class="card border-0 box-shadow-lg">
        <div class="card-header py-5">

            <div class="card-body py-5">

                <div class="mw-1000 mx-auto">
                    <div class="d-flex flex-column flex-lg-row flex-md-row gap-4 justify-content-around">
                        <a href="{{ route('warranty.activate') }}" class="btn btn--primary btn-sm">
                            {{ translate('Warranty Activate') }}
                        </a>

                        <a href="{{ route('warranty.lookup.start') }}" class="btn btn--primary btn-sm">
                            {{ translate('Warranty Lookup') }}
                        </a>

                        <a href="{{ route('warranty-policy') }}" class="btn btn--primary btn-sm">
                            {{ translate('Warranty Policy') }}
                        </a>
                    </div>


                    <div class="mt-5 pt-md-5 mx-auto text-center max-width-350px">
                        <img class="mb-2" src="{{ theme_asset(path: 'public/assets/front-end/img/track-truck.svg') }}" alt="Warranty Lookup">
                        <div class="opacity-50">
                            {{ translate('Enter your Serial Number and Contact to receive an OTP and verify your warranty details.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    @endpush