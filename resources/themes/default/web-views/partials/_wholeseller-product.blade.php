<head>
</head>

@if(auth()->guard('customer')->check() && auth()->guard('customer')->user()->user_type == 1)
@php
$userTier = strtolower(trim(auth()->guard('customer')->user()->tier ?? ''));
@endphp

<section class="mt-4">
    <div class="container">
        <div class="card p-4">
            <div class="d-flex justify-content-between gap-2 flex-wrap align-items-center mb-4">
                <h3 class="fw-semibold mb-0 text-capitalize text-center mobile-head">{{ translate('Wholesale Products') }}</h3>

                <a href="{{ route('wholesale.products') }}" class="btn btn-sm btn-outline-primary">{{ translate('View All') }}</a>

            </div>
            <div class="row g-4">
                @if(isset($wholesaleProducts) && count($wholesaleProducts) > 0)
                @foreach($wholesaleProducts as $item)
                @php
                $product = $item->product;
                $priceRanges = $item->price_list;
                $image = $product->thumbnail ?? null;

                $filteredRanges = collect($priceRanges)->filter(function ($range) use ($userTier) {
                return strtolower(trim($range->tier)) === $userTier;
                });
                @endphp

                <div class="col-md-6 col-sm-6 col-lg-3">
                    <div class="card bg-white shadow-lg rounded-lg overflow-hidden position-relative transition-all"
                        style="backdrop-filter: blur(10px); transition: all 0.3s ease-in-out;">
                        <!-- Outer div - Bootstrap Flexbox -->
                        <div class="d-flex flex-column w-100">

                            <!-- Image Section -->
                            <div class="w-100 w-lg-50 d-flex justify-content-center align-items-center">
                                <a href="{{ route('web.product.view', [
                                    $product->slug,
                                    'variant' => $item->variation_key
                                ]) }}" class="text-center w-100">
                                    <img class="" style="height: 190px; object-fit: cover;"
                                        src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                        alt="{{ $product->name }}">
                                </a>
                            </div>

                            <!-- Content Section -->
                            <div class="w-100 w-lg-50 p-3 d-flex flex-column gap-2">
                                <h6 class="text-center fs-5 fw-semibold text-dark mb-2" style="min-height: 40px;">
                                    <a href="{{ route('web.product.view', [$product->slug]) }}" class="text-dark text-decoration-none">
                                        {{ Str::limit($product->name, 40) }}
                                    </a>
                                </h6>

                                <!-- Pricing Ranges -->
                                <ul class="list-unstyled px-2 text-muted mt-1">
                                    @php
                                    $displayPrice = $product->unit_price;

                                    if ($item->variation_type && $item->variation_key) {
                                    $displayPrice = $product->getVariationPrice($item->variation_type, $item->variation_key);
                                    }
                                    @endphp

                                    <!-- Line-through original price (variation ya default) -->
                                    <li class="category-single-product-price line-through text-gray-500 text-center">
                                        {{ setCurrencySymbol(
            amount: usdToDefaultCurrency(amount: $displayPrice),
            currencyCode: getCurrencyCode()
        ) }}
                                    </li>

                                    <!-- Variation Label (sirf agar variation hai tab dikhao) -->
                                    @if($item->variation_type && $item->variation_key)
                                    <li class="text-center mb-2">
                                        <span class="badge badge-soft-info fw-medium">
                                            {{ str_replace([':', '|'], [' : ', ' • '], $item->variation_key) }}
                                        </span>
                                    </li>
                                    @endif

                                    <!-- Wholesale Tier Pricing -->
                                    @forelse($filteredRanges as $range)
                                    <li class="d-flex flex-column align-items-center gap-1 small text-center">
                                        <span>Min Quantity: {{ $range->min_qty }} pcs</span>

                                        <h4 class="text-muted text-accent mb-1">
                                            <span class="text-primary fw-semibold fs-5">
                                                {{ setCurrencySymbol(
                    amount: usdToDefaultCurrency(amount: $range->price_per_piece),
                    currencyCode: getCurrencyCode()
                ) }}
                                            </span>
                                            / Piece

                                            <span class="btn btn-primary btn-sm px-2 py-0 ms-1">-{{ $range->discount }}%</span>

                                            <span class="text-nowrap small d-block mt-1">
                                                @if ($product->tax_model === "exclude")
                                                ({{ translate('tax') }}: {{ webCurrencyConverter(amount: $product->tax) }})
                                                @else
                                                ({{ translate('tax_included') }})
                                                @endif
                                            </span>
                                        </h4>
                                    </li>
                                    @empty
                                    <li class="text-danger small text-center">
                                        {{ translate('No pricing available for your tier.') }}
                                    </li>
                                    @endforelse
                                </ul>

                                <!-- Button -->
                                <div class="mt-1 text-center">
                                   <a href="{{ route('web.product.view', [
                                        $product->slug,
                                        'variant' => $item->variation_key
                                    ]) }}" class="btn btn-sm btn-outline-primary">

                                        {{ translate('Add To Purchase Order') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12 text-center text-muted py-5">
                    {{ translate('No wholesale products found.') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif