@extends('layouts.back-end.app')
@section('title', translate('create_hero_section'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
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

                {{-- Language Fields --}}
                @foreach($language as $lang)
                <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('heading') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="heading[]" class="form-control"
                            placeholder="{{ translate('Enter Heading') }}" {{ $lang==$defaultLanguage ? 'required' : ''
                            }}>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('subheading') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="subheading[]" class="form-control"
                            placeholder="{{ translate('Enter Subheading') }}">
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                {{-- Image Upload --}}
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
                    <button type="submit" class="btn btn--primary" id="submitBtn">{{ translate('submit') }}</button>
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
@endsection