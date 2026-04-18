@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('branch_Stocks'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/branch-management.css') }}">
@endpush

@section('content')
@php
    $selectedBranchName = request()->filled('branch_id') ? ($branchList[request('branch_id')] ?? request('branch_id')) : null;
    $selectedProductName = request()->filled('product_id') ? ($productList[request('product_id')] ?? request('product_id')) : null;

    $toolbarFields = [
        [
            'type' => 'select',
            'name' => 'branch_id',
            'label' => translate('Branch'),
            'value' => request('branch_id'),
            'options' => ['' => translate('All')] + $branchList->toArray(),
            'col_class' => 'col-xl-2 col-lg-6',
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'select',
            'name' => 'product_id',
            'label' => translate('Product'),
            'value' => request('product_id'),
            'options' => ['' => translate('All')] + $productList->toArray(),
            'col_class' => 'col-xl-3 col-lg-6',
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'text',
            'name' => 'attribute',
            'label' => translate('Attribute'),
            'value' => request('attribute'),
            'placeholder' => translate('Attribute'),
            'col_class' => 'col-xl-2 col-lg-6',
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
            'placeholder' => translate('search_by_branch_name_or_product_name'),
            'aria_label' => translate('search_by_branch_name_or_product_name'),
            'col_class' => 'col-xl-3 col-lg-12',
        ],
    ];

    $toolbarSummary = [];
    if ($selectedBranchName) {
        $toolbarSummary[] = ['label' => translate('Branch'), 'value' => Str::limit($selectedBranchName, 28), 'muted' => true];
    }
    if ($selectedProductName) {
        $toolbarSummary[] = ['label' => translate('Product'), 'value' => Str::limit($selectedProductName, 28), 'muted' => true];
    }
    if (request()->filled('attribute')) {
        $toolbarSummary[] = ['label' => translate('Attribute'), 'value' => Str::limit(request('attribute'), 28), 'muted' => true];
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
            'url' => route('admin.branch.export', ['export_scope' => 'branch_stock']),
            'form_id' => 'branch-stock-toolbar',
            'label' => translate('export'),
        ],
    ];
@endphp

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
            {{ translate('branch_Stocks') }}
            <span class="badge badge-soft-dark radius-50 fz-12">{{ $branches->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'branch-stock-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.branch.branch-stock-list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('branch_Stocks'),
            'listHeaderTotal' => $branches->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th class="text-center">{{ translate('SL') }}</th>
                        <th>{{ translate('Branch_Name') }}</th>
                        <th>{{ translate('Product_name') }}</th>
                        <th>{{ translate('Variation') }}</th>
                        <th class="text-center">{{ translate('Current_Stock') }}</th>
                        <th class="text-center">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $key => $stock)
                        <tr>
                            <td class="text-center">{{ $key + 1 + ($branches->currentPage() - 1) * $branches->perPage() }}</td>
                            <td>
                                @if($stock->branch_id)
                                    <a href="{{ route('admin.branch.view', $stock->branch_id) }}" class="crm-primary-link">
                                        {{ $stock->branch?->getTranslatedField('branch_name') ?? translate('not_available') }}
                                    </a>
                                @else
                                    {{ translate('not_available') }}
                                @endif
                            </td>
                            <td>
                                <a
                                    href="#"
                                    class="crm-primary-link view-history-btn"
                                    data-branch-id="{{ $stock->branch_id }}"
                                    data-product-id="{{ $stock->product_id }}"
                                    data-variation-type="{{ $stock->variation_type }}"
                                    data-variation-key="{{ $stock->variation_key }}"
                                >
                                    {{ $stock->product?->getTranslatedField('name') ?? translate('not_available') }}
                                </a>
                            </td>
                            <td>
                                @if($stock->variation_type && $stock->variation_type !== 'No Variation')
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-soft-primary">{{ $stock->variation_type }}</span>
                                        @if($stock->variation_key && $stock->variation_key !== 'No Variation')
                                            <small class="text-muted">
                                                ({{ Str::replace('|', ' • ', Str::replace(':', ' : ', $stock->variation_key)) }})
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-soft-dark">{{ translate('Default') }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $stock->total_stock }}</td>
                            <td class="text-center">
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary view-history-btn"
                                            data-branch-id="{{ $stock->branch_id }}"
                                            data-product-id="{{ $stock->product_id }}"
                                            data-variation-type="{{ $stock->variation_type }}"
                                            data-variation-key="{{ $stock->variation_key }}"
                                        >
                                            <i class="tio-history"></i> {{ translate('View History') }}
                                        </button>
                                    </div>
                                    <div class="dropdown crm-row-actions__menu">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                            <i class="tio-more-horizontal"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a
                                                href="{{ route('admin.branch.export', ['product_id' => $stock->product_id, 'branch_id' => $stock->branch_id, 'variation_type' => $stock->variation_type]) }}"
                                                class="dropdown-item"
                                            >
                                                <i class="tio-download mr-2"></i>{{ translate('Export History') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ translate('No data available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-center justify-content-md-end">
                {!! $branches->links() !!}
            </div>
        </div>

        @if(count($branches) == 0)
            @include('layouts.back-end._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<div class="modal fade branch-history-modal" id="stockHistoryModal" tabindex="-1" role="dialog" aria-labelledby="stockHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="stockHistoryModalLabel">{{ translate('Stock Transfer History') }}</h5>
                <button type="button" class="close branch-history-close" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>{{ translate('Branch') }}:</strong>
                            <span id="modalBranchName">-</span>
                        </div>
                        <div>
                            <strong>{{ translate('Product') }}:</strong>
                            <span id="modalProductName">-</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>{{ translate('Variation') }}:</strong>
                            <span id="modalVariation">-</span>
                        </div>
                        <div>
                            <strong>{{ translate('Current_Stock') }}:</strong>
                            <span id="modalCurrentStock">-</span>
                        </div>
                    </div>
                </div>
                <a
                    type="button"
                    class="btn btn-outline--primary text-nowrap p-2"
                    id="exportStockHistory"
                    data-base-url="{{ route('admin.branch.export') }}"
                    href="#"
                >
                    <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}" class="excel" alt="">
                    <span class="ps-1">{{ translate('export') }}</span>
                </a>

                <hr>

                <div id="historyTableContainer" class="branch-history-table-container">
                    <table class="table table-sm table-bordered text-start">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th>{{ translate('DATE') }}</th>
                                <th>{{ translate('Type') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <th>{{ translate('Reference') }}</th>
                                <th>{{ translate('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>

                <div id="noHistoryMessage" class="branch-history-empty text-center text-muted py-4 d-none">
                    <i class="tio-inbox branch-history-empty__icon"></i>
                    <p class="mt-2">{{ translate('No transfer history found') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary branch-history-close">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    @include('admin-views.branch.partials._stock-history-js-config')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/branch-stock-history.js') }}"></script>
@endpush
