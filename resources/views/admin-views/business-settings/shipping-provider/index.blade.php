@extends('layouts.back-end.app')

@section('title', translate('shipping'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png')}}" alt="">
                {{translate('3rd_party')}}
            </h2>
        </div>

        @include('admin-views.business-settings.third-party-inline-menu')

        <div class="row gy-3" id="shipping-provider-cards">
            <div class="col-12">
                <div class="mt-2 valley-alert">
                    <img width="16" class="mt-1" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" alt="">
                    <p class="mb-0">
                        <strong>{{translate('NB').':'}}</strong>
                        Please re-check if you have put all the data correctly or contact your shipping provider for assistance.
                    </p>
                </div>
            </div>

            @foreach($shippingProviders as $provider)
                <div class="col-md-6">
                    <div class="card h-100">
                        <form action="{{route('admin.business-settings.shipping-provider-update')}}" method="POST" id="{{$provider['key_name']}}-shipping-form">
                            @csrf
                            @method('PUT')
                            <div class="card-header d-flex flex-wrap align-content-around">
                                <h5 class="text-uppercase mb-0">{{ str_replace('_', ' ', $provider['key_name']) }}</h5>

                                <label class="switcher show-status-text">
                                    <input class="switcher_input toggle-switch-message" type="checkbox" name="status" value="1"
                                           id="{{$provider['key_name']}}-shipping" {{$provider['is_active'] == 1 ? 'checked' : ''}}
                                           data-modal-id="toggle-status-modal"
                                           data-toggle-id="{{$provider['key_name']}}-shipping"
                                           data-on-image=""
                                           data-off-image=""
                                           data-on-title="Want to turn ON {{ ucwords(str_replace('_',' ',$provider['key_name'])) }} as the shipping provider?"
                                           data-off-title="Want to turn OFF {{ ucwords(str_replace('_',' ',$provider['key_name'])) }} as the shipping provider?"
                                           data-on-message="<p>If enabled, system can use this shipping provider.</p>"
                                           data-off-message="<p>If disabled, system cannot use this shipping provider.</p>">
                                    <span class="switcher_control" data-ontitle="{{ translate('on') }}" data-offtitle="{{ translate('off') }}"></span>
                                </label>
                            </div>

                            <div class="card-body">
                                <input name="gateway" value="{{$provider['key_name']}}" type="hidden">
                                <input name="mode" value="live" type="hidden">

                                @php($skip=['gateway','mode','status'])
                                @foreach($provider['live_values'] as $keyName => $value)
                                    @if(!in_array($keyName, $skip))
                                        <div class="form-group mb-10px mt-20px">
                                            <label class="form-label">{{ucwords(str_replace('_',' ',$keyName))}} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="{{$keyName}}" placeholder="{{ucwords(str_replace('_',' ',$keyName))}}" value="{{env('APP_MODE')=='demo' ? '' : $value}}">
                                        </div>
                                    @endif
                                @endforeach

                                <div class="text-right mt-20px">
                                    <button type="submit" class="btn btn-primary px-5">{{translate('save')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
