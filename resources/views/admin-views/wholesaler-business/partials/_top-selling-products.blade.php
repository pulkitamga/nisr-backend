<div class="card-header gap-10">
    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/top-selling-product-icon.png')}}" alt="">
        {{translate('top_selling_products')}}
    </h4>
</div>

<div class="card-body">
    <div class="grid-item-wrap">
        @if(isset($topSellProduct))
        @foreach($topSellProduct as $key => $product)
        @php
        $mainProduct = $product->product; // relationship se actual product
        @endphp

        @if($mainProduct)
        <div class="cursor-pointer get-view-by-onclick" data-link="{{ route('admin.products.view', [
                 'addedBy' => $mainProduct->added_by === 'seller' ? 'vendor' : 'in-house',
                 'id' => $mainProduct->id
             ]) }}">
            <div class="grid-item bg-transparent basic-box-shadow">
                <div class="d-flex gap-10 align-items-center">
                    <img src="{{ getStorageImages(path: $mainProduct->thumbnail_full_url, type: 'backend-product') }}"
                        class="avatar avatar-lg rounded avatar-bordered" alt="{{ $mainProduct->name . '_image' }}">
                    <div class="title-color line--limit-2">
                        {{ Str::limit($mainProduct->name, 20) }}
                    </div>
                </div>

                <div class="orders-count py-2 px-3 d-flex gap-1">
                    <div>{{ translate('sold') }} :</div>
                    <div class="sold-count">{{ $product->total_sold ?? 0 }}</div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
        @else
        <div class="text-center">
            <p class="text-muted">{{translate('no_Top_Selling_Products')}}</p>
            <img class="w-75" src="{{dynamicAsset(path: 'public/assets/back-end/img/no-data.png')}}" alt="">
        </div>
        @endif
    </div>
</div>