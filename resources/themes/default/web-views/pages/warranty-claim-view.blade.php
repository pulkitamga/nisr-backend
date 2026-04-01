@extends('layouts.front-end.app')

@section('title', translate('Claim Details'))

@section('content')
<div class="container my-5 text-align-direction">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1">{{ translate('Claim Details') }}</h2>
            <p class="text-muted mb-0">{{ $claimViewData['claim_number'] }}</p>
        </div>
        <a href="{{ route('warranty.view', ['warranty_public_id' => $warranty->warranty_public_id]) }}" class="btn btn-outline-primary">
            {{ translate('Open Warranty') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>{{ translate('Status') }}:</strong> {{ $claimViewData['status'] }}</p>
                    <p><strong>{{ translate('Grouped Status') }}:</strong> {{ $claimViewData['grouped_status'] }}</p>
                    <p><strong>{{ translate('Serial Number') }}:</strong> {{ $claimViewData['serial_number'] }}</p>
                    <p><strong>{{ translate('Submitted On') }}:</strong> {{ $claimViewData['submitted_at']?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>{{ translate('Last Updated') }}:</strong> {{ $claimViewData['updated_at']?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>{{ translate('Product Name') }}:</strong> {{ $warranty->product?->name ?? ($warranty->product_name ?? 'N/A') }}</p>
                    <p><strong>{{ translate('Customer Name') }}:</strong> {{ $warranty->activated_by_name }}</p>
                    <p><strong>{{ translate('Email') }}:</strong> {{ $warranty->activated_by_email }}</p>
                    <p><strong>{{ translate('Phone') }}:</strong> {{ $warranty->activated_by_phone }}</p>
                </div>
            </div>
            @if(!empty($claimViewData['customer_meaning']))
                <div class="alert alert-info mt-3 mb-0" role="alert">
                    {{ $claimViewData['customer_meaning'] }}
                </div>
            @endif
        </div>
    </div>

    @if(!empty($claimViewData['subject']) || !empty($claimViewData['issue']) || !empty($claimViewData['details']))
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-3">{{ translate('Claim Summary') }}</h3>
            @if(!empty($claimViewData['subject']))
                <p><strong>{{ translate('Subject') }}:</strong> {{ $claimViewData['subject'] }}</p>
            @endif
            @if(!empty($claimViewData['issue']))
                <p><strong>{{ translate('Issue') }}:</strong> {{ $claimViewData['issue'] }}</p>
            @endif
            @if(!empty($claimViewData['details']))
                <p class="mb-0"><strong>{{ translate('Details') }}:</strong> {{ $claimViewData['details'] }}</p>
            @endif
        </div>
    </div>
    @endif

    @if($claimViewData['payment'])
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h3 class="mb-0">{{ translate('Payment Summary') }}</h3>
                @if($claimViewData['can_pay'])
                    <a href="{{ $claimViewData['payment']->payment_link }}" class="btn btn--primary">
                        {{ translate('Pay Claim Charges') }}
                    </a>
                @endif
            </div>
            <p><strong>{{ translate('Status') }}:</strong> {{ translate($claimViewData['payment']->payment_status) }}</p>
            <p><strong>{{ translate('Amount') }}:</strong> {{ number_format((float) $claimViewData['payment']->amount, 2) }}</p>
            <p class="mb-0"><strong>{{ translate('Payment Link Expires') }}:</strong> {{ $claimViewData['payment']->payment_link_expires_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
        </div>
    </div>
    @endif

    @if($claimViewData['attachments']->isNotEmpty())
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-3">{{ translate('Attachments') }}</h3>
            <div class="d-flex flex-wrap gap-2">
                @foreach($claimViewData['attachments'] as $attachment)
                    <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        {{ translate('Attachment') }} {{ $attachment['id'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h3 class="mb-3">{{ translate('Timeline') }}</h3>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Event Type') }}</th>
                            <th>{{ translate('Description') }}</th>
                            <th>{{ translate('Timestamp') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claimViewData['timeline_events'] as $event)
                            <tr>
                                <td>{{ translate($event->event_type) }}</td>
                                <td>{{ $event->description }}</td>
                                <td>{{ optional($event->timestamp ?? $event->created_at)?->format('Y-m-d H:i:s') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">{{ translate('No activity yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
