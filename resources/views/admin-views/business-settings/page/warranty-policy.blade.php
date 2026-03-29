@extends('layouts.back-end.app')

@section('title', translate('warranty_policy'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
$translations = [];
if ($warranty_policy) {
foreach ($warranty_policy->translations ?? [] as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
}
@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/Pages.png') }}" alt="">
            {{ translate('pages') }}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('warranty_policy') }}</h5>
                </div>
                <form action="{{ route('admin.business-settings.update-warranty') }}" method="post">
                    @csrf
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
                                $termValue = $lang == $defaultLanguage
                                ? ($warranty_policy->value ?? '')
                                : ($translations[$lang]['value'] ?? '');
                                @endphp
                                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                <textarea name="value[]" data-lang="{{ $lang }}" id="editor-{{ $lang }}" cols="30" rows="20" class="form-control summernote">{{ $termValue }}</textarea>
                            </div>
                            @endforeach
                            <div class="form-group mt-3">
                                <label for="version">{{ translate('Version') }}</label>
                                <input type="text" name="version" id="version" class="form-control" value="{{ old('version', $warranty_policy->version ?? '1.0') }}">
                                @error('version')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mt-3">
                                <label for="published_at">{{ translate('Applied Date') }}</label>
                                <input
                                    type="date"
                                    name="published_at"
                                    id="published_at"
                                    class="form-control"
                                    value="{{ old('published_at', isset($warranty_policy->published_at) ? $warranty_policy->published_at->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <input class="form-control btn--primary" type="submit" value="{{ translate('submit') }}" name="btn">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    'use strict';
    $(document).on('ready', function() {
        $('.summernote').each(function() {
            $(this).summernote({
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
    });
</script>
@endpush
@endsection
