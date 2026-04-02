@extends('layouts.back-end.app')
@section('title', translate('Claim View'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<style>
    .claim-rtl {
        direction: rtl;
        text-align: right;
    }
    .kv-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: .35rem;
        margin-bottom: .75rem;
        direction: inherit;
    }
    .kv-label {
        font-weight: 600;
        color: #2c3e50;
    }
    .kv-sep {
        font-weight: 600;
    }
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }
    .claim-rtl .kv-row {
        direction: rtl;
        justify-content: flex-start !important;
        text-align: right !important;
    }
    .claim-rtl .table th,
    .claim-rtl .table td,
    .claim-rtl h6,
    .claim-rtl .text-muted {
        text-align: right !important;
    }
    .claim-rtl .claim-overview-row,
    .claim-rtl .claim-summary-row,
    .claim-rtl .claim-activity-header {
        flex-direction: row-reverse !important;
    }
    .claim-rtl .claim-overview-row > [class*="col-"],
    .claim-rtl .claim-summary-row > [class*="col-"],
    .claim-rtl .claim-overview-row p,
    .claim-rtl .claim-overview-row div {
        text-align: right !important;
    }
    .claim-actions .crm-row-actions {
        justify-content: flex-start;
    }
    .claim-rtl .claim-actions .crm-row-actions {
        justify-content: flex-end;
    }
    .claim-rtl .attachments-nav {
        direction: rtl;
    }
    .claim-ltr .kv-row {
        direction: ltr;
        justify-content: flex-start;
        text-align: left;
    }
</style>
@endpush

@section('content')
@php
    $isRtl = Session::get('direction') === 'rtl';
    $totalCharges = (float)$claim->charges->sum('amount');
    $paidChargesTotal = (float)$claim->payments->where('payment_status', 'paid')->sum('amount');
    $outstandingCharges = (float)$claim->charges->where('is_paid', false)->sum('amount');
    $hasUnpaidCharges = $claim->charges->where('is_paid', false)->count() > 0;
    $buildClaimAction = function (string $label, string $routeName, string $modalId, string $buttonClass = 'btn-outline-secondary', string $menuClass = '') use ($claim) {
        return [
            'label' => translate($label),
            'url' => route($routeName, $claim->id),
            'target' => $modalId,
            'buttonClass' => $buttonClass,
            'menuClass' => $menuClass,
        ];
    };

    $primaryAction = null;
    $secondaryActions = [];
    $resumeStatuses = ['waiting_customer', 'waiting_parts', 'waiting_payment'];

    if (in_array($claim->status, ['new', 'triage_pending'], true)) {
        $primaryAction = $buildClaimAction('Decide', 'admin.warranty.claim.decide', '#decideModal', 'btn-primary');
    } elseif ($claim->status === 'approved') {
        $primaryAction = $buildClaimAction('Issue RMA', 'admin.warranty.claim.issue-rma', '#issueRmaModal', 'btn-info');
    } elseif ($claim->status === 'rma_issued') {
        $primaryAction = $buildClaimAction('receive', 'admin.warranty.claim.receive', '#receiveModal', 'btn-info');
    } elseif (in_array($claim->status, ['received', 'diagnosis_pending'], true)) {
        $primaryAction = $buildClaimAction('Diagnose', 'admin.warranty.claim.diagnose', '#diagnoseModal', 'btn-warning');
    } elseif ($claim->status === 'repair_pending') {
        $primaryAction = $buildClaimAction('Complete Repair', 'admin.warranty.claim.repair-complete', '#repairCompleteModal', 'btn-success');
    } elseif ($claim->status === 'replacement_pending') {
        $primaryAction = $buildClaimAction('Commit Replacement', 'admin.warranty.claim.replacement-commit', '#replacementCommitModal', 'btn-warning');
    } elseif ($claim->status === 'qc_pending') {
        $primaryAction = $buildClaimAction('QC Pass', 'admin.warranty.claim.qc-pass', '#qcPassModal', 'btn-primary');
    } elseif ($claim->status === 'shipped_ready') {
        $primaryAction = $buildClaimAction('Dispatch', 'admin.warranty.claim.dispatch', '#dispatchModal', 'btn-info');
    } elseif ($claim->status === 'dispatched') {
        $primaryAction = $buildClaimAction('Mark Resolved', 'admin.warranty.claim.resolve', '#resolveModal', 'btn-success');
    } elseif ($claim->status === 'resolved') {
        $primaryAction = $buildClaimAction('Close Claim', 'admin.warranty.claim.close', '#closeModal', 'btn-danger');
    } elseif ($claim->status === 'waiting_payment' && $hasUnpaidCharges) {
        $primaryAction = $buildClaimAction('Handle Payment', 'admin.warranty.claim.payment-handle', '#paymentHandlingModal', 'btn-warning');
    } elseif (in_array($claim->status, $resumeStatuses, true)) {
        $primaryAction = $buildClaimAction('Resume / Continue', 'admin.warranty.claim.resume', '#resumeClaimModal', 'btn-success');
    }

    if (in_array($claim->status, $resumeStatuses, true) && ($primaryAction['target'] ?? null) !== '#resumeClaimModal') {
        $secondaryActions[] = $buildClaimAction('Resume / Continue', 'admin.warranty.claim.resume', '#resumeClaimModal');
    }

    if ($hasUnpaidCharges && !in_array($claim->status, ['closed', 'rejected'], true) && ($primaryAction['target'] ?? null) !== '#paymentHandlingModal') {
        $secondaryActions[] = $buildClaimAction('Handle Payment', 'admin.warranty.claim.payment-handle', '#paymentHandlingModal', 'btn-outline-warning', 'text-warning');
    }

    if (!in_array($claim->status, ['closed'], true) && ($primaryAction['target'] ?? null) !== '#closeModal') {
        $secondaryActions[] = $buildClaimAction('Close Claim', 'admin.warranty.claim.close', '#closeModal', 'btn-outline-danger', 'text-danger');
    }
