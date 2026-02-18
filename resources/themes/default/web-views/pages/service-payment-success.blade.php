{{-- resources/views/service-payment-success.blade.php --}}
@extends('layouts.front-end.app')

@section('title', translate('Payment Successful'))

@section('content')
@include('layouts.front-end.partials._store-header')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 text-center">
                <div class="card-body p-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="success-checkmark mx-auto">
                            <div class="check-icon">
                                <span class="icon-line line-tip"></span>
                                <span class="icon-line line-long"></span>
                                <div class="icon-circle"></div>
                                <div class="icon-fix"></div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-success mb-3">{{ translate('Payment Successful!') }}</h2>
                    <h4 class="text-dark mb-4">{{ translate('Thank you for your payment') }}</h4>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <p class="mb-2 text-muted">{{ translate('Your service invoice has been paid successfully') }}</p>
                       
                        <p class="mt-3 mb-0">
                            {{ translate('We have received your payment and your service will continue shortly') }}
                        </p>
                    </div>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('store') }}" class="btn btn--primary px-4">
                            {{ translate('Back to Store') }}
                        </a>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .success-checkmark {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    .check-icon {
        width: 100px;
        height: 100px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 6px solid #4CAF50;
    }
    .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }
    .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0% 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    .check-icon::before, .check-icon::after {
        content: '';
        height: 100px;
        position: absolute;
        background: #FFFFFF;
        transform: rotate(-45deg);
    }
    .icon-line {
        height: 5px;
        background-color: #4CAF50;
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }
    .line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }
    .line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }
    .icon-circle {
        top: -4px;
        left: -4px;
        z-index: 10;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(45deg, #4CAF50, #8BC34A);
        position: absolute;
        box-sizing: content-box;
        border: 4px solid rgba(76, 175, 80, 0.3);
    }
    @keyframes rotate-circle { 
        0% { transform: rotate(-45deg); } 
        5% { transform: rotate(-45deg); } 
        12% { transform: rotate(-405deg); } 
        100% { transform: rotate(-405deg); } 
    }
    @keyframes icon-line-tip { 
        0% { width: 0; left: 1px; top: 19px; } 
        54% { width: 0; left: 1px; top: 19px; } 
        70% { width: 50px; left: -8px; top: 37px; } 
        84% { width: 17px; left: 21px; top: 48px; } 
        100% { width: 25px; left: 14px; top: 45px; } 
    }
    @keyframes icon-line-long { 
        0% { width: 0; right: 46px; top: 54px; } 
        65% { width: 0; right: 46px; top: 54px; } 
        84% { width: 55px; right: 0px; top: 35px; } 
        100% { width: 47px; right: 8px; top: 38px; } 
    }
</style>
@endsection