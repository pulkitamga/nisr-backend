@extends('layouts.front-end.app')

@section('content')
<div class="container my-5 text-align-direction">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- Success Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg--primary text-white text-center py-4">
                    <h6 class="mb-0">{{ translate('Claim Submitted Successfully!') }}</h6>
                </div>

                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <p class="lead text-muted text-primary">
                            {{ translate('Your warranty claim has been received and is under review.') }}
                        </p>
                    </div>

                    <div class="bg-light rounded-3 p-4 mb-4">
                        <div class="row text-center text-md-start">
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-muted d-block">{{ translate('Claim Number') }}</small>
                                <h5 class="fw-bold text-primary" id="claimNumber">
                                    {{ $claim->claim_number }}
                                </h5>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-muted d-block">{{ translate('Serial Number') }}</small>
                                <h5 class="fw-bold">{{ $claim->warranty->serial_number }}</h5>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-muted d-block">{{ translate('Submitted On') }}</small>
                                <h6>{{ $claim->submitted_at->format('d M, Y \a\t h:i A') }}</h6>
                            </div>

                            @if (!empty($claim->response_due))
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-muted d-block">{{ translate('Expected Response') }}</small>
                                <h6 class="text-warning">
                                    <!-- {{ $claim->response_due?->diffForHumans() }} -->
                                    <small class="d-block">{{ $claim->response_due?->format('d M, Y h:i A') }}</small>
                                </h6>
                            </div>
                            @endif

                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('home') }}" class="btn btn--primary mt-4">
                            {{ translate('Back to Home') }}
                        </a>
                        <a href="{{ route('warranty.lookup.start') }}" class="btn btn-primary mt-4">
                            {{ translate('Track_claim') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
