@php use Carbon\Carbon; @endphp
@extends('layouts.back-end.app')

@section('title', translate('ticket_details') . ' #' . $ticket->id)

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset('public/assets/back-end/img/support_ticket.png') }}" alt="">
            {{ translate('ticket_details') }} #{{ $ticket->id }}
        </h2>
        <a href="javascript:history.back()" class="btn btn--primary">{{ translate('back') }}</a>
    </div>

    <div class="row">
        <!-- Ticket Info -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100" style=" direction : {{Session::get('direction') === "rtl" ? 'ltr' : 'rtl'}};">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('ticket_information') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ translate('subject') }}:</strong> {{ $ticket->subject ?? 'N/A' }}</p>
                    <p><strong>{{ translate('type') }}:</strong> {{ ucfirst($ticket->type) }}</p>
                    <p><strong>{{ translate('sub_type') }}:</strong> {{ $ticket->sub_type ? Str::replace('_', ' ', $ticket->sub_type) : 'N/A' }}</p>
                    <p><strong>{{ translate('priority') }}:</strong> <span class="badge badge-soft-{{ $ticket->priority == 'low' ? 'primary' : ($ticket->priority == 'medium' ? 'info' : ($ticket->priority == 'high' ? 'warning' : 'danger')) }}">{{ ucfirst($ticket->priority) }}</span></p>
                    <p><strong>{{ translate('status') }}:</strong> <span class="badge badge-soft-info">{{ $ticket->status_details->name ?? 'N/A' }}</span></p>
                    <p><strong>{{ translate('department') }}:</strong> {{ $ticket->department->name ?? 'Unassigned' }}</p>
                    <p><strong>{{ translate('assigned_employee') }}:</strong> {{ $ticket->employee->name ?? 'Unassigned' }}</p>
                    <p><strong>{{ translate('created_at') }}:</strong> {{ $ticket->created_at->format('d M, Y H:i') }}</p>
                    <p><strong>{{ translate('reopen_count') }}:</strong> {{ $ticket->reopen_count ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Customer / Inbox Message Info -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100" style=" direction : {{Session::get('direction') === "rtl" ? 'ltr' : 'rtl'}};">
                <div class="card-header">
                    <h5 class="mb-0">
                        @if($ticket->customer)
                            {{ translate('customer_information') }}
                        @else
                            {{ translate('inbox_message_source') }}
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($ticket->customer)
                        <p><strong>{{ translate('name') }}:</strong> {{ $ticket->customer->f_name }} {{ $ticket->customer->l_name }}</p>
                        <p><strong>{{ translate('email') }}:</strong> {{ $ticket->customer->email }}</p>
                        <p><strong>{{ translate('phone') }}:</strong> {{ $ticket->customer->phone ?? 'N/A' }}</p>
                    @elseif($ticket->relatedInboxMessages->isNotEmpty())
                        @php
                            $msg = $ticket->relatedInboxMessages->first();
                        @endphp
                        <p><strong>{{ translate('sender_name') }}:</strong> {{ $msg->sender_name ?? 'N/A' }}</p>
                        <p><strong>{{ translate('sender_email') }}:</strong> {{ $msg->sender_email ?? 'N/A' }}</p>
                        <p><strong>{{ translate('sender_phone') }}:</strong> {{ $msg->sender_phone ?? 'N/A' }}</p>
                        <p><strong>{{ translate('subject') }}:</strong> {{ $msg->subject ?? 'N/A' }}</p>
                        <p><strong>{{ translate('message') }}:</strong><br>{{ nl2br(e($msg->message)) }}</p>

                        @if($msg->attachment)
                            <p><strong>{{ translate('attachment') }}:</strong>
                                <a href="{{ $msg->attachment_full_url }}" target="_blank">{{ translate('view') }}</a>
                            </p>
                        @endif
                    @else
                        <p class="text-muted">{{ translate('no_customer_or_source_found') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">{{ translate('activity_log') }}</h5>
        </div>
        <div class="card-body">
            @if($ticket->supportActivities->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('title') }}</th>
                                <th>{{ translate('description') }}</th>
                                <th>{{ translate('employee') }}</th>
                                <th>{{ translate('noted_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticket->supportActivities as $act)
                                <tr>
                                    <td>{{ $act->title }}</td>
                                    <td>{{ $act->description }}</td>
                                    <td>{{ $act->employee?->name ?? 'System' }}</td>
                                    <td>{{ Carbon::parse($act->noted_at)->format('d M, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">{{ translate('no_activity_logged') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection