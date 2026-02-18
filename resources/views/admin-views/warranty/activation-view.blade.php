@extends('layouts.back-end.app')
@section('title', translate('Warranty Details') . ' - ' . $warranty->serial_number)

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">
                    <span class="text-uppercase">{{ $warranty->serial_number }}</span>
                    <span class="badge badge-soft-{{ $warranty->status == 'active' ? 'success' : ($warranty->status == 'expired' ? 'danger' : 'warning') }} ml-2">
                        {{ ucfirst($warranty->statusLabel()) }}
                    </span>
                </h1>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('admin.warranty.activation.list') }}" class="btn btn-light">
                    <i class="tio-arrow-backward"></i> {{ translate('Back to List') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Warranty Info -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="tio-verified"></i> {{ translate('Warranty Information') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled list-unstyled-py-3">
                        <li><strong>{{ translate('Product') }}:</strong> {{ $warranty->product?->name ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Warranty Duration') }}:</strong> {{ $warranty->warranty_months }} {{ translate('months') }}</li>
                        <li><strong>{{ translate('Start Date') }}:</strong> {{ $warranty->start_date?->format('d M, Y') ?? 'Not Started' }}</li>
                        <li><strong>{{ translate('End Date') }}:</strong> {{ $warranty->end_date?->format('d M, Y') ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Remaining Days') }}:</strong>
                            <span class="text-{{ $warranty->remaining_days > 30 ? 'success' : 'warning' }}">
                                {{ $warranty->remaining_days }} {{ translate('days') }}
                            </span>
                        </li>
                        <li><strong>{{ translate('Activation Method') }}:</strong>
                            <span class="badge badge-soft-info">{{ ucfirst(str_replace('_', ' ', $warranty->activation_method)) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="tio-user"></i> {{ translate('Customer Details') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled list-unstyled-py-3">
                        <li><strong>{{ translate('Name') }}:</strong> {{ $warranty->user?->f_name ?? $warranty->activated_by_name ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Email') }}:</strong> {{ $warranty->user?->email ?? $warranty->activated_by_email ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Phone') }}:</strong> {{ $warranty->user?->phone ?? $warranty->activated_by_phone ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Activated IP') }}:</strong> {{ $warranty->activated_ip ?? 'N/A' }}</li>
                        <li><strong>{{ translate('Purchase Date') }}:</strong> {{ $warranty->purchase_date?->format('d M, Y') ?? 'N/A' }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Replacement Info -->
        @if($warranty->originalWarranty || $warranty->replacements->isNotEmpty())
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="tio-refresh"></i> {{ translate('Replacement History') }}</h5>
                </div>
                <div class="card-body">
                    @if($warranty->originalWarranty)
                    <div class="alert alert-warning soft">
                        <strong>{{ translate('This is a replacement of') }}:</strong><br>
                        <a href="{{ route('admin.warranty.activation.view', $warranty->originalWarranty->id) }}">
                            {{ $warranty->originalWarranty->serial_number }}
                        </a>
                    </div>
                    @endif

                    @foreach($warranty->replacements as $replacement)
                    <div class="alert soft mb-2">
                        <strong>{{ translate('Replaced With') }}:</strong><br>
                        <a href="{{ route('admin.warranty.activation.view', $replacement->newWarranty->id) }}">
                            {{ $replacement->newWarranty->serial_number }}
                        </a><br>
                        <small>
                            {{ translate('On') }}: {{ $replacement->replaced_at->format('d M, Y h:i A') }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Timeline -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="tio-history"></i> {{ translate('Activity Timeline') }}
                <span class="badge badge-soft-dark ml-2">{{ $warranty->timelineEvents->count() }}</span>
            </h5>
            <small class="text-muted">
                <i class="tio-info-outlined" data-bs-toggle="tooltip" title="All activation, replacement, manual override events"></i>
            </small>
        </div>
        <div class="card-body p-0">
            @if($warranty->timelineEvents->isEmpty())
            <div class="text-center py-5">
                <img src="{{ dynamicAsset('public/assets/back-end/img/empty/timeline.svg') }}" alt="No Activity" class="w-100px mb-3">
                <p class="text-muted">{{ translate('No activity recorded yet.') }}</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('Date & Time') }}</th>
                            <th>{{ translate('Event') }}</th>
                            <th>{{ translate('Description') }}</th>
                            <th>{{ translate('By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranty->timelineEvents->sortByDesc('timestamp') as $event)
                        @php
                        $eventType = $event->event_type;
                        $badgeClass = match($eventType) {
                        'manual_activated', 'activation_approved' => 'success',
                        'activation_rejected', 'blacklisted' => 'danger',
                        'replaced', 'replacement_issued' => 'info',
                        'manual_override', 'status_changed' => 'warning',
                        default => 'primary'
                        };
                        $icon = match($eventType) {
                        'manual_activated' => 'tio-checkmark-circle',
                        'replaced' => 'tio-refresh',
                        'activation_approved' => 'tio-verified',
                        'activation_rejected' => 'tio-clear',
                        'blacklisted' => 'tio-block',
                        default => 'tio-info-outlined'
                        };
                        @endphp
                        <tr>
                            <td>
                                <div class="text-nowrap">
                                    <strong>{{ $event->timestamp->format('d M, Y') }}</strong><br>
                                    <small class="text-muted">{{ $event->timestamp->format('h:i A') }}</small>
                                    <div class="text-success small">{{ $event->timestamp->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $badgeClass }} badge-pill">
                                    <i class="{{ $icon }} mr-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $eventType)) }}
                                </span>
                            </td>
                            <td>
                                @if($event->description)
                                <div class="text-wrap" style="max-width: 300px;">
                                    {{ Str::limit($event->description, 80) }}
                                    @if(strlen($event->description) > 80)
                                    <a href="javascript:" class="text-primary small" onclick="showFullDescription('{{ addslashes($event->description) }}')">
                                        {{ translate('Show more') }}
                                    </a>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($event->user)
                                    <div class="avatar avatar-circle avatar-sm">
                                        <img src="{{ $event->user->image ? dynamicAsset('storage/admin/'.$event->user->image) : dynamicAsset('public/assets/back-end/img/placeholder.jpg') }}"
                                            alt="{{ $event->user->name }}" class="avatar-img">
                                    </div>
                                    @endif
                                    <div>
                                        <strong>{{ $event->user?->name ?? 'User' }}</strong><br>
                                    </div>
                                </div>
                            </td>
                           
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('css_or_js')
<style>
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6ef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: -20px;
        width: 12px;
        height: 12px;
        background: #377dff;
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 3px #377dff;
    }

    .timeline-content {
        background: #f8f9fc;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #377dff;
    }
</style>
@endpush