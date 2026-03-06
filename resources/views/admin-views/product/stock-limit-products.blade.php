@extends('layouts.back-end.app')

@section('title', translate('stock_limit_products'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex flex-column gap-1">
            <h2 class="h1 text-capitalize d-flex gap-2 align-items-center">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" class="mb-1 mr-1"
                    alt="">
                {{ translate('Products_Stocked_List') }}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">
                    {{ $products->total() }}
                </span>
            </h2>

        </div>
        <div class="row mt-30">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-auto">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-custom input-group-merge">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                            placeholder="{{ translate('search_by_Product_Name') }}"
                                            aria-label="Search orders" value="{{ $searchValue }}" required>
                                        <input type="hidden" value="{{ $status }}" name="status">
                                        <button type="submit" class="btn btn--primary">
                                            {{ translate('search') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 mt-1 col-md-6 col-lg-3">
                                <select name="qty_order_sort" class="form-control action-select-onchange-get-view"
                                    data-url-prefix="{{ route('admin.products.stock-limit-products', ['in_house', '']) }}/?sortOrderQty=">
                                    <option value="default" {{ $sortOrderQty == 'default' ? 'selected' : '' }}>
                                        {{ translate('default') }}
                                    </option>
                                    <option value="quantity_asc" {{ $sortOrderQty == 'quantity_asc' ? 'selected' : '' }}>
                                        {{ translate('inventory_quantity(low_to_high)') }}
                                    </option>
                                    <option value="quantity_desc" {{ $sortOrderQty == 'quantity_desc' ? 'selected' : '' }}>
                                        {{ translate('inventory_quantity(high_to_low)') }}
                                    </option>
                                    <option value="order_asc" {{ $sortOrderQty == 'order_asc' ? 'selected' : '' }}>
                                        {{ translate('order_volume(low_to_high)') }}
                                    </option>
                                    <option value="order_desc" {{ $sortOrderQty == 'order_desc' ? 'selected' : '' }}>
                                        {{ translate('order_volume(high_to_low)') }}
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('product_Name') }}</th>
                                    <th class="text-center">{{ translate('variation') }}</th>
                                    <th class="text-center">{{ translate('unit_price') }}</th>
                                    <th class="text-center">{{ translate('quantity') }}</th>
                                    <th class="text-center">{{ translate('orders') }}</th>
                                    <th class="text-center">{{ translate('active_status') }}</th>
                                    <th class="text-center">{{ translate('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sl = $products->firstItem(); @endphp

                                @foreach ($products as $product)
                                    @if ($product->variations->count())
                                        @foreach ($product->variations as $variation)
                                            <tr>
                                                <td>{{ $sl++ }}</td>

                                                {{-- Product name --}}
                                                <td>
                                                    <a href="{{ route('admin.products.view', [
                                                        'addedBy' => $product->added_by == 'seller' ? 'vendor' : 'in-house',
                                                        'id' => $product->id,
                                                    ]) }}"
                                                        class="media align-items-center gap-2">

                                                        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product') }}"
                                                            class="avatar border object-fit-cover">

                                                        <span class="media-body title-color">
                                                            {{ Str::limit($product->name, 20) }}
                                                        </span>
                                                    </a>
                                                </td>
                                                {{-- Variation Name --}}
                                                <td class="text-center">
                                                    @if (!empty($variation['type']))
                                                        <span class="badge badge-soft-primary d-inline-block">
                                                            {{ $variation['type'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                {{-- Price --}}
                                                <td class="text-center">
                                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency($variation['price'] ?? 0), currencyCode: getCurrencyCode()) }}
                                                </td>

                                                {{-- Stock --}}
                                                <td class="text-center">
                                                    {{ $variation['qty'] ?? 0 }}
                                                </td>

                                                {{-- Orders --}}
                                                <td class="text-center">
                                                    {{ $variation['order_details_count'] ?? 0 }}
                                                </td>
                                                {{-- Status --}}
                                                <td class="text-center">
                                                    @if ($product->request_status != 2)
                                                        <form action="{{ route('admin.products.status-update') }}" method="post"
                                                            id="product-status{{ $product['id'] }}-form"
                                                            class="admin-product-status-form">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $product['id'] }}">
                                                            <label class="switcher mx-auto">
                                                                <input type="checkbox" class="switcher_input toggle-switch-message"
                                                                    name="status"
                                                                    id="product-status{{ $product['id'] }}" value="1"
                                                                    {{ $product['status'] == 1 ? 'checked' : '' }}
                                                                    data-modal-id="toggle-status-modal"
                                                                    data-toggle-id="product-status{{ $product['id'] }}"
                                                                    data-on-image="product-status-on.png"
                                                                    data-off-image="product-status-off.png"
                                                                    data-on-title="{{ translate('Want_to_Turn_ON') . ' ' . $product['name'] . ' ' . translate('status') }}"
                                                                    data-off-title="{{ translate('Want_to_Turn_OFF') . ' ' . $product['name'] . ' ' . translate('status') }}"
                                                                    data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                                                                    data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </form>
                                                    @endif
                                                </td>

                                                {{-- Action --}}
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-outline-info btn-sm action-update-product-quantity"
                                                            data-url="{{ route('admin.products.get-variations') . '?id=' . $product->id . '&variation=' . urlencode($variation['type']) }}"
                                                            data-target="#update-quantity"
                                                            title="{{ translate('update_quantity') }}">
                                                            <i class="tio-add-circle"></i>
                                                        </button>
                                                        <a class="btn btn-outline-primary btn-sm"
                                                            href="{{ route('admin.products.stock-report', ['product_id' => $product->id, 'variation' => $variation['type']]) }}"
                                                            title="{{ translate('stock_report') }}">
                                                            <i class="tio-file-text"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        {{-- Products without variations --}}
                                        <tr>
                                            <td>{{ $sl++ }}</td>
                                            <td>
                                                <a href="{{ route('admin.products.view', [
                                                    'addedBy' => $product->added_by == 'seller' ? 'vendor' : 'in-house',
                                                    'id' => $product->id,
                                                ]) }}"
                                                    class="media align-items-center gap-2">

                                                    <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product') }}"
                                                        class="avatar border object-fit-cover">

                                                    <span class="media-body title-color">
                                                        {{ Str::limit($product->name, 20) }}
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">—</span>
                                            </td>
                                            <td class="text-center">
                                                {{ setCurrencySymbol(amount: usdToDefaultCurrency($product->unit_price), currencyCode: getCurrencyCode()) }}
                                            </td>
                                            <td class="text-center">{{ $product->current_stock }}</td>
                                            <td class="text-center">{{ $product->order_details_count }}</td>
                                            <td class="text-center">
                                                @if ($product->request_status != 2)
                                                    <form action="{{ route('admin.products.status-update') }}" method="post"
                                                        id="product-status{{ $product['id'] }}-form"
                                                        class="admin-product-status-form">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $product['id'] }}">
                                                        <label class="switcher mx-auto">
                                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                                name="status"
                                                                id="product-status{{ $product['id'] }}" value="1"
                                                                {{ $product['status'] == 1 ? 'checked' : '' }}
                                                                data-modal-id="toggle-status-modal"
                                                                data-toggle-id="product-status{{ $product['id'] }}"
                                                                data-on-image="product-status-on.png"
                                                                data-off-image="product-status-off.png"
                                                                data-on-title="{{ translate('Want_to_Turn_ON') . ' ' . $product['name'] . ' ' . translate('status') }}"
                                                                data-off-title="{{ translate('Want_to_Turn_OFF') . ' ' . $product['name'] . ' ' . translate('status') }}"
                                                                data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                                                                data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </form>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="btn btn-outline-info btn-sm action-update-product-quantity"
                                                    data-url="{{ route('admin.products.get-variations') . '?id=' . $product->id }}"
                                                    data-target="#update-quantity"
                                                    title="{{ translate('update_quantity') }}">
                                                        <i class="tio-add-circle"></i>
                                                    </button>
                                                    <a class="btn btn-outline-primary btn-sm"
                                                        href="{{ route('admin.products.stock-report', ['product_id' => $product->id]) }}"
                                                        title="{{ translate('stock_report') }}">
                                                        <i class="tio-file-text"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $products->links() }}
                        </div>
                    </div>

                    @if (count($products) == 0)
                        @include(
                            'layouts.back-end._empty-state',
                            ['text' => 'no_product_found'],
                            ['image' => 'default']
                        )
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade update-stock-modal" id="update-quantity" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.products.update-quantity') }}" method="post" class="row">
                    <div class="modal-body">
                        @csrf
                        <div class="rest-part-content"></div>
                        <div class="d-flex justify-content-end gap-10 flex-wrap align-items-center">
                            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal" aria-label="Close">
                                {{ translate('close') }}
                            </button>
                            <button class="btn btn--primary" class="btn btn--primary px-4" type="submit">
                                {{ translate('submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
