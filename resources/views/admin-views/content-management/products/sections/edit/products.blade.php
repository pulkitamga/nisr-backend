@extends('layouts.back-end.app')

@section('title', translate('Edit Mission Section'))

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
            <h2 class="h1 text-capitalize">{{ translate('Edit Mission Section') }}</h2>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.content-management.about-us.update', ['section' => 'mission', 'id' => $model->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Language Tabs -->
                @php($activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage)
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
                    <!-- Title -->
                    <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->title : ($translations[$lang]['title'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>

                    <!-- Content -->
                    <label class="mt-3">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" rows="5"
                        class="form-control">{{ $lang == $defaultLanguage ? $model->description : ($translations[$lang]['description'] ?? '') }}</textarea>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach
                <div class="form-group">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control">
                    @if($model->image)

                    <div class="mt-4" style="width: 600px; height: 250px; background-color: #f0f0f0; 
                         border: 2px dashed #139d91; overflow: hidden; 
                              position: relative; display: flex; justify-content: center; align-items: center;">
                        <img src="{{ Storage::url($model->image) }}" alt="Image Preview"
                            style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
                    </div>

                    @endif
                </div>
                <!-- Submit -->
                <div class="form-group mt-3">
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
    });
</script>
@endpush

