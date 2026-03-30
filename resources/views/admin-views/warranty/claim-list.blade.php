{{-- resources/views/admin-views/warranty/claim-list.blade.php --}}
@extends('layouts.back-end.app')

@php
    $pageTitleKey = $pageTitleKey ?? 'claims_list';
@endphp

@section('title', translate($pageTitleKey))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/warranty.png') }}" alt="">
            {{ translate($pageTitleKey) }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $claims->total() }}</span>
        </h2>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                @csrf
                <div class="row g-3">

                    {{-- Date Range --}}
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Select_Date') }}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right"></span>
                            <input type="text" name="fhilter_date"
                                class="js-daterangepicker-with-range form-control cursor-pointer"
                                value="{{ request('fhilter_date') }}"
                                placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Status') }}</label>
                        <select class="form-control js-select2-custom set-filter" name="status">
                            <option {{ !request()->has('status') ? 'selected' : '' }} disabled>{{ translate('select_status') }}</option>
                            <option {{ request('status') == 'all' ? 'selected' : '' }} value="all">{{ translate('All') }}</option>

                            <option {{ request('status') == 'new' ? 'selected' : '' }} value="new">{{ translate('new') }}</option>
                            <option {{ request('status') == 'triage_pending' ? 'selected' : '' }} value="triage_pending">{{ translate('triage_pending') }}</option>
                            <option {{ request('status') == 'approved' ? 'selected' : '' }} value="approved">{{ translate('approved') }}</option>
                            <option {{ request('status') == 'rma_issued' ? 'selected' : '' }} value="rma_issued">{{ translate('rma_issued') }}</option>
                            <option {{ request('status') == 'received' ? 'selected' : '' }} value="received">{{ translate('received') }}</option>
                            <option {{ request('status') == 'repair_pending' ? 'selected' : '' }} value="repair_pending">{{ translate('repair_pending') }}</option>
                            <option {{ request('status') == 'replacement_pending' ? 'selected' : '' }} value="replacement_pending">{{ translate('replacement_pending') }}</option>
                            <option {{ request('status') == 'diagnosis_pending' ? 'selected' : '' }} value="diagnosis_pending">{{ translate('diagnosis_pending') }}</option>
                            <option {{ request('status') == 'qc_pending' ? 'selected' : '' }} value="qc_pending">{{ translate('qc_pending') }}</option>
                            <option {{ request('status') == 'shipped_ready' ? 'selected' : '' }} value="shipped_ready">{{ translate('shipped_ready') }}</option>
                            <option {{ request('status') == 'dispatched' ? 'selected' : '' }} value="dispatched">{{ translate('dispatched') }}</option>
                            <option {{ request('status') == 'resolved' ? 'selected' : '' }} value="resolved">{{ translate('resolved') }}</option>
                            <option {{ request('status') == 'closed' ? 'selected' : '' }} value="closed">{{ translate('closed') }}</option>
                            <option {{ request('status') == 'rejected' ? 'selected' : '' }} value="rejected">{{ translate('rejected') }}</option>
                            <option {{ request('status') == 'waiting_customer' ? 'selected' : '' }} value="waiting_customer">{{ translate('waiting_customer') }}</option>
                            <option {{ request('status') == 'waiting_parts' ? 'selected' : '' }} value="waiting_parts">{{ translate('waiting_parts') }}</option>
                            <option {{ request('status') == 'waiting_payment' ? 'selected' : '' }} value="waiting_payment">{{ translate('waiting_payment') }}</option>
                        </select>
                    </div>

                    {{-- Choose First (limit) --}}
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Choose_First') }}</label>
                        <input type="number" class="form-control" min="1"
                            value="{{ request('choose_first') ?: '' }}"
                            placeholder="{{ translate('Ex') }} : 200" name="choose_first">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-12">
                        <label class="d-md-block">&nbsp;</label>
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.warranty.claim.all') }}" class="btn btn-secondary px-5">
                                {{ translate('reset') }}
                            </a>
                            <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ==== TABLE CARD ==== --}}
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 me-auto">
                {{ translate($pageTitleKey) }}
                <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $claims->total() }}</span>
            </h5>

            {{-- Search Form (preserves other filters) --}}
            <form action="{{ url()->current() }}" method="GET">
                @foreach(request()->except(['searchValue','page']) as $k=>$v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><i class="tio-search"></i></div>
                    </div>
                    <input type="search" name="searchValue" class="form-control"
                        placeholder="{{ translate('search_by_claim_or_serial') }}"
                        value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                </div>
            </form>

            {{-- Export Button (preserves filters) --}}
            <a href="{{ route('admin.warranty.claim.export', request()->all()) }}"
                class="btn btn-outline--primary text-nowrap">
                <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}" alt="">
                <span class="ps-2">{{ translate('export') }}</span>
            </a>
        </div>

        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100"
                style="text-align: start;">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('claim_number') }}</th>
                        <th>{{ translate('serial') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('customer') }}</th>
                        <th>{{ translate('submitted_at') }}</th>
                        <th>{{ translate('sla_due') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody id="claimTableBody">
                    @foreach($claims as $key => $claim)
                    @php
                    $badge = match($claim->status){
                    'new','waiting_customer','waiting_parts','waiting_payment' => 'warning',
                    'rejected','closed' => 'danger',
                    default => 'success'
                    };
                    @endphp
                    <tr>
                        <td>{{ $claims->firstItem() + $key }}</td>
                        <td>{{ $claim->claim_number }}</td>
                        <td>{{ $claim->serial_number }}</td>
                        <td>
                            <span class="badge badge-soft-{{ $badge }} fz-12">
                                {{ translate($claim->status) }}
                            </span>
                        </td>
                        <td>{{ $claim->warranty->user->name ?? $claim->warranty->activated_by_name }}</td>
                        <td>{{ $claim->submitted_at->format('Y-m-d H:i A') }}</td>
                        <td>{{ $claim->resolution_due?->format('Y-m-d H:i A') ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex flex-wrap gap-1 justify-content-center">

                                {{-- View --}}
                                <a href="{{ route('admin.warranty.claim.view', $claim->id) }}"
                                    class="btn btn-sm btn-outline-info">{{ translate('view') }}</a>


                                @if(!in_array($claim->status, ['closed']))
                                <button class="btn btn-dark btn-sm" data-toggle="modal" data-url="{{ route('admin.warranty.claim.close', $claim->id) }}" data-target="#closeModal">
                                    {{ translate('Close') }}
                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $claims->appends(request()->query())->links() !!}
            </div>
        </div>

        @if($claims->isEmpty())
        @include('layouts.back-end._empty-state', ['text'=>'no_record_found','image'=>'default'])
        @endif
    </div>
</div>


@include('admin-views.warranty.modals.close')


@endsection
@push('script')
<script>
    const claimListI18n = {
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
        btn.prop('disabled', true).html('<i class="tio-loading"></i> ' + claimListI18n.processing);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: function(res) {
                toastr.success(res.message || claimListI18n.success);
                location.reload();
            },
            error: function(xhr) {
                const validationErrors = xhr.responseJSON?.errors || {};
                const firstValidationError = Object.values(validationErrors)[0]?.[0];
                toastr.error(xhr.responseJSON?.message || firstValidationError || claimListI18n.error);
                btn.prop('disabled', false).html(originalLabel);
            }
        });
    });
</script>
@endpush
