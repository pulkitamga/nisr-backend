@extends('layouts.front-end.app')

@section('title', translate('Our Policies'))

@push('css_or_js')
<style>
    .policy-section {
        min-height: 600px;
        font-family: 'Public Sans', sans-serif;
    }

    .policy-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .policy-title {
        font-size: clamp(30px, 5vw, 45px);
        font-weight: 800;
        color: #0a1332;
        margin-bottom: 15px;
    }

    .policy-subtitle {
        color: #677788;
        font-size: 18px;
        max-width: 650px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .policy-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
        width: 100%;
    }

    .policy-card {
        background: #ffffff;
        border: 1px solid #e7eaf3;
        border-radius: 16px;
        padding: 25px;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        width: 100%;
        max-width: 700px;
        text-decoration: none;
    }

    .policy-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(10, 19, 50, 0.08);
        border-color: #cbd5e0;
    }

    .policy-icon {
        background: #f0f0f1;
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-inline-end: 20px;
        transition: all 0.3s ease;
    }

    .policy-card:hover .policy-icon {
        background: #f2f2f2;
        transform: scale(1.05);
    }

    .policy-icon i {
        color: #239e92;
        font-size: 22px;
    }

    .policy-content {
        flex-grow: 1;
        text-align: start;
    }

    .policy-content h3 {
        margin: 0;
        color: #1e2022;
        font-size: 20px;
        font-weight: 700;
        transition: color 0.3s ease;
    }

    .policy-card:hover .policy-content h3 {
        color: #0a1332;
    }

    .policy-content p {
        margin: 5px 0 0;
        color: #677788;
        font-size: 15px;
        line-height: 1.5;
    }

    @media (max-width: 576px) {
        .policy-card {
            padding: 20px;
        }

        .policy-icon {
            width: 45px;
            height: 45px;
            margin-inline-end: 15px;
        }

        .policy-icon i {
            font-size: 18px;
        }

        .policy-content h3 {
            font-size: 18px;
        }

        .policy-content p {
            font-size: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-5 policy-section">

    {{-- Header Section --}}
    <div class="policy-header">
        <h1 class="policy-title">
            {{ translate("We're Here to Help!") }}
        </h1>
        <p class="policy-subtitle">
            {{ translate('Have questions or need assistance? Our team is ready to support you. Reach out and let’s make our policies work for you!') }}
        </p>
    </div>

    {{-- Policy Cards Grid --}}
    <div class="policy-grid">

        {{-- SHIPPING POLICY --}}
        @if(isset($shipping_policy['status']) && $shipping_policy['status'] == 1)
        <a href="{{ route('shipping-policy') }}" class="policy-card">
            <div class="policy-icon">
                <i class="fa fa-truck"></i>
            </div>
            <div class="policy-content">
                <h3>{{ translate('Shipping Policy') }}</h3>
                <p>{{ translate('Fast, reliable delivery with real-time tracking. Learn about our shipping options.') }}</p>
            </div>
        </a>
        @endif

        {{-- RETURN POLICY --}}
        @if(isset($return_policy['status']) && $return_policy['status'] == 1)
        <a href="{{ route('return-policy') }}" class="policy-card">
            <div class="policy-icon">
                <i class="fa fa-refresh"></i>
            </div>
            <div class="policy-content">
                <h3>{{ translate('Return Policy') }}</h3>
                <p>{{ translate('Easy returns and exchanges within 30 days. Your satisfaction is guaranteed.') }}</p>
            </div>
        </a>
        @endif

        {{-- REFUND POLICY --}}
        @if(isset($refund_policy['status']) && $refund_policy['status'] == 1)
        <a href="{{ route('refund-policy') }}" class="policy-card">
            <div class="policy-icon">
                <i class="fa fa-money"></i>
            </div>
            <div class="policy-content">
                <h3>{{ translate('Refund Policy') }}</h3>
                <p>{{ translate('Quick refund processing to your original payment method within 5-7 business days.') }}</p>
            </div>
        </a>
        @endif

        {{-- CANCELLATION POLICY --}}
        @if(isset($cancellation_policy['status']) && $cancellation_policy['status'] == 1)
        <a href="{{ route('cancellation-policy') }}" class="policy-card">
            <div class="policy-icon">
                <i class="fa fa-times-circle"></i>
            </div>
            <div class="policy-content">
                <h3>{{ translate('Cancellation Policy') }}</h3>
                <p>{{ translate('Free cancellation within 24 hours of purchase. Learn about our cancellation process.') }}</p>
            </div>
        </a>
        @endif

        {{-- SERVICE POLICY --}}
        {{-- @if(isset($service_policy['status']) && $service_policy['status'] == 1)
        <a href="{{ route('#') }}" class="policy-card">
        <div class="policy-icon">
            <i class="fa fa-cogs"></i>
        </div>
        <div class="policy-content">
            <h3>{{ translate('Service Policy') }}</h3>
            <p>{{ translate('Professional service standards and quality commitment. Learn about our service terms.') }}</p>
        </div>
        </a>
        @endif --}}

        {{-- PRIVACY POLICY --}}
        @if(isset($privacy_policy['status']) && $privacy_policy['status'] == 1)
        <a href="{{ route('privacy-policy') }}" class="policy-card">
            <div class="policy-icon">
                <i class="fa fa-shield"></i>
            </div>
            <div class="policy-content">
                <h3>{{ translate('Privacy Policy') }}</h3>
                <p>{{ translate('Your data is safe with us. Learn how we protect your personal information.') }}</p>
            </div>
        </a>
        @endif

    </div>

    {{-- Empty State - No Policies Available --}}
    @php
    $hasAnyPolicy = (
    (isset($shipping_policy['status']) && $shipping_policy['status'] == 1) ||
    (isset($return_policy['status']) && $return_policy['status'] == 1) ||
    (isset($refund_policy['status']) && $refund_policy['status'] == 1) ||
    (isset($cancellation_policy['status']) && $cancellation_policy['status'] == 1) ||
    (isset($service_policy['status']) && $service_policy['status'] == 1) ||
    (isset($privacy_policy['status']) && $privacy_policy['status'] == 1)
    );
    @endphp

    @if(!$hasAnyPolicy)
    <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 16px; margin-top: 30px;">
        <i class="fa fa-file-text-o" style="font-size: 48px; color: #a0aec0; margin-bottom: 20px;"></i>
        <h3 style="color: #2d3748; font-size: 22px; margin-bottom: 10px;">{{ translate('No Policies Available') }}</h3>
        <p style="color: #718096; font-size: 16px; max-width: 500px; margin: 0 auto;">
            {{ translate('We are currently updating our policies. Please check back soon.') }}
        </p>
    </div>
    @endif

</div>
@endsection