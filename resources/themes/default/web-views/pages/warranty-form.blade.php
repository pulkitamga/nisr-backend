@extends('layouts.front-end.app')
@section('title', translate('Warranty Activation'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}" />
    @include('web-views.partials._premium-page-styles')
    <style>
        [dir="rtl"] .nisr-option-card input {
            margin-inline-start: .15rem;
            margin-inline-end: 0;
        }
    </style>
@endpush

@section('content')
    <div class="nisr-page-shell">
        <section class="container rtl text-align-direction">
            <div class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('Warranty Activation') }}</span>
                <h1 class="nisr-page-title">{{ translate('Activate_your_warranty') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Register_your_purchase_details_now_to_speed_up_future_support_claims_and_service_validation') }}
                </p>
                <div class="nisr-hero-actions">
                    <a href="{{ route('warranty.track.page') }}" class="nisr-link-pill">{{ translate('back') }}</a>
                    <a href="{{ route('warranty.lookup.start') }}" class="nisr-link-pill">{{ translate('Need_to_check_an_existing_warranty') }}</a>
                </div>
            </div>
        </section>

        <section class="container pb-5 rtl text-align-direction">
            <div class="nisr-page-grid">
                <div class="nisr-surface">
                    @if (session('errors'))
                        <div class="nisr-alert mb-4">
                            @foreach (session('errors')->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('warranty.activate.store') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-4">
                        @csrf

                        <section class="nisr-form-section">
                            <div class="nisr-form-section-head">
                                <span class="nisr-section-kicker">1</span>
                                <div>
                                    <h2 class="nisr-section-title">{{ translate('Purchase_details') }}</h2>
                                    <p class="nisr-section-copy mb-0">{{ translate('Serial_number_purchase_date_invoice_number_and_purchase_location') }}</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="warrantyActivationSerialNumber">{{ translate('Serial Number') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text"
                                               id="warrantyActivationSerialNumber"
                                               name="serial_number"
                                               class="form-control @error('serial_number') is-invalid @enderror"
                                               value="{{ old('serial_number') }}"
                                               required>
                                        <div class="input-group-append">
                                            @include('partials.serial-scan-button', ['targetInput' => '#warrantyActivationSerialNumber'])
                                        </div>
                                    </div>
                                    @error('serial_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="purchaseDate">{{ translate('Purchase Date') }} <span class="text-danger">*</span></label>
                                    <input type="date"
                                           id="purchaseDate"
                                           name="purchase_date"
                                           class="form-control @error('purchase_date') is-invalid @enderror"
                                           value="{{ old('purchase_date') }}"
                                           required
                                           max="{{ now()->format('Y-m-d') }}">
                                    @error('purchase_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="invoiceNumber">{{ translate('Invoice Number') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           id="invoiceNumber"
                                           name="invoice_number"
                                           class="form-control @error('invoice_number') is-invalid @enderror"
                                           value="{{ old('invoice_number') }}"
                                           required>
                                    @error('invoice_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="receiptUpload">{{ translate('Receipt/Proof of Purchase') }} <span class="text-danger">*</span></label>
                                    <input type="file"
                                           id="receiptUpload"
                                           name="receipt"
                                           class="form-control @error('receipt') is-invalid @enderror"
                                           accept="image/*,application/pdf"
                                           required>
                                    @error('receipt')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="nisr-form-section">
                            <div class="nisr-form-section-head">
                                <span class="nisr-section-kicker">2</span>
                                <div>
                                    <h2 class="nisr-section-title">{{ translate('Retailer_details') }}</h2>
                                    <p class="nisr-section-copy mb-0">{{ translate('Select_the_branch_or_enter_the_distributor_name_where_the_purchase_was_made') }}</p>
                                </div>
                            </div>

                            <label class="d-block mb-3">{{ translate('Retailer Source') }} <span class="text-danger">*</span></label>
                            <div class="nisr-option-grid mb-3">
                                <label class="nisr-option-card">
                                    <input class="custom-control-input"
                                           type="radio"
                                           name="retailer_source"
                                           id="source_branch"
                                           value="branch"
                                           {{ old('retailer_source', 'branch') === 'branch' ? 'checked' : '' }}>
                                    <span>
                                        <span class="nisr-option-card__title">{{ translate('Select Branch') }}</span>
                                        <span class="nisr-option-card__copy">{{ translate('Choose_an_official_branch_from_the_list') }}</span>
                                    </span>
                                </label>

                                <label class="nisr-option-card">
                                    <input class="custom-control-input"
                                           type="radio"
                                           name="retailer_source"
                                           id="source_distributor"
                                           value="distributor"
                                           {{ old('retailer_source') === 'distributor' ? 'checked' : '' }}>
                                    <span>
                                        <span class="nisr-option-card__title">{{ translate('Enter Distributor Name') }}</span>
                                        <span class="nisr-option-card__copy">{{ translate('Use_this_if_the_purchase_was_made_through_a_distributor_or_retail_partner') }}</span>
                                    </span>
                                </label>
                            </div>

                            <div id="branch-group" class="{{ old('retailer_source') === 'distributor' ? 'd-none' : '' }}">
                                <label for="retailerBranch">{{ translate('Select Branch') }}</label>
                                <select id="retailerBranch"
                                        name="retailer_branch_id"
                                        class="form-control @error('retailer_branch_id') is-invalid @enderror"
                                        {{ old('retailer_source') === 'distributor' ? 'disabled' : '' }}>
                                    <option value="">{{ translate('-- Select Branch --') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('retailer_branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('retailer_branch_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div id="distributor-group" class="mt-3 {{ old('retailer_source') !== 'distributor' ? 'd-none' : '' }}">
                                <label for="retailerName">{{ translate('Distributor / Retailer name') }}</label>
                                <input type="text"
                                       id="retailerName"
                                       name="retailer_name"
                                       class="form-control @error('retailer_name') is-invalid @enderror"
                                       placeholder="{{ translate('Distributor / Retailer name') }}"
                                       value="{{ old('retailer_name') }}"
                                       {{ old('retailer_source') === 'distributor' ? 'required' : '' }}>
                                @error('retailer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </section>

                        <section class="nisr-form-section">
                            <div class="nisr-form-section-head">
                                <span class="nisr-section-kicker">3</span>
                                <div>
                                    <h2 class="nisr-section-title">{{ translate('Customer_details') }}</h2>
                                    <p class="nisr-section-copy mb-0">{{ translate('Use_a_phone_and_email_that_you_can_access_for_future_updates') }}</p>
                                </div>
                            </div>

                            @if(!$isLoggedIn)
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="customerName">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="customerName"
                                               name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="customerPhone">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                        <input type="tel"
                                               id="customerPhone"
                                               name="phone"
                                               class="form-control mobile_number phone-input-with-country-picker @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}"
                                               required>
                                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <div>
                                            <input type="hidden" class="country-picker-country-code w-50" name="country_code" readonly>
                                            <input type="hidden" class="country-picker-phone-number w-50" name="mobile_number" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="customerEmail">{{ translate('Email') }} <span class="text-danger">*</span></label>
                                        <input type="email"
                                               id="customerEmail"
                                               name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}"
                                               required>
                                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @else
                                <div class="nisr-mini-card">
                                    <strong>{{ translate('Logged in as') }}</strong>
                                    <p>{{ $userData['name'] }} ({{ $userData['email'] }})</p>
                                </div>
                                @foreach(['name', 'phone', 'email'] as $f)
                                    <input type="hidden" name="{{ $f }}" value="{{ $userData[$f] }}">
                                @endforeach
                            @endif
                        </section>

                        <section class="nisr-form-section">
                            <div class="nisr-form-section-head">
                                <span class="nisr-section-kicker">4</span>
                                <div>
                                    <h2 class="nisr-section-title">{{ translate('Proof_and_consent') }}</h2>
                                    <p class="nisr-section-copy mb-0">{{ translate('A_clear_receipt_image_or_pdf_to_verify_the_purchase') }}</p>
                                </div>
                            </div>

                            @php
                                $recaptcha = getWebConfig(name: 'recaptcha');
                            @endphp
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div id="recaptcha_element" class="w-100" data-type="image"></div>
                            @else
                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-6">
                                        <label for="defaultCaptchaValue">{{ translate('enter_captcha_value') }}</label>
                                        <input type="text"
                                               id="defaultCaptchaValue"
                                               class="form-control"
                                               name="default_captcha_value"
                                               placeholder="{{ translate('enter_captcha_value') }}"
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

                            <label class="d-flex align-items-start gap-2 mb-0">
                                <input type="checkbox" name="consent_checked" value="1" required style="margin-top:.35rem;">
                                <span class="nisr-section-copy mb-0">
                                    {{ translate('I have read and agree to the') }}
                                    <a href="{{ route('warranty-policy') }}" target="_blank">{{ translate('Warranty Policy') }}</a>
                                </span>
                            </label>
                        </section>

                        <button type="submit" class="btn btn--primary nisr-submit">
                            {{ translate('Activate Warranty') }}
                        </button>
                    </form>
                </div>

                <aside class="nisr-surface nisr-surface--soft">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('Have_these_ready_before_you_submit') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('Use_a_phone_and_email_that_you_can_access_for_future_updates') }}</p>
                    </div>

                    <ul class="nisr-checklist mb-4">
                        <li>{{ translate('Serial_number_purchase_date_invoice_number_and_purchase_location') }}</li>
                        <li>{{ translate('A_clear_receipt_image_or_pdf_to_verify_the_purchase') }}</li>
                        <li>{{ translate('Use_a_phone_and_email_that_you_can_access_for_future_updates') }}</li>
                    </ul>

                    <div class="nisr-mini-card mb-3">
                        <strong>{{ translate('Need_to_check_an_existing_warranty') }}</strong>
                        <p>{{ translate('Warranty Lookup') }}</p>
                    </div>

                    <div class="nisr-inline-actions">
                        <a href="{{ route('warranty.lookup.start') }}" class="nisr-link-pill">{{ translate('Warranty Lookup') }}</a>
                        <a href="{{ route('warranty.track.page') }}" class="nisr-link-pill">{{ translate('back') }}</a>
                    </div>
                </aside>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('[name="retailer_source"]');
            const branch = document.getElementById('branch-group');
            const distrib = document.getElementById('distributor-group');
            const branchSel = branch ? branch.querySelector('select') : null;
            const distribIn = distrib ? distrib.querySelector('input') : null;

            function toggle() {
                const selected = document.querySelector('[name="retailer_source"]:checked');
                if (!selected || !branch || !distrib || !branchSel || !distribIn) {
                    return;
                }

                const src = selected.value;

                if (src === 'branch') {
                    branch.classList.remove('d-none');
                    distrib.classList.add('d-none');
                    branchSel.disabled = false;
                    distribIn.disabled = true;
                    distribIn.removeAttribute('required');
                    branchSel.required = true;
                } else {
                    branch.classList.add('d-none');
                    distrib.classList.remove('d-none');
                    branchSel.disabled = true;
                    distribIn.disabled = false;
                    distribIn.required = true;
                    branchSel.removeAttribute('required');
                }
            }

            radios.forEach(function(radio) {
                radio.addEventListener('change', toggle);
            });

            toggle();
        });
    </script>

    @include('partials.serial-scanner-assets')
@endsection

@push('script')
    @php
        $recaptcha = getWebConfig(name: 'recaptcha');
    @endphp
    @if(isset($recaptcha) && $recaptcha['status'] == 1 && !empty($recaptcha['site_key']))
        <script>
            "use strict";
            var onloadCallback = function() {
                grecaptcha.render('recaptcha_element', {'sitekey': '{{ $recaptcha['site_key'] }}'});
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    @endif
@endpush
