@extends('layouts.back-end.app')

@section('title', translate('cookie_settings'))

@section('content')

@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <div class="mb-4 pb-2">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/system-setting.png')}}" alt="">
            {{translate('system_Setup')}}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <form action="{{ route('admin.business-settings.cookie-settings') }}" method="post"
        enctype="multipart/form-data" id="update-settings">
        @csrf
        <div class="card">
            <div class="border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center gap-10">
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2">
                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/cookie.png')}}" alt="">
                        {{translate('cookie_settings').':'}}
                    </h5>
                    <label class="switcher" for="cookie-setting-status">
                        <input type="checkbox" class="switcher_input toggle-switch-message" value="1" name="status"
                            id="cookie-setting-status" {{isset($cookieSetting) && $cookieSetting['status']==1?'checked':''}}
                            data-modal-id="toggle-modal"
                            data-toggle-id="cookie-setting-status"
                            data-on-image="cookie-on.png"
                            data-off-image="cookie-off.png"
                            data-on-title="{{translate('by_Turning_OFF_Cookie_Settings')}}"
                            data-off-title="{{translate('by_Turning_ON_Cookie_Settings')}}"
                            data-on-message="<p>{{translate('if_you_disable_it_customers_cannot_see_Cookie_Settings_in_frontend')}}</p>"
                            data-off-message="<p>{{translate('if_you_enable_it_customers_will_see_Cookie_Settings_in_frontend')}}</p>">
                        <span class="switcher_control"></span>
                    </label>
                </div>
            </div>
            <div class="card-body">
                @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
<ul class="nav nav-tabs mb-4">
                    @foreach($language as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="loyalty-point-section" id="cookie_setting_status_section">
                    <div class="form-group">
                        @foreach($language as $lang)
                        <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                            id="{{ $lang }}-form">

                            @php
                            $translation = $cookieSetting->translations->firstWhere('locale', $lang); // $lang = 'eg', for example
                            $translationValue = $translation && $translation->key === 'cookie_text' ? $translation->value : '';
                            $settingValue = json_decode($cookieSetting['value'], true);
                            $cookieValue = $lang == $defaultLanguage
                            ? ($settingValue['cookie_text'] ?? '')
                            : ($translationValue ?? '');
                            @endphp

                            <label class="title-color d-flex"
                                for="loyalty_point_exchange_rate">{{translate('cookie_text')}} ({{ strtoupper($lang) }})</label>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">

                            <textarea name="cookie_text[]" data-lang="{{ $lang }}" id="" cols="30" rows="6" class="form-control">{{$cookieValue}}</textarea>
                        </div>
                        @endforeach

                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" id="submit" class="btn px-5 btn--primary">{{translate('Save')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


@endsection
