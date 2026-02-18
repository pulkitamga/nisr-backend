@extends('layouts.front-end.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">

            {{-- ✅ If status = active --}}
            @if($warranty->status === 'active')
                <div class="alert alert-success">
                    <h4>{{ translate('Warranty Activated Successfully!') }}</h4>
                    <p>{{ translate('Serial Number') }}: {{ $warranty->serial_number }}</p>
                    <p>{{ translate('Valid Until') }}: {{ $warranty->end_date->format('Y-m-d') }}</p>
                    <p>{{ translate('A confirmation has been sent to your email.') }}</p>
                </div>

            {{-- ✅ If status = pending_review --}}
            @elseif($warranty->status === 'pending_review')
                <div class="alert alert-warning">
                    <h4>{{ translate('Your request is pending review.') }}</h4>
                    <p>{{ translate('Our team is currently reviewing your activation and will update you soon.') }}</p>
                    <p>{{ translate('Serial Number') }}: {{ $warranty->serial_number }}</p>
                </div>
            @endif

            <a href="{{ route('home') }}" class="btn btn--primary mt-4">
                {{ translate('Back to Home') }}
            </a>
            <a href="{{ route('warranty.lookup.start') }}" class="btn btn-primary mt-4">
                {{ translate('Track_warranty') }}
            </a>

        </div>
    </div>
</div>
@endsection
