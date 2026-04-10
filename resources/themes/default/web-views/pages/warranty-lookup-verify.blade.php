@extends('layouts.front-end.app')
@section('title', translate('Verify_Warranty_OTP'))

@push('css_or_js')
    @include('web-views.partials._premium-page-styles')
@endpush

@section('content')
    <div class="nisr-page-shell">
        <section class="container rtl text-align-direction">
            <div class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('Verify Warranty OTP') }}</span>
                <h1 class="nisr-page-title">{{ translate('Verify_your_access') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Enter_the_code_sent_to_your_registered_contact_to_continue_to_your_warranty_record') }}
                </p>
            </div>
        </section>

        <section class="container pb-5 rtl text-align-direction">
            <div class="nisr-page-grid nisr-page-grid--narrow">
                <div class="nisr-surface text-center">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('Verify Warranty OTP') }}</h2>
                        <p class="nisr-section-copy mb-0">
                            {{ translate('We have sent an OTP to your registered contact:') }}
                            <strong class="d-block mt-2 text-dark">{{ $contact }}</strong>
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="nisr-alert text-start mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="nisr-alert text-start mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('warranty.lookup.verify') }}" method="POST" class="d-grid gap-4">
                        @csrf
                        <input type="hidden" name="warranty_id" value="{{ $warranty_id }}">
                        <input type="hidden" name="contact" value="{{ $contact }}">

                        <div>
                            <label for="otp" class="form-label">{{ translate('Enter OTP') }}</label>
                            <input type="text"
                                   class="form-control nisr-otp-input py-2"
                                   name="otp"
                                   id="otp"
                                   maxlength="4"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   placeholder="{{ translate('Enter 4-digit OTP') }}"
                                   required>
                        </div>

                        <button type="submit" class="btn btn--primary nisr-submit">
                            {{ translate('Verify OTP') }}
                        </button>
                    </form>

                    <div class="nisr-inline-actions justify-content-center">
                        <a href="{{ route('warranty.lookup.start') }}" class="nisr-link-pill">
                            {{ translate('Resend or Try Again') }}
                        </a>
                        <a href="{{ route('warranty.lookup.start') }}" class="nisr-link-pill">
                            {{ translate('Return_to_lookup') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
