@extends('layouts.back-end.app')

@section('title', translate('Wholesale_Products_List'))

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
            {{translate('Wholesale_Products_List')}}
        </h2>
    </div>

    @php
        $wholesaleProductSummary = [];
        if (request()->filled('searchValue')) {
            $wholesaleProductSummary[] = [
                'label' => translate('Search'),
                'value' => request('searchValue'),
            ];
        }
    @endphp

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'wholesale-product-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.wholesale.product.list'),
        'toolbarFields' => [
            [
                'name' => 'choose_first',
                'label' => translate('Rows'),
                'type' => 'number',
                'value' => request('choose_first', 15),
                'attributes' => ['min' => 1],
                'placeholder' => '15',
                'col_class' => 'col-xl-2 col-lg-3 col-md-6',
            ],
            [
                'name' => 'searchValue',
                'label' => translate('Search'),
                'type' => 'search',
                'value' => request('searchValue'),
                'placeholder' => translate('search_by_Product_Name'),
                'aria_label' => translate('search_by_Product_Name'),
                'col_class' => 'col-xl-4 col-lg-6 col-md-6',
            ],
        ],
        'toolbarSummary' => $wholesaleProductSummary,
    ])

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                @include('admin-views.crm.partials._list-card-header', [
                    'listHeaderTitle' => translate('Wholesale_Products_List'),
                    'listHeaderTotal' => $wholesale_products->total(),
                    'listHeaderActions' => [
                        [
                            'type' => 'export',
                            'url' => route('admin.wholesale.product.export-excel'),
                            'form_id' => 'wholesale-product-toolbar',
                            'label' => translate('export'),
                        ],
                        [
                            'href' => route('admin.wholesale.product.add'),
                            'class' => 'btn btn--primary text-nowrap',
                            'icon_html' => '<i class="tio-add"></i>',
                            'label' => translate('add_New_Product'),
                        ],
                    ],
                ])

                <div class="card-body pt-0">
                    <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Product_name')}}</th>
                                <th>{{translate('Product_Category')}}</th>
                                <th>{{translate('product_Sub_Category')}}</th>
                                <th>{{translate('Variation')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wholesale_products as $key => $product)
                            <tr>
                                <td>{{ $wholesale_products->firstItem() + $key }}</td>
                                <td>
                                    <a class="crm-primary-link" href="{{ route('admin.wholesale.product.view', $product->id) }}">
                                        {{ $product->product->getTranslatedField('name') ?? __('N/A') }}
                                    </a>
                                </td>
                                <td>{{ $product->category->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $product->subcategory->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $product->variation_type ?? __('no_variation') }}</td>

                                <td>
                                    <label class="switcher mx-auto">
                                        <input type="checkbox"
                                            class="switcher_input product-status-toggle"
                                            data-id="{{ $product->id }}"
                                            {{ $product->status ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>

                                <td>
                                    <div class="crm-row-actions">
                                        <div class="crm-row-actions__primary">
                                            <a class="btn btn-sm btn-info"
                                                href="{{ route('admin.wholesale.product.view', $product->id) }}">
                                                {{ translate('View') }}
                                            </a>
                                            <a class="btn btn-sm btn-outline--primary"
                                                href="{{ route('admin.wholesale.product.edit', $product->id) }}">
                                                {{ translate('Edit') }}
                                            </a>
                                        </div>
                                        <div class="dropdown crm-row-actions__menu">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                <i class="tio-more-horizontal"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <form method="POST" action="{{ route('admin.wholesale.product.delete', $product->id) }}"
                                                    class="delete-product-form crm-row-actions__form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger confirm-delete-btn">
                                                        {{ translate('Delete') }}
                                                    </button>
                                                </form>
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
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $wholesale_products->appends(request()->query())->links() !!}
                    </div>
                </div>

                @if($wholesale_products->isEmpty())
                @include('layouts.back-end._empty-state', ['text'=>'no_record_found','image'=>'default'])
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin-views.wholesaler-business.partials._list-js-config', [
    'confirmDeletionText' => translate('This action cannot be undone.'),
])
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-list.js') }}"></script>
@endpush
