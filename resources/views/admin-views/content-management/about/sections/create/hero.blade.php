@extends('layouts.back-end.app')
@section('title', translate('create_hero_section'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h2 class="h1 text-capitalize mb-3">{{ translate('create_hero_section') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.about-us.store', ['section' => 'hero']) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                {{-- Language Tabs --}}
                @php
                    $activeLanguage = $errors->any() ? $defaultLanguage : (in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage);
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

                {{-- Language Fields --}}
                @foreach($language as $lang)
                <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="heading[]" class="form-control"
                            placeholder="{{ translate('enter_heading') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Subheading') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="subheading[]" class="form-control"
                            placeholder="{{ translate('Enter Subheading') }}">
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                {{-- Image Upload --}}
                <div class="form-group">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" id="hero_image" name="image" class="form-control" accept="image/*">
                </div>

                {{-- Image Preview --}}
                <div id="image-preview" class="mt-2 position-relative d-inline-block" style="display: none;">
                    <img id="imagePreview" src="#" alt="Image Preview" style="width: 100px;">
                    <button type="button" id="remove_btn" class="btn btn-sm btn-danger position-absolute" style="top: -5px; inset-inline-end: -5px; padding: 0 5px; line-height: 1.2; font-size: 12px; border-radius: 50%;">&times;</button>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary" id="submitBtn">{{ translate('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script --}}
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
    const heroImage = document.getElementById('hero_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('imagePreview');

    heroImage.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = '';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('remove_btn').addEventListener('click', function () {
        heroImage.value = '';
        previewImg.src = '';
        imagePreview.style.display = 'none';
    });
</script>
@endsection
