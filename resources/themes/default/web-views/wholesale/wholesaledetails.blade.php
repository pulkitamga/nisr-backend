@extends('layouts.front-end.app')

@section('title', $product['name'])

@push('css_or_js')
@include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}" />
<script src="https://cdn.tailwindcss.com"></script>
@endpush

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
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif
<style>
    .form-check-input.moq-toggle {
        width: 100%;
        height: auto;
        transform: scale(1.5);
        cursor: pointer;
    }

</style>

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif
<div class="container mx-5 my-5 ">
    <div class="row">
        <div class="col-md-3 text-center">
            <img src="{{ getStorageImages(path: $product->product->thumbnail_full_url, type: 'product') }}" alt="{{ $product->product->name }}" class="img-fluid rounded shadow-sm" style="max-width: 280px;">
        </div>
        <div class="col-md-9">
            <h2 class="fw-bold mb-2">{{ $product->product->name }}</h2>
            <p class="text-muted mb-4">{{ $product->product->description }}</p>
            @if(auth()->guard('customer')->check() && auth()->guard('customer')->user()->user_type == 1)
            <form action="{{ route('web.addwholesale') }}" method="POST">
                @csrf
                {{-- Send actual product ID --}}
                <input type="hidden" name="product_id" value="{{ $product->product->id }}">
                <input type="hidden" name="seller_id" value="{{ $product->seller_id }}">
                <input type="hidden" name="name" value="{{ $product->product->name }}">
                <input type="hidden" name="tax" value="{{ $product->product->tax }}">
                <input type="hidden" name="tax_model" value="{{ $product->product->tax_model }}">
                <input type="hidden" name="thumbnail" value="{{ getStorageImages(path: $product->product->thumbnail_full_url, type: 'product') }}">
        
                @php
                    $range = $filteredRanges[0] ?? null;
                @endphp
        
                @if($range)
                    <input type="hidden" name="price" value="{{ $range->price_per_piece }}">
                    <input type="hidden" name="discount" value="{{ getProductPriceByType(product: $product, type: 'discount', result: 'value') }}">
                    <input type="hidden" name="shipping_cost" value="0">
                    <input type="hidden" name="price_range_id" value="{{ $range->id }}" id="selected-range-id" data-min="{{ $range->min_qty }}" data-max="{{ $range->max_qty }}">
        
                    <!-- Quantity Selection -->
                    <label for="quantity" class="fw-semibold">{{ translate('Select Quantity') }}:</label>
                    <div class="d-flex align-items-center mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
<input type="number" id="quantity" name="quantity" class="form-control mx-2 text-center w-25" required 
    min="{{ $moqOverride ? 1 : $range->min_qty }}">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                    </div>
                    <p class="text-muted mb-3">{{ translate('Min') }}: <strong id="min-val">{{ $range->min_qty }}</strong> {{ translate('pcs') }}
                    <div class="mb-3">
                        <span class="d-flex align-items-end gap-2">
                            <span class="text-success fs-4 fw-bold">
                                {{ webCurrencyConverter($range->price_per_piece) }} / piece
                            </span>
                             <span class="text-nowrap fs-10">
                        @if ($product->product->tax_model === "exclude")
                        ({{ translate('tax')}}
                        : {{ webCurrencyConverter(amount: $product->product['tax'])}}
                        )
                        @else
                        ({{ translate('tax_included')}})
                        @endif
                    </span>
                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                <del class="text-muted fs-5 fw-semibold">
                                    {{ webCurrencyConverter($product->unit_price) }}
                                </del>
                            @endif
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submit-btn">{{ __("Add to Purchase Order") }}</button>
                @else
                    <p class="text-danger">{{ __("No price range available.") }}</p>
                @endif
            </form>
        @else
            <p class="text-danger">{{ __("You need to be a wholesaler to add to cart.") }}</p>
        @endif
        

        </div>
    </div>
</div>
@endsection
@push('css_or_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantityInput = document.getElementById('quantity');
        const submitBtn = document.getElementById('submit-btn');
        const selectedRange = document.getElementById('selected-range-id');
console.log('moq is ', $moqOverride)
       @if(!$moqOverride)
       console.log('moq is ', $moqOverride)

const minQty = parseInt(selectedRange.dataset.min);
const minimumQtyText = @json(translate('Minimum quantity allowed is'));
const maxQty = parseInt(selectedRange.dataset.max);

quantityInput.value = minQty;

window.changeQuantity = function (amount) {
    let currentQty = parseInt(quantityInput.value) || 0;
    let newQty = currentQty + amount;

    if (newQty < minQty) {
        alert(`${minimumQtyText} ${minQty}.`);
        quantityInput.value = minQty;
    } else {
        quantityInput.value = newQty;
    }

    checkQuantity();
};

quantityInput.addEventListener('blur', checkQuantity);
quantityInput.addEventListener('input', validateSilently);

function validateSilently() {
    const qty = parseInt(quantityInput.value);
    submitBtn.disabled = isNaN(qty) || qty < minQty;
}

function checkQuantity() {
    const qty = parseInt(quantityInput.value);
    if (isNaN(qty) || qty < minQty) {
        alert(`${minimumQtyText} ${minQty}.`);
        quantityInput.value = minQty;
        submitBtn.disabled = true;
    } else {
        submitBtn.disabled = false;
    }
}

@else
console.log('moq is ', $moqOverride)
quantityInput.value = 1;
submitBtn.disabled = false;

window.changeQuantity = function (amount) {
    let currentQty = parseInt(quantityInput.value) || 0;
    let newQty = currentQty + amount;
    if (newQty > 0) quantityInput.value = newQty;
};

quantityInput.addEventListener('input', function () {
    const qty = parseInt(quantityInput.value);
    submitBtn.disabled = isNaN(qty) || qty < 1;
});
@endif

    });
</script>


@endpush
