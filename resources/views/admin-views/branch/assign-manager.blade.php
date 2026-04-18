@extends('layouts.back-end.app')

@section('title', translate('manage_branch'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
<div class="content container-fluid main-card {{Session::get('direction')}}">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" class="mb-1" alt="">
            {{ translate('manage_branch') }}
        </h2>
    </div>
     <div class="page-header border-0 mb-4">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs flex-wrap page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.branch.view', $seller['id']) }}">{{translate('branch_overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active"
                           href="{{ route('admin.branch.assign-manager', $seller['id']) }}">{{translate('assign_manager')}}</a>
                    </li>
                    
                </ul>
            </div>
        </div>
       
      @if(empty($managers))
    <form class="user" action="{{ route('admin.branch.add-manager', $seller['id']) }}" method="post" enctype="multipart/form-data" id="add-manager-form">
        
        @else
            <form class="user" action="{{ route('admin.branch.update-manager', $seller['id']) }}" method="post" enctype="multipart/form-data" id="update-manager-form">
              
        @endif
        @csrf
        <div class="card mt-3">
            <div class="card-body">
                <input type="hidden" name="status" value="active">
                <input type="hidden" name="branch_id" value="{{$seller['id']}}">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 ps-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}" class="mb-1" alt="">
                    {{translate('assign_manager')}}
                </h5>
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label for="exampleInputEmail" class="title-color d-flex gap-1 align-items-center">{{translate('Name')}}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $managers['name'] ?? '') }}" placeholder="{{translate('Ex').':'.'Jhone Doe'}}" required> 

 
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="exampleInputEmail" class="title-color d-flex gap-1 align-items-center">{{translate('Phone')}}</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{old('Phone', $managers['phone'] ?? '')}}" placeholder="{{translate('Ex').':'.'Enter phone'}}" required>
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="exampleInputEmail" class="title-color d-flex gap-1 align-items-center">{{translate('Email')}}</label>
                        <input type="email" class="form-control form-control-user" id="exampleInputEmail" name="email" value="{{old('email', $managers['email'] ?? '')}}" placeholder="{{translate('Ex').':'.'Jhone@company.com'}}" required  {{ !empty($managers['email']) ? 'disabled' : '' }} >
                    </div>
                     @if(empty($managers))
                    <div class="col-lg-4 form-group">
                        <label for="user_password" class="title-color d-flex gap-1 align-items-center">
                            {{translate('password')}}
                            <span class="input-label-secondary cursor-pointer d-flex" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                <img alt="" width="16" src={{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}>
                            </span>
                        </label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control password-check"
                                   name="password" required id="user_password" minlength="8"
                                   placeholder="{{ translate('password_minimum_8_characters') }}"
                                   data-hs-toggle-password-options='{
                                                         "target": "#changePassTarget",
                                                        "defaultClass": "tio-hidden-outlined",
                                                        "showClass": "tio-visible-outlined",
                                                        "classChangeTarget": "#changePassIcon"
                                                }'>
                            <div id="changePassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                        <span class="text-danger mx-1 password-error"></span>
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="confirm_password" class="title-color d-flex gap-1 align-items-center">{{translate('confirm_password')}}</label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control"
                                   name="confirm_password" required id="confirm_password"
                                   placeholder="{{ translate('confirm_password') }}"
                                   data-hs-toggle-password-options='{
                                                         "target": "#changeConfirmPassTarget",
                                                        "defaultClass": "tio-hidden-outlined",
                                                        "showClass": "tio-visible-outlined",
                                                        "classChangeTarget": "#changeConfirmPassIcon"
                                                }'>
                            <div id="changeConfirmPassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changeConfirmPassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pass invalid-feedback">{{translate('repeat_password_not_match').'.'}}</div>
                    </div>
                    @endif
                </div>


                @if(empty($managers))
        <div class="d-flex align-items-center justify-content-end gap-10">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary reset-button">{{translate('Reset')}} </button>
                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="add-manager-form" data-redirect-route="{{ route('admin.branch.assign-manager', $seller['id']) }}"
                            data-message="{{translate('want_to_add_this_record').'?'}}">{{translate('Add')}}</button>
                </div>
        
        @else
         
        

            <div class="d-flex align-items-center justify-content-end gap-10">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary reset-button">{{translate('Reset')}} </button>
                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="update-manager-form" data-redirect-route="{{ route('admin.branch.assign-manager', $seller['id']) }}"
                            data-message="{{translate('want_to_update_this_record').'?'}}">{{translate('Update')}}</button>
                            <a href="{{ route('admin.employee.update', $AdminData['id']) }}"> <button type="button" class="btn btn--primary btn-user"  data-redirect-route=""
                            >{{translate('Manage_Manager_Details')}}</button></a>
                </div>
              
        @endif
                 
            </div>
        </div>

        
    </form>
</div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/branch.js')}}"></script>
 
     
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/maintenance-mode-setting.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/business-setting.js') }}"></script>
@endpush