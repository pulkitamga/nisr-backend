@php
    use Illuminate\Support\Str;

    $dateTypeOptions = [
        '' => translate('select_Date_Type'),
        'this_week' => translate('this_Week'),
        'this_month' => translate('this_Month'),
        'this_year' => translate('this_Year'),
        'custom_date' => translate('custom_Date'),
    ];

    $toolbarFields = [
        [
            'type' => 'select',
            'name' => 'from_branch',
            'label' => translate('From_branch'),
            'value' => request('from_branch', ''),
            'options' => ['' => translate('All')] + $branches->all(),
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'select',
            'name' => 'to_branch',
            'label' => translate('to_branch'),
            'value' => request('to_branch', ''),
            'options' => ['' => translate('All')] + $branches->all(),
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'select',
            'name' => 'transfer_type',
            'label' => translate('transfer_type'),
            'value' => request('transfer_type', ''),
            'options' => ['' => translate('All')] + $types,
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'select',
            'name' => 'date_type',
            'label' => translate('date_type'),
            'value' => request('date_type', ''),
            'options' => $dateTypeOptions,
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'date',
            'name' => 'from',
            'label' => translate('Start_Date'),
            'value' => request('from'),
        ],
        [
            'type' => 'date',
            'name' => 'to',
            'label' => translate('End_Date'),
            'value' => request('to'),
        ],
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'search',
            'label' => translate('Search'),
            'value' => request('search'),
            'placeholder' => translate('search_by_serial_no'),
            'aria_label' => translate('search_by_serial_no'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [];

    if (request()->filled('from_branch')) {
        $toolbarSummary[] = [
            'label' => translate('From_branch'),
            'value' => $branches->get((int) request('from_branch')) ?? request('from_branch'),
        ];
    }

    if (request()->filled('to_branch')) {
        $toolbarSummary[] = [
            'label' => translate('to_branch'),
            'value' => $branches->get((int) request('to_branch')) ?? request('to_branch'),
        ];
    }

    if (request()->filled('transfer_type')) {
        $toolbarSummary[] = [
            'label' => translate('transfer_type'),
            'value' => $types[(string) request('transfer_type')] ?? request('transfer_type'),
        ];
    }

    if (request()->filled('date_type')) {
        $toolbarSummary[] = [
            'label' => translate('date_type'),
            'value' => $dateTypeOptions[(string) request('date_type')] ?? request('date_type'),
        ];
    }

    if (request()->filled('from') || request()->filled('to')) {
        $toolbarSummary[] = [
            'label' => translate('DATE'),
            'value' => trim(implode(' - ', array_filter([request('from'), request('to')]))),
            'muted' => true,
        ];
    }

    if (request()->filled('search')) {
        $toolbarSummary[] = [
            'label' => translate('Search'),
            'value' => Str::limit(request('search'), 28),
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
            'url' => route('admin.warranty.serial-transaction.export'),
            'form_id' => 'warranty-transaction-toolbar',
            'label' => translate('export'),
        ],
    ];
@endphp

@extends('layouts.back-end.app')
@section('title', translate('Serial_Transaction_History'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/warranty-transactions.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/serial.png') }}" class="mb-1 me-1" alt="">
            <span class="page-header-title">{{ translate('Serial_Transaction_History') }}</span>
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $transactions->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-transaction-toolbar',
        'toolbarAction' => route('admin.warranty.serial-transaction.list'),
        'toolbarResetUrl' => route('admin.warranty.serial-transaction.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('transaction_list'),
            'listHeaderTotal' => $transactions->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('From') }}</th>
                            <th>{{ translate('To') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('DATE') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            @php
                                $transferTypeLabel = $types[$transaction->transfer_type] ?? $transaction->transfer_type;
                            @endphp
                            <tr>
                                <td><strong>{{ $transaction->serial_number }}</strong></td>
                                <td>{{ $transaction->fromBranch->branch_name ?? translate('not_available') }}</td>
                                <td>
                                    @if($transaction->to_branch_id)
                                        {{ $transaction->toBranch->branch_name ?? translate('not_available') }}
                                    @elseif($transaction->distributor_id)
                                        {{ $transaction->distributor->company_name ?? translate('not_available') }}
                                        <span class="badge badge-soft-success ms-1">{{ translate('Wholesaler') }}</span>
                                    @else
                                        {{ translate('not_available') }}
                                    @endif
                                </td>
                                <td><span class="badge badge-soft-info">{{ $transferTypeLabel }}</span></td>
                                <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($transaction->transferred_at)->format('d M Y, h:i A') }}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary view-history-btn"
                                        data-serial="{{ $transaction->serial_number }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#historyModal">
                                        <i class="tio-visible"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    @include('layouts.back-end._empty-state', ['text' => 'no_transactions_found', 'image' => 'default'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->isNotEmpty())
                <div class="d-flex justify-content-end mt-3 px-4 pb-4">
                    {!! $transactions->links() !!}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1"
    data-history-url-template="{{ route('admin.warranty.serial-transaction.history-modal', '__SERIAL__') }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="tio-history"></i> {{ translate('transaction_history_for') }}: <span id="modalSerial"></span></h5>
                <button type="button" class="btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"><i class="tio-clear"></i></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/warranty-transactions.js') }}"></script>
@endpush
