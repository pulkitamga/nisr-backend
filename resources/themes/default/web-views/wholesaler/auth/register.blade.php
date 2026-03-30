@extends('layouts.front-end.app')

@section('title', translate('register'))

@push('css_or_js')
<link rel="stylesheet"
    href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush


@section('content')
<form action="{{ route('customer.auth.with-us')}}" id="wholesale-register-form" method="post">
    @csrf
    <div class="py-5">
        <div class="first-el">
            <section>
                <div class="container">
                    <div class="create-an-account p-3 p-sm-4">
                        <img src="{{theme_asset('assets/img/media/form-bg.png')}}" alt=""
                            class="create-an-accout-bg-img">
                        <div class="row">
                            @include('web-views.wholesaler.auth.partial.header')
                            <div class="col-lg-8">
                                <div class="bg-white p-3 p-sm-4 rounded">
                                    <h4 class="mb-4 text text-capitalize">{{translate('create_an_account')}}</h4>
                                    <div class="row">

                                        <input type="hidden" name="is_wholesaler" value="on">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('first_name')}}
                                                    <span class="input-required-icon">*</span>
                                                </label>
                                                <input class="form-control text-align-direction"
                                                    value="{{ old('f_name')}}" type="text" name="f_name"
                                                    placeholder="{{ translate('Ex') }}: {{ translate('Jhone') }}"
                                                    required>
                                                <div class="invalid-feedback">{{
                                                    translate('please_enter_your_first_name')}}!</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('last_name') }}
                                                    <span class="input-required-icon">*</span>
                                                </label>
                                                <input class="form-control text-align-direction" type="text"
                                                    value="{{old('l_name') }}" name="l_name"
                                                    placeholder="{{ translate('ex') }}: {{ translate('Doe') }}"
                                                    required>
                                                <div class="invalid-feedback">{{
                                                    translate('please_enter_your_last_name') }}!</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('email_address') }}
                                                    <span class="input-required-icon">*</span>
                                                </label>
                                                <input class="form-control text-align-direction" type="email"
                                                    value="{{old('email') }}" name="email"
                                                    placeholder="{{ translate('enter_email_address') }}"
                                                    autocomplete="off" required>
                                                <div class="invalid-feedback">{{
                                                    translate('please_enter_valid_email_address') }}!</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('phone_number') }}
                                                    <span class="input-required-icon">*</span>
                                                </label>
                                                <input
                                                    class="form-control text-align-direction phone-input-with-country-picker"
                                                    type="tel" value="{{ old('phone') }}"
                                                    placeholder="{{ translate('enter_phone_number') }}" required>

                                                <input type="hidden" class="country-picker-phone-number w-50"
                                                    name="phone" readonly>

                                            </div>
                                        </div>
                                      <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('password') }} *
                                                </label>
                                                <div class="password-toggle rtl">
                                                    <input class="form-control text-align-direction" 
                                                        name="password"
                                                        type="password" 
                                                        id="password" 
                                                        placeholder="{{ translate('minimum_8_characters_long') }}" 
                                                        required>

                                                    <label class="password-toggle-btn">
                                                        <input class="custom-control-input" type="checkbox" class="toggle-pass" data-target="#password">
                                                        <i class="tio-hidden password-toggle-indicator"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">
                                                    {{ translate('confirm_password') }} *
                                                </label>
                                                <div class="password-toggle rtl">
                                                    <input class="form-control text-align-direction"
                                                        name="con_password"
                                                        type="password" 
                                                        id="confirm_password"
                                                        placeholder="{{ translate('minimum_8_characters_long') }}" 
                                                        required>

                                                    <label class="password-toggle-btn">
                                                        <input class="custom-control-input" type="checkbox" class="toggle-pass" data-target="#confirm_password">
                                                        <i class="tio-hidden password-toggle-indicator"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($web_config['ref_earning_status'])
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label font-semibold">{{ translate('refer_code') }}
                                                    <small class="text-muted">({{ translate('optional')
                                                        }})</small></label>
                                                <input type="text" id="referral_code" class="form-control"
                                                    name="referral_code"
                                                    placeholder="{{ translate('use_referral_code') }}">
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-sm-6">
                                             @php($recaptcha = getWebConfig(name: 'recaptcha'))
                                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                            <div id="recaptcha_element" class="w-100" data-type="image"></div>
                                            @else
                                            <div class="form-group">
                                                <label class="form-label font-semibold">{{ translate('captcha') }}
                                                    <span class="input-required-icon">*</span></label>
                                                <div class="row">
                                                    <div class="col-6 pe-2">
                                                        <input type="text" class="form-control"
                                                            name="default_recaptcha_value_customer_regi" value=""
                                                            id="customer-register-recaptcha-input"
                                                            placeholder="{{ translate('enter_captcha_value') }}"
                                                            autocomplete="off">
                                                    </div>
                                                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                                                        <a href="javascript:"
                                                            class="d-flex align-items-center align-items-center get-regi-recaptcha-verify get-session-recaptcha-auto-fill"
                                                            data-link="{{ URL('/customer/auth/code/captcha') }}"
                                                            data-session="{{ 'default_recaptcha_id_customer_regi' }}"
                                                            data-input="#customer-register-recaptcha-input">
                                                            <img alt=""
                                                                src="{{ URL('/customer/auth/code/captcha/1?captcha_session_id=default_recaptcha_id_customer_regi') }}"
                                                                class="input-field rounded __h-40"
                                                                id="default_recaptcha_id">
                                                            <i class="tio-refresh icon cursor-pointer p-2"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mx-auto mt-4 __max-w-356">
                                                <button class="w-100 btn btn--primary" id="sign-up" type="submit">
                                                    {{ translate('sign_up') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @if($sellWithUsIsActive)
            @include('web-views.wholesaler.auth.partial.why-with-us')
            @endif

            @if($businessProcessIsActive)
            @include('web-views.wholesaler.auth.partial.business-process')
            @endif
            @if($downloadVendorAppIsActive)
            @include('web-views.wholesaler.auth.partial.download-app')
            @endif
            @if($helpTopics->count() > 0)
            @include('web-views.wholesaler.auth.partial.faq')
            @endif

        </div>
    </div>
</form>


<div class="modal fade registration-success-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="{{ translate('Close') }}"><i
                        class="tio-clear"></i></button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                    <img src="{{theme_asset(path: 'public/assets/front-end/img/congratulations.png')}}" width="70"
                        class="mb-3 mb-20" alt="">
                    <h5 class="modal-title">{{translate('congratulations')}}</h5>
                    <div class="text-center">{{translate('your_registration_is_successful').',
                        '.translate('please-wait_for_admin_approval').'.'.translate('you_will_get_a_mail_soon')}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<span id="get-confirm-and-cancel-button-text" data-sure="{{translate('are_you_sure').'?'}}"
    data-message="{{translate('want_to_apply_as_a_vendor').'?'}}" data-confirm="{{translate('yes')}}"
    data-cancel="{{translate('no')}}"></span>
<span id="proceed-to-next-validation-message" data-mail-error="{{translate('please_enter_your_email').'.'}}"
    data-phone-error="{{translate('please_enter_your_phone_number').'.'}}"
    data-valid-mail="{{translate('please_enter_a_valid_email_address').'.'}}"
    data-enter-password="{{translate('please_enter_your_password').'.'}}"
    data-enter-confirm-password="{{translate('please_enter_your_confirm_password').'.'}}"
    data-password-not-match="{{translate('passwords_do_not_match').'.'}}">
</span>
@endsection


@push('script')

<script>
$(document).on("change", ".toggle-pass", function () {
    let target = $(this).data("target");
    let input = $(target);
    let icon = $(this).siblings("i");

    if (input.attr("type") === "password") {
        input.attr("type", "text");
        icon.removeClass("tio-hidden").addClass("tio-visible");
    } else {
        input.attr("type", "password");
        icon.removeClass("tio-visible").addClass("tio-hidden");
    }
});
</script>
@if(isset($recaptcha) && $recaptcha['status'] == 1)
<script type="text/javascript">
    "use strict";
            var onloadCallback = function () {
                grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
                });
            };
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
@endif
<script>
    $("#wholesale-register-form").on("submit", function(e) {
        e.preventDefault();

 if ({{ auth('customer')->check() ? 'true' : 'false' }}) {
        toastr.error(@json(__('You are already logged in with another account. Logout then continue.')));
        return false; 
    }
        let form = $(this);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: form.serialize(),
             headers: {
        'X-Requested-With': 'XMLHttpRequest'  
    },
            beforeSend: function() {
                $("#sign-up").attr("disabled", true).html(@json(__('Processing...')));
            },
            success: function(response) {
        console.log("✅ AJAX Success Response:", response);

                if (response.errors) {
                    $.each(response.errors, function(k, v) {
                        toastr.error(v.message);
                    });
                    return;
                }
                if (response.error) {
                    toastr.error(response.error);
                    return;
                }

                // success
                if (response.status == 1) {
                    toastr.success(response.message);
                    if (response.redirect_url) {
                        window.location.href = "/wholesaler/auth/login";
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, msgs) {
                        msgs.forEach(msg => toastr.error(msg));
                    });
                }
            },

            complete: function() {
                $("#sign-up").attr("disabled", false).html("{{ translate('sign_up') }}");
            },
        });
    });
</script>
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>



@endpush

