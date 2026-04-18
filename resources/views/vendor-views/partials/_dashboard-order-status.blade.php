<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_pending" href="{{route('vendor.orders.list',['pending'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/pending.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Pending')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['pending']}}</span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_confirmed" href="{{route('vendor.orders.list',['confirmed'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/confirmed.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('confirmed')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['confirmed']}}</span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_packaging" href="{{route('vendor.orders.list',['processing'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/packaging.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Packaging')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['processing']}}</span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_out-for-delivery" href="{{route('vendor.orders.list',['out_for_delivery'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/out-of-delivery.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('out_For_Delivery')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['out_for_delivery']}}</span>
    </a>
</div>


<div class="ol-sm-6 col-lg-3">
    <a class="order-stats order-stats_delivered" href="{{route('vendor.orders.list',['delivered'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivered.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Delivered')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['delivered']}}</span>
    </a>
</div>
<div class="ol-sm-6 col-lg-3">
    <a class="order-stats order-stats_canceled" href="{{route('vendor.orders.list',['canceled'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/canceled.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('canceled')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['canceled']}}</span>
    </a>
</div>
<div class="ol-sm-6 col-lg-3">
    <a class="order-stats order-stats_returned" href="{{route('vendor.orders.list',['returned'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/returned.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('Returned')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['returned']}}</span>
    </a>
</div>
<div class="ol-sm-6 col-lg-3">
    <a class="order-stats order-stats_failed" href="{{route('vendor.orders.list',['failed'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/failed-to-deliver.png')}}" alt="">
            <h6 class="order-stats__subtitle">{{translate('failed_To_Delivery')}}</h6>
        </div>
        <span class="order-stats__title">{{$orderStatus['failed']}}</span>
    </a>
</div>
