@extends('layouts.front-end.app')

@section('title', translate('view_notification'))

@section('content')
@include('layouts.front-end.partials._store-header')

<style>
    .key {
        font-weight: 500;
        color: #000;
    }
</style>
<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 __customer-profile customer-profile-notifications px-0">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-0 mb-md-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('view_notifications') }}</h5>
                    </div>
                    <div class="col-md-12 px-0">
                        <div class="col-sm-12 col-md-12 px-0" >
                            <div class="pair-list border p-3 rounded shadow-sm">
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="key fw-bold" style="min-width: 150px;">{{ translate('title') }}</span>
                                    <span class="value">{{ $SupportnotificationData->title }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="key fw-bold" style="min-width: 150px;">{{ translate('message') }}</span>
                                    <span class="value">{{ $SupportnotificationData->message }}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2">
                                    <span class="key fw-bold" style="min-width: 150px;">{{ translate('occur') }}</span>
                                    <span class="value">
                                        @if(!empty($SupportnotificationData->created_at))
                                            @php($createdAt = \Carbon\Carbon::parse($SupportnotificationData->created_at))
                                            {!! $createdAt->diffInDays(\Carbon\Carbon::now()) < 7
                                                ? formatDateTimeForDisplay($createdAt, 'D h:i A')
                                                : formatDateTimeForDisplay($createdAt, 'd M Y h:i A') !!}
                                        @else
                                            {{ translate('not_available') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
