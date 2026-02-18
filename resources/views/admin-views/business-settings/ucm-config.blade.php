@php use App\Utils\Helpers; @endphp
@extends('layouts.back-end.app')
@section('title', translate('UCM_API_Config'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/swiper/swiper-bundle.min.css')}}"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png')}}" alt="">
                {{translate('3rd_party')}}
            </h2>
        </div>

        @include('admin-views.business-settings.third-party-inline-menu')

        <div class="bg-white rounded-top">
            <div class="card-body pb-0">
                <div class="d-flex flex-wrap justify-content-between gap-3 border-bottom">
                   
                    <div class="text-primary d-flex align-items-center gap-3 font-weight-bolder mb-2 text-capitalize">
                        {{translate('how_it_works')}}
                        <div class="ripple-animation" data-toggle="modal" data-target="#getInformationModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M9.00033 9.83268C9.23644 9.83268 9.43449 9.75268 9.59449 9.59268C9.75449 9.43268 9.83421 9.2349 9.83366 8.99935V5.64518C9.83366 5.40907 9.75366 5.21463 9.59366 5.06185C9.43366 4.90907 9.23588 4.83268 9.00033 4.83268C8.76421 4.83268 8.56616 4.91268 8.40616 5.07268C8.24616 5.23268 8.16644 5.43046 8.16699 5.66602V9.02018C8.16699 9.25629 8.24699 9.45074 8.40699 9.60352C8.56699 9.75629 8.76477 9.83268 9.00033 9.83268ZM9.00033 13.166C9.23644 13.166 9.43449 13.086 9.59449 12.926C9.75449 12.766 9.83421 12.5682 9.83366 12.3327C9.83366 12.0966 9.75366 11.8985 9.59366 11.7385C9.43366 11.5785 9.23588 11.4988 9.00033 11.4993C8.76421 11.4993 8.56616 11.5793 8.40616 11.7393C8.24616 11.8993 8.16644 12.0971 8.16699 12.3327C8.16699 12.5688 8.24699 12.7668 8.40699 12.9268C8.56699 13.0868 8.76477 13.1666 9.00033 13.166ZM9.00033 17.3327C7.84755 17.3327 6.76421 17.1138 5.75033 16.676C4.73644 16.2382 3.85449 15.6446 3.10449 14.8952C2.35449 14.1452 1.76088 13.2632 1.32366 12.2493C0.886437 11.2355 0.667548 10.1521 0.666992 8.99935C0.666992 7.84657 0.885881 6.76324 1.32366 5.74935C1.76144 4.73546 2.35505 3.85352 3.10449 3.10352C3.85449 2.35352 4.73644 1.7599 5.75033 1.32268C6.76421 0.88546 7.84755 0.666571 9.00033 0.666016C10.1531 0.666016 11.2364 0.884905 12.2503 1.32268C13.2642 1.76046 14.1462 2.35407 14.8962 3.10352C15.6462 3.85352 16.24 4.73546 16.6778 5.74935C17.1156 6.76324 17.3342 7.84657 17.3337 8.99935C17.3337 10.1521 17.1148 11.2355 16.677 12.2493C16.2392 13.2632 15.6456 14.1452 14.8962 14.8952C14.1462 15.6452 13.2642 16.2391 12.2503 16.6768C11.2364 17.1146 10.1531 17.3332 9.00033 17.3327ZM9.00033 15.666C10.8475 15.666 12.4206 15.0168 13.7195 13.7185C15.0184 12.4202 15.6675 10.8471 15.667 8.99935C15.667 7.15213 15.0178 5.57907 13.7195 4.28018C12.4212 2.98129 10.8481 2.33213 9.00033 2.33268C7.1531 2.33268 5.58005 2.98185 4.28116 4.28018C2.98227 5.57852 2.3331 7.15157 2.33366 8.99935C2.33366 10.8466 2.98283 12.4196 4.28116 13.7185C5.57949 15.0174 7.15255 15.6666 9.00033 15.666Z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-ucm" role="tabpanel" aria-labelledby="nav-ucm-tab">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mt-3">
                            @php($ucm = getWebConfig(name: 'ucm_api_config')) @endphp
                            <form action="{{route('admin.business-settings.ucm.update')}}" method="post">
                                @csrf
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center gap-2 text-capitalize">
                                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/ucm.png')}}" alt="">
                                        {{translate('UCM_API_Config')}}
                                    </h5>
                                    <label class="switcher">
                                        <input type="checkbox" name="status" value="1"
                                               id="ucm_config" {{$ucm['status']??0 == 1 ? 'checked':''}} class="switcher_input toggle-switch-message"
                                               data-modal-id="toggle-modal"
                                               data-toggle-id="ucm_config"
                                               data-on-image="maintenance_mode-on.png"
                                               data-off-image="maintenance_mode-off.png"
                                               data-on-title="{{translate('want_to_Turn_ON_the_UCM_API_config').'?'}}"
                                               data-off-title="{{translate('want_to_Turn_OFF_the_UCM_API_config').'?'}}"
                                               data-on-message="<p>{{translate('enabling_UCM_API_allows_integration_with_Grandstream_PBX')}}</p>"
                                               data-off-message="<p>{{translate('disabling_UCM_API_stops_all_PBX_integration')}}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="title-color mb-0">{{translate('host')}}</label>
                                                    <i class="tio-info-outined" data-toggle="tooltip" title="{{translate('UCM_IP_address_or_domain')}}"></i>
                                                </div>
                                                <input type="text" class="form-control" name="host"
                                                       placeholder="192.168.1.100"
                                                       value="{{env('APP_MODE')=='demo'?'':($ucm['host']??'')}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="title-color mb-0">{{translate('port')}}</label>
                                                    <i class="tio-info-outined" data-toggle="tooltip" title="{{translate('default_8089')}}"></i>
                                                </div>
                                                <input type="text" class="form-control" name="port"
                                                       placeholder="8089"
                                                       value="{{env('APP_MODE')=='demo'?'':($ucm['port']??'8089')}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="title-color mb-0">{{translate('username')}}</label>
                                                    <i class="tio-info-outined" data-toggle="tooltip" title="{{translate('API_username_from_UCM')}}"></i>
                                                </div>
                                                <input type="text" class="form-control" name="username"
                                                       placeholder="api_user"
                                                       value="{{env('APP_MODE')=='demo'?'':($ucm['username']??'')}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="title-color mb-0">{{translate('password')}}</label>
                                                    <i class="tio-info-outined" data-toggle="tooltip" title="{{translate('API_user_password')}}"></i>
                                                </div>
                                                <input type="password" class="form-control" name="password"
                                                       placeholder="********"
                                                       value="{{env('APP_MODE')=='demo'?'':($ucm['password']??'')}}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="digest" value="1"
                                                           {{ ($ucm['digest']??1)==1 ? 'checked':'' }}>
                                                    {{translate('Use_Digest_Authentication_(Recommended)')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-end gap-10">
                                        <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                                class="btn btn--primary px-5 {{env('APP_MODE')!='demo'?'':'call-demo'}}">
                                            {{translate('save')}}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How it works Modal -->
    <div class="modal fade" id="getInformationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close border-0" data-dismiss="modal"><i class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="swiper instruction-carousel pb-3">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide text-center">
                                <img width="80" class="mb-3" src="{{dynamicAsset('public/assets/back-end/img/ucm-guide-1.png')}}" alt="">
                                <h4>{{translate('Enable_API_on_UCM')}}</h4>
                                <p>{{translate('Go_to_Value-added_Features_→_API_Configuration_→_Enable_HTTPS_API')}}</p>
                            </div>
                            <div class="swiper-slide text-center">
                                <img width="80" class="mb-3" src="{{dynamicAsset('public/assets/back-end/img/ucm-guide-2.png')}}" alt="">
                                <h4>{{translate('Set_Username_&_Password')}}</h4>
                                <p>{{translate('Create_a_dedicated_API_user_and_whitelist_your_server_IP')}}</p>
                            </div>
                            <div class="swiper-slide text-center">
                                <img width="80" class="mb-3" src="{{dynamicAsset('public/assets/back-end/img/ucm-guide-3.png')}}" alt="">
                                <h4>{{translate('Save_&_Test')}}</h4>
                                <p>{{translate('Save_settings_here_and_test_with_Send_Test_Call')}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="instruction-pagination-custom my-2"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/swiper/swiper-bundle.min.js')}}"></script>
    <script>
        $(document).on('ready', function () {
            new Swiper('.instruction-carousel', {
                loop: true,
                pagination: { el: '.instruction-pagination-custom', clickable: true }
            });
        });
    </script>
@endpush