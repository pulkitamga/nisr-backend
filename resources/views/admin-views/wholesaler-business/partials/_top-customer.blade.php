<!-- Top Wholesalers -->
<div class="card-header">
    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/top-customers.png') }}" alt="">
        {{ translate('top_wholesalers') }}
    </h4>
</div>

<div class="card-body">
    @if($topWholesaler && count($topWholesaler))
        <div class="grid-card-wrap">
            @foreach($topWholesaler as $item)
            
                @if(isset($item->wholeseller))
                    <div class="cursor-pointer"
                         onclick="location.href='{{ route('admin.wholesale.business.wholesaler.profile', [$item->wholeseller->wholesalerBusiness->id]) }}'">
                        <div class="grid-card basic-box-shadow">
                            <div class="text-center">
                                <img class="avatar rounded-circle avatar-lg"
                                     src="{{ getStorageImages(path: $item->wholeseller->image_full_url, type:'backend-profile') }}"
                                     alt="wholesaler-img">
                            </div>
                            <h5 class="mb-0">{{ $item->wholeseller->f_name ?? translate('not_found') }}</h5>
                            <div class="orders-count d-flex gap-1">
                                <div>{{ translate('orders') }} :</div>
                                <div>{{ $item->count }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center">
            <p class="text-muted">{{ translate('no_top_wholesalers') }}</p>
            <img class="w-75" src="{{ dynamicAsset(path: 'public/assets/back-end/img/no-data.png') }}" alt="">
        </div>
    @endif
</div>
