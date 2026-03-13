@extends('layouts.front-end.app')

@section('title', translate('Warranty Claim Payment'))

@section('content')
@include('layouts.front-end.partials._store-header')

<div class="container py-5" dir="{{ Session::get('direction') === 'rtl' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header text-white text-center py-4">
                    <h3 class="mb-0">{{ translate('Pay Warranty Claim') }}</h3>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h5 class="mb-2">
                            {{ translate('Claim Number') }}:
                            <span class="bidi-ltr">{{ $claim->claim_number }}</span>
                        </h5>
                        <h2 class="text-primary fw-bold mb-1">
                            {{ webCurrencyConverter((float)$payment->amount) }}
                        </h2>
                        <p class="text-muted">{{ translate('Total Amount Due') }}</p>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-4 text-center text-dark">
                        {{ translate('Choose Payment Method') }}
                    </h5>

                    @if ($digital_payment['status'] == 1 && $payment_gateways_list->count() > 0)
                        <div class="row g-3">
                            @foreach ($payment_gateways_list as $gateway)
                                <div class="col-12 col-sm-6">
                                    <form method="POST" action="{{ route('customer.warranty-claim-payment-request') }}" class="h-100">
                                        @csrf
                                        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                        <input type="hidden" name="payment_method" value="{{ $gateway->key_name }}">
                                        <input type="hidden" name="payment_platform" value="web">

                                        <button type="submit" class="gateway-card w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 rounded-3 border bg-light hover-shadow position-relative overflow-hidden">
                                            <div class="gateway-icon mb-3">
                                                <img width="80" class="img-fluid"
                                                    src="{{ dynamicStorage('storage/app/public/payment_modules/gateway_image') }}/{{ json_decode($gateway->additional_data)->gateway_image ?? 'default.png' }}"
                                                    alt="{{ $gateway->key_name }}"
                                                    onerror="this.src='{{ dynamicAsset('public/assets/front-end/img/payment-placeholder.png') }}'">
                                            </div>
                                            <div class="gateway-title fw-bold text-dark">
                                                @if($gateway->additional_data && json_decode($gateway->additional_data)->gateway_title)
                                                    {{ json_decode($gateway->additional_data)->gateway_title }}
                                                @else
                                                    {{ ucwords(str_replace('_', ' ', $gateway->key_name)) }}
                                                @endif
                                            </div>
                                            <small class="text-success mt-2">
                                                {{ translate('Click to Pay') }}
                                            </small>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning text-center py-4">
                            <i class="tio-info-outlined fs-30"></i>
                            <p class="mt-3">{{ translate('No payment gateway available at the moment') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .gateway-card {
        transition: all 0.3s ease;
        background: linear-gradient(145deg, #ffffff, #f8f9fa) !important;
        border: 2px solid #e9ecef !important;
    }

    .gateway-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
        border-color: var(--c-primary) !important;
    }
</style>
@endsection
