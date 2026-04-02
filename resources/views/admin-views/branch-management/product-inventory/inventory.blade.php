@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Branch Product Inventory'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
@php
    $toolbarFields = [
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'col_class' => 'col-xl-2 col-lg-4',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_product_name_or_code'),
            'aria_label' => translate('search_by_product_name_or_code'),
            'col_class' => 'col-xl-5 col-lg-8',
        ],
    ];

    $toolbarSummary = [];
    if (request()->filled('searchValue')) {
        $toolbarSummary[] = ['label' => translate('search'), 'value' => Str::limit(request('searchValue'), 28), 'muted' => true];
    }
    if (request()->filled('choose_first')) {
        $toolbarSummary[] = ['label' => translate('Rows_to_show'), 'value' => request('choose_first'), 'muted' => true];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.branch.product-inventory.export'),
            'form_id' => 'branch-inventory-toolbar',
            'label' => translate('export'),
        ],
    ];

    $requestStatusMap = [
        0 => translate('Pending'),
        1 => translate('Approved'),
        2 => translate('Denied'),
    ];
@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex gap-2 align-items-center">
            <img src="{{ asset('public/assets/back-end/img/stock.png') }}" alt="" width="30">
            {{ translate('Branch Product Inventory') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $products->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'branch-inventory-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.branch.product-inventory'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Branch Product Inventory'),
            'listHeaderTotal' => $products->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Code') }}</th>
                        <th>{{ translate('Category') }}</th>
                        <th>{{ translate('Sub Category') }}</th>
                        <th>{{ translate('Sub Sub Category') }}</th>
                        <th>{{ translate('Brand') }}</th>
                        <th>{{ translate('Unit') }}</th>
                        <th>{{ translate('Type') }}</th>
                        <th>{{ translate('Details') }}</th>
                        <th>{{ translate('Price') }}</th>
                        <th>{{ translate('Purchase Price') }}</th>
                        <th>{{ translate('Tax') }}</th>
                        <th>{{ translate('Discount') }}</th>
                        <th>{{ translate('Current Stock') }}</th>
                        <th>{{ translate('Min Order Qty') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Request Status') }}</th>
                        <th>{{ translate('Shipping Cost') }}</th>
                        <th>{{ translate('Images') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $key => $product)
                        <tr>
                            <td>{{ $products->firstItem() + $key }}</td>
                            <td>
                                <a
                                    href="{{ route('admin.products.view', ['addedBy' => ($product->added_by == 'seller' ? 'vendor' : 'in-house'), 'id' => $product->id]) }}"
                                    class="crm-primary-link"
                                >
                                    {{ Str::limit($product->getTranslatedField('name'), 25) }}
                                </a>
                            </td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->category?->getTranslatedField('name') ?? translate('not_available') }}</td>
                            <td>{{ $product->subCategory?->getTranslatedField('name') ?? translate('not_available') }}</td>
                            <td>{{ $product->subSubCategory?->getTranslatedField('name') ?? translate('not_available') }}</td>
                            <td>{{ $product->brand?->getTranslatedField('name') ?? translate('not_available') }}</td>
                            <td>{{ getUnitLabel($product->unit) }}</td>
                            <td>{{ translate($product->product_type) }}</td>
                            <td>{!! Str::limit($product->details, 50) !!}</td>
                            <td>{{ setCurrencySymbol(usdToDefaultCurrency($product->unit_price)) }}</td>
                            <td>{{ setCurrencySymbol(usdToDefaultCurrency($product->purchase_price)) }}</td>
                            <td>{{ $product->tax }} <small>({{ $product->tax_type }})</small></td>
                            <td>{{ $product->discount }}</td>
                            <td><span class="badge badge-light">{{ $product->current_stock }}</span></td>
                            <td>{{ $product->minimum_order_qty }}</td>
                            <td>
                                <span class="badge badge-{{ $product->status == 1 ? 'success' : 'danger' }}">
                                    {{ $product->status == 1 ? translate('Active') : translate('Inactive') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $product->request_status == 0 ? 'warning' : ($product->request_status == 1 ? 'success' : 'danger') }}">
                                    {{ $requestStatusMap[$product->request_status] ?? translate('not_available') }}
                                </span>
                            </td>
                            <td>{{ setCurrencySymbol(usdToDefaultCurrency($product->shipping_cost)) }}</td>
                            <td class="d-flex flex-wrap gap-1">
                                @foreach($product->images ?? [] as $image)
                                    <img src="{{ asset('storage/app/public/product/' . $image['image_name']) }}" width="40" class="border rounded" alt="product">
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" class="text-center">{{ translate('No branch products found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-end">
                {!! $products->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
