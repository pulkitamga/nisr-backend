<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">

<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('wholesaler_login')}}</title>
    <link rel="shortcut icon"
        href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">


</head>

<body>
    <main id="content" role="main" class="main">
        <div class="auth-wrapper">
            <div class="auth-wrapper-left"
                style="background: url('{{ dynamicAsset(path: 'public/assets/back-end/img/login-bg.png') }}') no-repeat center center / cover">
                <div class="auth-left-cont">
                    @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))
                    <a class="d-inline-flex mb-5" href="{{ route('home') }}">
                        <img width="310" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                            alt="Logo">
                    </a>
                    <h2 class="title">{{translate('Make Your Business')}} <span
                            class="font-weight-bold c1 d-block text-capitalize">{{translate('Profitable...')}}</span>
                    </h2>
                </div>
            </div>
            <div class="auth-wrapper-right">
                <div class="auth-wrapper-form">
                    <div class="d-block d-lg-none">
                        <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                            <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                                alt="Logo">
                        </a>
                    </div>

                    <form autocomplete="off" class="customer-centralize-login-form mt-2"
                        action="{{ route('wholesale.auth.login') }}" method="post" id="wholesaler-login-form">
                        @csrf
                        <input type="hidden" name="login_type" class="auth-login-type-input" value="manual-login">

                        <div>
                            <div class="mb-5">
                                <h1 class="display-4">{{translate('sign_in')}}</h1>
                                <h1 class="h4 text-gray-900 mb-4">
                                    {{translate('welcome_back_to')}} {{translate('wholesaler_login')}}
                                </h1>
                            </div>
                        </div>

                        <div class="js-form-message form-group">
                            <label class="input-label" for="signingVendorEmail">{{translate('your_email')}}</label>

                            <input type="email" class="form-control form-control-lg" name="user_identity" id="si-email"
                                tabindex="1" placeholder="{{ translate('enter_email_address') }}" aria-label="{{ translate('enter_email_address') }}" required
                                data-msg="Please enter a valid email address.">
                        </div>
                        <div class="js-form-message form-group">
                            <label class="input-label" for="signingVendorPassword" tabindex="0">
                                <span class="d-flex justify-content-between align-items-center">
                                    {{translate('password')}}
                                    <a href="{{route('customer.auth.recover-password')}}">
                                        {{translate('forgot_password')}}
                                    </a>
                                </span>
                            </label>

                            <div class="input-group input-group-merge">
                                <input type="password" class="js-toggle-password form-control form-control-lg"
                                    name="password" type="password" id="si-password"
                                    placeholder="{{ translate('8+_characters_required') }}"
                                    aria-label="{{ translate('8+_characters_required') }}" required
                                    data-msg="Your password is invalid. Please try again."
                                    data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                    "defaultClass": "tio-hidden-outlined",
                                    "showClass": "tio-visible-outlined",
                                    "classChangeTarget": "#changePassIcon"
                                    }'>
                                <div id="changePassTarget" class="input-group-append">
                                    <a class="input-group-text" href="javascript:">
                                        <i id="changePassIcon" class="tio-visible-outlined"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="termsCheckbox" name="remember">
                                <label class="custom-control-label text-muted" for="termsCheckbox">
                                    {{translate('remember_me')}}
                                </label>
                            </div>
                        </div>

                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        <div id="recaptcha_element" class="w-100" data-type="image"></div>
                        <br />
                        @else
                        <div class="row mb-3 mt-1">
                            <div class="col-6 pe-0">
                                <input type="text" class="form-control form-control-lg form-control-focus-none" name="default_captcha_code" value=""
                                    placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                            </div>
                            <div class="col-6 input-icons rounded">
                                <a href="javascript:" class="get-contact-recaptcha-verify"
                                    data-link="{{ URL('/contact/code/captcha') }}">
                                    <img src="{{ URL('/contact/code/captcha/1') }}"
                                        class="input-field w-90 h-75 p-0 rounded" id="default_recaptcha_id" alt="">
                                    <i class="tio-refresh icon"></i>
                                </a>
                            </div>
                        </div>
                        @endif

                        <button class="btn btn--primary btn-block btn-shadow font-semi-bold" type="submit">
                            {{ translate('sign_in') }}
                        </button>
                    </form>
                    @if(env('APP_MODE')=='demo')
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-10">
                                <span id="vendor-email"
                                    data-email="{{ \App\Enums\DemoConstant::VENDOR['email'] }}">{{translate('email')}} :
                                    {{ \App\Enums\DemoConstant::VENDOR['email'] }}</span><br>
                                <span id="vendor-password"
                                    data-password="{{ \App\Enums\DemoConstant::VENDOR['password'] }}">{{translate('password')}}
                                    : {{ \App\Enums\DemoConstant::VENDOR['password'] }}</span>
                            </div>
                            <div class="col-2">
                                <button class="btn btn--primary" id="copyLoginInfo"><i class="tio-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
    <span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
    <span id="route-get-session-recaptcha-code" data-route="{{ route('get-session-recaptcha-code') }}"
        data-mode="{{ env('APP_MODE') }}"></span>

    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>


    <script>
        $(document).ready(function() {
            $(document).on('submit', '#wholesaler-login-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                @php($recaptcha = getWebConfig(name: 'recaptcha'))
                @if(isset($recaptcha) && $recaptcha['status'] == 1 && !empty($recaptcha['site_key']))
                var recaptchaResponse = grecaptcha.getResponse();
                if (recaptchaResponse.length === 0) {
                    toastr.error("{{ translate('please_check_the_recaptcha') }}");
                    return;
                }
                @endif

                @if(isset($recaptcha) && $recaptcha['status'] != 1)
                var defaultCaptchaInput = $('input[name="default_captcha_code"]');
                if (defaultCaptchaInput.length === 0 || !defaultCaptchaInput.val().trim()) {
                    toastr.error("{{ translate('enter_captcha_value') }}");
                    return;
                }
                @endif

                $.ajax({
                    url: form.attr("action"),
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(res) {

                        // SUCCESS STATUS CHECK IN ONE LINE
                        let isSuccess =
                            res.status === 'success' ||
                            res.status === 'ok' ||
                            res.status === true ||
                            res.status === 1 ||
                            res.status === '1' ||
                            res.status === 'true';

                        if (isSuccess) {
                            toastr.success(res.message || @json(__('Login successful')));

                            if (res.redirect_url) {
                                window.location.href = res.redirect_url;
                            } else {
                                window.location.reload();
                            }

                        } else {
                            toastr.error(res.message || @json(__('Login failed')));
                        }
                    },

                    error: function(xhr) {
                        let message = @json(__('Something went wrong!'));
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        grecaptcha.reset();
                        @endif
                    }
                });
            });
        });
    </script>

    <!-- ====== reCAPTCHA Script ====== -->
    @php($recaptcha = getWebConfig(name: 'recaptcha'))
  
    @if(isset($recaptcha) && $recaptcha['status'] == 1 && !empty($recaptcha['site_key']))
    <script type="text/javascript">
        "use strict";
        var onloadCallback = function() {
            grecaptcha.render('recaptcha_element', {'sitekey': '{{ $recaptcha['site_key'] }}'});
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    @endif
    <script
        src="{{ theme_asset(path: 'public/assets/front-end/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>


    <script src="{{ theme_asset(path: 'public/assets/front-end/js/custom.js') }}"></script>

    <script>
        $(document).on('ready', function() {
            $('.js-toggle-password').each(function() {
                new HSTogglePassword(this).init()
            });
            $('.js-validate').each(function() {
                $.HSCore.components.HSValidation.init($(this));
            });
        });
    </script>
</body>

</html>
