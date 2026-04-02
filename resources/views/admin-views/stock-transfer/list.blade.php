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
            'url' => route('admin.stock-transfer.export'),
            'form_id' => 'stock-transfer-toolbar',
            'label' => translate('export'),
        ],
        [
            'type' => 'button',
            'label' => translate('transfer_New_Product_Stock'),
            'href' => route('admin.stock-transfer.add'),
            'class' => 'btn btn--primary text-nowrap',
            'icon_html' => '<i class="tio-add"></i>',
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
        'toolbarId' => 'stock-transfer-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.stock-transfer.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('stock_Transfer_List'),
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
                        <th>{{ translate('Variation') }}</th>
                        <th class="text-center">{{ translate('Qty') }}</th>
                        <th class="text-center">{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('CSV') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aStockTransfers as $key => $transferRequest)
                        @foreach($transferRequest->products as $productIndex => $product)
                            <tr>
                                <td class="text-center align-middle">
                                    {{ $productIndex === 0 ? $aStockTransfers->firstItem() + $key : '' }}
                                </td>
                                <td class="align-middle">{{ $transferRequest->toBranch?->getTranslatedField('branch_name') ?? translate('not_available') }}</td>
                                <td class="text-start align-middle">
                                    {{ $transferRequest->transfer_date ? date('M d, Y', strtotime($transferRequest->transfer_date)) : translate('not_available') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.stock-transfer.view', $transferRequest->id) }}" class="crm-primary-link">
                                        {{ optional($product->product)->getTranslatedField('name') ?? translate('not_available') }}
                                    </a>
                                </td>
                                <td>{{ $product->category?->getTranslatedField('name') ?? translate('not_available') }}</td>
                                <td>
                                    @if($product->variation_type)
                                        <span class="badge badge-soft-primary me-1">{{ $product->variation_type }}</span>
                                        @if($product->variation_key)
                                            <small class="text-muted">
                                                ({{ Str::replace(':', ' : ', Str::replace('|', ' • ', $product->variation_key)) }})
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-dark">{{ translate('Default') }}</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">{{ $product->quantity }}</td>
                                <td class="text-center align-middle">{{ translate($product->status) }}</td>
                                <td class="text-center align-middle">
                                    @if($product->serial_csv_path)
                                        <span class="badge badge-soft-success">{{ translate('Available') }}</span>
                                    @else
                                        <span class="crm-row-actions__chip">{{ translate('no_csv_file_found') }}</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a href="{{ route('admin.stock-transfer.view', $transferRequest->id) }}" class="btn btn-outline-info btn-sm">
                                                {{ translate('view') }}
                                            </a>
                                        </div>
                                        <div class="dropdown crm-row-actions__menu">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                <i class="tio-more-horizontal"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($product->serial_csv_path)
                                                    <a href="{{ route('admin.stock-transfer.download-csv', $product->id) }}" class="dropdown-item">
                                                        <i class="tio-download mr-2"></i>{{ translate('download_csv') }}
                                                    </a>
                                                @else
                                                    <button type="button" class="dropdown-item disabled" disabled>
                                                        <i class="tio-file-outlined mr-2"></i>{{ translate('no_csv_file_found') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">{{ translate('No data available') }}</td>
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
