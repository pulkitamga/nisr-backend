@extends('layouts.back-end.app')

@section('title', translate('Add Why Join Us Item'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? [];
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $language, true)) {
    $baseLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Add Why Join Us Item') }}</h2>

    <form action="{{ route('admin.content-management.career.store', ['section' => 'why_join_us']) }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <!-- Language Tabs -->
        <ul class="nav nav-tabs mb-4">
            @foreach($language as $lang)
            <li class="nav-item">
                <a class="nav-link form-system-language-tab {{ $lang == $baseLanguage ? 'active' : '' }}"
                    href="javascript:" id="{{ $lang }}-link">
                    {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                </a>
            </li>
            @endforeach
        </ul>

        <!-- Multilingual Fields -->
        @foreach($language as $lang)
        <div class="form-system-language-form {{ $lang != $baseLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">

            <!-- Title -->
            <div class="form-group">
                <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="title[]" class="form-control">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                <textarea class="form-control summernote" name="description[]"></textarea>
            </div>

            <input type="hidden" name="lang[]" value="{{ $lang }}">
        </div>
        @endforeach

        <!-- Icon Field -->
        <div class="form-group">
            <label>{{ translate('Icon') }}</label>
            <input type="text" name="icon" class="form-control" placeholder="e.g., fas fa-users">
        </div>

        <!-- Is Active Checkbox -->
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" checked>
                {{ translate('Active') }}
            </label>
        </div>

        <!-- Submit -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
        </div>
    </form>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script>
    'use strict';

    // Language Tabs Logic
    $('.form-system-language-tab').on('click', function () {
        var lang = this.id.replace('-link', '');
        $('.form-system-language-tab').removeClass('active');
        $('#' + lang + '-link').addClass('active');
        $('.form-system-language-form').addClass('d-none');
        $('#' + lang + '-form').removeClass('d-none');
    });

    // Summernote Initialization
    $(document).on('ready', function () {
        $('.summernote').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        });
    });
</script>
@endpush
