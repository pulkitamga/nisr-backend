@extends('layouts.front-end.app')

@section('content')
<div class="container my-5 text-align-direction">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ translate('Verify OTP') }}</h4>
                </div>
                <div class="card-body text-center">
                    <p>{{ translate('An OTP has been sent to') }}: <strong>{{ $contact }}</strong></p>

                    <form action="{{ route('warranty.verify-otp.post') }}" method="POST" class="text-start">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Enter OTP') }} <span class="text-danger">*</span></label>
                            <input type="text" name="otp" class="form-control @error('otp') is-invalid @enderror" maxlength="4" required>
                            @error('otp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ translate('Verify') }}</button>
                    </form>

                    <div class="mt-4">
                        <span id="resend-info" class="text-muted">{{ translate('You can resend OTP in') }} <span id="timer">30</span> {{ __('sec') }}</span>
                        <form id="resend-form" action="{{ route('warranty.resend-otp') }}" method="POST" style="display: none;">
                            @csrf
                            <button type="submit" class="btn btn-link">{{ translate('Resend OTP') }}</button>
                        </form>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('warranty.activate') }}">{{ translate('Back to Form') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let seconds = 30;
const timerEl = document.getElementById('timer');
const resendInfo = document.getElementById('resend-info');
const resendForm = document.getElementById('resend-form');

const countdown = setInterval(() => {
    seconds--;
    timerEl.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(countdown);
        resendInfo.style.display = 'none';
        resendForm.style.display = 'block';
    }
}, 1000);
</script>
@endsection
