@extends('layouts.front-end.app')
@section('title', translate('warranty_Policy'))

@section('content')

<div class="container rtl pt-4 pb-5 text-align-direction tracking-page">
    <div class="card border-0 box-shadow-lg">
        <div class="card-header py-5">
            <div class="card-body py-5">
                <h6 class="text-end small font-bold fs-14">
                    <a href="{{ route('warranty.track.page') }}" class="btn btn--primary">
                        <span class="text-primary"><i class="tio-arrow"></i></span>
                        {{ translate('back') }}
                    </a>
                </h6>
                <h2 class="text-center mb-3 headerTitle">{{ translate('Warranty Policy') }}</h2>
                <div class="card __card">
                    <div class="card-body text-justify">
                        @if($policy)
                            <p><strong>{{ translate('Version') }}: {{ $policy->version ?? '-' }}</strong></p>
                            <p><strong>{{ translate('Effective Date') }}: {{ optional($policy->published_at)->format('Y-m-d') ?? '-' }}</strong></p>
                            <div>{!! getTranslatedValue($policy, 'value', $policy->value ?? '') !!}</div>
                        @else
                            <p class="mb-0">{{ translate('No policy available right now.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