@endphp
<div class="content container-fluid {{ $isRtl ? 'claim-rtl' : 'claim-ltr' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5><span class="bidi-ltr">{{ $claim->claim_number }}</span> - <span class="bidi-auto">{{ translate($claim->status) }}</span></h5>
            <a href="{{ route('admin.warranty.claim.all') }}" class="btn btn-sm btn-secondary">
                {{ translate('Back') }}
            </a>
        </div>

        <div class="card-body mb-4 {{ $isRtl ? 'text-end' : 'text-start' }}">
            <div class="row mb-4 claim-overview-row {{ $isRtl ? 'flex-row-reverse' : '' }}">
                <div class="col-md-6 {{ $isRtl ? 'text-end' : 'text-start' }}">
                    <h6 class="font-weight-bold">{{ translate('Warranty') }}</h6>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('serial') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{ $claim->warranty->serial_number }}</span>
                    </p>
                    <!-- <p><strong>{{ translate('Product') }}:</strong> {{ $claim->warranty->product->name ?? 'N/A' }}</p> -->
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('Customer') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-auto">{{$claim->warranty->user->f_name ?? $claim->warranty->activated_by_name}}</span>
                    </p>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('Phone') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{$claim->warranty->user->phone ?? $claim->warranty->activated_by_phone}}</span>
                    </p>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('Email') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{$claim->warranty->user->email ?? $claim->warranty->activated_by_email}}</span>
                    </p>
                </div>
                <div class="col-md-6 {{ $isRtl ? 'text-end' : 'text-start' }}">
                    <h6 class="font-weight-bold">{{ translate('Claim') }}</h6>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('Submitted') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{ $claim->submitted_at->format('Y-m-d H:i A') }}</span>
                    </p>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('Description') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-auto">{{ $claim->description }}</span>
                    </p>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('RMA') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{ $claim->rma_number ?? '—' }}</span>
                    </p>
                    <p class="kv-row">
                        <span class="kv-label">{{ translate('RMA Deadline') }}</span>
                        <span class="kv-sep">:</span>
                        <span class="kv-value bidi-ltr">{{ $claim->rma_deadline?->format('Y-m-d') ?? '—' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body mb-4">
            <div class="row claim-summary-row {{ $isRtl ? 'flex-row-reverse' : '' }}">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="p-3 border rounded">
                        <div class="text-muted">{{ translate('Total Charges') }}</div>
                        <div class="h5 mb-0">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $totalCharges)) }}</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="p-3 border rounded">
                        <div class="text-muted">{{ translate('Paid Amount') }}</div>
                        <div class="h5 mb-0 text-success">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $paidChargesTotal)) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded">
                        <div class="text-muted">{{ translate('Outstanding Amount') }}</div>
                        <div class="h5 mb-0 text-danger">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $outstandingCharges)) }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <h6 class="font-weight-bold mb-3">{{ translate('Payment Records') }}</h6>
                <table class="table table-sm table-bordered text-start"
                    style="text-align: start;">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('Channel') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Reference') }}</th>
                            <th>{{ translate('Payment Link') }}</th>
                            <th>{{ translate('Paid At') }}</th>
                            <th>{{ translate('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claim->payments->sortByDesc('id') as $payment)
                        <tr>
                            <td>{{ translate($payment->payment_channel) }}</td>
                            <td>{{ translate($payment->payment_status) }}</td>
                            @php
                                $paymentAmount = ($payment->payment_channel === 'client_reject_payment')
                                    ? 0
                                    : (float)$payment->amount;
                            @endphp
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $paymentAmount)) }}</td>
                            <td><span class="bidi-ltr">{{ $payment->payment_reference ?? '—' }}</span></td>
                            <td>
                                @if($payment->payment_link)
                                    <a href="{{ $payment->payment_link }}" target="_blank">{{ translate('Open Link') }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="bidi-ltr">{{ $payment->paid_at?->format('Y-m-d H:i A') ?? '—' }}</span></td>
                            <td><span class="bidi-ltr">{{ $payment->created_at?->format('Y-m-d H:i A') ?? '—' }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ translate('No payment records found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($claim->attachments->count())
            <h6 class="mt-4">{{ translate('Attachments') }}</h6>

            @php
            $attachments = $claim->attachments->chunk(6); // 6 per slide
            @endphp

            <div id="attachments-slider">
                @foreach($attachments as $index => $chunk)
                <div class="attachment-slide" style="{{ $index == 0 ? '' : 'display:none' }}">
                    <div class="row">
                        @foreach($chunk as $a)
                        <div class="col-md-3 mb-3">
                            <a href="{{ asset('storage/' . $a->file_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $a->file_path) }}" class="img-fluid img-thumbnail" style="max-height:120px; object-fit: cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between mt-2 attachments-nav">
                <button type="button" class="btn btn-sm btn-secondary" id="prevAtt">{{ translate('Previous') }}</button>
                <button type="button" class="btn btn-sm btn-secondary" id="nextAtt">{{ translate('Next') }}</button>
            </div>
            @endif
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body mb-4">
            <div class="d-flex justify-content-between mb-4 claim-activity-header {{ $isRtl ? 'flex-row-reverse' : '' }}">
                <h6 class="mt-4">{{ translate('activity_log') }}</h6>
                <div class="mt-4 claim-actions">
                    <div class="crm-row-actions">
                        @if($hasUnpaidCharges)
                            <div class="crm-row-actions__chips">
                                <span class="crm-row-actions__chip">
                                    {{ translate('Outstanding Amount') }}:
                                    <span class="bidi-ltr ms-1">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $outstandingCharges)) }}</span>
                                </span>
                            </div>
                        @endif

                        @if($primaryAction)
                            <div class="crm-row-actions__primary">
                                <button class="btn btn-sm {{ $primaryAction['buttonClass'] }}" data-toggle="modal"
                                    data-url="{{ $primaryAction['url'] }}" data-target="{{ $primaryAction['target'] }}">
                                    {{ $primaryAction['label'] }}
                                </button>
                            </div>
                        @endif

                        @if(!empty($secondaryActions))
                            <div class="dropdown crm-row-actions__menu">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button"
                                    id="claim-detail-actions-{{ $claim->id }}" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                    <i class="tio-more-horizontal"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="claim-detail-actions-{{ $claim->id }}">
                                    @foreach($secondaryActions as $action)
                                        <button class="dropdown-item {{ $action['menuClass'] }}" data-toggle="modal"
                                            data-url="{{ $action['url'] }}" data-target="{{ $action['target'] }}">
                                            {{ $action['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            <div class="table-responsive datatable-custom">

                <table

                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Description') }}</th>
                            <th>{{ translate('Time') }}</th>
                            <th>{{ translate('User') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timeline as $e)
                        <tr>
                            <td>{{ $e->translated_event_type ?? translate($e->event_type) }}</td>
                            <td>{{ $e->translated_description ?? $e->description }}</td>
                            <td><span class="bidi-ltr">{{ $e->timestamp->format('Y-m-d H:i A') }}</span></td>
                            <td>{{ $e->user?->name ?? translate('Admin') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ translate('No activity') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end"> {{ $timeline->links() }}
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin-views.warranty.modals.decide')
@include('admin-views.warranty.modals.receive')
@include('admin-views.warranty.modals.diagnose')
@include('admin-views.warranty.modals.repair-complete')
@include('admin-views.warranty.modals.qc-pass')
@include('admin-views.warranty.modals.dispatch')
@include('admin-views.warranty.modals.replacement-commit')
@include('admin-views.warranty.modals.close')
@include('admin-views.warranty.modals.payment-handling')
@include('admin-views.warranty.modals.issue-rma')
@include('admin-views.warranty.modals.resume-claim')
@include('admin-views.warranty.modals.resolve')
@include('partials.serial-scanner-assets')
@include('admin-views.warranty.partials._claim-js-config')


@endsection
@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/warranty-claims.js') }}"></script>
@endpush
