@extends('layouts.back-end.app')

@section('title', translate(''.$type.'_Charges'))
<style type="text/css">
      #map {
        height: 50vh;
        width: 100%;
        position: relative;
      }

      .map-controls {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background-color: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      #searchInput {
        width: 250px;
        padding: 5px;
      }
</style>
@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" alt="">
                {{translate($type.'_Charges')}}
            </h2>
        </div>
        <div id="area_wise_shipping">
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                        {{translate('add_'.$type.'_charges')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.extra-charges.add')}}" method="post">
                        @csrf
                        <input type="hidden" class="form-control" name="type" value="{{$type}}">
                        <div class="row">
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <label class="title-color d-flex" for="title">{{translate('category')}}</label>
                                            <select id="category" name="category" class="form-control js-select2-custom">
                                            	<option value="0" selected="" disabled="">---Select---</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category['id']}}" >
                                                        {{$category['name']}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <label class="title-color d-flex" for="cost">{{translate('charges')}}</label>
                                            <input type="number" min="0" step="0.01" max="1000000" name="charges"
                                                   class="form-control" placeholder="{{translate('ex')}} :">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-10 mt-5">
                            <button type="submit" class="btn btn--primary px-5">{{translate('submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="px-3 py-4">
                    <h5 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/delivery.png')}}" alt="">
                        {{translate($type.'_Charges')}}
                        <span class="badge badge-soft-dark radius-50 fz-12">{{ $aExtraCharges->count() }}</span>
                    </h5>
                </div>
                <div class="table-responsive pb-3">
                    <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Catgeory')}}</th>
                            <th>{{translate('Charges')}}</th>
                            <th class="text-center">{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($aExtraCharges as $key=>$charges)
                            <tr>
                                <th class="align-center">{{$key+1}}</th>
                                <td>{{$charges['category']['name']}}</td>
                                <td>
                                    {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $charges['charges']), currencyCode: getCurrencyCode(type: 'default'))}}
                                </td>
                                <td>
                                    <form action="{{route('admin.extra-charges.update-status')}}"
                                          method="post" id="shipping-methods{{$charges['id']}}-form">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$charges['id']}}">
                                        <label class="switcher mx-auto">
                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                   id="shipping-methods{{$charges['id']}}" name="status" value="1"
                                                   {{$charges->status == 1 ? 'checked' : ''}}
                                                   data-modal-id = "toggle-status-modal"
                                                   data-toggle-id = "shipping-methods{{$charges['id']}}"
                                                   data-on-image = "category-status-on.png"
                                                   data-off-image = "category-status-off.png"
                                                   data-on-title = "{{translate('want_to_Turn_ON_This_Charges').'?'}}"
                                                   data-off-title = "{{translate('want_to_Turn_OFF_This_Charges').'?'}}"
                                                   data-on-message = "<p>{{translate('if_you_enable_this_charges_will_be_shown_in_the_user_app_and_website_for_customer_checkout')}}</p>"
                                                   data-off-message = "<p>{{translate('if_you_disable_this_charges_will_not_be_shown_in_the_user_app_and_website_for_customer_checkout')}}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-center gap-10">
                                        <a title="{{translate('delete')}}"
                                           class="btn btn-outline-danger btn-sm delete-data-without-form"
                                           data-action="{{route('admin.extra-charges.delete')}}"
                                           data-id="{{ $charges['id'] }}">
                                            <i class="tio-delete"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($aExtraCharges)==0)
                    @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
                @else
                    <div class="d-flex justify-content-end my-2 mx-2">
                        {!! $aExtraCharges->links() !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <span id="get-shipping-type-data" data-action="{{route('admin.business-settings.shipping-type.index')}}" data-success="{{translate('shipping_method_updated_successfully').'!!'}}"></span>
    <span id="route-get-country-state" data-url="{{route('admin.business-settings.shipping-method.getStates')}}"></span>
    <span id="route-get-state-cities" data-url="{{route('admin.business-settings.shipping-method.getCities')}}"></span>
    @php($default_location = getWebConfig(name: 'default_location'))
@endsection

