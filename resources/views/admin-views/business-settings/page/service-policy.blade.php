@extends('layouts.back-end.app')

@section('title', translate('service_policy'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';

$translations = [];
foreach ($service_policy->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}

@endphp

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/Pages.png')}}" alt="">
            {{translate('pages')}}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{translate('service_policy')}}</h5>
                </div>
                <form action="{{route('admin.business-settings.update-service')}}" method="post">
                    @csrf
                    <div class="card-body">
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
                        <div class="form-group">


                            @foreach($language as $lang)
                            <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                                id="{{ $lang }}-form">

                                @php
                                $termValue = $lang == $defaultLanguage
                                ? ($service_policy->value ?? '')
                                : ($translations[$lang]['value'] ?? '');
                                @endphp
                                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">

                                <textarea name="value[]" data-lang="{{ $lang }}" id="editor" cols="30" rows="20" class="form-control summernote">{{ $termValue }}</textarea>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <input class="form-control btn--primary" type="submit" value="{{translate('submit')}}" name="btn">
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