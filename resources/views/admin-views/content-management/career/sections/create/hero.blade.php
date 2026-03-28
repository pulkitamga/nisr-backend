@extends('layouts.back-end.app')
@section('title', translate('Add Banner'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $language ?? [], true)) {
    $baseLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Add Section') }}</h2>
    <div class="card p-4">
        <form action="{{ route('admin.content-management.career.store', ['section' => 'hero']) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Language Tabs --}}
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

            @foreach($language as $lang)
            <div class="form-system-language-form {{ $lang != $baseLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">

                <!-- Title -->
                <div class="form-group">
                    <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control" {{ $lang==$baseLanguage ? 'required' : '' }}>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" rows="4" class="form-control summernote"></textarea>
                </div>
                <div class="form-group">
                    <label>{{ translate('Button Text') }}  ({{ strtoupper($lang) }})</label>
                    <input type="text" name="buttonText[]" class="form-control">
                </div>


                <input type="hidden" name="lang[]" value="{{ $lang }}">
            </div>
            @endforeach
            <!-- Button Text -->



            <!-- Button Link (non-translatable) -->
            <div class="form-group">
                <label>{{ translate('Button Link') }}</label>
                <input type="url" name="button_link" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ translate('image') }}</label>
                <input type="file" id="hero_image" name="image" class="form-control" accept="image/*"
                    onchange="previewImage(event)">
            </div>

            {{-- Image Preview --}}
            <div class="image-preview-box mt-4" style="width: 600px; height: 250px; background-color: #f0f0f0;
             border: 2px dashed #139d91; overflow: hidden;
             position: relative; display: flex; justify-content: center; align-items: center;">
                <img id="imagePreview" src="#" alt="Image Preview"
                    style="width: 100%; height: 100%; object-fit: cover; display: none; position: absolute; top: 0; left: 0;">
            </div>
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

    // Language tab logic
    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.id.replace('-link', '');
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.form-system-language-form').forEach(f => f.classList.add('d-none'));
            document.getElementById(lang + '-form').classList.remove('d-none');
        });
    });
</script>

<script>
    // Language Tab Logic
    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.id.replace('-link', '');
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.form-system-language-form').forEach(f => f.classList.add('d-none'));
            document.getElementById(lang + '-form').classList.remove('d-none');
        });
    });

    // Image Preview and Validation
    function previewImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function() {
            const preview = document.getElementById('imagePreview');
            preview.src = reader.result;
            preview.style.display = 'block';

            preview.onload = function() {
                const width = preview.naturalWidth;
                const height = preview.naturalHeight;


            };
        };

        reader.readAsDataURL(file);
    }
</script>
@endpush
