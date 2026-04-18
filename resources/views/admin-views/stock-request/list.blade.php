@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Stock_Request'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
@php
    $toolbarFields = [
        [
            'type' => 'daterange',
            'name' => 'restock_date',
            'label' => translate('Request Date'),
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
            'label' => translate('Search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_product_name_or_code'),
            'aria_label' => translate('search_by_product_name_or_code'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [];
    if (request()->filled('restock_date')) {
        $toolbarSummary[] = ['label' => translate('Request Date'), 'value' => Str::limit(request('restock_date'), 28), 'muted' => true];
    }
    if (request()->filled('searchValue')) {
        $toolbarSummary[] = ['label' => translate('Search'), 'value' => Str::limit(request('searchValue'), 28), 'muted' => true];
    }
    if (request()->filled('choose_first')) {
        $toolbarSummary[] = ['label' => translate('Rows_to_show'), 'value' => request('choose_first'), 'muted' => true];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.stock-request.export'),
            'form_id' => 'stock-request-toolbar',
            'label' => translate('export'),
        ],
        [
            'type' => 'button',
            'label' => translate('Add_New_Stock_Request'),
            'href' => route('admin.stock-request.add'),
            'class' => 'btn btn--primary text-nowrap',
            'icon_html' => '<i class="tio-add"></i>',
        ],
    ];
@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('Stock_Request_List') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $aStockRequests->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'stock-request-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.stock-request.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Stock_Request_List'),
            'listHeaderTotal' => $aStockRequests->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th class="text-center">{{ translate('SL') }}</th>
                        <th class="text-start">{{ translate('From_branch') }}</th>
                        <th class="text-start">{{ translate('Request Date') }}</th>
                        <th>{{ translate('Products') }}</th>
                        <th>{{ translate('Category') }}</th>
                        <th>{{ translate('Variation') }}</th>
                        <th class="text-center">{{ translate('QTY') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aStockRequests as $key => $transferRequest)
                        <tr>
                            <th scope="row" class="text-center align-middle">{{ $aStockRequests->firstItem() + $key }}</th>
                            <td class="align-middle">{{ $transferRequest->fromBranch?->getTranslatedField('branch_name') ?? translate('not_available') }}</td>
                            <td class="text-start align-middle">
                                {{ $transferRequest->transfer_date ? date('M d, Y', strtotime($transferRequest->transfer_date)) : translate('not_available') }}
                            </td>
                            <td class="text-start">
                                <a href="{{ route('admin.stock-request.view', $transferRequest->id) }}" class="crm-primary-link">
                                    {{ $transferRequest->products->map(fn($product) => optional($product->product)->getTranslatedField('name') ?? translate('not_available'))->implode(', ') }}
                                </a>
                            </td>
                            <td class="text-start">
                                {{ $transferRequest->products->map(fn($product) => optional($product->category)->getTranslatedField('name') ?? translate('not_available'))->implode(', ') }}
                            </td>
                            <td class="text-start">
                                @foreach($transferRequest->products as $product)
                                    <div class="mb-1">
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
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-center align-middle">{{ $transferRequest->products->map(fn($product) => $product->quantity)->implode(', ') }}</td>
                            <td class="text-center">
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <a title="{{ translate('View') }}" class="btn btn-outline-info btn-sm" href="{{ route('admin.stock-request.view', $transferRequest->id) }}">
                                            {{ translate('View') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ translate('No data available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {{ $aStockRequests->links() }}
            </div>
        </div>

        @if(count($aStockRequests) == 0)
            @include('layouts.back-end._empty-state', ['text' => 'no_product_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<span id="message-select-word" data-text="{{ translate('Select') }}"></span>
<div class="modal fade update-stock-modal restock-stock-update" id="update-stock" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.products.update-quantity') }}" method="post" class="row">
                <div class="modal-body py-4">
                    @csrf
                    <div class="rest-part-content"></div>
                    <div class="btn--container">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                            {{ translate('Close') }}
                        </button>
                        <button class="btn btn--primary" type="submit">{{ translate('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
