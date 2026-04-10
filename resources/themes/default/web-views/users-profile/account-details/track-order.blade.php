@extends('layouts.front-end.app')

@section('title', translate('order_Details'))

@push('css')


@endpush

@section('content')
<div class="container pb-5 mb-2 mt-3">
    <div class="row g-3">
        @include('web-views.partials._profile-aside')

        <section class="col-lg-9">
            @include('web-views.users-profile.account-details.partial',['order'=>$orderDetails])

            @if($orderDetails->order_type == 'default_type' && getWebConfig(name: 'order_verification'))
                <div class="card border-0 mb-4">
                    <div class="card-body p-0">
                        <div class="bg-light rounded p-3 d-inline-block">
                            {{translate('Verification_Code')}} : <strong>{{$orderDetails['verification_code']}}</strong>
                        </div>
                    </div>
                </div>
            @endif

            @php($hasBostaTracking = $orderDetails->delivery_service_name == 'bosta' && !empty($orderDetails->third_party_delivery_tracking_id))

            @if(!$hasBostaTracking)
                <h6 class="font-weight-bold text-center m-0 pt-4 pb-4">
                    <span class="text-capitalize">{{ translate('your_order')}}</span> <span>:</span> <span
                            class="text-base">{{$orderDetails['id']}}</span>
                </h6>

                <ul class="nav nav-tabs media-tabs nav-justified order-track-info">
                    <li class="nav-item">
                        <div class="nav-link active-status">
                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                <div class="media-tab-media mx-sm-auto mb-3">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-placed.png')}}"
                                         alt="">
                                </div>
                                <div class="media-body">
                                    <div class="text-sm-center">
                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('order_placed')}}</h6>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png')}}"
                                             width="14" alt="">
                                        <span class="text-muted fs-12">{!! formatDateTimeForDisplay($orderDetails->created_at, 'h:i A, d M Y') !!}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    @if ($orderDetails['order_status']!='returned' && $orderDetails['order_status']!='failed' && $orderDetails['order_status']!='canceled')
                        @if(!$isOrderOnlyDigital)
                            <li class="nav-item ">
                                <div class="nav-link {{($orderDetails['order_status']=='confirmed') || ($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-confirmed.png')}}"
                                                 alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('order_confirmed')}}</h6>
                                            </div>
                                            @if(($orderDetails['order_status']=='confirmed') || ($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered') && \App\Utils\order_status_history($orderDetails['id'],'confirmed'))
                                                <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-1">
                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png')}}"
                                                         width="14" alt="">
                                                    <span class="text-muted fs-12">
                                                        {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'],'confirmed'), 'h:i A, d M Y') !!}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item">
                                <div class="nav-link {{($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/shipment.png')}}"
                                                 alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('preparing_shipment')}}</h6>
                                            </div>
                                            @if( ($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')  && \App\Utils\order_status_history($orderDetails['id'],'processing'))
                                                <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png')}}"
                                                         width="14" alt="">
                                                    <span class="text-muted fs-12">
                                                        {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'],'processing'), 'h:i A, d M Y') !!}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item">
                                <div class="nav-link {{($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/on-the-way.png')}}"
                                                 alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('order_is_on_the_way')}}</h6>
                                            </div>

                                            @if( ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered'))
                                                <div class="d-flex align-items-center justify-content-sm-center mt-1">
                                                    @if(\App\Utils\order_status_history($orderDetails['id'],'out_for_delivery'))
                                                        <img class="mx-sm-1"
                                                             src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png')}}"
                                                             width="20" alt="">
                                                        <span class="text-muted fs-14">
                                                                {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'],'out_for_delivery'), 'h:i A, d M Y') !!}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($orderDetails->delivery_type == 'third_party_delivery')
                                                <div class="mt-1">
                                                    <span class="d-flex align-items-center justify-content-sm-center text-nowrap">
                                                        <span class="text-muted fs-14 text-capitalize">{{translate('delivery_service_name')}} : </span> <span
                                                                class="fs-14 fw-semibold text-dark">{{$orderDetails->delivery_service_name}}</span>
                                                    </span>
                                                    <span class="d-flex align-items-center justify-content-sm-center text-nowrap">
                                                        <span class="text-muted fs-14 text-capitalize"> {{translate('tracking_ID')}} : </span><span
                                                                class="fs-14 fw-semibold text-dark">{{$orderDetails->third_party_delivery_tracking_id}}</span>
                                                    </span>
                                                </div>
                                            @endif
                                            @if ($orderDetails->delivery_type == 'self_delivery' && isset($orderDetails->delivery_man))
                                                <div class="mt-1">
                                                    <span class="d-flex align-items-center justify-content-sm-center text-nowrap">
                                                        <span class="text-muted fs-14 text-capitalize">{{translate('delivery_man_name')}} : </span> <span
                                                                class="fs-14 fw-semibold text-dark">{{$orderDetails->delivery_man->f_name.' '.$orderDetails->delivery_man->l_name}}</span>
                                                    </span>
                                                    <span class="d-flex align-items-center justify-content-sm-center text-nowrap">
                                                        <span class="text-muted fs-14 text-capitalize"> {{translate('contact_number')}} : </span><span
                                                                class="fs-14 fw-semibold text-dark">{{$orderDetails->delivery_man->phone}}</span>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item">
                                <div class="nav-link {{($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/delivered.png')}}"
                                                 alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('order_delivered')}}</h6>
                                            </div>
                                            @if(($orderDetails['order_status']=='delivered') && \App\Utils\order_status_history($orderDetails['id'],'delivered'))
                                                <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png')}}"
                                                         width="14" alt="">
                                                    <span class="text-muted fs-12">
                                                        {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'],'delivered'), 'h:i A, d M Y') !!}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @else
                            <?php
                                $digitalProductProcessComplete = true;
                                foreach ($orderDetails->orderDetails as $detail) {
                                    $productData = json_decode($detail->product_details);
                                    if ($productData->product_type == 'digital' && $productData->digital_product_type == 'ready_after_sell' && $detail->digital_file_after_sell == null) {
                                        $digitalProductProcessComplete = false;
                                    }
                                }
                            ?>

                            <li class="nav-item">
                                <div class="nav-link {{ ($orderDetails['order_status'] == 'processing' || $orderDetails['order_status'] == 'processed' || $orderDetails['order_status'] == 'out_for_delivery' || $orderDetails['order_status'] == 'delivered') ? 'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img alt=""
                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/shipment.png') }}">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                    {{ translate('Processing') }}
                                                </h6>
                                            </div>
                                            @if(($orderDetails['order_status'] == 'processing' || $orderDetails['order_status'] == 'processed' || $orderDetails['order_status'] == 'out_for_delivery' || $orderDetails['order_status'] == 'delivered') && \App\Utils\order_status_history($orderDetails['id'], 'processing'))
                                                <div
                                                    class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                    <img width="14" alt=""
                                                         src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}">
                                                    <span class="text-muted fs-12">
                                                        {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'], 'processing'), 'h:i A, d M Y') !!}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item">
                                <div
                                    class="nav-link {{($orderDetails['order_status']=='delivered' && $digitalProductProcessComplete)?'active-status' : ''}}">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mb-3 mx-sm-auto">
                                            <img
                                                src="{{theme_asset(path: 'public/assets/front-end/img/track-order/delivered.png') }}"
                                                alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('delivery_complete') }}</h6>
                                            </div>

                                            @if(($orderDetails['order_status']=='delivered') && $digitalProductProcessComplete && \App\Utils\order_status_history($orderDetails['id'],'delivered'))
                                                <div
                                                    class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                        width="14" alt="">
                                                    <span class="text-muted fs-12">
                                                        {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'],'delivered'), 'h:i A, d M Y') !!}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endif
                    @elseif(in_array($orderDetails['order_status'], ['returned', 'canceled']))
                        <li class="nav-item">
                            <div class="nav-link active-status">
                                <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                    <div class="media-tab-media mx-sm-auto mb-3">
                                        <img src="{{ theme_asset(path: 'public/assets/front-end/img/track-order/'.$orderDetails['order_status'].'.png') }}" alt="">
                                    </div>
                                    <div class="media-body">
                                        <div class="text-sm-center">
                                            <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                {{ translate('order') }} {{ translate($orderDetails['order_status']) }}
                                            </h6>
                                        </div>
                                        @if(\App\Utils\order_status_history($orderDetails['id'], $orderDetails['order_status']))
                                            <div class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                                <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                     width="14" alt="">
                                                <span class="text-muted fs-12">
                                                {!! formatDateTimeForDisplay(\App\Utils\order_status_history($orderDetails['id'], $orderDetails['order_status']), 'h:i A, d M Y') !!}
                                            </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @else
                        <li class="nav-item">
                            <div class="nav-link active-status">
                                <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                    <div class="media-tab-media mx-sm-auto mb-3">
                                        <img
                                            src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-failed.png') }}"
                                            alt="">
                                    </div>
                                    <div class="media-body">
                                        <div class="text-sm-center">
                                            <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('Failed_to_Deliver') }}</h6>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                            <span class="text-muted fs-12">
                                                {{ translate('sorry_we_can_not_complete_your_order') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            @endif

            @if($hasBostaTracking)


            <h5 class="mb-3 text-center fw-bold">{{ translate('Shipping_Journey') }}</h5>

            <div class="track-unique-container" style="background: #ffffff !important; padding: 32px 40px !important; border-radius: 12px !important; border: 1px solid #e5e7eb !important; max-width: 1280px; margin: 20px auto;">
                <div>
                    <span class="text-muted fs-12 uppercase d-block">{{ translate('Tracking_ID') }}</span>
                    <strong class="fs-18">#{{ $orderDetails->third_party_delivery_tracking_id }}</strong>
                    <!-- <div class="progress-indicator" id="progressIndicator">0%</div> -->
                </div>
                <div class="track-unique-header" style="display: flex !important; align-items: center !important; gap: 6px !important; color: #6b7280 !important; font-size: 13px !important; margin-bottom: 12px !important;">
                    <i class="far fa-clock"></i>
                    <span>{{ __('Last updated at') }}: <span id="bosta-updated-time">{{ __('Please wait...') }}</span></span>
                </div>

                <h1 class="track-unique-title" style="font-size: 32px !important; font-weight: 700 !important; color: #000000 !important; margin-bottom: 50px !important; letter-spacing: -0.5px !important; display: block !important;">
                    {{ __('Will arrive on') }} <span class="track-unique-highlight" id="bosta-status-val" style="color: #239e92 !important;">{{ __('Soon') }}</span>
                </h1>

                <div class="track-unique-stepper" style="display: flex !important; flex-direction: row !important; justify-content: space-between !important; align-items: flex-start !important; position: relative !important; width: 100% !important; min-height: 100px !important; padding: 0 !important;">

                    <div class="track-unique-bar-bg" style="position: absolute !important; top: 16px !important; inset-inline-start: 5%; inset-inline-end: 5%; height: 2px !important; background-color: #e5e7eb !important; z-index: 1 !important;"></div>

                    <div class="track-unique-bar-fill" id="bosta-line-fill" style="position: absolute !important; top: 16px !important; inset-inline-start: 5%; width: 0%; height: 2px !important; background-color: #10b981 !important; z-index: 2 !important; transition: width 0.8s ease !important;"></div>

                    <div class="track-unique-node" data-code="10" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">{{ __('Order Created') }}</div>
                        <div class="track-unique-date" id="date-node-10" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="21" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">{{ __('Order Picked Up') }}</div>
                        <div class="track-unique-date" id="date-node-21" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="30" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">{{ __('In Progress') }}</div>
                        <div class="track-unique-date" id="date-node-30" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="41" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">{{ __('Out for Delivery') }}</div>
                        <div class="track-unique-date" id="date-node-41" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="45" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">{{ __('Delivered') }}</div>
                        <div class="track-unique-date" id="date-node-45" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>
                </div>
            </div>

            @endif

        </section>
    </div>
</div>
@endsection

@push('script')
@if($hasBostaTracking)

<script>
    $(document).ready(function() {
        function fetchBostaTracking() {
            $.ajax({
                url: '{{ route("order.track", ["id" => $orderDetails->id]) }}',
                method: 'GET',
                success: function(res) {
                    if (res.success && res.tracking) {
                        const data = res.tracking;
                        const timeline = data.timeline || [];
                        const nodes = $('.track-unique-node');
                        let lastDoneIdx = -1;

                        nodes.each(function(i) {
                            const code = $(this).data('code');
                            const event = timeline.find(e => e.code === code);

                            if (event && event.done) {
                                lastDoneIdx = i;
                                // Apply completed styles directly to elements
                                $(this).find('.track-unique-icon').css({
                                    'background-color': '#10b981',
                                    'border-color': '#10b981'
                                });
                                $(this).find('.fas.fa-check').show();
                                $(this).find('.track-unique-label').css('color', '#000000');

                                if (event.date) {
                                    const d = new Date(event.date);
                                    $(this).find('.track-unique-date').text(d.toLocaleDateString('en-GB', {
                                        weekday: 'long',
                                        day: 'numeric',
                                        month: 'long'
                                    }));
                                }
                            }
                        });

                        // Update Progress Line Fill
                        if (lastDoneIdx !== -1) {
                            const percent = (lastDoneIdx / (nodes.length - 1)) * 90; // 90 to match 5% to 95% span
                            $('#bosta-line-fill').css('width', percent + '%');
                        }

                        $('#bosta-status-val').text(data.state);
                        if (data.updatedAt) {
                            $('#bosta-updated-time').text(new Date(data.updatedAt).toLocaleString());
                        }
                    }
                }
            });
        }
        fetchBostaTracking();
    });
</script>
@endif
@endpush
