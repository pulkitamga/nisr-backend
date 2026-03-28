@extends('layouts.back-end.app')

@section('title', translate('Edit Dealer Section'))

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
            <h2 class="h1 text-capitalize">{{ translate('Edit Dealer Section') }}</h2>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.content-management.about-us.update', ['section' => 'dealers', 'id' => $model->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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

                @foreach($language as $lang)
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">
                    <!-- Description -->
                    <label for="description">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" rows="5"
                        class="form-control">{{ $lang == $defaultLanguage ? $model->description : ($translations[$lang]['description'] ?? '') }}</textarea>
                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    <label class="mt-3">{{ translate('Dealer Name') }} ({{ strtoupper($lang)
                        }})</label>
                    <input type="text" name="dealer_name[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->dealer_name : ($translations[$lang]['dealer_name'] ?? '') }}"
                        required>
                    <label>{{ translate('Location') }} ({{ strtoupper($lang)
                        }})</label>
                    <input type="text" name="location[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $model->location : ($translations[$lang]['location'] ?? '')  }}">
                </div>
                @endforeach


                <div class="form-group">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control">
                    @if($model->image)
                    <img src="{{ Storage::url($model->image) }}" style="width: 100px;">
                    @endif
                </div>

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
    // Language tab switcher
    $(document).ready(function() {
        $('.form-system-language-tab').on('click', function() {
            let lang = $(this).attr('id').replace('-link', '');
            $('.form-system-language-tab').removeClass('active');
            $('.form-system-language-form').addClass('d-none');
            $(this).addClass('active');
            $('#' + lang + '-form').removeClass('d-none');
        });
    });
</script>
@endpush

