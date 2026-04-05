@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('order_List'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
    @php
        $businessMode = getWebConfig(name: 'business_mode');
        $selectedFilter = $filter ?? 'all';
        $selectedSellerId = request('seller_id', 'all');
        $selectedCustomerId = request('customer_id', 'all');
        $selectedDateType = $dateType ?? '';

        $statusLabel = match ($status) {
            'processing' => translate('packaging'),
            'failed' => translate('failed_to_Deliver'),
            'all' => translate('all'),
            default => translate(str_replace('_', ' ', $status)),
        };

        $orderTypeOptions = [];
        if ($businessMode == 'multi') {
            $orderTypeOptions['all'] = translate('all');
        }
        $orderTypeOptions['admin'] = translate('in_House_Order');
        if ($businessMode == 'multi') {
            $orderTypeOptions['seller'] = translate('vendor_Order');
            if (($status == 'all' || $status == 'delivered') && !request()->has('delivery_man_id')) {
                $orderTypeOptions['POS'] = translate('POS_Order');
            }
        }

        $sellerOptions = ['all' => translate('all_shop'), '0' => translate('inhouse')];
        foreach ($sellers as $seller) {
            if (isset($seller->shop)) {
                $sellerOptions[(string)$seller->id] = $seller->shop->name;
            }
        }

        $dateTypeOptions = [
            '' => translate('select_Date_Type'),
            'this_year' => translate('this_Year'),
            'this_month' => translate('this_Month'),
            'this_week' => translate('this_Week'),
            'custom_date' => translate('custom_Date'),
        ];

        $selectedSellerLabel = $sellerOptions[(string)$selectedSellerId] ?? translate('all_shop');
        $selectedCustomerLabel = $customer === 'all'
            ? translate('all_customer')
            : ($customer->name ?? trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')));

        $toolbarFields = [];

        if (request('delivery_man_id')) {
            $toolbarFields[] = [
                'type' => 'html',
                'html' => '<input type="hidden" name="delivery_man_id" value="' . e(request('delivery_man_id')) . '">',
                'col_class' => 'd-none',
            ];
        }

        $toolbarFields[] = [
            'type' => 'select',
            'name' => 'filter',
            'label' => translate('order_type'),
            'value' => $selectedFilter,
            'options' => $orderTypeOptions,
            'input_class' => 'form-control',
            'wrapper_attributes' => ['id' => 'filter_area'],
            'attributes' => ['id' => 'filter'],
            'col_class' => 'col-xl-2 col-lg-6',
        ];

        if ($businessMode == 'multi') {
            $toolbarFields[] = [
                'type' => 'select',
                'name' => 'seller_id',
                'label' => translate('store'),
                'value' => (string)$selectedSellerId,
                'options' => $sellerOptions,
                'input_class' => 'form-control',
                'wrapper_attributes' => [
                    'id' => 'seller_id_area',
                    'style' => $selectedFilter === 'admin' ? 'display:none' : '',
                ],
                'attributes' => ['id' => 'seller_id'],
                'col_class' => 'col-xl-3 col-lg-6',
            ];
        }

        $toolbarFields[] = [
            'type' => 'html',
            'html' => view('admin-views.order.partials._customer-filter', compact('customer'))->render(),
            'col_class' => 'col-xl-3 col-lg-6',
        ];

        $toolbarFields[] = [
            'type' => 'select',
            'name' => 'date_type',
            'label' => translate('date_type'),
            'value' => $selectedDateType,
            'options' => $dateTypeOptions,
            'input_class' => 'form-control',
            'attributes' => ['id' => 'date_type'],
            'col_class' => 'col-xl-2 col-lg-6',
        ];

        $toolbarFields[] = [
            'type' => 'date',
            'name' => 'from',
            'label' => translate('start_date'),
            'value' => $from,
            'input_class' => 'form-control',
            'attributes' => ['id' => 'from_date'],
            'wrapper_attributes' => ['id' => 'from_div'],
            'col_class' => 'col-xl-2 col-lg-6',
        ];

        $toolbarFields[] = [
            'type' => 'date',
            'name' => 'to',
            'label' => translate('end_date'),
            'value' => $to,
            'input_class' => 'form-control',
            'attributes' => ['id' => 'to_date'],
            'wrapper_attributes' => ['id' => 'to_div'],
            'col_class' => 'col-xl-2 col-lg-6',
        ];

        $toolbarFields[] = [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'attributes' => ['min' => '1'],
            'col_class' => 'col-xl-2 col-lg-6',
        ];

        $toolbarFields[] = [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => $searchValue,
            'placeholder' => translate('search_by_Order_ID'),
            'aria_label' => translate('search_by_Order_ID'),
            'col_class' => 'col-xl-4 col-lg-12',
        ];

        $toolbarSummary = [
            ['label' => translate('order_status'), 'value' => $statusLabel],
        ];

        if ($selectedFilter !== 'all') {
            $toolbarSummary[] = [
                'label' => translate('order_type'),
                'value' => $orderTypeOptions[$selectedFilter] ?? translate('all'),
                'muted' => true,
            ];
        }
        if ($businessMode == 'multi' && $selectedSellerId !== 'all' && $selectedFilter !== 'admin') {
            $toolbarSummary[] = [
                'label' => translate('store'),
                'value' => $selectedSellerLabel,
                'muted' => true,
            ];
        }
        if ($selectedCustomerId !== 'all' && $selectedCustomerId !== null) {
            $toolbarSummary[] = [
                'label' => translate('customer'),
                'value' => Str::limit($selectedCustomerLabel, 28),
                'muted' => true,
            ];
        }
        if (!empty($selectedDateType)) {
            $toolbarSummary[] = [
                'label' => translate('date_type'),
                'value' => $dateTypeOptions[$selectedDateType] ?? translate('select_Date_Type'),
                'muted' => true,
            ];
        }
        if (!empty($from) || !empty($to)) {
            $toolbarSummary[] = [
                'label' => translate('Select_Date'),
                'value' => trim(($from ?: '...') . ' - ' . ($to ?: '...')),
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
                'url' => route('admin.orders.export-excel', ['status' => $status]),
                'form_id' => 'order-list-toolbar',
                'label' => translate('export'),
            ],
        ];
    @endphp

    <div class="content container-fluid">
        <div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <h2 class="h1 mb-0">
                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/all-orders.png') }}" class="mb-1 me-1" alt="">
                    <span class="page-header-title">{{ $statusLabel }}</span>
                    {{ translate('orders') }}
                </h2>
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $orders->total() }}</span>
            </div>

            @include('admin-views.crm.partials._list-toolbar', [
                'toolbarId' => 'order-list-toolbar',
                'toolbarAction' => route('admin.orders.list', ['status' => $status]),
                'toolbarResetUrl' => route('admin.orders.list', ['status' => $status]),
                'toolbarFields' => $toolbarFields,
                'toolbarSummary' => $toolbarSummary,
            ])

            <div class="card">
                @include('admin-views.crm.partials._list-card-header', [
                    'listHeaderTitle' => translate('order_list'),
                    'listHeaderTotal' => $orders->total(),
                    'listHeaderActions' => $headerActions,
                ])

                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('order_ID') }}</th>
                                <th class="text-capitalize">{{ translate('order_date') }}</th>
                                <th class="text-capitalize">{{ translate('customer_info') }}</th>
                                <th>{{ translate('store') }}</th>
                                <th class="text-capitalize">{{ translate('total_amount') }}</th>
                                @if($status == 'all')
                                    <th class="text-center">{{ translate('order_status') }}</th>
                                @else
                                    <th class="text-capitalize">{{ translate('payment_method') }}</th>
                                @endif
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($orders as $key => $order)
                            <tr class="status-{{ $order['order_status'] }} class-all">
                                <td>{{ $orders->firstItem() + $key }}</td>
                                <td>
                                    <a class="title-color crm-primary-link" href="{{ route('admin.orders.details', ['id' => $order['id']]) }}">
                                        {{ $order['id'] }}
                                        {!! $order->order_type == 'POS' ? '<span class="text--primary">(POS)</span>' : '' !!}
                                    </a>
                                </td>
                                <td>
                                    <bdi dir="ltr" class="d-inline-block">
                                        {{ \Carbon\Carbon::parse($order['created_at'])->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}
                                    </bdi>
                                </td>
                                <td>
                                    @if($order->is_guest)
                                        <strong class="title-name">{{ translate('guest_customer') }}</strong>
                                    @elseif($order->customer_id == 0)
                                        <strong class="title-name">{{ translate('walking_customer') }}</strong>
                                    @else
                                        @if($order->customer)
                                            <a class="text-body text-capitalize crm-primary-link" href="{{ route('admin.orders.details', ['id' => $order['id']]) }}">
                                                <strong class="title-name">{{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}</strong>
                                            </a>
                                            @if($order->customer['phone'])
                                                <a class="d-block title-color" href="tel:{{ formatPhoneForDisplay($order->customer['phone']) }}">
                                                    <bdi dir="ltr">{{ formatPhoneForDisplay($order->customer['phone']) }}</bdi>
                                                </a>
                                            @else
                                                <a class="d-block title-color" href="mailto:{{ $order->customer['email'] }}">{{ $order->customer['email'] }}</a>
                                            @endif
                                        @else
                                            <label class="badge badge-danger fz-12">
                                                {{ translate('customer_not_found') }}
                                            </label>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(isset($order->seller_id) && isset($order->seller_is))
                                        <a href="{{ $order->seller_is == 'seller' && $order->seller?->shop ? route('admin.vendors.view', ['id' => $order->seller->shop->id]) : 'javascript:' }}" class="store-name font-weight-medium">
                                            @if($order->seller_is == 'seller')
                                                {{ isset($order->seller?->shop) ? $order->seller?->shop?->name : translate('Store_not_found') }}
                                            @elseif($order->seller_is == 'admin')
                                                {{ translate('in_House') }}
                                            @endif
                                        </a>
                                    @else
                                        {{ translate('Store_not_found') }}
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        @php($orderTotalPriceSummary = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order))
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['totalAmount']), currencyCode: getCurrencyCode()) }}
                                    </div>

                                    <div class="crm-row-actions__chips mt-2 justify-content-start">
                                        <span class="crm-row-actions__chip {{ $order->payment_status == 'paid' ? 'text-success' : 'text-danger' }}">
                                            {{ $order->payment_status == 'paid' ? translate('paid') : translate('unpaid') }}
                                        </span>
                                    </div>
                                </td>
                                @if($status == 'all')
                                    <td class="text-center text-capitalize">
                                        @if($order['order_status'] == 'pending')
                                            <span class="crm-row-actions__chip">{{ translate($order['order_status']) }}</span>
                                        @elseif($order['order_status'] == 'processing' || $order['order_status'] == 'out_for_delivery')
                                            <span class="crm-row-actions__chip">
                                                {{ str_replace('_', ' ', $order['order_status'] == 'processing' ? translate('packaging') : translate($order['order_status'])) }}
                                            </span>
                                        @elseif($order['order_status'] == 'confirmed')
                                            <span class="crm-row-actions__chip">{{ translate($order['order_status']) }}</span>
                                        @elseif($order['order_status'] == 'failed')
                                            <span class="crm-row-actions__chip text-danger">{{ translate('failed_to_deliver') }}</span>
                                        @elseif($order['order_status'] == 'delivered')
                                            <span class="crm-row-actions__chip">{{ translate($order['order_status']) }}</span>
                                        @else
                                            <span class="crm-row-actions__chip text-danger">{{ translate($order['order_status']) }}</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="text-capitalize">{{ translate($order['payment_method']) }}</td>
                                @endif
                                <td>
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a class="btn btn-outline--primary square-btn btn-sm" title="{{ translate('view') }}"
                                                href="{{ route('admin.orders.details', ['id' => $order['id']]) }}">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/eye.svg') }}" class="svg" alt="">
                                            </a>
                                        </div>
                                        <div class="dropdown crm-row-actions__menu">
                                            <button class="btn btn-outline-secondary square-btn btn-sm dropdown-toggle crm-row-actions__toggle"
                                                type="button" id="order-row-actions-{{ $order['id'] }}" data-bs-toggle="dropdown"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                aria-label="{{ translate('More actions') }}">
                                                <i class="tio-more-horizontal"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                aria-labelledby="order-row-actions-{{ $order['id'] }}">
                                                <a class="dropdown-item" title="{{ translate('invoice') }}" target="_blank"
                                                    href="{{ route('admin.orders.generate-invoice', [$order['id']]) }}">
                                                    <i class="tio-download-to mr-2"></i>{{ translate('invoice') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="d-flex justify-content-lg-end">
                        {!! $orders->links() !!}
                    </div>
                </div>
                @if(count($orders) == 0)
                    @include('layouts.back-end._empty-state', ['text' => 'no_order_found'], ['image' => 'default'])
                @endif
            </div>

            <div class="js-nav-scroller hs-nav-scroller-horizontal d-none">
                <span class="hs-nav-scroller-arrow-prev d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">{{ translate('order_list') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <span id="message-date-range-text" data-text="{{ translate('invalid_date_range') }}"></span>
    <span id="js-data-example-ajax-url" data-url="{{ route('admin.orders.customers') }}"></span>
    <span id="character-trigger-limit" data-limit="{{ getWebConfig('character_trigger_limit_for_autosearch') }}"></span>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/order.js') }}"></script>
@endpush
