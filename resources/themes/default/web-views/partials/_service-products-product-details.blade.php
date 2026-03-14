@if(isset($product))
@php($overallRating = getOverallRating($product->reviews))
@php($serviceTitle = $product->translations->firstWhere('key', 'service_tittle')->value ?? $product->service->title)
<div class="flash_deal_product rtl cursor-pointer mb-2 get-view-by-onclick"
    data-link="{{ route('service',$product->slug) }}">
   
    <div class="d-flex">
        <div class="d-flex align-items-center justify-content-center p-3">
            <div class="flash-deals-background-image image-default-bg-color">
                <img class="__img-125px" alt="" src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}">
            </div>
        </div>
        <div class=" flash_deal_product_details pl-3 pr-3 pr-1 d-flex align-items-center">
            <div>
                <div>
                    <span class="flash-product-title">
                        {{ $serviceTitle }}
                    </span>
                </div>
                @if($overallRating[0] != 0 )
                <div class="flash-product-review">
                    @for($inc=1;$inc<=5;$inc++) @if ($inc <=(int)$overallRating[0]) <i class="tio-star text-warning">
                        </i>
                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0]>
                            ((int)$overallRating[0]))
                            <i class="tio-star-half text-warning"></i>
                            @else
                            <i class="tio-star-outlined text-warning"></i>
                            @endif
                            @endfor
                            <label class="badge-style2">
                                ( {{ count($product->reviews) }} )
                            </label>
                </div>
                @endif
                <div class="d-flex flex-wrap gap-8 align-items-center row-gap-0">
                    @if($product->discount > 0)
                    <del class="category-single-product-price">
                        {{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}
                    </del>
                    @endif
                    <span class="flash-product-price fw-semibold text-dark">
                        {{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}
                    </span>
                </div>

            </div>
        </div>
    </div>
</div>
@endif
