@extends('layouts.back-end.app')
@section('title', translate('Order_details'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path:'public/assets/back-end/css/owl.min.css') }}">
<style>

</style>


@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif



<div class="content container-fluid">
    <h3>{{ translate('Order Tracking') }} - #{{ $order->purchase_order_no }}</h3>



    <div>
        <ul class="nav nav-tabs mb-4" id="trackingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab" aria-controls="delivery" aria-selected="true">{{ __('Delivery') }}</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">{{ __('Payment') }}</a>
            </li>
        </ul>
    </div>
    <div class="card">

        <div class="card-body">
            <div class="tab-content mt-3" id="trackingTabsContent">
                <div class="tab-pane fade show active" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">
                    @include('admin-views.wholesaler-business.partials.delivery_tables')
                </div>

                <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                    @include('admin-views.wholesaler-business.partials.payment_tables')
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')

<script>
    document.getElementById('payment_amount').addEventListener('input', function() {
        let remaining = parseFloat(document.getElementById('remaining_before').value) || 0;
        let entered = parseFloat(this.value) || 0;
        let after = remaining - entered;

        document.getElementById('remaining_after').value = after.toFixed(2);
    });
</script>
@endpush
