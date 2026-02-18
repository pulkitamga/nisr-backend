@extends('layouts.front-end.app')

@section('title', translate('my_Wholesale_Order_List'))

@section('content')
    @include('layouts.front-end.partials._store-header')
<style>
    .collapse {
    visibility: visible !important;
}

.navbar-collapse{

    flex-grow: 0 !important;
}
</style>
<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')

        <section class="col-12 col-lg-9 __customer-profile customer-profile-wishlist px-0 mb-4">
            <div class="card __card web-direction customer-profile-orders h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('my_Quotations') }}</h5>
                    </div>

                    @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table __table __table-2 text-center">
                            <thead class="thead-light">
                                <tr>
                                    <td class="text-start ">{{ translate('Order_List') }}</td>
                                    <td>{{ translate('Status') }}</td>
                                    <td>{{ translate('Total') }}</td>
                                    <td>{{ translate('Action') }}</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    
                                    <td><div class="media-order">
                                        <a href="{{ route('account-order-details', ['id'=>$order->id]) }}"
                                            class="d-block position-relative">
                                            @if($order->seller_is == 'seller')
                                            <img alt="{{ translate('shop') }}"
                                                src="{{ getStorageImages(path: $order?->seller?->shop->image_full_url, type: 'shop') }}">
                                            @elseif($order->seller_is == 'admin')
                                            <img alt="{{ translate('shop') }}"
                                                src="{{ getStorageImages(path: $web_config['fav_icon'], type: 'shop') }}">
                                            @endif
                                        </a>
                                        <div class="cont text-start">
                                            <h6 class="font-weight-bold m-0 mb-1">
                                                <a href="{{ route('account-order-details', ['id'=>$order->id]) }}"
                                                    class="fs-14 font-semibold">
                                                    {{ translate('order') }} #{{$order['id']}}
                                                </a>
                                            </h6>
                                           
                                            <div class="text-secondary-50 fs-12 font-semibold mt-1">
                                                {{date('d M, Y h:i A',strtotime($order['created_at'])) }}
                                            </div>
                                        </div>
                                    </div></td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $order->status == 'approved' ? 'success' : 'primary' }}">
                                            {{ translate($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ webCurrencyConverter($order->final_price ?? 0) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('wholesale.account.order.quotation', $order->id) }}"
                                            class="btn-outline--info text-base p-1 btn-shadow rounded-full" title="{{ translate('view_order_details') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        {{ $orders->links() }}
                    </div>
                    @else
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="d-flex flex-column justify-content-center align-items-center gap-3">
                            <img src="{{ theme_asset('public/assets/front-end/img/empty-icons/empty-orders.svg') }}"
                                alt="" width="100">
                            <h5 class="text-muted fs-14 font-semi-bold text-center">{{
                                translate('You_have_not_any_order_yet') }}!</h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

@endsection