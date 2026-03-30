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
        <div class="card-body py-5">
            <h6 class="text-end small font-bold fs-14">
                <a href="{{ route('warranty.lookup.start') }}">
                    <span class="text-primary"><i class="tio-refresh"></i></span>
                    {{ translate('clear') }}
                </a>
            </h6>
            <div class="mw-1000 mx-auto">
                <h3 class="text-center text-capitalize font-bold fs-25">{{ translate('Warranty Lookup') }}</h3>

                <form action="{{ route('warranty.lookup.submit') }}" method="POST" class="p-3">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger alert-block">
                            <span class="closet __closet" data-dismiss="alert">×</span>
                            <strong>{{ $errors->first() }}</strong>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group">
                                <input type="text" id="warrantyLookupSerialNumber" name="serial_number" class="form-control form-control-sm prepended-form-control"
                                       placeholder="{{ translate('Enter Serial Number') }}" required>
                                <div class="input-group-append">
                                    @include('partials.serial-scan-button', ['targetInput' => '#warrantyLookupSerialNumber'])
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <input type="text" name="contact" class="form-control form-control-sm prepended-form-control"
                                   placeholder="{{ translate('Email or Phone Number') }} - {{ translate('Example: 01553883833') }}" required>
                            <span class="fs-12 text-muted">* {{ translate('Enter the phone number without country code, for example 01553883833. International format is also accepted.') }}</span>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn--primary btn-sm w-100 font-bold">
                                {{ translate('Send OTP') }}
                            </button>
                        </div>
                    </div>

                    {{-- CAPTCHA --}}
                    <div class="mt-4">
                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha_element" class="w-100" data-type="image"></div>
                        @else
                            <div class="row mb-3 mt-1">
                                <div class="col-6 pe-0">
                                    <input type="text" class="form-control" name="default_captcha_value"
                                           placeholder="{{ translate('Enter Captcha Value') }}" autocomplete="off">
                                </div>
                                <div class="col-6 input-icons rounded">
                                    <a href="javascript:" class="get-contact-recaptcha-verify"
                                       data-link="{{ URL('/contact/code/captcha') }}">
                                        <img src="{{ URL('/contact/code/captcha/1') }}" class="input-field __h-44 rounded"
                                             id="default_recaptcha_id" alt="">
                                        <i class="tio-refresh icon"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-md-5 mx-auto text-center max-width-350px">
                        <img class="mb-2" src="{{ theme_asset(path: 'public/assets/front-end/img/track-truck.svg') }}" alt="Warranty Lookup">
                        <div class="opacity-50">
                            {{ translate('Enter your Serial Number and Contact to receive an OTP and verify your warranty details.') }}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.serial-scanner-assets')
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
