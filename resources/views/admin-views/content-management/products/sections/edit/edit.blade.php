@extends('layouts.back-end.app')

@section('title', translate('Edit_product_section'))

@push('css_or_js')
<!-- Summernote CSS -->
@endpush

@section('content')

@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
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

                @php
                $translations = [];
                foreach ($products->translations as $translation) {
                $translations[$translation->locale][$translation->key] = $translation->value;
                }
                @endphp
                @foreach($language as $lang)
                <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">

                    <!-- Title -->
                    <label for="heading">{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="heading[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $products->heading : ($translations[$lang]['heading'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>

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
                    <input type="file" name="image" class="form-control" id="image-input">
                    @if ($products->image)
                    <div class="mt-2">
                        <img id="image-preview" src="{{ Storage::url($products->image) }}" width="100"
                            alt="Current Image">
                    </div>
                    @endif
                </div>

                <div id="preview-container" class="mt-3">
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
    document.getElementById('image-input').addEventListener('change', function (e) {
        const previewContainer = document.getElementById('preview-container');
        const files = e.target.files;

        // Clear old previews
        previewContainer.innerHTML = '';

        if (files && files[0]) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.marginTop = '10px';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '<p style="color:red">{{ __('Invalid file type. Please select an image.') }}</p>';
            }
        }
    });
</script>


@endpush


