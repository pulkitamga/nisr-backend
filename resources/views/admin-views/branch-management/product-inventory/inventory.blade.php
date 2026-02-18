@extends('layouts.back-end.app')

@section('title', translate('Product_Inventory'))

@section('content')
<div class="content container-fluid">
    <!-- Page Title -->
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex gap-2 align-items-center">
            <img src="{{ asset('public/assets/back-end/img/stock.png') }}" alt="" width="30">
            {{ translate('Branch Product Inventory') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ count($products)}}</span>
        </h2>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="searchValue" class="form-control" placeholder="{{ translate('Search_by_Name_or_Email_or_Phone') }}" value="{{ request('searchValue') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Table -->
    <div class="card">
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
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span>{{ Str::limit($product->name, 25) }}</span>
                            </div>
                        </td>
                        <td>{{ $product->code }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>{{ $product->subCategory->name ?? 'N/A' }}</td>
                        <td>{{ $product->subSubCategory->name ?? 'N/A' }}</td>
                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                        <td>{{ $product->unit }}</td>
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
                            @php
                            $statusMap = ['Pending', 'Approved', 'Denied'];
                            @endphp
                            <span class="badge badge-soft-{{ $product->request_status == 0 ? 'warning' : ($product->request_status == 1 ? 'success' : 'danger') }}">
                                {{ $statusMap[$product->request_status] ?? 'N/A' }}
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
                        <td colspan="21" class="text-center">{{ translate('No products found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
