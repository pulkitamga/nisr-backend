@extends('layouts.back-end.app')

@section('title', translate('Add_New_User'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
<div class="content container-fluid main-card {{Session::get('direction')}}">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
            {{ translate('add_new_User') }}
        </h2>
    </div>
    <form class="user" action="{{route('admin.department.add-users', $dept_id)}}" method="post" enctype="multipart/form-data" id="add-department-users-form">
        @csrf
        <div class="card">
            <div class="card-body">
                <input type="hidden" name="dept_id" value="{{$dept_id}}">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
                    {{ translate('department_information') }}
                </h5>
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Name')}}</label>
                            <input class="form-control" type="text" name="name" value="{{$departments->name}}" 
                                value="" readonly disabled 
                                placeholder="{{translate('enter_department_name')}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-body">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" class="mb-1" alt="">
                    {{ translate('User_details') }}
                </h5>
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('role')}} </label>
                            <select id="role_id" name="role_id" class="form-control js-select2-custom">
                                <option value="0" selected="" disabled="">---Select---</option>
                                @foreach ($aRoles as $key => $role)
                                    <option value="{{ $role->id }}">{{ $role->name}}</option>                                    
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Name')}}</label>
                            <input class="form-control" type="text" name="user_name"
                                value=""
                                placeholder="{{translate('enter_name')}}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Email')}}</label>
                            <input class="form-control" type="email" name="email"
                                value=""
                                placeholder="{{translate('enter_email')}}">
                        </div>
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="user_password" class="title-color d-flex gap-1 align-items-center">
                            {{translate('password')}}
                            <span class="input-label-secondary cursor-pointer d-flex" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                <img alt="" width="16" src={{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}>
                            </span>
                        </label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control password-check" name="password" required id="user_password" minlength="8" placeholder="{{ translate('password_minimum_8_characters') }}"data-hs-toggle-password-options='{
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
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-footer">
                <div class="d-flex align-items-center justify-content-end gap-10">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary reset-button">{{translate('reset')}} </button>
                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="add-department-users-form" data-redirect-route="{{route('admin.department.add-users', $dept_id)}}"
                            data-message="{{translate('want_to_add_this_user_in_department').'?'}}">{{translate('submit')}}</button>
                </div>
            </div>
        </div>
    </form>    
</div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/department.js')}}"></script>
@endpush
