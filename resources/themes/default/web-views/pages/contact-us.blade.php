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

    .contact-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        background: linear-gradient(135deg, #f4fbfb 0%, #ffffff 58%, #eef8f6 100%);
        border: 1px solid #dcebea;
    }

    .contact-hero__copy {
        position: relative;
        z-index: 1;
        max-width: 42rem;
        margin: 0 auto;
    }

    .contact-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: 1rem;
        padding: .45rem .9rem;
        border-radius: 999px;
        background: rgba(18, 157, 145, 0.08);
        color: #12857f;
        font-size: .85rem;
        font-weight: 700;
    }

    .contact-primary-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(300px, .85fr);
        gap: 1.5rem;
        align-items: start;
    }

    .contact-panel,
    .contact-form-panel,
    .contact-support-panel,
    .contact-social-panel {
        border: 1px solid #dde9e8;
        border-radius: 1.25rem;
        background: #fff;
        box-shadow: 0 .9rem 2rem rgba(16, 56, 62, 0.06);
    }

    .contact-form-panel {
        padding: 1.5rem 1.5rem 1.25rem;
    }

    .contact-form-panel--primary {
        position: relative;
        overflow: hidden;
        border-color: #cfe4e2;
        box-shadow: 0 1.2rem 2.8rem rgba(16, 56, 62, 0.08);
    }

    .contact-form-panel--primary::before {
        content: '';
        position: absolute;
        inset-inline-end: -4rem;
        top: -4rem;
        width: 12rem;
        height: 12rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(18, 157, 145, 0.12) 0%, rgba(18, 157, 145, 0) 68%);
        pointer-events: none;
    }

    .contact-panel__head {
        margin-bottom: 1.25rem;
    }

    .contact-panel__eyebrow {
        display: inline-block;
        margin-bottom: .5rem;
        color: #12857f;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .03em;
    }

    .contact-panel__title {
        margin: 0;
        color: #17393f;
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .contact-panel__text {
        margin: .65rem 0 0;
        color: #657e84;
        font-size: .96rem;
        line-height: 1.65;
    }

    .contact-support-panel {
        padding: 1.25rem;
    }

    .contact-form-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin: 1rem 0 1.35rem;
    }

    .contact-form-meta__item {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem .9rem;
        border: 1px solid #d8e6e5;
        border-radius: .95rem;
        background: #f8fcfc;
        color: #4d6870;
        font-size: .9rem;
        line-height: 1.4;
    }

    .contact-form-meta__item i {
        color: #129d91;
    }

    .contact-field-note {
        display: block;
        margin-top: .45rem;
        color: #6f868b;
        font-size: .84rem;
        line-height: 1.55;
    }

    .contact-form-captcha {
        padding: .9rem 1rem;
        border: 1px dashed #cfe1df;
        border-radius: 1rem;
        background: #fbfdfd;
    }

    .contact-form-captcha.is-invalid {
        border-color: #dc3545;
        background: #fff8f8;
    }

    .contact-form-captcha__title {
        display: block;
        margin-bottom: .3rem;
        color: #17393f;
        font-size: .92rem;
        font-weight: 700;
    }

    .contact-form-captcha__text {
        display: block;
        margin-bottom: .85rem;
        color: #6f868b;
        font-size: .84rem;
        line-height: 1.55;
    }

    .contact-support-stack {
        display: grid;
        gap: 1rem;
    }

    .contact-card {
        border: 1px solid #e0e8e7;
        border-radius: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        background: linear-gradient(180deg, #ffffff 0%, #f9fcfc 100%);
    }

    .contact-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(18, 157, 145, 0.14);
        border-color: #129d91;
    }

    .contact-icon {
        width: 3.25rem;
        height: 3.25rem;
    }

    .contact-link:hover {
        text-decoration: underline;
        color: #129d91 !important;
    }

    .contact-note {
        margin-top: 1rem;
        padding: 1rem 1.1rem;
        border-radius: 1rem;
        background: #f4fbfb;
        border: 1px dashed #b9dcda;
    }

    .contact-note__title {
        margin: 0 0 .45rem;
        color: #17393f;
        font-size: 1rem;
        font-weight: 700;
    }

    .contact-note__text {
        margin: 0;
        color: #638088;
        font-size: .9rem;
        line-height: 1.6;
    }

    .contact-social-panel {
        padding: 1.35rem;
    }

    .contact-secondary-shell {
        border: 1px solid #dde9e8;
        border-radius: 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: 0 .9rem 2rem rgba(16, 56, 62, 0.05);
    }

    .contact-secondary-shell__header {
        padding: 1.4rem 1.5rem 0;
    }

    .branch-search-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem 1.25rem;
        border-bottom: 1px solid #e6efee;
    }

    .branch-search-toolbar__copy {
        max-width: 28rem;
    }

    .branch-search-toolbar__title {
        margin: 0 0 .35rem;
        color: #17393f;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .branch-search-toolbar__text {
        margin: 0;
        color: #6f868b;
        font-size: .9rem;
        line-height: 1.55;
    }

    .branch-search-form {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: flex-start;
        margin: 0;
    }

    .branch-search-form__field {
        min-width: min(100%, 24rem);
        flex: 1 1 20rem;
    }

    .branch-search-form__actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .branch-search-summary {
        padding: 0 1.5rem 1rem;
        color: #6f868b;
        font-size: .88rem;
    }

    .branch-search-summary strong {
        color: #17393f;
    }

    .contact-social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 1rem;
    }

    .contact-social-card {
        border: 1px solid #e5edec;
        border-radius: 1rem;
        background: #fff;
        padding: 1.1rem;
        text-align: center;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .contact-social-card:hover {
        transform: translateY(-3px);
        border-color: #129d91;
        box-shadow: 0 .75rem 1.5rem rgba(18, 157, 145, 0.1);
    }

    .contact-map-shell {
        position: relative;
        overflow: hidden;
        border: 1px solid #dbe9e8;
        border-radius: 1.5rem;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
        box-shadow: 0 1rem 2.25rem rgba(14, 55, 60, 0.08);
    }

    .contact-map-shell::before {
        content: '';
        position: absolute;
        inset-inline-end: -5rem;
        top: -5rem;
        width: 16rem;
        height: 16rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(18, 157, 145, 0.1) 0%, rgba(18, 157, 145, 0) 70%);
        pointer-events: none;
    }

    .contact-map-shell__header {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem 1.1rem;
        border-bottom: 1px solid #e5efee;
    }

    .contact-map-shell__copy {
        max-width: 38rem;
    }

    .contact-map-shell__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        align-content: flex-start;
    }

    .contact-map-pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem .85rem;
        border: 1px solid #d7e8e6;
        border-radius: 999px;
        background: #fcfefe;
        color: #547076;
        font-size: .86rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .contact-map-pill i {
        color: #129d91;
    }

    @media (max-width: 991.98px) {
        .contact-primary-grid {
            grid-template-columns: 1fr;
        }

        .contact-secondary-shell__header {
            padding-inline: 1rem;
        }

        .branch-search-toolbar,
        .branch-search-summary {
            padding-inline: 1rem;
        }

        .contact-map-shell__header {
            padding-inline: 1rem;
        }

        #map {
            height: 420px;
        }
    }

    .map-canvas {
        height: 620px;
        width: 100%;
    }

    .map-branch-card {
        width: min(100%, 320px);
        font-family: inherit;
        color: #17393f;
    }

    .map-branch-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        padding-bottom: .9rem;
        margin-bottom: .95rem;
        border-bottom: 1px solid #e7efee;
    }

    .map-branch-card__title {
        margin: 0;
        color: #12857f;
        font-size: 1.22rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .map-branch-card__status {
        display: inline-flex;
        align-items: center;
        padding: .36rem .68rem;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .map-branch-card__status.is-open {
        background: rgba(18, 157, 145, 0.12);
        color: #0f7f77;
    }

    .map-branch-card__status.is-closed {
        background: rgba(183, 71, 71, 0.11);
        color: #ad4747;
    }

    .map-branch-card__list {
        display: grid;
        gap: .7rem;
        margin-bottom: 1rem;
    }

    .map-branch-card__row {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: .4rem .7rem;
        align-items: start;
        color: #5f757a;
        font-size: .9rem;
        line-height: 1.55;
    }

    .map-branch-card__label {
        color: #29464b;
        font-weight: 700;
    }

    .map-branch-card__hours {
        display: grid;
        gap: .45rem;
        padding: .95rem 1rem;
        margin-bottom: 1rem;
        border: 1px solid #e5efee;
        border-radius: 1rem;
        background: #fbfdfd;
    }

    .map-branch-card__hours-title {
        margin: 0 0 .2rem;
        color: #12857f;
        font-size: .95rem;
        font-weight: 700;
    }

    .map-branch-card__hours-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .45rem .85rem;
        font-size: .82rem;
        color: #5f757a;
    }

    .map-branch-card__hours-grid strong {
        color: #29464b;
    }

    .map-branch-card__actions {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .map-branch-card__actions .btn {
        flex: 1 1 9rem;
    }

    .gm-style .gm-style-iw-c {
        padding: 1rem 1rem .95rem !important;
        border-radius: 1.25rem !important;
        box-shadow: 0 1rem 2.4rem rgba(16, 56, 62, 0.16) !important;
    }

    .gm-style .gm-style-iw-d {
        overflow: hidden !important;
    }

    .gm-style .gm-ui-hover-effect {
        top: 10px !important;
        inset-inline-end: 10px !important;
    }
</style>
@endpush

@section('content')


<section>
    <div class="container">

        <div class="contact-hero my-4 text-center d-sm-block position-relative blog-banner-container">
            <div class="text--primary w-100 position-absolute">
                <img class="blog-banner-svg svg" src="{{ theme_asset(path: 'public/assets/front-end/img/blogs/background.svg') }}" alt="">
            </div>
            <div class="contact-hero__copy py-5 px-3">
                <div class="contact-hero__eyebrow">{{ translate('We_are_here_to_help') }}</div>
                <h1 class="mb-2 fw-semibold h2">
                    {{translate('Contact_us') }}
                </h1>
                <p class="fs-20 mb-0">
                    {{ translate('Send_your_question_and_we_will_route_it_to_the_right_team') }}
                </p>
            </div>
        </div>
    </div>
</section>

<div class="__inline-58 py-lg-5 ">
    <section class="py-lg-4 mb-5">
        <div class="container rtl text-align-direction">
            <div class="contact-primary-grid">
                <div class="contact-form-panel contact-form-panel--primary">
                    <div class="contact-panel__head">
                        <span class="contact-panel__eyebrow">{{ translate('send_us_a_message') }}</span>
                        <h2 class="contact-panel__title">{{translate('Tell_us_how_we_can_help')}}</h2>
                        <p class="contact-panel__text">{{ translate('Share_your_request_and_the_right_team_will_get_back_to_you') }}</p>
                    </div>
                    <div class="contact-form-meta">
                        <div class="contact-form-meta__item">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span>{{ translate('Typical_reply_time_within_one_business_day') }}</span>
                        </div>
                        <div class="contact-form-meta__item">
                            <i class="fa fa-headphones" aria-hidden="true"></i>
                            <span>{{ translate('You_can_call_email_or_send_a_message_here') }}</span>
                        </div>
                    </div>
                    <div class="for-send-message">
                        <form action="{{route('contact.store')}}" method="POST" id="getResponse">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{translate('your_name')}}</label>
                                        <input class="form-control name @error('name') is-invalid @enderror" name="name" type="text"
                                            value="{{ old('name') }}" placeholder="{{ translate('John_Doe') }}">
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-email">{{translate('email_address')}}</label>
                                        <input class="form-control email @error('email') is-invalid @enderror" name="email" type="email"
                                            value="{{ old('email') }}"
                                            placeholder="{{ translate('enter_email_address') }}">
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-phone">{{translate('Best_phone_number_to_reach_you')}}</label>
                                        <input class="form-control mobile_number phone-input-with-country-picker @error('mobile_number') is-invalid @enderror"
                                            id="cf-phone" type="tel" inputmode="tel" autocomplete="tel-national" dir="ltr"
                                            value="{{ old('mobile_number') }}"
                                            placeholder="{{translate('phone_number_example')}}" required>

                                        <div class="">
                                            <input type="hidden" class="country-picker-country-code w-50"
                                                name="country_code" readonly>
                                            <input type="hidden" class="country-picker-phone-number w-50"
                                                name="mobile_number" readonly>
                                        </div>
                                        <small class="contact-field-note">{{ translate('Include_the_best_number_for_a_callback_or_whatsapp_if_available') }}</small>
                                        @error('mobile_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-subject">{{translate('What_is_your_message_about')}}</label>
                                        <input class="form-control subject @error('subject') is-invalid @enderror" type="text" name="subject"
                                            id="cf-subject" value="{{ old('subject') }}"
                                            placeholder="{{translate('order_issue_branch_question_or_general_inquiry')}}"
                                            required>
                                        <small class="contact-field-note">{{ translate('Use_a_short_clear_title_so_we_can_route_your_request_faster') }}</small>
                                        @error('subject')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cf-message">{{translate('How_can_we_help')}}</label>
                                        <textarea class="form-control message @error('message') is-invalid @enderror" name="message" rows="4"
                                            id="cf-message"
                                            placeholder="{{ translate('Briefly_explain_your_request_and_include_any_order_branch_or_product_details') }}"
                                            required>{{ old('message') }}</textarea>
                                        <small class="contact-field-note">{{ translate('The_more_details_you_share_the_faster_we_can_help') }}</small>
                                        @error('message')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @php($recaptcha = getWebConfig(name: 'recaptcha'))
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div class="contact-form-captcha {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}" tabindex="-1">
                                <span class="contact-form-captcha__title">{{ translate('Quick_security_check') }}</span>
                                <span class="contact-form-captcha__text">{{ translate('Please_confirm_you_are_not_a_robot_before_sending_your_message') }}</span>
                                <div id="recaptcha_element" class="w-100" data-type="image"></div>
                                <div class="contact-recaptcha-feedback invalid-feedback d-block {{ $errors->has('g-recaptcha-response') ? '' : 'd-none' }}">
                                    @error('g-recaptcha-response')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            <br />
                            @else
                            <div class="row mb-3 mt-1 contact-form-captcha {{ $errors->has('default_captcha_value') ? 'is-invalid' : '' }}" tabindex="-1">
                                <div class="col-12 mb-2">
                                    <span class="contact-form-captcha__title">{{ translate('Quick_security_check') }}</span>
                                    <span class="contact-form-captcha__text">{{ translate('Type_the_characters_from_the_image_then_send_your_message') }}</span>
                                </div>
                                <div class="col-6 pe-0">
                                    <input type="text" class="form-control @error('default_captcha_value') is-invalid @enderror" name="default_captcha_value" value=""
                                        placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                                    @error('default_captcha_value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
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

                <aside class="contact-support-panel">
                    <div class="contact-panel__head">
                        <span class="contact-panel__eyebrow">{{ translate('Contact_details') }}</span>
                        <h2 class="contact-panel__title">{{ translate('Choose_the_fastest_way_to_reach_us') }}</h2>
                        <p class="contact-panel__text">{{ translate('Use_the_form_for_detailed_requests_or_choose_a_direct_channel_below') }}</p>
                    </div>

                    <div class="contact-support-stack">
                        <div class="contact-card text-center p-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/724/724664.png" alt="Phone"
                                class="contact-icon mb-3">
                            <h3 class="fw-semibold mb-2" style="font-size: 1.1rem;">{{ translate('Call Us') }}</h3>
                            <a href="tel:{{ getWebConfig(name: 'company_phone') }}"
                                class="text-decoration-none contact-link" style="font-size: 1rem;">
                                <i class="fa fa-phone me-2"></i>{{ getWebConfig(name: 'company_phone') }}
                            </a>
                        </div>

                        <div class="contact-card text-center p-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/561/561188.png" alt="Email"
                                class="contact-icon mb-3">
                            <h3 class="fw-semibold mb-2" style="font-size: 1.1rem;">{{ translate('Email Us') }}</h3>
                            <a href="mailto:{{ getWebConfig(name: 'company_email') }}"
                                class="text-decoration-none contact-link" style="font-size: 1rem;">
                                <i class="fa fa-envelope me-2"></i>{{ getWebConfig(name: 'company_email') }}
                            </a>
                        </div>

                        <div class="contact-card text-center p-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png" alt="Address"
                                class="contact-icon mb-3">
                            <h3 class="fw-semibold mb-2" style="font-size: 1.1rem;">{{ translate('address') }}</h3>
                            <p class="mb-0" style="font-size: 1rem;">
                                <i class="fa fa-map-marker me-2"></i>{{ getWebConfig(name: 'shop_address') }}
                            </p>
                        </div>
                    </div>

                    <div class="contact-note">
                        <h3 class="contact-note__title">{{ translate('Need_a_quick_answer') }}</h3>
                        <p class="contact-note__text">{{ translate('For_faster_help_include_your_phone_order_or_branch_details_in_your_message') }}</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

</div>
<section class="mt-4 mt-md-0">
    <div class="container">
        <div class="contact-secondary-shell my-4 mb-5">
            <div class="contact-secondary-shell__header">
                <span class="contact-panel__eyebrow">{{ translate('Find_a_branch') }}</span>
                <h2 class="contact-panel__title">{{ translate('Our_branches') }}</h2>
                <p class="contact-panel__text">{{ translate('Browse_branch_hours_locations_and_directions') }}</p>
            </div>

            <div class="branch-search-toolbar">
                <div class="branch-search-toolbar__copy">
                    <h3 class="branch-search-toolbar__title">
                        {{ translate('Branches') }} <span class="badge badge-soft-dark radius-50 fz-12 px-1">({{ $branchesTable->total() }})</span>
                    </h3>
                    <p class="branch-search-toolbar__text">{{ translate('Search_branches_by_name_address_location_or_phone') }}</p>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="branch-search-form">
                    <div class="branch-search-form__field">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control input-search-table"
                            placeholder="{{ translate('Search_for_a_branch_name_address_location_or_phone_number') }}"
                            aria-label="{{ translate('Search_for_a_branch_name_address_location_or_phone_number') }}">
                    </div>
                    <div class="branch-search-form__actions">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> {{translate('Search')}}
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">{{ translate('Clear') }}</a>
                        @endif
                    </div>
                </form>
            </div>

            @if(request()->filled('search'))
                <div class="branch-search-summary">
                    {{ translate('Showing_branch_results_for') }} <strong>{{ request('search') }}</strong>
                </div>
            @endif


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
                                    <a href="https://www.google.com/maps?q={{ $branch->branch_latitude ?? 0 }},{{ $branch->branch_longitude ?? 0 }}" target="_blank" class="btn btn-sm btn-outline-primary direction-btn" title="{{ translate('Direction') }}">
                                        <svg width="18" height="18" style="margin-inline-end: 4px;" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M256 0C167.6 0 96 71.6 96 160c0 114.9 139.8 266.7 145.2 272.7 6 6.5 15.6 6.5 21.6 0C276.2 426.7 416 274.9 416 160 416 71.6 344.4 0 256 0zm0 240c-44.2 0-80-35.8-80-80s35.8-80 80-80 80 35.8 80 80-35.8 80-80 80z" />
                                        </svg> {{translate('Direction')}}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center"> {{translate('No_branches_matched_your_search')}}</td>
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

@if(!empty($web_config['social_media']))
<section class="pb-5">
    <div class="container">
        <div class="contact-social-panel">
            <div class="contact-panel__head mb-4">
                <span class="contact-panel__eyebrow">{{ translate('follow_us') }}</span>
                <h2 class="contact-panel__title">{{ translate('Stay_connected_with_us') }}</h2>
                <p class="contact-panel__text">{{ translate('Follow_our_channels_for_updates_announcements_and_new_services') }}</p>
            </div>

            <div class="contact-social-grid">
                @foreach ($web_config['social_media'] as $item)
                <a href="{{ $item->link }}" target="_blank" class="contact-social-card text-decoration-none">
                    <img src="{{ match($item->name) {
                        'facebook' => 'https://cdn-icons-png.flaticon.com/512/733/733547.png',
                        'instagram' => 'https://cdn-icons-png.flaticon.com/512/174/174855.png',
                        'twitter' => 'https://cdn-icons-png.flaticon.com/512/733/733579.png',
                        'linkedin' => 'https://cdn-icons-png.flaticon.com/512/145/145807.png',
                        'pinterest' => 'https://cdn-icons-png.flaticon.com/512/2111/2111498.png',
                        'youtube' => 'https://cdn-icons-png.flaticon.com/512/1384/1384060.png',
                        default => 'https://cdn-icons-png.flaticon.com/512/25/25694.png',
                    } }}" class="mb-3" width="54" height="54" alt="{{ ucfirst($item->name) }}">
                    <h5 class="card-title mb-2 fs-5 text-dark">{{ ucfirst($item->name) }}</h5>
                    <span class="text-muted d-block small">
                        {{ '@' . parse_url($item->link, PHP_URL_HOST) }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<section>


    <div class="container mb-4 mb-md-5">
        <div class="contact-map-shell">
            <div class="contact-map-shell__header">
                <div class="contact-map-shell__copy">
                    <span class="contact-panel__eyebrow">{{ translate('Live_branch_map') }}</span>
                    <h2 class="contact-panel__title">{{ translate('Explore_branches_on_the_map') }}</h2>
                    <p class="contact-panel__text">{{ translate('Tap_a_marker_to_view_branch_details_hours_and_directions') }}</p>
                </div>
                <div class="contact-map-shell__meta">
                    <div class="contact-map-pill">
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <span>{{ translate('Use_the_map_to_compare_nearby_branches_and_plan_your_visit') }}</span>
                    </div>
                </div>
            </div>
            <div id="map" class="map-canvas"></div>
        </div>
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
                center: centerCoordinates,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                styles: [
                    { elementType: 'geometry', stylers: [{ color: '#eef5f4' }] },
                    { elementType: 'labels.text.fill', stylers: [{ color: '#50696f' }] },
                    { elementType: 'labels.text.stroke', stylers: [{ color: '#f7fbfb' }] },
                    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#d6e6e3' }] },
                    { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#e7f1ef' }] },
                    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
                    { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#d9e7e5' }] },
                    { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#dceceb' }] },
                    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#bfe2df' }] },
                ]
            });

            var branches = @json(
                $branches->map(function ($branch) {
                    $branchData = $branch->toArray();
                    $branchData['is_open_now'] = $branch->isOpenNow();

                    return $branchData;
                })
            );

            branches.forEach(function(branch) {
                if (!branch.branch_latitude || !branch.branch_longitude) {
                    return;
                }

                let position = {
                    lat: parseFloat(branch.branch_latitude),
                    lng: parseFloat(branch.branch_longitude)
                };

                const isOpenNow = Boolean(branch.is_open_now);
                const markerColor = isOpenNow ? '#129d91' : '#b95f5f';
                const markerIcon = {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="56" viewBox="0 0 44 56" fill="none">
                            <path d="M22 54C22 54 40 34.55 40 22C40 12.06 31.94 4 22 4C12.06 4 4 12.06 4 22C4 34.55 22 54 22 54Z" fill="${markerColor}"/>
                            <circle cx="22" cy="22" r="9" fill="white"/>
                            <circle cx="22" cy="22" r="4.5" fill="${markerColor}"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(44, 56),
                    anchor: new google.maps.Point(22, 54),
                };

                let marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: branch.branch_name,
                    icon: markerIcon,
                    animation: google.maps.Animation.DROP
                });

                const formatHours = (from, to) => from && to ? `${from} - ${to}` : @json(translate('Closed'));
                const statusLabel = isOpenNow ? @json(translate('Available_now')) : @json(translate('Currently_closed'));
                const directionUrl = `https://www.google.com/maps/dir/?api=1&destination=${position.lat},${position.lng}`;

                let infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="map-branch-card" dir="{{ session('direction') }}">
                            <div class="map-branch-card__head">
                                <h5 class="map-branch-card__title">${branch.branch_name}</h5>
                                <span class="map-branch-card__status ${isOpenNow ? 'is-open' : 'is-closed'}">${statusLabel}</span>
                            </div>

                            <div class="map-branch-card__list">
                                <div class="map-branch-card__row">
                                    <span class="map-branch-card__label">{{ translate('Phone') }}</span>
                                    <span>${branch.phone || '-'}</span>
                                </div>
                                <div class="map-branch-card__row">
                                    <span class="map-branch-card__label">{{ translate('Email') }}</span>
                                    <span>${branch.email || '-'}</span>
                                </div>
                                <div class="map-branch-card__row">
                                    <span class="map-branch-card__label">{{ translate('Address') }}</span>
                                    <span>${branch.branch_address || '-'}</span>
                                </div>
                            </div>

                            <div class="map-branch-card__hours">
                                <h6 class="map-branch-card__hours-title">{{ translate('Branch Timings') }}</h6>
                                <div class="map-branch-card__hours-grid">
                                    <div><strong>{{ translate('Saturday') }}</strong> ${formatHours(branch.sat_branch_hours_from, branch.sat_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Sunday') }}</strong> ${formatHours(branch.sun_branch_hours_from, branch.sun_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Monday') }}</strong> ${formatHours(branch.mon_branch_hours_from, branch.mon_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Tuesday') }}</strong> ${formatHours(branch.tue_branch_hours_from, branch.tue_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Wednesday') }}</strong> ${formatHours(branch.wed_branch_hours_from, branch.wed_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Thursday') }}</strong> ${formatHours(branch.thu_branch_hours_from, branch.thu_branch_hours_to)}</div>
                                    <div><strong>{{ translate('Friday') }}</strong> ${formatHours(branch.fri_branch_hours_from, branch.fri_branch_hours_to)}</div>
                                </div>
                            </div>

                            <div class="map-branch-card__actions">
                                <a href="${directionUrl}" target="_blank" class="btn btn--primary btn-sm">{{ translate('Open_in_google_maps') }}</a>
                                ${branch.phone ? `<a href="tel:${branch.phone}" class="btn btn-outline-secondary btn-sm">{{ translate('Contact_branch_directly') }}</a>` : ''}
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

        }
    </script>

</section>
@endsection


@push('script')


@php($recaptcha = getWebConfig(name: 'recaptcha'))
@if(isset($recaptcha) && $recaptcha['status'] == 1 && !empty($recaptcha['site_key']))
<script type="text/javascript">
    "use strict";
    let contactRecaptchaWidgetId;

    function setContactRecaptchaError(message) {
        const wrapper = document.querySelector('.contact-form-captcha');
        const feedback = document.querySelector('.contact-recaptcha-feedback');
        if (wrapper) {
            wrapper.classList.add('is-invalid');
        }
        if (feedback) {
            feedback.textContent = message;
            feedback.classList.remove('d-none');
        }
    }

    function clearContactRecaptchaError() {
        const wrapper = document.querySelector('.contact-form-captcha');
        const feedback = document.querySelector('.contact-recaptcha-feedback');
        if (wrapper) {
            wrapper.classList.remove('is-invalid');
        }
        if (feedback) {
            feedback.textContent = '';
            feedback.classList.add('d-none');
        }
    }

    var onloadCallback = function() {
        contactRecaptchaWidgetId = grecaptcha.render('recaptcha_element', {
            'sitekey': '{{ $recaptcha['site_key'] }}',
            'callback': clearContactRecaptchaError,
            'expired-callback': function() {
                setContactRecaptchaError(@json(translate('please_check_the_recaptcha')));
            }
        });
    };
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<script>
    "use strict";
    $("#getResponse").on('submit', function(e) {
        var response = grecaptcha.getResponse(contactRecaptchaWidgetId);
        if (response.length === 0) {
            e.preventDefault();
            setContactRecaptchaError(@json(translate('please_check_the_recaptcha')));
        }
    });
</script>
@endif

@if($errors->any())
<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('getResponse');
        if (!form) {
            return;
        }

        const firstInvalidField = form.querySelector('.is-invalid');
        const firstInvalidCaptcha = form.querySelector('.contact-form-captcha.is-invalid');
        const target = firstInvalidField || firstInvalidCaptcha;

        if (!target) {
            return;
        }

        target.scrollIntoView({behavior: 'smooth', block: 'center'});

        if (typeof target.focus === 'function') {
            window.setTimeout(function () {
                target.focus({preventScroll: true});
            }, 150);
        }
    });
</script>
@endif


<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@endpush
