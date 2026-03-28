@extends('layouts.back-end.app')
@section('title', translate('Edit Banner'))
@push('css_or_js')
<!-- Summernote CSS -->
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush


@section('content')


@php
$language = getWebConfig('pnc_language') ?? ['en'];
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $language, true)) {
    $baseLanguage = $language[0] ?? 'en';
}
$activeLanguage = getDefaultLanguage();
$activeLanguage = in_array($activeLanguage, $language, true) ? $activeLanguage : $baseLanguage;
$translations = [];
foreach ($job->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
@endphp
<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Edit Section') }}</h2>
    <div class="card p-4">
        <form action="{{ route('admin.content-management.career.update', ['section' => 'hero', 'id' => $job->id]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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

            @foreach($language as $lang)
            <div class="{{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{ $lang }}-form">
                <!-- Title -->
                <div class="form-group">
                    <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $baseLanguage ? $job->title : ($translations[$lang]['title'] ?? '') }}" {{
                    $lang==$baseLanguage ? 'required' : '' }}>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label>{{ translate('Description') }}</label>
                    <textarea name="description[]" rows="4" class="form-control summernote">
                    {!! $lang == $baseLanguage ? $job->description : ($translations[$lang]['description'] ?? '') !!}
                </textarea>
                </div>
                <div class="form-group">
                    <label>{{ translate('Button Text') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="buttonText[]" class="form-control" value="{{ $lang == $baseLanguage ? $job->button_text : ($translations[$lang]['buttonText'] ?? '') }}">
                </div>
            </div>

            <input type="hidden" name="lang[]" value="{{ $lang }}">
            @endforeach
            <!-- Button Text Field -->


            <!-- Button Link Field -->
            <div class="form-group">
                <label>{{ translate('Button Link') }}</label>
                <input type="url" name="button_link" class="form-control" value="{{ $job->button_link }}">
            </div>

             <div class="form-group mt-3">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control">
                    @if($job->image)
                    <img src="{{ Storage::url($job->image) }}" style="width: 100px;">
                    @endif
                </div>
            <!-- Submit Button -->
            <div class="form-group mt-3">
                <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<!-- Summernote JS -->
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script>
    'use strict';
    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.id.replace('-link', '');
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.form-system-language-form').forEach(f => f.classList.add('d-none'));
            document.getElementById(lang + '-form').classList.remove('d-none');
        });
    });

    $(document).on('ready', function() {
        // Initialize Summernote for the description field
        $('.summernote').summernote({
            height: 150, // Set the height of the editor
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']], // Text styling options
                ['font', ['strikethrough', 'superscript', 'subscript']], // Font options
                ['fontsize', ['fontsize']], // Font size options
                ['color', ['color']], // Color options
                ['para', ['ul', 'ol', 'paragraph']], // Paragraph formatting
                ['height', ['height']] // Height adjustment
            ]
        });
    });
</script>
@endpush
