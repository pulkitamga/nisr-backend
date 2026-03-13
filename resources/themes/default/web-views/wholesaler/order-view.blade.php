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
                        <h3>{{ translate('Wholesale Order') }} #{{ $order->id }}</h3>
                    </div>

                    {{-- Order Info Card --}}
                    <div class="border rounded p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>{{ translate('Delivery Status') }}:</strong>
                                <span>{{ $order->delivery_status ?? 'Pending' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Payment Method') }}:</strong>
                                <span>{{ $order->payment_method ?? 'Pending' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Payment Status') }}:</strong>
                                <span>{{ $order->payment_status ?? 'Unpaid' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Order Status') }}:</strong>
                                <span class="badge badge-{{ $order->status == 'approved' ? 'success' : 'primary' }}">
                                    {{ translate($order->status) }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Approved By') }}:</strong>
                                <span>{{ $order->approver->name ?? 'Pending' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Approved At') }}:</strong>
                                <span>
                                    {{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('d M Y,
                                    h:i A') : 'Pending' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('Ordered At') }}:</strong>
                                <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Product Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ translate('Product') }}</th>
                                    <th>{{ translate('Quantity') }}</th>
                                    <th>{{ translate('Price') }}</th>
                                    <th>{{ translate('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="media gap-3 align-items-center">


                                            <img class="d-block get-view-by-onclick aspect-1 object-cover"
                                                data-link="{{ route('product',$item->product['slug']) }}"
                                                src="{{ getStorageImages(path: $item->product->thumbnail_full_url, type: 'product') }}"
                                                alt="{{ translate('product') }}" width="100">{{ $item->product->name ??
                                            '-' }}
                                        </div>
                                    </td>
                                    <td class=" align-middle">{{ $item->product_quantity }}</td>
                                    <td class=" align-middle">{{ webCurrencyConverter($item->base_price) }}</td>
                                    <td class=" align-middle">{{ webCurrencyConverter(($item->product_quantity) * $item->base_price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <h5 class="mt-3">
                        <strong>{{ translate('Total Price') }}:</strong>
                        {{ webCurrencyConverter($order->items->sum(fn($i) => ($i->product_quantity ?? 0) *
                        $i->base_price)) }}
                    </h5>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection