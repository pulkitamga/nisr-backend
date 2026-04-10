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
    @include('web-views.partials._premium-page-styles')
@endpush

@section('content')
    <div class="nisr-page-shell">
        <section class="container rtl text-align-direction">
            <div class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('Warranty Lookup') }}</span>
                <h1 class="nisr-page-title">{{ translate('Find_your_warranty_record') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Enter_your_serial_number_and_the_contact_used_during_activation_to_view_warranty_status') }}
                </p>
                <div class="nisr-hero-actions">
                    <span class="nisr-stat-pill">{{ translate('Use_the_same_phone_or_email_linked_to_the_activation_record') }}</span>
                    <a href="{{ route('warranty.activate') }}" class="nisr-link-pill">{{ translate('Need_to_activate_a_new_warranty') }}</a>
                </div>
            </div>
        </section>

        <section class="container pb-5 rtl text-align-direction">
            <div class="nisr-page-grid">
                <div class="nisr-surface">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('Warranty Lookup') }}</h2>
                        <p class="nisr-section-copy mb-0">
                            {{ translate('Enter_your_serial_number_and_the_contact_used_during_activation_to_view_warranty_status') }}
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="nisr-alert mb-4">
                            <strong>{{ $errors->first() }}</strong>
                        </div>
                    @endif

                    <form id="getResponse" action="{{ route('warranty.lookup.submit') }}" method="POST" class="d-grid gap-4">
                        @csrf

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="warrantyLookupSerialNumber">{{ translate('Serial Number') }}</label>
                                <div class="input-group">
                                    <input type="text"
                                           id="warrantyLookupSerialNumber"
                                           name="serial_number"
                                           class="form-control form-control-sm prepended-form-control"
                                           value="{{ old('serial_number') }}"
                                           placeholder="{{ translate('Enter Serial Number') }}"
                                           required>
                                    <div class="input-group-append">
                                        @include('partials.serial-scan-button', ['targetInput' => '#warrantyLookupSerialNumber'])
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label for="warrantyLookupContact">{{ translate('Email or Phone Number') }}</label>
                                <input type="text"
                                       id="warrantyLookupContact"
                                       name="contact"
                                       class="form-control form-control-sm prepended-form-control"
                                       value="{{ old('contact') }}"
                                       placeholder="{{ translate('Email or Phone Number') }} - {{ translate('Example: 01553883833') }}"
                                       required>
                                <small class="nisr-field-note d-block mt-2">
                                    * {{ translate('Enter the phone number without country code, for example 01553883833. International format is also accepted.') }}
                                </small>
                            </div>
                        </div>

                        <div>
                            @php
                                $recaptcha = getWebConfig(name: 'recaptcha');
                            @endphp
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div id="recaptcha_element" class="w-100" data-type="image"></div>
                            @else
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <label for="defaultCaptchaValue">{{ translate('Enter Captcha Value') }}</label>
                                        <input type="text"
                                               id="defaultCaptchaValue"
                                               class="form-control"
                                               name="default_captcha_value"
                                               placeholder="{{ translate('Enter Captcha Value') }}"
                                               autocomplete="off">
                                    </div>
                                    <div class="col-md-6">
                                        <a href="javascript:" class="get-contact-recaptcha-verify d-inline-flex"
                                           data-link="{{ URL('/contact/code/captcha') }}">
                                            <img src="{{ URL('/contact/code/captcha/1') }}"
                                                 class="input-field __h-44 rounded"
                                                 id="default_recaptcha_id"
                                                 alt="{{ translate('captcha') }}">
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn--primary nisr-submit font-bold">
                            {{ translate('Send OTP') }}
                        </button>
                    </form>
                </div>

                <aside class="nisr-surface nisr-surface--soft">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('How_it_works') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('We_are_here_to_help') }}</p>
                    </div>

                    <div class="nisr-mini-card mb-3">
                        <strong>{{ translate('Step_1') }}</strong>
                        <p>{{ translate('Use_the_same_phone_or_email_linked_to_the_activation_record') }}</p>
                    </div>
                    <div class="nisr-mini-card mb-3">
                        <strong>{{ translate('Step_2') }}</strong>
                        <p>{{ translate('If_OTP_verification_is_enabled_we_will_send_a_code_before_showing_the_record') }}</p>
                    </div>
                    <div class="nisr-mini-card">
                        <strong>{{ translate('Step_3') }}</strong>
                        <p>{{ translate('Need_to_activate_a_new_warranty') }}</p>
                    </div>

                    <div class="nisr-inline-actions">
                        <a href="{{ route('warranty.activate') }}" class="nisr-link-pill">{{ translate('Activate Warranty') }}</a>
                    </div>
                </aside>
            </div>
        </section>
    </div>

    @include('partials.serial-scanner-assets')
@endsection

@push('script')
    @php
        $recaptcha = getWebConfig(name: 'recaptcha');
    @endphp
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
