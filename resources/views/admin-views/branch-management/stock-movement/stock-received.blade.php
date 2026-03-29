@extends('layouts.back-end.app')

@section('title', translate('Stock_Transfer'))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Stock_Transfer_List') }}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1"></span>
            </h2>
        </div>

        <div class="mt-20">
            <div class="card">
                <div class="card-header gap-3 align-items-center">
                    <h5 class="mb-0 mr-auto">
                        {{translate('stock_Transfer_List')}}
                        <span class="badge badge-soft-dark radius-50 fz-14 ml-1"></span>
                    </h5>

                    <form action="{{ url()->current() }}" method="GET">
                        <input type="hidden" name="restock_date" value="{{request('restock_date')}}">
                        <input type="hidden" name="category_id" value="{{request('category_id')}}">
                        <input type="hidden" name="sub_category_id" value="{{request('sub_category_id')}}">
                        <input type="hidden" name="brand_id" value="{{request('brand_id')}}">
                        <div class="input-group input-group-merge input-group-custom">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                    placeholder="{{ translate('search_by_Product_Name')}}"  aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                            <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                        </div>
                    </form>
                    <div class="dropdown d-none">
                        <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.products.restock-export', ['restock_date' => request('restock_date'),'brand_id' => request('brand_id'), 'category_id' => request('category_id'), 'sub_category_id' => request('sub_category_id'),  'searchValue' => request('searchValue')])}}">
                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>
                    </div>
                    <a href="{{route('admin.stock-transfer.add')}}" type="button" class="btn btn--primary text-nowrap">
                        <i class="tio-add"></i>
                        {{translate('transfer_New_Product_Stock')}}
                    </a>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th class="text-center">{{ translate('SL') }}</th>
                            <th class="text-start">{{ translate('To Branch') }}</th>
                            <!-- <th class="text-start">{{ translate('To Branch') }}</th> -->
                            <th class="text-start">{{ translate('Transfer Date') }}</th>
                            <th class="">{{ translate('Products') }}</th>
                            <th class="">{{ translate('Category') }}</th>
                            <th class="">{{ translate('Attribute') }}</th>
                            <th class="text-center">{{ translate('Qty') }}</th>
                            <th class="text-center">{{ translate('Status') }}</th>
                            <!-- <th class="text-center">{{ translate('action') }}</th> -->
                        </tr>
                        </thead>
                        <tbody> 
                            @foreach($aStockTransfers as $key => $transferRequest)
                                @php
                                    $productCount = $transferRequest->products->count(); // Get the number of products
                                @endphp
                                @foreach($transferRequest->products as $index => $product)
                                    <tr>
                                        @if ($index == 0)
                                            <th scope="row" class="text-center align-middle" rowspan="{{ $productCount }}">{{ $aStockTransfers->firstItem() + $key }}</th>
                                            <td rowspan="{{ $productCount }}" class="align-middle">
                                                {{ $transferRequest->toBranch ? $transferRequest->toBranch->getTranslatedField('branch_name') : 'N/A' }}
                                            </td>
                                            <!-- <td rowspan="{{ $productCount }}" class="text-start align-middle">
                                                {{ $transferRequest->toBranch ? $transferRequest->toBranch->getTranslatedField('branch_name') : 'N/A' }}
                                            </td> -->
                                            <td rowspan="{{ $productCount }}" class="text-startStockTransfers align-middle">
                                                {{ $transferRequest->transfer_date ? date('M d, Y', strtotime($transferRequest->transfer_date)) : 'N/A' }}
                                            </td>
                                        @endif
                                        <td class="">{{ $product->product->getTranslatedField('name') }}</td>
                                        <td class="">{{ $product->category->getTranslatedField('name') }}</td>
                                        <td class="">{{ $product->attribute }}</td>
                                        <td class="text-center align-middle">{{ $product->quantity }}</td>
                                        <td class="text-success text-center align-middle">{{ translate( $product->status ) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {{ $aStockTransfers->links() }}
                    </div>
                </div>

                @if(count($aStockTransfers)==0)
                    @include('layouts.back-end._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                @endif
            </div>
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
                            <button class="btn btn--primary" class="btn btn--primary" type="submit">
                                {{ translate('update') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script type="text/javascript">
        changeInputTypeForDateRangePicker($('input[name="restock_date"]'));
    </script>
@endpush

