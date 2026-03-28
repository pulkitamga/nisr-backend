@extends('layouts.back-end.app')

@section('title', translate('Edit Timeline Section'))

@php
$language = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
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

                <!-- Translatable Fields -->
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
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

                <div class="form-group mt-4">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewIcon(this)">
                </div>

                <div class="form-group">
                    <br>
                    <label>{{ translate('Image Preview') }}</label>

                    <div id="previewWrapper" style="position: relative; display: inline-block;">
                        <img id="iconPreview"
                            src="{{ Storage::url($model->image) }}"
                            style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #ccc; display: {{ $model->image ? 'inline-block' : 'none' }};">

                        <button type="button" id="removeImageBtn"
                            style="position:absolute; top:-10px; right:-10px; background:#ff0000; color:#fff; border:none; border-radius:50%; width:22px; height:22px; font-size:14px; cursor:pointer; display: {{ $model->image ? 'block' : 'none' }};">
                            &times;
                        </button>
                    </div>

                    <input type="hidden" name="remove_image" id="remove_image" value="0">
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


<script>
    function previewIcon(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.getElementById('iconPreview');
                img.src = e.target.result;
                img.style.display = 'inline-block';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

     document.getElementById('removeImageBtn').addEventListener('click', function () {
        document.getElementById('iconPreview').src = "";
        document.getElementById('iconPreview').style.display = "none";

        document.querySelector("input[name='image']").value = "";
        document.getElementById('removeImageBtn').style.display = "none";

        document.getElementById('remove_image').value = 1;
    });
</script>

@endpush


