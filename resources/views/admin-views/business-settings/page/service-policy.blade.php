@extends('layouts.back-end.app')

@section('title', translate('Service_policy'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}

$policyTranslations = [];
foreach (($service_policy?->translations ?? collect()) as $translation) {
$policyTranslations[$translation->locale][$translation->key] = $translation->value;
}

$pageTranslations = [];
foreach (($servicePolicyPage?->translations ?? collect()) as $translation) {
$pageTranslations[$translation->locale][$translation->key] = $translation->value;
}

@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/Pages.png')}}" alt="">
            {{translate('Pages')}}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="{{route('admin.business-settings.update-service')}}" method="post">
                    @csrf
                <div class="card-header">
                    <h5 class="mb-0">{{translate('Service_policy')}}</h5>
                    <label class="switcher show-status-text justify-content-end" for="service-policy-status">
                        <input type="checkbox" class="switcher_input toggle-switch-message" value="1" name="status"
                               id="service-policy-status" {{ ($servicePolicyPage->status ?? 0) == 1 ? 'checked' : '' }}
                               data-modal-id="toggle-modal"
                               data-toggle-id="service-policy-status"
                               data-on-image=""
                               data-off-image=""
                               data-on-title="{{ translate('Want_to_Turn_ON').' '.translate('Service_policy').' '.translate('Status') }}"
                               data-off-title="{{ translate('Want_to_Turn_OFF').' '.translate('Service_policy').' '.translate('Status') }}"
                               data-on-message="<p>{{ translate('if_you_enable_this_option_service_policy_page_will_be_shown_in_the_user_app_and_website') }}</p>"
                               data-off-message="<p>{{ translate('if_you_disable_this_option_service_policy_page_will_not_be_shown_in_the_user_app_and_website') }}</p>">
                            <span class="switcher_control" data-ontitle="{{ translate('On') }}" data-offtitle="{{ translate('OFF') }}"></span>
                    </label>
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
                        <div class="form-group">


                            @foreach($language as $lang)
                            <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                                id="{{ $lang }}-form">

                                @php
                                $titleValue = $lang == $defaultLanguage
                                ? ($servicePolicyPage?->getRawOriginal('title') ?? '')
                                : ($pageTranslations[$lang]['title'] ?? '');
                                $termValue = $lang == $defaultLanguage
                                ? ($service_policy?->value ?? '')
                                : ($policyTranslations[$lang]['value'] ?? '');
                                @endphp
                                <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="title[]" value="{{ $titleValue }}" class="form-control mb-3" maxlength="255">
                                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">

                                <textarea name="value[]" data-lang="{{ $lang }}" id="editor" cols="30" rows="20" class="form-control summernote">{{ $termValue }}</textarea>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <input class="form-control btn--primary" type="submit" value="{{translate('Submit')}}" name="btn">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    'use strict';
    $(document).on('ready', function() {
        $('.summernote').summernote({
            'height': 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
            ]
        });
    });
</script>
@endpush
