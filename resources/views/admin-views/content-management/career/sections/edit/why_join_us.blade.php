@extends('layouts.back-end.app')

@section('title', translate('Edit Why Join Us Item'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')


@php
$language = getWebConfig('pnc_language') ?? ['en'];
$defaultLanguage = $language[0];
$translations = [];
foreach ($job->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
@endphp
<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Edit Why Join Us Item') }}</h2>

    <form
        action="{{ route('admin.content-management.career.update', ['section' => 'why_join_us', 'id' => $job->id]) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <ul class="nav nav-tabs mb-4">
            @foreach($language as $lang)
            <li class="nav-item">
                <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                    href="javascript:" id="{{ $lang }}-link">
                    {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                </a>
            </li>
            @endforeach
        </ul>


        @foreach($language as $lang)
        <div class="{{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form" id="{{ $lang }}-form">
            <!-- Title -->
            <div class="form-group">
                <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="title[]" class="form-control"
                    value="{{ $lang == $defaultLanguage ? $job->title : ($translations[$lang]['title'] ?? '') }}" {{
                    $lang==$defaultLanguage ? 'required' : '' }}>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                <textarea name="description[]" rows="4" class="form-control summernote">
                    {!! $lang == $defaultLanguage ? $job->description : ($translations[$lang]['description'] ?? '') !!}
                </textarea>
            </div>
        </div>

        <input type="hidden" name="lang[]" value="{{ $lang }}">
        @endforeach

        <!-- Icon Field -->
        <div class="form-group">
            <label>{{ translate('Icon') }}</label>
            <input type="text" name="icon" class="form-control" value="{{ $job->icon }}">
        </div>
        <!-- Is Active Checkbox -->
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" {{ $job->is_active ? 'checked' : '' }}>
                {{ translate('Active') }}
            </label>
        </div>

        <!-- Submit Button -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
        </div>
    </form>
</div>
@endsection

@push('script')
<!-- Summernote JS -->
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script>
    'use strict';
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