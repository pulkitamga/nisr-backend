@extends('layouts.front-end.app')

@section('title', translate('Warranty Details'))

@section('content')
<div class="container my-5 text-align-direction">
    <h2>{{ translate('Warranty Details') }}</h2>
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>{{ translate('Serial Number') }}:</strong> {{ $warranty->serial_number }}</p>
            <p><strong>{{ translate('Status') }}:</strong> {{ translate($warranty->status) }}</p>
            <p><strong>{{ translate('Start Date') }}:</strong> {{ $warranty->start_date ? $warranty->start_date->format('Y-m-d') : 'N/A' }}</p>
            <p><strong>{{ translate('End Date') }}:</strong> {{ $warranty->end_date ? $warranty->end_date->format('Y-m-d') : 'N/A' }}</p>
            <p><strong>{{ translate('Customer Name') }}:</strong> {{ $warranty->activated_by_name }}</p>
            <p><strong>{{ translate('Email') }}:</strong> {{ $warranty->activated_by_email }}</p>
            <p><strong>{{ translate('Phone') }}:</strong> {{ $warranty->activated_by_phone }}</p>
        </div>
    </div>

    @if ($latestClaim)
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-3">{{ translate('Latest Claim') }}</h3>
            <p><strong>{{ translate('Claim Number') }}:</strong> {{ $latestClaim->claim_number }}</p>
            <p><strong>{{ translate('Claim Status') }}:</strong> {{ translate($latestClaim->status) }}</p>
            <p><strong>{{ translate('Submitted On') }}:</strong> {{ $latestClaim->submitted_at ? $latestClaim->submitted_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
            @if ($latestClaim->response_due)
            <p><strong>{{ translate('Expected Response') }}:</strong> {{ $latestClaim->response_due->format('Y-m-d H:i:s') }}</p>
            @endif
        </div>
    </div>
    @endif

    @if ($warranty->isActive() && !$openClaim)
    <a href="{{ route('warranty.claim.create', $warranty->warranty_public_id) }}" class="btn btn--primary mb-4">
        {{ translate('start_claim_process') }}
    </a>
    @elseif ($openClaim)
    <div class="alert alert-warning mb-4" role="alert">
        {{ translate('There is already an open claim for this warranty.') }}
    </div>
    @endif

    <h3>{{ translate('Activity Log') }}</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{ translate('Event Type') }}</th>
                <th>{{ translate('Description') }}</th>
                <th>{{ translate('Timestamp') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($timelineEvents as $event)
            <tr>
                <td>{{ translate($event->event_type) }}</td>
                <td>{{ $event->description }}</td>
                <td>{{ $event->timestamp->format('Y-m-d H:i:s') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">{{ translate('No activity yet') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $timelineEvents->links() }}
</div>
@endsection
