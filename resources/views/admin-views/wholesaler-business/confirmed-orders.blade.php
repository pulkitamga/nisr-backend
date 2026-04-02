@extends('layouts.back-end.app')

@section('title', translate('Confirmed_Orders'))

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
            {{translate('Confirmed_Orders')}}
        </h2>
    </div>
    @php
        $confirmedSummary = [];
        if (request()->filled('date_from') || request()->filled('date_to')) {
            $confirmedSummary[] = [
                'label' => translate('date'),
                'value' => trim((request('date_from') ?: '...') . ' - ' . (request('date_to') ?: '...')),
            ];
        }
        if (request('delivery_status') && request('delivery_status') !== 'all') {
            $confirmedSummary[] = [
                'label' => translate('Delivery Status'),
                'value' => request('delivery_status'),
            ];
        }
        if (request('payment_status') && request('payment_status') !== 'all') {
            $confirmedSummary[] = [
                'label' => translate('Payment Status'),
                'value' => request('payment_status'),
            ];
        }
        if (request('price_sort')) {
            $confirmedSummary[] = [
                'label' => translate('Price'),
                'value' => request('price_sort') === 'low_high' ? translate('Low to High') : translate('High to Low'),
            ];
        }
        if (request()->filled('searchValue')) {
            $confirmedSummary[] = [
                'label' => translate('search'),
                'value' => request('searchValue'),
            ];
        }
    @endphp

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'wholesale-confirmed-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.wholesale.business.wholesale.confirmedorder'),
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
                'name' => 'delivery_status',
                'label' => translate('Delivery Status'),
                'type' => 'select',
                'value' => request('delivery_status', 'all'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                'options' => [
                    'all' => translate('all'),
                    'delivered' => translate('delivered'),
                    'partials' => translate('partials'),
                    'pending' => translate('pending'),
                ],
            ],
            [
                'name' => 'payment_status',
                'label' => translate('Payment Status'),
                'type' => 'select',
                'value' => request('payment_status', 'all'),
                'col_class' => 'col-xl-2 col-lg-4 col-md-6',
                'options' => [
                    'all' => translate('all'),
                    'paid' => translate('paid'),
                    'unpaid' => translate('unpaid'),
                    'partials' => translate('partials'),
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
                'label' => translate('search'),
                'type' => 'search',
                'value' => request('searchValue'),
                'placeholder' => translate('Search...'),
                'aria_label' => translate('Search'),
                'col_class' => 'col-xl-1 col-lg-3 col-md-6',
            ],
        ],
        'toolbarSummary' => $confirmedSummary,
    ])

    <div class="card mt-3">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Confirmed_Orders'),
            'listHeaderTotal' => $orders->total(),
            'listHeaderActions' => [
                [
                    'type' => 'export',
                    'url' => route('admin.wholesale.business.wholesale-confirm.export'),
                    'form_id' => 'wholesale-confirmed-toolbar',
                    'label' => translate('export'),
                ],
            ],
        ])

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Date')}}</th>
                            <th>{{translate('Purchase_Order_No')}}</th>
                            <th>{{translate('external_Po_Number')}}</th>
                            <th>{{translate('Quotation_No')}}</th>
                            <th>{{translate('Confirmed_order_no')}}</th>
                            <th>{{translate('Inovice_no')}}</th>
                            <th>{{translate('Wholesaler')}}</th>
                            <th>{{translate('Delivery_Status')}}</th>
                            <th>{{translate('Payment_status')}}</th>
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
                            <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($order->confirmed_at)->format('d/m/Y') }}</span></td>
                            <td>{{ $order->purchase_order_no }}</td>
                            <td>{{ $order->external_po_number ?? '' }}</td>
                            <td>{{ $order->quotation_no }}</td>
                            <td>{{ $order->confirm_order_no ?? ''}}</td>
                            <td>{{ $order->invoice_no ?? ''}}</td>
                            <td>
                                <a class="crm-primary-link" href="{{ route('admin.wholesale.business.confirm-order.tracking-page', $order->id) }}">
                                    {{ $order->wholeseller->wholesalerBusiness->company_name ?? __('N/A') }}
                                </a>
                            </td>
                            <td>
                                @php
                                $deliveryStatus = strtolower($order->delivery_status ?? 'pending');
                                $deliveryColors = [
                                'delivered' => 'wholesale-status-pill--success',
                                'partials' => 'wholesale-status-pill--warning',
                                'pending' => 'wholesale-status-pill--danger',
                                ];
                                $deliveryClass = $deliveryColors[$deliveryStatus] ?? 'wholesale-status-pill--muted';
                                @endphp

                                <span class="wholesale-status-pill {{ $deliveryClass }}">
                                    {{ translate($deliveryStatus) }}
                                </span>
                            </td>
                            <td>
                                @php
                                $status = strtolower($order->payment_status ?? 'unpaid');
                                $statusColors = [
                                'paid' => 'wholesale-status-pill--success',
                                'partials' => 'wholesale-status-pill--warning',
                                'unpaid' => 'wholesale-status-pill--danger',
                                ];
                                $colorClass = $statusColors[$status] ?? 'wholesale-status-pill--muted';
                                @endphp

                                <span class="wholesale-status-pill {{ $colorClass }}">
                                    {{ translate($status) }}
                                </span>
                            </td>

                            <td> {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount:   $order->final_price), currencyCode: getCurrencyCode()) }}
                            </td>
                            <td>
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <a class="btn btn-sm btn-info"
                                            href="{{ route('admin.wholesale.business.confirm-order.tracking-page', $order->id) }}">
                                            {{ translate('view') }}
                                        </a>
                                        @if (!$order->invoice_no)
                                            <button type="button" class="btn btn-sm btn-primary wholesale-open-invoice-modal" data-order-id="{{ $order->id }}">
                                                {{ translate('Invoice No') }}
                                            </button>
                                        @elseif (!$order->confirm_order_no)
                                            <button type="button" class="btn btn-sm btn-primary wholesale-open-confirm-order-modal" data-order-id="{{ $order->id }}">
                                                {{ translate('Confirm Order No') }}
                                            </button>
                                        @elseif (strtolower($order->payment_status ?? 'unpaid') !== 'paid')
                                            <a class="btn btn-sm btn-primary" href="{{ route('admin.wholesale.business.orders.payment', $order->id) }}">
                                                {{ translate('Payment') }}
                                            </a>
                                        @else
                                            <a class="btn btn-sm btn-primary" href="{{ route('admin.wholesale.business.orders.delivery', $order->id) }}">
                                                {{ translate('Delivery') }}
                                            </a>
                                        @endif
                                    </div>
                                    @if (!$order->invoice_no || !$order->confirm_order_no)
                                        <div class="crm-row-actions__chips">
                                            @if (!$order->invoice_no)
                                                <span class="crm-row-actions__chip">{{ translate('Invoice No') }}</span>
                                            @endif
                                            @if (!$order->confirm_order_no)
                                                <span class="crm-row-actions__chip">{{ translate('Confirm Order No') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="dropdown crm-row-actions__menu">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                            <i class="tio-more-horizontal"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a data-id="{{ $order->order_id }}" class="dropdown-item wholesale-order-status-history" data-toggle="modal" data-target="#exampleModalLong">
                                                {{ translate('History') }}
                                            </a>
                                            @if (!$order->invoice_no)
                                                <button type="button" class="dropdown-item wholesale-open-invoice-modal" data-order-id="{{ $order->id }}">
                                                    {{ translate('Invoice No') }}
                                                </button>
                                            @endif
                                            @if (!$order->confirm_order_no)
                                                <button type="button" class="dropdown-item wholesale-open-confirm-order-modal" data-order-id="{{ $order->id }}">
                                                    {{ translate('Confirm Order No') }}
                                                </button>
                                            @endif
                                            @if ($order->invoice_no)
                                                <a class="dropdown-item" href="{{ route('admin.wholesale.business.confirm-order.complete.invoice', $order->id) }}">
                                                    {{ translate('Invoice') }}
                                                </a>
                                            @endif
                                            <a class="dropdown-item" href="{{ route('admin.wholesale.business.orders.payment', $order->id) }}">
                                                {{ translate('Payment') }}
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.wholesale.business.orders.delivery', $order->id) }}">
                                                {{ translate('Delivery') }}
                                            </a>
                                            @if ($order->attachments)
                                                <a class="dropdown-item text-info" href="{{ asset('storage/wholesale_attachment/'.$order->attachments) }}" target="_blank">
                                                    {{ translate('Attachment') }}
                                                </a>
                                            @endif
                                            <a class="dropdown-item text-danger wholesale-delete-action" href="javascript:void(0);"
                                                data-delete-url="{{ route('admin.wholesale.business.confirem.order.delete', $order->id) }}">
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
                                {{ translate('No order found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
        <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.wholesale.business.order.assign-invoice-no') }}">
                    @csrf
                    <input type="hidden" name="order_id" id="invoice_order_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('Assign Invoice No') }}</h5>
                            <button type="button"
                                class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                    aria-hidden="true">x</span></i></button>
                        </div>
                        <div class="modal-body">
                            <label>{{ translate('Invoice No') }}</label>
                            <input type="text" name="invoice_no" id="invoice_no" class="form-control" required>
                            <small id="invoiceAvailability" class="text-sm mt-1"></small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="submitInvoice" class="btn btn--primary" disabled> {{ translate('Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.wholesale.business.order.assign-confirm-no') }}">
                    @csrf
                    <input type="hidden" name="order_id" id="confirm_order_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('Assign Confirm Order No') }}</h5>
                            <button type="button"
                                class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                    aria-hidden="true">x</span></i></button>
                        </div>
                        <div class="modal-body">
                            <label>{{ translate('Confirm Order No') }}</label>
                            <input type="text" name="confirm_order_no" id="confirm_order_no" class="form-control"
                                required>
                            <small id="confirmOrderAvailability" class="text-sm mt-1"></small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="submitConfirmOrder" class="btn btn--primary"
                                disabled> {{ translate('Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="table-responsive mt-4">
        <div class="px-4 d-flex justify-content-center justify-content-md-end">
            {{ $orders->links() }}
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
