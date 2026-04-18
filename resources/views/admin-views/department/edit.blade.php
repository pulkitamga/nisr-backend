@extends('layouts.back-end.app')

@section('title', translate('Update_Department'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
<div class="content container-fluid main-card {{Session::get('direction')}}">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
            {{ translate('Update_Department') }}
        </h2>
    </div>
    <form class="user" action="{{route('admin.department.update',[$department['id']])}}" method="post" enctype="multipart/form-data" id="add-department-form">
        @csrf
        <input type="hidden" name="id" value="{{$department['id']}}">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 ps-4">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
                    {{ translate('department_information') }}
                </h5>
                @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : [];
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
                <ul class="nav nav-tabs w-fit-content mb-4">
                    @foreach($language as $lang)
                        <li class="nav-item text-capitalize">
                            <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}"
                               id="{{$lang}}-link">
                                {{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}
                            </span>
                        </li>
                    @endforeach
                </ul>
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        @foreach($language as $lang)
                            <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                                 id="{{$lang}}-form">
                                <label class="title-color" for="name">{{ translate('Name') }}
                                    ({{ strtoupper($lang) }})</label>
                                <input type="text" name="name[]"
                                       value="{{$lang == $defaultLanguage ? $department->getRawOriginal('name') : $department->getTranslatedField('name', $lang, '') }}"
                                       class="form-control" id="name"
                                       placeholder="{{translate('enter_department_name')}}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{$lang}}">
                        @endforeach
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Status')}}</label>
                            <select class="form-control" name="status" id="status">
                                <option value="1" {{ $department->status == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="0" {{ $department->status == 0 || $department->status == '' ? 'selected' : '' }}>{{ __('Block') }}</option>
                            </select>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="title-color d-flex">{{translate('Department_Head')}}</label>
                            <input
                                class="form-control"
                                type="text"
                                value="{{ $department->employee?->name ?? 'No department head assigned' }}"
                                readonly
                            >
                            <small class="text-muted">{{ __('Update this from Employees -> Edit Employee -> Department Head For Escalation.') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-footer">
                <div class="d-flex align-items-center justify-content-end gap-10">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary reset-button">{{translate('Reset')}} </button>
                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="add-department-form" data-redirect-route="{{route('admin.department.list')}}"
                            data-message="{{translate('want_to_update_this_department').'?'}}">{{translate('Update')}}</button>
                </div>
            </div>
        </div>
    </form>    
</div>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/department.js')}}"></script>
@endpush
