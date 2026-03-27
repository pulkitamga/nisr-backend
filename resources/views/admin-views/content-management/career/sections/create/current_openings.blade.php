@extends('layouts.back-end.app')

@section('title', translate('Add Job Opening'))


@push('css_or_js')
<!-- Summernote CSS -->
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$baseLanguage = config('app.locale', 'en');
if (!in_array($baseLanguage, $language ?? [], true)) {
    $baseLanguage = $language[0] ?? 'en';
}
@endphp

@section('content')
<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Add Job Opening') }}</h2>
<div class="card p-4">
    <form action="{{ route('admin.content-management.career.store', ['section' => 'current_openings']) }}" method="POST"
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

        <!-- Language Fields -->
        @foreach($language as $lang)
        <div class="form-system-language-form {{ $lang != $baseLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
            <div class="form-group">
                <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="title[]" class="form-control" placeholder="{{ translate('Enter title') }}" {{
                    $lang==$baseLanguage ? 'required' : '' }}>
            </div>

            <div class="form-group">
                <label>{{ translate('Job Description') }} ({{ strtoupper($lang) }})</label>
                <textarea class="form-control summernote" name="job_description[]" {{
                    $lang==$baseLanguage ? 'required' : '' }}></textarea>
            </div>
            <div class="form-group">
                <label>{{ translate('Skills (comma separated)') }} ({{ strtoupper($lang) }})</label>
                <textarea class="form-control summernote" name="skills[]" {{
                    $lang==$baseLanguage ? 'required' : '' }}></textarea>
            </div>
            <div class="form-group">
                <label>{{ translate('Location') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="location[]" class="form-control" {{
                    $lang==$baseLanguage ? 'required' : '' }}>
            </div>

            <input type="hidden" name="lang[]" value="{{ $lang }}">
        </div>
        @endforeach


        <!-- Experience Field -->
        <div class="form-group">
            <label>{{ translate('Experience') }}</label>
            <input type="text" name="experience" class="form-control">
        </div>


        <!-- Submit -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
        </div>
    </form>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 150
        });

        $('.form-system-language-tab').on('click', function() {
            let lang = $(this).attr('id').replace('-link', '');
            $('.form-system-language-tab').removeClass('active');
            $(this).addClass('active');
            $('.form-system-language-form').addClass('d-none');
            $('#' + lang + '-form').removeClass('d-none');
        });
    });
</script>
@endpush
