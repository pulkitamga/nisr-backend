@extends('layouts.back-end.app')
@section('title', translate('Claim View'))

@section('content')
<div class="content container-fluid">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5>{{ $claim->claim_number }} - {{ translate($claim->status) }}</h5>
            <a href="{{ route('admin.warranty.claim.all') }}" class="btn btn-sm btn-secondary">
                {{ translate('Back') }}
            </a>
        </div>

        <div class="card-body mb-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">{{ translate('Warranty') }}</h6>
                    <p><strong>{{ translate('serial') }}:</strong> {{ $claim->warranty->serial_number }}</p>
                    <!-- <p><strong>{{ translate('Product') }}:</strong> {{ $claim->warranty->product->name ?? 'N/A' }}</p> -->
                    <p><strong>{{ translate('Customer') }}:</strong> {{$claim->warranty->user->f_name ?? $claim->warranty->activated_by_name}}</p>
                    <p><strong>{{ translate('Phone') }}:</strong> {{$claim->warranty->user->phone ?? $claim->warranty->activated_by_phone}}</p>
                    <p><strong>{{ translate('Email') }}:</strong> {{$claim->warranty->user->email ?? $claim->warranty->activated_by_email}}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">{{ translate('Claim') }}</h6>
                    <p><strong>{{ translate('Submitted') }}:</strong> {{ $claim->submitted_at->format('Y-m-d H:i A') }}</p>
                    <p><strong>{{ translate('Description') }}:</strong> {{ $claim->description }}</p>
                    <p><strong>{{ translate('RMA') }}:</strong> {{ $claim->rma_number ?? '—' }}</p>
                    <p><strong>{{ translate('RMA Deadline') }}:</strong> {{ $claim->rma_deadline?->format('Y-m-d') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body mb-4">

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
                <button type="button" class="btn btn-sm btn-secondary" id="prevAtt">Prev</button>
                <button type="button" class="btn btn-sm btn-secondary" id="nextAtt">Next</button>
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
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.decide', $claim->id) }}" data-url="{{ route('admin.warranty.claim.decide', $claim->id) }}" data-target="#decideModal">
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

                    @if($claim->status === 'waiting_payment')
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.payment-handle', $claim->id) }}" data-target="#paymentHandlingModal">
                        {{ translate('Handle Payment') }}
                    </button>
                    @endif
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
                            <td>{{ $e->timestamp->format('Y-m-d H:i A') }}</td>
                            <td>{{ $e->user?->name ?? 'Admin' }}</td>
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
        btn.prop('disabled', true).html('<i class="tio-loading"></i> Processing...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: function(res) {
                toastr.success(res.message || 'Success!');
                location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error!');
                btn.prop('disabled', false).html('Submit');
            }
        });
    });
</script>
@endpush
