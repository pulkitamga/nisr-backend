@extends('layouts.back-end.app')
@section('title', translate('Claim View'))

@push('css_or_js')
<style>
    .kv-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: .35rem;
        margin-bottom: .75rem;
        direction: ltr;
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
        justify-content: flex-end;
        text-align: right;
    }
    .claim-ltr .kv-row {
        justify-content: flex-start;
        text-align: left;
    }
</style>
@endpush

@section('content')
@php
    $isRtl = Session::get('direction') === 'rtl';
    $totalCharges = (float)$claim->charges->sum('amount');
    $paidChargesTotal = (float)$claim->charges->where('is_paid', true)->sum('amount');
    $outstandingCharges = max(0, $totalCharges - $paidChargesTotal);
    $hasUnpaidCharges = $claim->charges->where('is_paid', false)->count() > 0;
@endphp
<div class="content container-fluid {{ $isRtl ? 'claim-rtl' : 'claim-ltr' }}">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5><span class="bidi-ltr">{{ $claim->claim_number }}</span> - <span class="bidi-auto">{{ translate($claim->status) }}</span></h5>
            <a href="{{ route('admin.warranty.claim.all') }}" class="btn btn-sm btn-secondary">
                {{ translate('Back') }}
            </a>
        </div>

        <div class="card-body mb-4">
            <div class="row mb-4">
                <div class="col-md-6">
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
                <div class="col-md-6">
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
            <div class="row">
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
                <table class="table table-sm table-bordered">
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
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $payment->amount)) }}</td>
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

            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-sm btn-secondary" id="prevAtt">{{ translate('Previous') }}</button>
                <button type="button" class="btn btn-sm btn-secondary" id="nextAtt">{{ translate('Next') }}</button>
            </div>
            @endif
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body mb-4">
            <div class="d-flex justify-content-between mb-4">
                <h6 class="mt-4">{{ translate('activity_log') }}</h6>
                <div class="mt-4 d-flex flex-wrap gap-2">

                    @if($claim->status === 'new')
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.decide', $claim->id) }}" data-target="#decideModal">
                        {{ translate('Decide') }}
                    </button>
                    @endif

                    @if($claim->status === 'approved')
                    <button class="btn btn-info btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.issue-rma', $claim->id) }}" data-target="#issueRmaModal">
                        {{ translate('Issue RMA') }}
                    </button>
                    @endif

                    @if($claim->status === 'rma_issued')
                    <button class="btn btn-info btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.receive', $claim->id) }}" data-target="#receiveModal">
                        {{ translate('receive') }}
                    </button>
                    @endif
                    @if(in_array($claim->status, ['received', 'diagnosis_pending']))
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.diagnose', $claim->id) }}" data-target="#diagnoseModal">
                        {{ translate('Diagnose') }}
                    </button>
                    @endif
                    @if($claim->status === 'repair_pending')
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.repair-complete', $claim->id) }}" data-target="#repairCompleteModal">
                        {{ translate('Complete Repair') }}
                    </button>
                    @endif

                    @if($claim->status === 'replacement_pending')
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.replacement-commit', $claim->id) }}" data-target="#replacementCommitModal">
                        {{ translate('Commit Replacement') }}
                    </button>
                    @endif

                    @if($claim->status === 'qc_pending')
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.qc-pass', $claim->id) }}" data-target="#qcPassModal">
                        {{ translate('QC Pass') }}
                    </button>
                    @endif
                    @if($claim->status === 'shipped_ready')
                    <button class="btn btn-info btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.dispatch', $claim->id) }}" data-target="#dispatchModal">
                        {{ translate('Dispatch') }}
                    </button>
                    @endif

                    @if($claim->status === 'dispatched')
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.resolve', $claim->id) }}" data-target="#resolveModal">
                        {{ translate('Mark Resolved') }}
                    </button>
                    @endif

                    @if($claim->status === 'resolved')
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.close', $claim->id) }}" data-target="#closeModal">
                        {{ translate('Close Claim') }}
                    </button>
                    @endif

                    @if(in_array($claim->status, ['waiting_customer', 'waiting_parts', 'waiting_payment']))
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.resume', $claim->id) }}" data-target="#resumeClaimModal">
                        {{ translate('Resume / Continue') }}
                    </button>
                    @endif
                    @if($hasUnpaidCharges && !in_array($claim->status, ['closed', 'rejected']))
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.payment-handle', $claim->id) }}" data-target="#paymentHandlingModal">
                        {{ translate('Handle Payment') }}
                    </button>
                    @endif

                    @if(!in_array($claim->status, ['closed']))
                    <button class="btn btn-dark btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.close', $claim->id) }}" data-target="#closeModal">
                        {{ translate('Close') }}
                    </button>
                    @endif
                </div>

            </div>
            <div class="table-responsive datatable-custom">

                <table
                    style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
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
                            <td>{{ translate($e->event_type) }}</td>
                            <td>{{ $e->description }}</td>
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


@endsection
@push('script')
<script>
    const claimWorkflowI18n = {
        processing: @json(translate('Processing...')),
        success: @json(translate('Success!')),
        error: @json(translate('Something went wrong.'))
    };

    $(document).on('click', '[data-toggle="modal"]', function() {
        const button = $(this);
        const url = button.data('url');
        const modalId = button.data('target');
        const form = $(modalId).find('form');

        if (url) {
            form.attr('action', url);
        }
    });

    $(document).on('submit', '.claim-modal-form', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type=submit]');
        const originalLabel = btn.html();
        btn.prop('disabled', true).html('<i class="tio-loading"></i> ' + claimWorkflowI18n.processing);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: function(res) {
                const successMessage = res?.payment_link
                    ? `${res.message || claimWorkflowI18n.success} ${res.payment_link}`
                    : (res?.message || claimWorkflowI18n.success);
                toastr.success(successMessage);
                location.reload();
            },
            error: function(xhr) {
                const validationErrors = xhr.responseJSON?.errors || {};
                const firstValidationError = Object.values(validationErrors)[0]?.[0];
                toastr.error(xhr.responseJSON?.message || firstValidationError || claimWorkflowI18n.error);
                btn.prop('disabled', false).html(originalLabel);
            }
        });
    });
</script>
@endpush
