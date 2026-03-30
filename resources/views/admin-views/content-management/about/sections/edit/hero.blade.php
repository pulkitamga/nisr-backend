@extends('layouts.back-end.app')

@section('title', translate('Edit Hero Section'))

@php
$language = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}

$translations = [];
foreach ($model->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
@endphp

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h2 class="h1 text-capitalize">{{ translate('Edit Hero Section') }}</h2>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.content-management.about-us.update', ['section' => 'hero', 'id' => $model->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Language Tabs -->
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

                <!-- Language Fields -->
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">
                    <!-- Heading -->
                    <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="heading[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->heading : ($translations[$lang]['heading'] ?? '') }}">

                    <!-- Subheading -->
                    <label class="mt-3">{{ translate('Subheading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="subheading[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->subheading : ($translations[$lang]['subheading'] ?? '') }}">

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <!-- Image -->
                <div class="form-group mt-3">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" id="image_input" class="form-control">
                    @if($model->image)
                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                    @endif
                    <div id="image-preview" class="mt-2 position-relative d-inline-block" style="{{ $model->image ? '' : 'display:none;' }}">
                        <img id="preview_img" src="{{ $model->image ? Storage::url($model->image) : '' }}" style="width: 100px;">
                        <button type="button" id="remove_btn" class="btn btn-sm btn-danger position-absolute" style="top: -5px; inset-inline-end: -5px; padding: 0 5px; line-height: 1.2; font-size: 12px; border-radius: 50%;">&times;</button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-group">
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('.form-system-language-tab').on('click', function () {
            var lang = $(this).attr('id').replace('-link', '');
            $('.form-system-language-tab').removeClass('active');
            $('.form-system-language-form').addClass('d-none');
            $(this).addClass('active');
            $('#' + lang + '-form').removeClass('d-none');
        });

        // Preview image on file choose
        $('#image_input').on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview_img').attr('src', e.target.result);
                    $('#image-preview').show();
                    var removeInput = document.getElementById('remove_image');
                    if (removeInput) removeInput.value = 0;
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove image button
        $('#remove_btn').on('click', function () {
            $('#image_input').val('');
            $('#preview_img').attr('src', '');
            $('#image-preview').hide();
            var removeInput = document.getElementById('remove_image');
            if (removeInput) removeInput.value = 1;
        });
    });
</script>
@endpush
