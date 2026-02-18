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

            @if($orderDetails->delivery_service_name == 'bosta' && $orderDetails->third_party_delivery_tracking_id)


            <h5 class="mb-3 text-center fw-bold">{{ translate('Shipping_Journey') }}</h5>

            <div class="track-unique-container" style="background: #ffffff !important; padding: 32px 40px !important; border-radius: 12px !important; border: 1px solid #e5e7eb !important; max-width: 1280px; margin: 20px auto;">
                <div>
                    <span class="text-muted fs-12 uppercase d-block">{{ translate('Tracking_ID') }}</span>
                    <strong class="fs-18">#{{ $orderDetails->third_party_delivery_tracking_id }}</strong>
                    <!-- <div class="progress-indicator" id="progressIndicator">0%</div> -->
                </div>
                <div class="track-unique-header" style="display: flex !important; align-items: center !important; gap: 6px !important; color: #6b7280 !important; font-size: 13px !important; margin-bottom: 12px !important;">
                    <i class="far fa-clock"></i>
                    <span>Last updated at: <span id="bosta-updated-time">Please wait...</span></span>
                </div>

                <h1 class="track-unique-title" style="font-size: 32px !important; font-weight: 700 !important; color: #000000 !important; margin-bottom: 50px !important; letter-spacing: -0.5px !important; display: block !important;">
                    Will arrive on <span class="track-unique-highlight" id="bosta-status-val" style="color: #239e92 !important;">Soon</span>
                </h1>

                <div class="track-unique-stepper" style="display: flex !important; flex-direction: row !important; justify-content: space-between !important; align-items: flex-start !important; position: relative !important; width: 100% !important; min-height: 100px !important; padding: 0 !important;">

                    <div class="track-unique-bar-bg" style="position: absolute !important; top: 16px !important; left: 5%; right: 5%; height: 2px !important; background-color: #e5e7eb !important; z-index: 1 !important;"></div>

                    <div class="track-unique-bar-fill" id="bosta-line-fill" style="position: absolute !important; top: 16px !important; left: 5%; width: 0%; height: 2px !important; background-color: #10b981 !important; z-index: 2 !important; transition: width 0.8s ease !important;"></div>

                    <div class="track-unique-node" data-code="10" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">Order Created</div>
                        <div class="track-unique-date" id="date-node-10" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="21" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">Order Picked Up</div>
                        <div class="track-unique-date" id="date-node-21" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="30" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">In Progress</div>
                        <div class="track-unique-date" id="date-node-30" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="41" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">Out for Delivery</div>
                        <div class="track-unique-date" id="date-node-41" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>

                    <div class="track-unique-node" data-code="45" style="display: flex !important; flex-direction: column !important; align-items: center !important; flex: 1 !important; position: relative !important; z-index: 3 !important;">
                        <div class="track-unique-icon" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; border: 2px solid #d1d5db !important; background-color: #ffffff !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important;">
                            <i class="fas fa-check" style="color: #ffffff !important; font-size: 14px !important; display: none;"></i>
                        </div>
                        <div class="track-unique-label" style="font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">Delivered</div>
                        <div class="track-unique-date" id="date-node-45" style="font-size: 12px !important; color: #6b7280 !important; text-align: center !important;">-</div>
                    </div>
                </div>
            </div>

            @endif

            <div class="card border-0 mt-4">
                <div class="card-body">
                    @if($orderDetails->order_type == 'default_type' && getWebConfig(name: 'order_verification'))
                    <div class="bg-light rounded p-3 d-inline-block">
                        {{translate('Verification_Code')}} : <strong>{{$orderDetails['verification_code']}}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('script')
@if($orderDetails->delivery_service_name == 'bosta' && !empty($orderDetails->third_party_delivery_tracking_id))

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