@extends('layouts.back-end.app')

@section('title', translate('create_blog'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <div class="cms-admin-heading">
        <h1 class="cms-admin-heading__title h3">{{ translate('create_blog') }}</h1>
    </div>

    <form action="{{ route('admin.content-management.blog.store') }}" method="POST" enctype="multipart/form-data">
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
        <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
            <div class="form-group">
                <label>{{ translate('heading') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="heading[]" class="form-control"
                       placeholder="{{ translate('Enter Heading') }}">
            </div>

            <div class="form-group">
                <label>{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                <textarea name="description[]" class="form-control" rows="5"
                          placeholder="{{ translate('Enter Description') }}"></textarea>
            </div>

            <input type="hidden" name="lang[]" value="{{ $lang }}">
        </div>
        @endforeach

        {{-- Image Upload --}}
        <div class="form-group">
            <label>{{ translate('image') }}</label>
            <input type="file" id="blog_image" name="image" class="form-control" accept="image/*" onchange="previewImage(event)" required>
        </div>

        {{-- Image Preview Box --}}
        <div class="image-preview-box mt-3" style="width: 300px; height: 200px; background-color: #f8f9fa; 
             border: 2px dashed #ddd; display: flex; justify-content: center; align-items: center;">
            <img id="imagePreview" src="#" alt="Image Preview" style="display: none; max-width: 100%; max-height: 100%;">
            <span id="imagePreviewPlaceholder" style="color: #aaa;">{{ translate('No Image Selected') }}</span>
        </div>

        {{-- Blog Type --}}
        <div class="form-group mt-3">
            <label>{{ translate('blog_type') }}</label>
            <select name="blog_type" class="form-control" required>
                @foreach($blogTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>

        {{-- Category --}}
        <div class="form-group">
            <label>{{ translate('category') }}</label>
            <select name="category" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-4 d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.content-management.blog') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
        </div>
    </form>
</div>
<script>
    // Image Preview Function
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePreviewPlaceholder');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
            placeholder.style.display = 'block';
        }
    }
</script>

@endsection
