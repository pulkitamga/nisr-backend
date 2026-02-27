@php use App\Utils\Helpers; @endphp
@extends('layouts.back-end.app')
@section('title', translate('Wholesaler_dashboard'))
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    @if (Helpers::module_permission_check('dashboard'))

        <div class="content container-fluid">
            <div class="page-header pb-0 mb-0 border-0">
                <div class="flex-between align-items-center">
                    <div>
                        <h1 class="page-header-title">{{ translate('welcome') . ' ' . auth('admin')->user()->name }}</h1>
                        <p>{{ translate('monitor_your_business_analytics_and_statistics') . '.' }}</p>
                    </div>
                </div>
            </div>
            <div class="card mb-2 remove-card-shadow">
                <div class="card-body">
                    <div class="row flex-between align-items-center g-2 mb-3">
                        <div class="col-sm-6">
                            <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/business_analytics.png') }}"
                                    alt="">{{ translate('wholesaler_business_analytics') }}
                            </h4>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-sm-end">
                            <select class="custom-select w-auto" name="statistics_type" id="statistics_type">
                                <option value="overall"
                                    {{ session()->has('statistics_type') && session('statistics_type') == 'overall' ? 'selected' : '' }}>
                                    {{ translate('overall_statistics') }}
                                </option>
                                <option value="today"
                                    {{ session()->has('statistics_type') && session('statistics_type') == 'today' ? 'selected' : '' }}>
                                    {{ translate('todays_Statistics') }}
                                </option>
                                <option value="this_month"
                                    {{ session()->has('statistics_type') && session('statistics_type') == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_Months_Statistics') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2" id="order_stats">
                        @include('admin-views.wholesaler-business.partials._dashboard-order-status', ['data' => $data])
                    </div>
                </div>
            </div>

            <div class="row g-1">
               
                <div class="col-md-6 col-xl-6">
                    <div class="card h-100 remove-card-shadow">
                        @include('admin-views.wholesaler-business.partials._top-customer', [
                            'topWholesaler' => $data['top_wholesaler'],
                        ])
                    </div>
                </div>

                <div class="col-md-6 col-xl-6">
                    <div class="card h-100 remove-card-shadow">
                        @include('admin-views.wholesaler-business.partials._top-selling-products', [
                            'topSellProduct' => $data['topSellProduct'],
                        ])
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-12 mb-2 mb-sm-0">
                        <h3 class="text-center">{{ translate('hi') }} {{ auth('admin')->user()->name }}
                            {{ ' , ' . translate('welcome_to_dashboard') }}.</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <span id="earning-statistics-url" data-url="{{ route('admin.dashboard.earning-statistics') }}"></span>
    <span id="order-status-url" data-url="{{ route('admin.dashboard.order-status') }}"></span>
    <span id="seller-text" data-text="{{ translate('vendor') }}"></span>
    <span id="message-commission-text" data-text="{{ translate('commission') }}"></span>
    <span id="in-house-text" data-text="{{ translate('In-house') }}"></span>
    <span id="customer-text" data-text="{{ translate('customer') }}"></span>
    <span id="store-text" data-text="{{ translate('store') }}"></span>
    <span id="product-text" data-text="{{ translate('product') }}"></span>
    <span id="order-text" data-text="{{ translate('order') }}"></span>
    <span id="brand-text" data-text="{{ translate('brand') }}"></span>
    <span id="business-text" data-text="{{ translate('business') }}"></span>
    <span id="orders-text" data-text="{{ $data['order'] }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/dashboard.js') }}"></script>
@endpush
