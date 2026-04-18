@extends('layouts.back-end.app')

@section('title', translate('Quotation_Sent'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-list.css') }}">
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Quotation_Sent')}}
        </h2>
    </div>

    @php
        $quotationSummary = [];
        if (request()->filled('date_from') || request()->filled('date_to')) {
            $quotationSummary[] = [
                'label' => translate('DATE'),
                'value' => trim((request('date_from') ?: '...') . ' - ' . (request('date_to') ?: '...')),
            ];
        }
        if (request('tier') && request('tier') !== 'all') {
            $quotationSummary[] = [
                'label' => translate('Tier'),
                'value' => request('tier'),
            ];
        }
        if (request('status') && request('status') !== 'all') {
            $quotationSummary[] = [
                'label' => translate('Status'),
                'value' => request('status'),
            ];
        }
        if (request('price_sort')) {
            $quotationSummary[] = [
                'label' => translate('Price'),
                'value' => request('price_sort') === 'low_high' ? translate('Low to High') : translate('High to Low'),
            ];
        }
        if (request()->filled('searchValue')) {
            $quotationSummary[] = [
                'label' => translate('Search'),
                'value' => request('searchValue'),
            ];
        }
    @endphp

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'wholesale-quotation-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.wholesale.business.wholesale.order'),
        'toolbarFields' => [
            [
                'name' => 'date_from',
                'label' => translate('Date From'),
                'type' => 'date',
                'value' => request('date_from'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
            ],
            [
                'name' => 'date_to',
                'label' => translate('Date To'),
                'type' => 'date',
                'value' => request('date_to'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
            ],
            [
                'name' => 'tier',
                'label' => translate('Tier'),
                'type' => 'select',
                'value' => request('tier', 'all'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                'options' => collect($tiers ?? [])
                    ->pluck('name', 'name')
                    ->prepend(translate('All'), 'all')
                    ->all(),
            ],
            [
                'name' => 'status',
                'label' => translate('Status'),
                'type' => 'select',
                'value' => request('status', 'all'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                'options' => [
                    'all' => translate('All'),
                    'sent' => translate('sent'),
                    'accepted' => translate('accepted'),
                    'rejected' => translate('rejected'),
                ],
            ],
            [
                'name' => 'price_sort',
                'label' => translate('Price'),
                'type' => 'select',
                'value' => request('price_sort'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                'options' => [
                    '' => translate('Default'),
                    'low_high' => translate('Low to High'),
                    'high_low' => translate('High to Low'),
                ],
            ],
            [
                'name' => 'choose_first',
                'label' => translate('Rows'),
                'type' => 'number',
                'value' => request('choose_first', 15),
                'attributes' => ['min' => 1],
                'placeholder' => '15',
                'col_class' => 'col-xl-1 col-lg-3 col-md-6',
            ],
            [
                'name' => 'searchValue',
                'label' => translate('Search'),
                'type' => 'search',
                'value' => request('searchValue'),
                'placeholder' => translate('Search...'),
                'aria_label' => translate('Search'),
                'col_class' => 'col-xl-1 col-lg-3 col-md-6',
            ],
        ],
        'toolbarSummary' => $quotationSummary,
    ])

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mt-3">
                @include('admin-views.crm.partials._list-card-header', [
                    'listHeaderTitle' => translate('Quotation_list'),
                    'listHeaderTotal' => $orders->total(),
                    'listHeaderActions' => [
                        [
                            'type' => 'export',
                            'url' => route('admin.wholesale.business.wholesale-quotation.export'),
                            'form_id' => 'wholesale-quotation-toolbar',
                            'label' => translate('export'),
                        ],
                    ],
                ])

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('DATE')}}</th>
                                    <th>{{translate('Order_No')}}</th>
                                    <th>{{translate('Quotation_No')}}</th>
                                    <th>{{translate('Wholesaler')}}</th>
                                    <th>{{translate('Tier')}}</th>
                                    <th class="text-center">{{translate('Status')}}</th>
                                    <th>{{translate('Final_price')}}</th>
                                    <th class="text-center">{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowColor = ['bg-light', 'bg-white']; @endphp

                                @forelse($orders as $key => $order)
                                @php $bgClass = $rowColor[$key % 2]; @endphp
                                <tr class="{{ $bgClass }}">
                                    <td>{{ $orders->firstItem() + $key }}</td>
                                    <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span></td>
                                    <td>{{ $order->purchase_order_no }}</td>
                                    <td>{{ $order->quotation_no }}</td>
                                    <td>
                                        <a class="crm-primary-link" href="{{ route('admin.wholesale.business.orders.invoice', $order->id) }}">
                                            {{ $order->wholeseller->wholesalerBusiness->company_name ?? __('N/A') }}
                                        </a>
                                    </td>
                                    <td>{{ $order->wholeseller_tier ?? __('N/A') }}</td>
                                    <td>
                                        @php
                                        $status = $order->status;
                                        $color = match($status) {
                                        'sent' => 'wholesale-status-pill--info',
                                        'accepted' => 'wholesale-status-pill--success',
                                        'rejected' => 'wholesale-status-pill--danger',
                                        default => 'wholesale-status-pill--muted',
                                        };
                                        @endphp

                                        <span class="wholesale-status-pill {{ $color }}">
                                            {{ translate($status) }}
                                        </span>
                                    </td>

                                    <td> {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount:  $order->final_price), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td>
                                        <div class="crm-row-actions">
                                            <div class="crm-row-actions__primary">
                                                <a class="btn btn-sm btn-info"
                                                    href="{{ route('admin.wholesale.business.orders.invoice', $order->id) }}">
                                                    {{ translate('View') }}
                                                </a>
                                                <a class="btn btn-sm btn-outline--primary"
                                                    href="{{ route('admin.wholesale.business.orders.invoice.edit', $order->id) }}">
                                                    {{ translate('Edit') }}
                                                </a>
                                            </div>
                                            <div class="dropdown crm-row-actions__menu">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                    <i class="tio-more-horizontal"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a data-id="{{ $order->order_id }}" class="dropdown-item wholesale-order-status-history" data-toggle="modal" data-target="#exampleModalLong">
                                                        {{ translate('History') }}
                                                    </a>
                                                    <a href="javascript:void(0);" class="dropdown-item text-danger wholesale-delete-action"
                                                        data-delete-url="{{ route('admin.wholesale.business.quotation.delete', $order->id) }}">
                                                        {{ translate('Delete') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        {{ translate('No_Order_Found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.wholesaler-business.partials.activity-modal')

    <span class="status-history-url" data-url="{{ route('admin.wholesale.business.ajax-activity-history', ['order' => ':id'] ) }}"></span>


    @endsection
    @push('script')
    @include('admin-views.wholesaler-business.partials._list-js-config')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-list.js') }}"></script>
    @endpush
