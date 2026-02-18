@extends('layouts.front-end.app')
@section('title', translate('warranty_form'))
@push('css_or_js')
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}" />
<style>
    @media (max-width: 767.98px) {
        .__inline-24 {
            font-size: 16px !important;
            font-weight: 700;
            padding: 10px;
        }

        .fs-18 {
            font-size: 14px !important;
        }
    }

    [dir="rtl"] .padd-input-div {
        padding-right: 1.4rem;
    }

    [dir="rtl"] .radio-input-label::after,
    [dir="rtl"] .radio-input-label::before {
        position: absolute;
        left: auto;
        right: -1.5rem;
    }
</style>
@endpush
@section('content')

<div class="container rtl pt-4 pb-5 text-align-direction tracking-page">
    <div class="card border-0 box-shadow-lg">
        <div class="card-header py-5">

            <div class="card-header">
                 <h6 class="text-end small font-bold fs-14">
                <a href="{{ route('warranty.track.page') }}" class="btn btn--primary">
                    <span class="text-primary"><i class="tio-arrow"></i></span>
                    {{ translate('back') }}
                </a>
            </h6>
                <h4 class="mb-0">{{ translate('Warranty Activation') }}</h4>
            </div>
            <div class="card-body py-5">

                    @if (session('errors'))
                    <div class="alert alert-danger">
                        @foreach (session('errors')->all() as $error)
                        <p>{{ $error }}</p>
                        @endforeach
                    </div>
                    @endif

                    <form action="{{ route('warranty.activate.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- Serial Number --}}
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Serial Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="serial_number"
                                class="form-control @error('serial_number') is-invalid @enderror"
                                value="{{ old('serial_number') }}" required>
                            @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Purchase Date --}}
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Purchase Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date"
                                class="form-control @error('purchase_date') is-invalid @enderror"
                                value="{{ old('purchase_date') }}" required
                                max="{{ now()->format('Y-m-d') }}">
                            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- ***** NEW RETAILER SOURCE SECTION ***** --}}
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Retailer Source') }} <span class="text-danger">*</span></label>

                            <div class="custom-control custom-radio mb-2 padd-input-div">
                                <input class="custom-control-input" type="radio" name="retailer_source"
                                    id="source_branch" value="branch"
                                    {{ old('retailer_source', 'branch') === 'branch' ? 'checked' : '' }}>
                                <label class="custom-control-label radio-input-label" for="source_branch">
                                    {{ translate('Select Branch') }}
                                </label>
                            </div>

                            <div class="custom-control custom-radio mb-2 padd-input-div">
                                <input class="custom-control-input" type="radio" name="retailer_source"
                                    id="source_distributor" value="distributor"
                                    {{ old('retailer_source') === 'distributor' ? 'checked' : '' }}>
                                <label class="custom-control-label radio-input-label" for="source_distributor">
                                    {{ translate('Enter Distributor Name') }}
                                </label>
                            </div>

                            <div id="branch-group"
                                class="mt-2 {{ old('retailer_source') === 'distributor' ? 'd-none' : '' }}">
                                <select name="retailer_branch_id"
                                    class="form-control @error('retailer_branch_id') is-invalid @enderror"
                                    {{ old('retailer_source') === 'distributor' ? 'disabled' : '' }}>
                                    <option value="">{{ translate('-- Select Branch --') }}</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('retailer_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('retailer_branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Distributor name input (shown when “distributor” is selected) --}}
                            <div id="distributor-group"
                                class="mt-2 {{ old('retailer_source') !== 'distributor' ? 'd-none' : '' }}">
                                <input type="text" name="retailer_name"
                                    class="form-control @error('retailer_name') is-invalid @enderror"
                                    placeholder="{{ translate('Distributor / Retailer name') }}"
                                    value="{{ old('retailer_name') }}"
                                    {{ old('retailer_source') === 'distributor' ? 'required' : '' }}>
                                @error('retailer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- END NEW SECTION --}}
                        @if(!$isLoggedIn)
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                            <input type="tel" name="phone"
                                class="form-control mobile_number phone-input-with-country-picker @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            <div class="">
                                <input type="hidden" class="country-picker-country-code w-50"
                                    name="country_code" readonly>
                                <input type="hidden" class="country-picker-phone-number w-50"
                                    name="mobile_number" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        @else
                        <div class="alert alert-info">
                            {{ translate('Logged in as') }}: {{ $userData['name'] }} ({{ $userData['email'] }})
                        </div>
                        @foreach(['name','phone','email'] as $f)
                        <input type="hidden" name="{{ $f }}" value="{{ $userData[$f] }}">
                        @endforeach
                        @endif
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Invoice Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" class="form-control"
                                value="{{ old('invoice_number') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ translate('Receipt/Proof of Purchase') }} <span class="text-danger">*</span></label>
                            <input type="file" name="receipt"
                                class="form-control @error('receipt') is-invalid @enderror"
                                accept="image/*,application/pdf" required>
                            @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- CAPTCHA (unchanged) --}}
                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        <div id="recaptcha_element" class="w-100" data-type="image"></div><br>
                        @else
                        <div class="row mb-3 mt-1">
                            <div class="col-6 pr-0">
                                <input type="text" class="form-control" name="default_captcha_value"
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

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="consent_checked" value="1" required>
                                {{ translate('I have read and agree to the') }}
                                <a href="{{ route('warranty-policy') }}" target="_blank">{{ translate('Warranty Policy') }}</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn--primary w-100">
                            {{ translate('Activate Warranty') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('[name="retailer_source"]');
        const branch = document.getElementById('branch-group');
        const distrib = document.getElementById('distributor-group');
        const branchSel = branch.querySelector('select');
        const distribIn = distrib.querySelector('input');

        function toggle() {
            const src = document.querySelector('[name="retailer_source"]:checked').value;

            if (src === 'branch') {
                branch.classList.remove('d-none');
                distrib.classList.add('d-none');

                branchSel.disabled = false;
                distribIn.disabled = true;
                distribIn.removeAttribute('required');

                // required only if no branch selected
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

        radios.forEach(r => r.addEventListener('change', toggle));
        toggle(); // initial state
    });
</script>
@endsection

@push('script')
@php($recaptcha = getWebConfig(name: 'recaptcha'))
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