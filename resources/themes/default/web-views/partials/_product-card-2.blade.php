@if(isset($product))
    @php
        $overallRating = getOverallRating($product->reviews);
        $isWholesaler = auth('customer')->check() && auth('customer')->user()->user_type == 1;
        $wholesalePrice = $isWholesaler && isset($product->wholesalePrices)
            ? $product->wholesalePrices->sortBy('min_qty')->first()
            : null;
    @endphp

    <div class="flash_deal_product get-view-by-onclick" data-link="{{ route('product',$product->slug) }}">
        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
            <span class="for-discount-value p-1 ps-2 pe-2 font-bold fs-13">
                <span class="direction-ltr d-block">
                    -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                </span>
            </span>
        @endif

        <div class="d-flex">
            <div class="d-flex align-items-center justify-content-center p-12px">
                <div class="flash-deals-background-image">
                    <img class="__img-125px" alt="" src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}">
                </div>
            </div>

            <div class="flash_deal_product_details ps-3 pe-3 pe-1 d-flex mt-3">
                <div>
                    <div>
                        <a href="{{ route('product',$product->slug) }}"
                           class="flash-product-title text-capitalize fw-semibold">
                            {{ Str::limit($product['name'], 80) }}
                        </a>
                    </div>

                    @if($overallRating[0] != 0 )
                        <div class="flash-product-review">
                            @for($inc=1;$inc<=5;$inc++)
                                @if ($inc <= (int)$overallRating[0])
                                    <i class="tio-star text-warning"></i>
                                @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                    <i class="tio-star-half text-warning"></i>
                                @else
                                    <i class="tio-star-outlined text-warning"></i>
                                @endif
                            @endfor
                            <label class="badge-style2">
                                ({{ count($product->reviews) }})
                            </label>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-8 align-items-center row-gap-0">
                        @if($isWholesaler && $wholesalePrice)
                            <span class="flash-product-price text-success fw-bold">
                                {{ webCurrencyConverter($wholesalePrice->price) }}
                            </span>
                            <span class="badge badge-soft-primary text-xs">
                                {{ translate('Wholesale Price') }}
                            </span>
                        @else
                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                <del class="category-single-product-price">
                                    {{ webCurrencyConverter($product->unit_price) }}
                                </del>
                            @endif
                            <span class="flash-product-price text-dark fw-semibold">
                                {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif
