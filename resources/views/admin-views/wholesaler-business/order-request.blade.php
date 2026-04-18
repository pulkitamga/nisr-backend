@extends('layouts.back-end.app')

@section('title', translate('Wholesaler Order Requests'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-list.css') }}">
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
                {{ translate('Wholesaler_Purchase_Requests') }}
            </h2>
        </div>

        @php
            $purchaseRequestSummary = [];
            if (request()->filled('date_from') || request()->filled('date_to')) {
                $purchaseRequestSummary[] = [
                    'label' => translate('DATE'),
                    'value' => trim((request('date_from') ?: '...') . ' - ' . (request('date_to') ?: '...')),
                ];
            }
            if (request('tier') && request('tier') !== 'all') {
                $purchaseRequestSummary[] = [
                    'label' => translate('Tier'),
                    'value' => request('tier'),
                ];
            }
            if (request('status') && request('status') !== 'all') {
                $purchaseRequestSummary[] = [
                    'label' => translate('Status'),
                    'value' => request('status'),
                ];
            }
            if (request()->filled('searchValue')) {
                $purchaseRequestSummary[] = [
                    'label' => translate('Search'),
                    'value' => request('searchValue'),
                ];
            }
        @endphp

        @include('admin-views.crm.partials._list-toolbar', [
            'toolbarId' => 'wholesale-purchase-toolbar',
            'toolbarAction' => url()->current(),
            'toolbarResetUrl' => route('admin.wholesale.business.order.request'),
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
                        'pending' => translate('Pending'),
                        'processed' => translate('processed'),
                        'Quotationsend' => translate('Quotationsend'),
                    ],
                ],
                [
                    'name' => 'choose_first',
                    'label' => translate('Rows'),
                    'type' => 'number',
                    'value' => request('choose_first', 15),
                    'attributes' => ['min' => 1],
                    'placeholder' => '15',
                    'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                ],
                [
                    'name' => 'searchValue',
                    'label' => translate('Search'),
                    'type' => 'search',
                    'value' => request('searchValue'),
                    'placeholder' => translate('Search...'),
                    'aria_label' => translate('Search'),
                    'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                ],
            ],
            'toolbarSummary' => $purchaseRequestSummary,
        ])

        <div class="card mt-3">
            @include('admin-views.crm.partials._list-card-header', [
                'listHeaderTitle' => translate('Wholesaler_Purchase_Requests'),
                'listHeaderTotal' => $orders->total(),
                'listHeaderActions' => [
                    [
                        'type' => 'export',
                        'url' => route('admin.wholesale.business.wholesale-purchase.export'),
                        'form_id' => 'wholesale-purchase-toolbar',
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
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('DATE') }}</th>
                                <th>{{ translate('Purchase_Order_No') }}</th>
                                <th>{{ translate('Wholesaler') }}</th>
                                <th>{{ translate('Tier') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowColor = ['bg-light', 'bg-white']; @endphp

                            @forelse($orders as $key => $order)
                                @php
                                    $bgClass = $rowColor[$key % 2];
                                    $status = strtolower($order->status);
                                @endphp
                                <tr class="{{ $bgClass }}">
                                    <td>{{ $orders->firstItem() + $key }}</td>
                                    <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span></td>
                                    <td>{{ $order->purchase_order_no }}</td>
                                    <td>
                                        <a
                                            class="crm-primary-link"
                                            href="{{ $status === 'quotationsend'
                                                ? route('admin.wholesale.business.purchase.order.view', $order->id)
                                                : route('admin.wholesale.business.order.view', $order->id) }}"
                                        >
                                            {{ $order->wholeseller->wholesalerBusiness->company_name ?? __('N/A') }}
                                        </a>
                                    </td>
                                    <td>{{ $order->wholeseller_tier ?? __('N/A') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($status) {
                                                'pending' => 'danger',
                                                'processed' => 'info',
                                                default => 'success',
                                            };
                                        @endphp
                                        <span class="btn bg-soft-{{ $badgeClass }} text-{{ $badgeClass }} p-2">
                                            {{ translate($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="crm-row-actions">
                                            <div class="crm-row-actions__primary">
                                                <a
                                                    class="btn btn-sm btn-info"
                                                    href="{{ $status === 'quotationsend'
                                                        ? route('admin.wholesale.business.purchase.order.view', $order->id)
                                                        : route('admin.wholesale.business.order.view', $order->id) }}"
                                                >
                                                    {{ translate('View') }}
                                                </a>
                                                @if ($status === 'pending')
                                                    <button type="button" class="btn btn-sm btn-warning wholesale-open-po-modal" data-order-id="{{ $order->id }}">
                                                        {{ translate('Assign Purchase Order No') }}
                                                    </button>
                                                @elseif ($status === 'processed')
                                                    <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.wholesale.business.order.view', $order->id) }}">
                                                        {{ translate('Edit') }}
                                                    </a>
                                                @else
                                                    <a data-id="{{ $order->order_id }}"
                                                        class="btn btn-sm btn-secondary wholesale-order-status-history"
                                                        data-toggle="modal" data-target="#exampleModalLong">
                                                        {{ translate('History') }}
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="dropdown crm-row-actions__menu">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                    <i class="tio-more-horizontal"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    @if ($status !== 'quotationsend')
                                                        <a data-id="{{ $order->order_id }}"
                                                            class="dropdown-item wholesale-order-status-history"
                                                            data-toggle="modal" data-target="#exampleModalLong">
                                                            {{ translate('History') }}
                                                        </a>
                                                    @endif
                                                    <a href="javascript:void(0);" class="dropdown-item text-danger wholesale-delete-action"
                                                        data-delete-url="{{ route('admin.wholesale.business.order.delete', $order->id) }}">
                                                        {{ translate('Delete') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        {{ translate('No order requests found') }}
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
    <!-- Modal -->
    <div class="modal fade" id="purchaseOrderModal" tabindex="-1" aria-labelledby="purchaseOrderLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="purchaseOrderForm" method="POST"
                action="{{ route('admin.wholesale.business.order.assign-number') }}">
                @csrf
                <input type="hidden" name="order_id" id="modal_order_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Assign Purchase Order No') }}</h5>
                        <button type="button"
                            class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                            data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span aria-hidden="true">x</span></i></button>


                    </div>
                    <div class="modal-body">
                        <label>{{ translate('Purchase_Order_No') }}</label>
                        <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control"
                            required>
                        <small id="availabilityMessage" class="text-sm mt-1"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitOrderNo" class="btn btn--primary"
                            disabled>{{ translate('Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <span class="status-history-url"
        data-url="{{ route('admin.wholesale.business.ajax-activity-history', ['order' => ':id']) }}"></span>

@endsection
@push('script')
    @include('admin-views.wholesaler-business.partials._list-js-config')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-list.js') }}"></script>
@endpush
