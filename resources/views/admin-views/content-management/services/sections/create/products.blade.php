@extends('layouts.back-end.app')
@section('title', translate('create_product_section'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header">

            <h2 class="h1 text-capitalize mb-3">{{ translate('create_product_section') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.about-us.store', ['section' => 'products']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Language Tabs --}}
                @php($activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage)
<ul class="nav nav-tabs mb-4">
                    @foreach ($language as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Language-specific group for all fields --}}
                @foreach ($language as $lang)
                <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control" {{ $lang==$defaultLanguage ? 'required'
                            : '' }}>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" rows="5" class="form-control"></textarea>
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <div class="form-group">
                    <label>{{ translate('image') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*"
                        onchange="previewImage(event)">
                </div>

                {{-- Image Preview Box --}}
                <div class="image-preview-box mt-4" style="width: 600px; height: 250px; background-color: #f0f0f0; 
           border: 2px dashed #139d91; overflow: hidden; 
           position: relative; display: flex; justify-content: center; align-items: center;">
                    <img id="imagePreview" src="#" alt="Image Preview"
                        style="width: 100%; height: 100%; object-fit: cover; display: none; position: absolute; top: 0; left: 0;">
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Language tab switching logic
    document.querySelectorAll('.form-system-language-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var lang = this.id.replace('-link', '');

            document.querySelectorAll('.form-system-language-tab').forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.form-system-language-form').forEach(function(formDiv) {
                if(formDiv.id === lang + '-form') {
                    formDiv.classList.remove('d-none');
                } else {
                    formDiv.classList.add('d-none');
                }
            });
        });
    });

    // Image preview function
    function previewImage(event) {
        var file = event.target.files[0];
        if(!file) return;
        
        var reader = new FileReader();

        reader.onload = function() {
            var preview = document.getElementById('imagePreview');
            preview.style.display = 'block';
            preview.src = reader.result;
        }

        reader.readAsDataURL(file);
    }
</script>
@endsection

