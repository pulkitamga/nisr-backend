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
                @php($activeLanguage = $errors->any() ? $defaultLanguage : (in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage))
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
                        value="{{ $lang == $defaultLanguage ? $model->heading : ($translations[$lang]['heading'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>

                    <!-- Subheading -->
                    <label class="mt-3">{{ translate('Subheading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="subheading[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->subheading : ($translations[$lang]['subheading'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <!-- Image -->
                <div class="form-group mt-3">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control">
                    @if($model->image)
                    <img src="{{ Storage::url($model->image) }}" style="width: 100px;">
                    @endif
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
    });
</script>
@endpush
