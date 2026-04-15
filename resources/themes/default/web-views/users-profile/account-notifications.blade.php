@extends('layouts.front-end.app')

@section('title', translate('my_notifications'))

@section('content')
@include('layouts.front-end.partials._store-header')

<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 __customer-profile customer-profile-notifications px-0">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-0 mb-md-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('notifications') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table __table __table-2 text-center">
                            <thead class="thead-light">
                                <tr>
                                    <td class="tdBorder" >{{translate('SL')}}</td>
                                    <td class="tdBorder">{{translate('notification_title')}}</td>
                                    <td class="tdBorder">{{translate('notification_message')}}</td>
                                    <td class="tdBorder">{{translate('notification_receiveds')}}</td>
                                    <td class="tdBorder">{{translate('action')}}</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php ($notifications = \App\Utils\Notifications::getUserNotifications(auth('customer')->id(), [2])); ?>
                                @php $i = 1; @endphp
                                @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ Str::limit($notification->title, 40, '...') }}</td>
                                    <td>{{ Str::limit($notification->message, 40, '...') }}</td>
                                    <td>
                                        @if(!empty($notification->created_at))
                                            @php($createdAt = \Carbon\Carbon::parse($notification->created_at))
                                            {!! $createdAt->diffInDays(\Carbon\Carbon::now()) < 7
                                                ? formatDateTimeForDisplay($createdAt, 'D h:i A')
                                                : formatDateTimeForDisplay($createdAt, 'd M Y h:i A') !!}
                                        @else
                                            {{ translate('not_available') }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="__btn-grp-sm flex-nowrap">
                                          
                                            <a href="{{route('notification.view',$notification->id)}}" title="{{ translate('view_notification') }}" class="btn-outline-success text-success __action-btn btn-shadow rounded-full">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <input type="hidden" id="notification_paginated_page" value="{{request('page')}}">
        </section>
    </div>
</div>
@endsection
