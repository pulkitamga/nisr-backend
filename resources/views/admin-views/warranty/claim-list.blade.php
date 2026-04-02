{{-- resources/views/admin-views/warranty/claim-list.blade.php --}}
@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@php
    $pageTitleKey = $pageTitleKey ?? 'claims_list';
    $isStatusLocked = !request()->routeIs('admin.warranty.claim.all') && $pageTitleKey !== 'claims_list';
    $selectedStatus = $isStatusLocked ? $pageTitleKey : request('status', 'all');
    $statusOptions = [
        'all' => translate('all'),
        'new' => translate('new'),
        'triage_pending' => translate('triage_pending'),
        'approved' => translate('approved'),
        'rma_issued' => translate('rma_issued'),
        'received' => translate('received'),
        'repair_pending' => translate('repair_pending'),
        'replacement_pending' => translate('replacement_pending'),
        'diagnosis_pending' => translate('diagnosis_pending'),
        'qc_pending' => translate('qc_pending'),
        'shipped_ready' => translate('shipped_ready'),
        'dispatched' => translate('dispatched'),
        'resolved' => translate('resolved'),
        'closed' => translate('closed'),
        'rejected' => translate('rejected'),
        'waiting_customer' => translate('waiting_customer'),
        'waiting_parts' => translate('waiting_parts'),
        'waiting_payment' => translate('waiting_payment'),
    ];
    $toolbarFields = [
        [
            'type' => 'daterange',
            'name' => 'fhilter_date',
            'label' => translate('Select_Date'),
            'value' => request('fhilter_date'),
            'placeholder' => translate('Select_Date'),
            'autocomplete' => 'off',
            'input_class' => 'js-daterangepicker-with-range form-control cursor-pointer',
            'attributes' => ['readonly' => 'readonly'],
        ],
    ];

    if ($isStatusLocked) {
        $toolbarFields[] = [
            'type' => 'hidden',
            'name' => 'status',
            'value' => $selectedStatus,
            'col_class' => 'd-none',
        ];
    } else {
        $toolbarFields[] = [
            'type' => 'select',
            'name' => 'status',
            'label' => translate('Status'),
            'value' => $selectedStatus,
            'options' => $statusOptions,
            'input_class' => 'form-control js-select2-custom set-filter',
        ];
    }

    $toolbarFields[] = [
        'type' => 'number',
        'name' => 'choose_first',
        'label' => translate('Rows_to_show'),
        'value' => request('choose_first'),
        'placeholder' => translate('Ex') . ' : 200',
        'attributes' => ['min' => '1'],
    ];
    $toolbarFields[] = [
        'type' => 'search',
        'name' => 'searchValue',
        'label' => translate('search'),
        'value' => request('searchValue'),
        'placeholder' => translate('search_by_claim_or_serial'),
        'aria_label' => translate('search_by_claim_or_serial'),
        'col_class' => 'col-xl-4 col-lg-12',
    ];

    $toolbarSummary = [
        [
            'label' => translate('Status'),
            'value' => $statusOptions[$selectedStatus] ?? translate($selectedStatus),
        ],
    ];

    if (!empty(request('fhilter_date'))) {
        $toolbarSummary[] = [
            'label' => translate('Select_Date'),
            'value' => Str::limit(request('fhilter_date'), 28),
            'muted' => true,
        ];
    }

    if (request()->filled('searchValue')) {
        $toolbarSummary[] = [
            'label' => translate('search'),
            'value' => Str::limit(request('searchValue'), 28),
            'muted' => true,
        ];
    }

    if (request()->filled('choose_first')) {
        $toolbarSummary[] = [
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'muted' => true,
        ];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.warranty.claim.export'),
            'form_id' => 'warranty-claim-toolbar',
            'label' => translate('export'),
        ],
    ];
    $toolbarResetUrl = $isStatusLocked ? url()->current() : route('admin.warranty.claim.all');
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

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-claim-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => $toolbarResetUrl,
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate($pageTitleKey),
            'listHeaderTotal' => $claims->total(),
            'listHeaderActions' => $headerActions,
        ])

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
                        <td><span class="bidi-ltr d-inline-block">{{ $claim->submitted_at->format('Y-m-d H:i A') }}</span></td>
                        <td><span class="bidi-ltr d-inline-block">{{ $claim->resolution_due?->format('Y-m-d H:i A') ?? '-' }}</span></td>
                        <td class="text-center">
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.warranty.claim.view', $claim->id) }}"
                                        class="btn btn-sm btn-outline-info">{{ translate('view') }}</a>
                                </div>

                                @if(!in_array($claim->status, ['closed']))
                                    <div class="dropdown crm-row-actions__menu">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button"
                                            id="claim-row-actions-{{ $claim->id }}" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                            <i class="tio-more-horizontal"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="claim-row-actions-{{ $claim->id }}">
                                            <button class="dropdown-item text-danger" data-toggle="modal"
                                                data-url="{{ route('admin.warranty.claim.close', $claim->id) }}"
                                                data-target="#closeModal">
                                                {{ translate('Close') }}
                                            </button>
                                        </div>
                                    </div>
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
@include('admin-views.warranty.partials._claim-js-config')


@endsection
@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/warranty-claims.js') }}"></script>
@endpush
