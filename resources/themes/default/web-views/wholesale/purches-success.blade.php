@extends('layouts.front-end.app')
@section('title', translate('order_Complete').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@section('content')
    @include('layouts.front-end.partials._store-header')

<main class="main-content d-flex flex-column gap-3 py-3 mb-5">
    <div class="container">
        <div class="card">
            <div class="card-body p-md-5">
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-md-10">
                        <div class="text-center d-flex flex-column align-items-center gap-3">
                            <img width="46" src="{{ theme_asset('assets/img/icons/check.png') }}" class="dark-support"
                                alt="">
                            <h3 class="text-capitalize">

                                {{ translate('Purchess_Order_Placed_Successfully') }}!

                            </h3>
                            <p class="text-muted">{{ translate('thank_you_for_your_order') }}! {{
                                translate('your_purchess_has_been_processed').'.'.translate('our_team_review_your_order_soon_check_in_my_quotation').'.'
                                }}</p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{route('store')}}"
                                    class="btn btn-outline-primary bg-primary-light border-transparent text-capitalize">{{
                                    translate('continue_shopping') }}</a>
                                <a href="{{ route('wholesale.account.order') }}" class="btn btn-primary text-capitalize">{{
                                    translate('track_order') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection