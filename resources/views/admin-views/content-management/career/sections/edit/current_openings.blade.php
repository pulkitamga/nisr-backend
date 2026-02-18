@extends('layouts.back-end.app')

@section('title', translate('Edit Job Opening'))

@push('css_or_js')
<!-- Summernote CSS -->
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')

@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
@endphp
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h2 class="h1 mb-4">{{ translate('Edit Job Opening') }}</h2>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.content-management.career.update', ['section' => 'current_openings', 'id' => $job->id]) }}"
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

                @php
                $translations = [];
                foreach ($job->translations as $translation) {
                $translations[$translation->locale][$translation->key] = $translation->value;
                }
                @endphp
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">

                    <!-- Title -->
                    <label for="title">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $job->title : ($translations[$lang]['title'] ?? '') }}" {{
                        $lang==$defaultLanguage ? 'required' : '' }}>

                    <label for="job_description" class="mt-3">{{ translate('Job Description') }} ({{ strtoupper($lang)
                        }})</label>
                    <textarea name="job_description[]" class="form-control summernote">
                            {!! $lang == $defaultLanguage ? $job->job_description : ($translations[$lang]['job_description'] ?? '') !!}
                        </textarea>

                    <label>{{ translate('Location') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="location[]" class="form-control" value="{{ $lang == $defaultLanguage ? $job->location : ($translations[$lang]['location'] ?? '') }}">

                    <label>{{ translate('Skills') }} ({{ strtoupper($lang) }})</label>
                    <textarea class="form-control summernote" name="skills[]"> {!! $lang == $defaultLanguage ? $job->skills : ($translations[$lang]['skills'] ?? '') !!}</textarea>
                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <div class="form-group">
                    <label>{{ translate('Experience') }}</label>
                    <input type="text" name="experience" class="form-control" value="{{ $job->experience }}">
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('script')
<!-- Summernote JS -->
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script>
    'use strict';
    $(document).on('ready', function() {
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