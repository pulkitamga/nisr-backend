@extends('layouts.back-end.app')

@section('title', translate("Transfer Stock Request"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('Requested_stock_details')}}
            </h2>
        </div>
        <div class="card card-top-bg-element mb-5">
            <div class="card-body">
                <hr>
                <div class="d-flex gap-3 flex-wrap flex-lg-nowrap">
                    <div class="row gy-3 flex-grow-1 w-100">
                        <div class="col-sm-6 col-xxl-3">
                            <h4 class="mb-3 text-capitalize">{{translate('Branch_information')}}</h4>

                            <div class="pair-list">
                                <div>
                                    <span class="key text-nowrap">{{translate('From_branch')}}</span>
                                    <span>:</span>
                                    <span class="value ">{{ $stockRequest->fromBranch ? $stockRequest->fromBranch->branch_name : 'N/A' }}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Phone')}}</span>
                                    <span>:</span>
                                    <span class="value">{{ $stockRequest->fromBranch ? $stockRequest->fromBranch->phone : 'N/A' }}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Address')}}</span>
                                    <span>:</span>
                                    <span class="value">{{ $stockRequest->fromBranch ? $stockRequest->fromBranch->branch_address : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center g-2 mb-3">
                    <div class="col-sm-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img width="20" class="mb-1"
                                 src="{{dynamicAsset(path: 'public/assets/back-end/img/admin-wallet.png')}}" alt="">
                            {{translate('Requested_Product_Stock')}}
                        </h4>
                    </div>
                </div>

                <div class="row g-2" id="order_stats">
                    <div class="table-responsive">
	                    <table id="datatable" class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
	                        <thead class="thead-light thead-50 text-capitalize">
	                        <tr>
	                            <th class="text-center">{{ translate('SL') }}</th>
	                            <th class="">{{ translate('Products') }}</th>
	                            <th class="">{{ translate('Category') }}</th>
	                            <th class="">{{ translate('Attribute') }}</th>
	                            <th class="text-center">{{ translate('QTY') }}</th>
	                            <th class="">{{ translate('From_branch') }}</th>
	                            <th class="">{{ translate('to_branch') }}</th>
	                        </tr>
	                        </thead>
	                        <tbody> 
                                @foreach($stockRequest->products as $index => $product)
                                	<tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="">{{ $product->product->name }}</td>
                                        <td class="">{{ $product->category->name }}</td>
                                        <td class="">{{ $product->attribute->name }}</td>
                                        <td class="text-center align-middle">{{ $product->quantity }}</td>
                                        <td class="">
                                            <div class="{{ $product->status == 'transferred' ? ' text-success' : 'text-danger' }} d-flex align-items-center gap-3 font-weight-bolder mb-2">
                                                @if($product->status == 'transferred')
                                                    {{$product->received_from->branch_name}}
                                                @endif
                                                @if($product->status != 'transferred')
                                                {{translate('Select_Branch')}}
                                                <div class="ripple-animation" data-toggle="modal" data-target="#showBranchesStockModal" data-stock-request-product-id="{{ $product->id }}" data-stock-request-id="{{ $product->stock_requests_id }}">
                                                    <i class="tio-edit"></i>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="">{{ $stockRequest->fromBranch ? $stockRequest->fromBranch->branch_name : 'N/A' }}</td>
                                    </tr>
                                @endforeach
	                        </tbody>
	                    </table>
	                </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin-views.stock-request.partials.branches')

    <span id="route-branches-products-stock" data-url="{{ route('admin.stock-request.get-branches-product-stock') }}"></span>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/stock-request-add-update.js') }}"></script>
@endpush
