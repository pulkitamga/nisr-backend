@extends('layouts.back-end.app')

@section('title', translate('Vendor_details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-print-none pb-2">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="mb-3">
                        <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}" alt="">
                            {{translate('Vendor_details')}}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('admin.branch.vendors') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{ translate('Back') }}
            </a>
        </div>

        <div class="row g-2">
            <div class="col-xl-6 col-xxl-4 col--xxl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-4 d-flex align-items-center gap-2">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}" alt="">
                            {{translate('Vendor').' # '.$vendor->id}}
                        </h4>

                        <div class="customer-details-new-card">
                            <div class="customer-details-new-card-content">
                                <h6 class="name line--limit-2" data-toggle="tooltip" data-placement="top" title="{{$vendor->f_name.' '.$vendor->l_name}}">
                                    {{$vendor->f_name.' '.$vendor->l_name}}
                                </h6>
                                <ul class="customer-details-new-card-content-list">
                                    <li>
                                        <span class="key">{{translate('Contact')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{!empty($vendor->phone) ? $vendor->phone : translate('no_Data_found')}}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('Email')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{$vendor->email ?? translate('no_Data_found')}}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('Status')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{ $vendor->status == 1 ? translate('Active') : translate('Inactive') }}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('free_Delivery_Over_Amount')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{$vendor->free_delivery_over_amount ?? translate('no_Data_found')}}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('sales_commission_percentage')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{$vendor->sales_commission_percentage ?? translate('no_Data_found')}}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('GST')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{$vendor->gst ?? translate('no_Data_found')}}</strong>
                                    </li>
                                    <li>
                                        <span class="key">{{translate('minimum_Order_Amount')}}</span>
                                        <span class="me-3">:</span>
                                        <strong class="value">{{$vendor->minimum_order_amount ?? translate('no_Data_found')}}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-xxl-8 col--xxl-8">
                <div class="card mb-2">
                    <div class="card-body">
                        <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{translate('bank_and_branch_details')}}</h4>
                        <ul class="customer-details-new-card-content-list">
                            <li>
                                <span class="key">{{translate('bank_Name')}}</span>
                                <span class="me-3">:</span>
                                <strong class="value">{{$vendor->bank_name ?? translate('no_Data_found')}}</strong>
                            </li>
                            <li>
                                <span class="key">{{translate('account_No')}}</span>
                                <span class="me-3">:</span>
                                <strong class="value">{{$vendor->account_no ?? translate('no_Data_found')}}</strong>
                            </li>
                            <li>
                                <span class="key">{{translate('holder_Name')}}</span>
                                <span class="me-3">:</span>
                                <strong class="value">{{$vendor->holder_name ?? translate('no_Data_found')}}</strong>
                            </li>
                            <li>
                                <span class="key">{{translate('Branch')}}</span>
                                <span class="me-3">:</span>
                                <strong class="value">{{$vendor->branch ?? translate('no_Data_found')}}</strong>
                            </li>
                            <li>
                                <span class="key">{{translate('app_language')}}</span>
                                <span class="me-3">:</span>
                                <strong class="value">{{$vendor->app_language ?? translate('no_Data_found')}}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{translate('products_and_stock')}}</h4>
                        <p>{{ translate('total_Products') }}: {{$totalProducts}}</p>
                        <p>{{ translate('total_stock') }}: {{$totalStock}}</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-xxl-12">
               
            </div>
        </div>
    </div>
@endsection
