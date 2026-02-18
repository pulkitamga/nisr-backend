@extends('layouts.front-end.app')
@section('title', translate('Verify_Warranty_OTP'))

@section('content')
<div class="container my-5">
    <div class="card border-0 box-shadow-lg">
        <div class="card-body py-5">
            <div class="mw-500 mx-auto text-center">
                <h3 class="font-bold mb-4">{{ translate('Verify Warranty OTP') }}</h3>

                @if ($errors->any())
                    <div class="alert alert-danger text-start">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger text-start">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="mb-4 text-muted">
                    {{ translate('We have sent an OTP to your registered contact:') }}
                    <strong>{{ $contact }}</strong><br>
                    {{ translate('Please enter the OTP below to continue.') }}
                </p>

                <form action="{{ route('warranty.lookup.verify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="warranty_id" value="{{ $warranty_id }}">
                    <input type="hidden" name="contact" value="{{ $contact }}">

                    <div class="form-group mb-4">
                        <label for="otp" class="form-label">{{ translate('Enter OTP') }}</label>
                        <input type="text" class="form-control text-center fs-4 py-2" name="otp" id="otp" maxlength="6"
                               placeholder="{{ translate('Enter 6-digit OTP') }}" required>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 mb-3">
                        {{ translate('Verify OTP') }}
                    </button>

                    <div class="text-center">
                        <a href="{{ route('warranty.lookup.start') }}" class="text-primary small">
                            <i class="tio-refresh"></i> {{ translate('Resend or Try Again') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
