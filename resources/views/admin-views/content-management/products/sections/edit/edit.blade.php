@extends('layouts.back-end.app')

@section('title', translate('Edit_product_section'))

@push('css_or_js')
<!-- Summernote CSS -->
@endpush

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
            <h2 class="h1 mb-4">{{ translate('Edit product section') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.products.update', ['id' => $products->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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

                @php
                $translations = [];
                foreach ($products->translations as $translation) {
                $translations[$translation->locale][$translation->key] = $translation->value;
                }
                @endphp
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">

                    <!-- Title -->
                    <label for="heading">{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="heading[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $products->heading : ($translations[$lang]['heading'] ?? '') }}">

                    <!-- Job Description -->
                    <label for="description" class="mt-3">{{ translate('Description') }} ({{ strtoupper($lang)
                        }})</label>
                    <textarea name="description[]" class="form-control " rows="5">
            {{$lang == $defaultLanguage ? $products->description : ($translations[$lang]['description'] ?? '') }}
        </textarea>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <input type="hidden" name="type" value="{{$products->type}}">
                 <div class="form-group">
                    <label>{{ translate('button_link') }}</label>
                    <input type="text" name="button_link" class="form-control"  value="{{ $products->button_link ?? '' }}" id="image-input">
                 
                </div>
                <div class="form-group">
                    <label>{{ translate('image') }}</label>
                    <input type="file" name="image" class="form-control" id="image_input">
                    @if ($products->image)
                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                    @endif
                    <div id="image-preview" class="mt-2 position-relative d-inline-block" style="{{ $products->image ? '' : 'display:none;' }}">
                        <img id="preview_img" src="{{ $products->image ? Storage::url($products->image) : '' }}" style="width: 100px;">
                        <button type="button" id="remove_btn" class="btn btn-sm btn-danger position-absolute" style="top: -5px; right: -5px; padding: 0 5px; line-height: 1.2; font-size: 12px; border-radius: 50%;">&times;</button>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                </div>
            </form>
        </div>

    </div>
</div>



@endsection

@push('script')
<script>
    document.getElementById('image_input').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview_img').src = e.target.result;
                document.getElementById('image-preview').style.display = '';
                var removeInput = document.getElementById('remove_image');
                if (removeInput) removeInput.value = 0;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('remove_btn').addEventListener('click', function () {
        document.getElementById('image_input').value = '';
        document.getElementById('preview_img').src = '';
        document.getElementById('image-preview').style.display = 'none';
        var removeInput = document.getElementById('remove_image');
        if (removeInput) removeInput.value = 1;
    });
</script>


@endpush

