@extends('layouts.back-end.app')

@section('title', translate('Product_Inventory'))

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/products.png') }}" alt="">
            {{ translate('Product_Inventory') }}
        </h2>
    </div>

    <!-- Search form for vendor -->
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

    <!-- Product Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Stock</th>
                        <th>Code</th>
                        <th>Vendor</th>
                        <th>{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $key => $product)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->current_stock }}</td>
                        <td>{{ $product->code }}</td>
                        <td>{{ $product->seller ? $product->seller->f_name . ' ' . $product->seller->l_name : 'N/A' }}</td>
                        <td>
                            <!-- Add Action Buttons as needed -->
                            <a href="{{ route('admin.branch.vendors.view', $product->seller->id) }}" class="btn btn-outline-info btn-sm">{{ translate('View') }}</a>
                        </td>
                    </tr>
                    @endforeach

                    @if($products->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">No products found for this branch</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <div class="d-flex justify-content-end">
                                {!! $products->links() !!}
                            </div>
                        </td>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>
</div>
@endsection
