{{-- resources/views/service-payment.blade.php --}}
@extends('layouts.front-end.app')

@section('title', translate('service_Payment'))

@section('content')
@include('layouts.front-end.partials._store-header')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-white text-center py-4">
                    <h3 class="mb-0">{{ translate('Pay Service Invoice') }}</h3>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2 class="text-primary fw-bold mb-1">
                            {{ webCurrencyConverter($invoice->total) }}
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
                                    <form method="POST" action="{{ route('customer.service-payment-request') }}" class="h-100">
                                        @csrf
                                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
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

                        <div class="text-center mt-5">
                            <i class="tio-lock text-primary" style="font-size: 24px;"></i>
                            <p class="text-muted small mt-2">
                                {{ translate('Your payment information is secure and encrypted') }}
                            </p>
                        </div>
                    @else
                        <div class="alert alert-warning text-center py-4">
                            <i class="tio-info-outlined fs-30"></i>
                            <p class="mt-3">{{ translate('No payment gateway available at the moment') }}</p>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        {{ translate('Powered by') }} {{ getWebConfig('company_name') }}
                    </small>
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
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        border-color: var(--c-primary) !important;
    }
    .gateway-icon img {
        transition: transform 0.3s;
    }
    .gateway-card:hover .gateway-icon img {
        transform: scale(1.1);
    }
</style>
@endsection