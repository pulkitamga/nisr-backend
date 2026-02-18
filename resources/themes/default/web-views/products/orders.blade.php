@extends('layouts.front-end.app')

@section('title', 'My Orders')

@section('content')
    @include('layouts.front-end.partials._store-header')


@if (session('warning'))
    <div class="max-w-xl mx-auto bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mt-4">
        {{ session('warning') }}
    </div>
@endif

<div class="container mt-5 mb-5">
    <h2 class="mb-4">My Wholesale Orders</h2>

    @if($orders->count())
    <div class="row">
        @foreach($orders as $order)
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-4 d-flex align-items-center justify-content-center">
                            <img src="{{ getStorageImages(path: $order->product->thumbnail_full_url, type: 'product') }}"
                                 alt="{{ $order->product->name }}"
                                 class="img-fluid rounded"
                                 style="max-height: 70px;">
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-2">{{ $order->product->name }}</h5>
                            <p class="mb-1">Quantity: <strong>{{ $order->product_quantity }}</strong></p>
                            <p class="mb-1">Price: <strong>{{ webCurrencyConverter($order->base_price) }}</strong></p>
                            <p class="mb-1">Order Date: {{ $order->created_at->format('d M Y, h:i A') }}</p>
                            <a href="{{ route('wholesale.invoice', ['order_id' => $order->id]) }}"
                               class="btn btn-sm btn-primary mt-2"
                               target="_blank">
                                View Quotation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p>No orders found.</p>
    @endif
</div>

@endsection
