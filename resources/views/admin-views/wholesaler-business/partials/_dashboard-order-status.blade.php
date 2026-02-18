<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card" href="{{route('admin.wholesale.business.wholesale.confirmedorder')}}">
        <h5 class="business-analytics__subtitle">{{translate('total_wholesale_order')}}</h5>
        <h2 class="business-analytics__title">{{ $data['order'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" width="30" height="30"
            class="business-analytics__img" alt="">
    </a>
</div>
{{-- <div class="col-sm-6 col-lg-3">
    <a class="business-analytics get-view-by-onclick card" href="{{route('admin.vendors.vendor-list')}}">
        <h5 class="business-analytics__subtitle">{{translate('total_quotation')}}</h5>
        <h2 class="business-analytics__title">{{ $data['store'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-stores.png')}}" class="business-analytics__img"
            alt="">
    </a>
</div> --}}
<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card">
        <h5 class="business-analytics__subtitle">{{translate('total_Products')}}</h5>
        <h2 class="business-analytics__title">{{ $data['product'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-product.png')}}"
            class="business-analytics__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card" href="{{route('admin.wholesale.business.list')}}">
        <h5 class="business-analytics__subtitle">{{translate('total_WHolesaler')}}</h5>
        <h2 class="business-analytics__title">{{ $data['customer'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-customer.png')}}"
            class="business-analytics__img" alt="">
    </a>
</div>


<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_pending" href="{{route('admin.wholesale.business.order.request')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: '/public/assets/back-end/img/pending.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Purchase_order')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['purchase']}}
        </span>
    </a>
</div>

<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_confirmed" href="{{route('admin.wholesale.business.wholesale.order')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/confirmed.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Quotation')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['quotation']}}
        </span>
    </a>
</div>




<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_pending" href="{{route('admin.wholesale.business.wholesale.order')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: '/public/assets/back-end/img/pending.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('rejected')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['rejected']}}
        </span>
    </a>
</div>

<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_confirmed" href="{{route('admin.wholesale.business.wholesale.confirmedorder')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/confirmed.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('confirmed')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['confirmed']}}
        </span>
    </a>
</div>

<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_packaging" href="{{route('admin.wholesale.business.wholesale.confirmedorder')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/packaging.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('partials')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['partials']}}
        </span>
    </a>
</div>

<div class="col-sm-6 col-lg-4">
    <a class="order-stats order-stats_out-for-delivery" href="{{route('admin.wholesale.business.wholesale.confirmedorder')}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/out-of-delivery.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('delivered')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['delivered']}}
        </span>
    </a>
</div>