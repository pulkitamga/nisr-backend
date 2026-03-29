@extends('layouts.back-end.app')

@section('title', translate('Edit Timeline Section'))

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
            <h2 class="h1 text-capitalize">{{ translate('Edit Timeline Section') }}</h2>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.content-management.about-us.update', ['section' => 'timeline', 'id' => $model->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Non-translatable field: Year -->
               
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

                <!-- Translatable Fields -->
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">
                    <!-- Title -->
                    <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->title : ($translations[$lang]['title'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>

                    <!-- Description -->
                    <label class="mt-3">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" rows="5"
                        class="form-control">{{ $lang == $defaultLanguage ? $model->description : ($translations[$lang]['description'] ?? '') }}</textarea>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach
 <div class="form-group">
                    <label>{{ translate('Year') }}</label>
                    <input type="text" name="year" class="form-control" value="{{ old('year', $model->year) }}"
                        required>
                </div>

                <!-- Non-translatable field: Icon -->
                <div class="form-group mt-4">
                    <label>{{ translate('Icon') }}</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $model->icon) }}">
                </div>

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
            let lang = $(this).attr('id').replace('-link', '');
            $('.form-system-language-tab').removeClass('active');
            $('.form-system-language-form').addClass('d-none');
            $(this).addClass('active');
            $('#' + lang + '-form').removeClass('d-none');
        });
    });
</script>
@endpush
