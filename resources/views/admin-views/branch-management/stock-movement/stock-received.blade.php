@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Stock_Transfer'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
@php
    $toolbarFields = [
        [
            'type' => 'daterange',
            'name' => 'restock_date',
            'label' => translate('Transfer Date'),
            'value' => request('restock_date'),
            'placeholder' => translate('Select_Date'),
            'autocomplete' => 'off',
            'input_class' => 'js-daterangepicker-with-range form-control cursor-pointer',
            'attributes' => ['readonly' => 'readonly'],
            'col_class' => 'col-xl-3 col-lg-6',
        ],
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'col_class' => 'col-xl-2 col-lg-6',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_product_name_or_code'),
            'aria_label' => translate('search_by_product_name_or_code'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [];
    if (request()->filled('restock_date')) {
        $toolbarSummary[] = ['label' => translate('Transfer Date'), 'value' => Str::limit(request('restock_date'), 28), 'muted' => true];
    }
    if (request()->filled('searchValue')) {
        $toolbarSummary[] = ['label' => translate('search'), 'value' => Str::limit(request('searchValue'), 28), 'muted' => true];
    }
    if (request()->filled('choose_first')) {
        $toolbarSummary[] = ['label' => translate('Rows_to_show'), 'value' => request('choose_first'), 'muted' => true];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.branch.stock.received.export'),
            'form_id' => 'branch-received-toolbar',
            'label' => translate('export'),
        ],
    ];
@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('Stock_Transfer_List') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $aStockTransfers->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'branch-received-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.branch.stock.received'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Stock_Transfer_List'),
            'listHeaderTotal' => $aStockTransfers->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th class="text-center">{{ translate('SL') }}</th>
                        <th class="text-start">{{ translate('To Branch') }}</th>
                        <th class="text-start">{{ translate('Transfer Date') }}</th>
                        <th>{{ translate('Products') }}</th>
                        <th>{{ translate('Category') }}</th>
                        <th>{{ translate('Attribute') }}</th>
                        <th class="text-center">{{ translate('Qty') }}</th>
                        <th class="text-center">{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aStockTransfers as $key => $transferRequest)
                        @php($productCount = $transferRequest->products->count())
                        @foreach($transferRequest->products as $index => $product)
                            <tr>
                                @if ($index === 0)
                                    <th scope="row" class="text-center align-middle" rowspan="{{ $productCount }}">{{ $aStockTransfers->firstItem() + $key }}</th>
                                    <td rowspan="{{ $productCount }}" class="align-middle">
                                        {{ $transferRequest->toBranch?->getTranslatedField('branch_name') ?? translate('not_available') }}
                                    </td>
                                    <td rowspan="{{ $productCount }}" class="align-middle">
                                        {{ $transferRequest->transfer_date ? date('M d, Y', strtotime($transferRequest->transfer_date)) : translate('not_available') }}
                                    </td>
                                @endif
                                <td>
                                    <a href="{{ route('admin.stock-transfer.view', $transferRequest->id) }}" class="crm-primary-link">
                                        {{ $product->product?->getTranslatedField('name') ?? translate('not_available') }}
                                    </a>
                                </td>
                                <td>{{ $product->category?->getTranslatedField('name') ?? translate('not_available') }}</td>
                                <td>{{ $product->attribute ?: translate('not_available') }}</td>
                                <td class="text-center align-middle">{{ $product->quantity }}</td>
                                <td class="text-success text-center align-middle">{{ translate($product->status) }}</td>
                                <td class="text-center align-middle">
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a href="{{ route('admin.stock-transfer.view', $transferRequest->id) }}" class="btn btn-outline-info btn-sm">
                                                {{ translate('view') }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ translate('No data available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {{ $aStockTransfers->links() }}
            </div>
        </div>

        @if(count($aStockTransfers) == 0)
            @include('layouts.back-end._empty-state', ['text' => 'no_product_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<span id="message-select-word" data-text="{{ translate('select') }}"></span>
<div class="modal fade update-stock-modal restock-stock-update" id="update-stock" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.products.update-quantity') }}" method="post" class="row">
                <div class="modal-body py-4">
                    @csrf
                    <div class="rest-part-content"></div>
                    <div class="btn--container">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                            {{ translate('close') }}
                        </button>
                        <button class="btn btn--primary" type="submit">{{ translate('update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
