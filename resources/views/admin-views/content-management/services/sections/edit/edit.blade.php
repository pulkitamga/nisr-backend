@extends('layouts.back-end.app')

@section('title', translate('Edit_services_section'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
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
            <div class="cms-admin-heading mb-0">
                <h1 class="cms-admin-heading__title h3">{{ translate('edit_services_section') }}</h1>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.services.update', ['id' => $products->id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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

                    <!-- Button Text -->
                    <label class="mt-3">{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="button_text[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? ($products->button_text ?? '') : ($translations[$lang]['button_text'] ?? '') }}">

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <input type="hidden" name="type" value="{{$products->type}}">
                 <div class="form-group">
                    <label>{{ translate('button_link') }}</label>
                    <input type="text" name="button_link" class="form-control"  value="{{ $products->button_link ?? '' }}">
                 
                </div>
                <div class="form-group">
                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control" id="image_input">
                    @if ($products->image)
                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                    @endif
                    <div id="image-preview" class="mt-2 position-relative d-inline-block" style="{{ $products->image ? '' : 'display:none;' }}">
                        <img id="preview_img" src="{{ $products->image ? Storage::url($products->image) : '' }}" style="width: 100px;">
                        <button type="button" id="remove_btn" class="btn btn-sm btn-danger position-absolute" style="top: -5px; inset-inline-end: -5px; padding: 0 5px; line-height: 1.2; font-size: 12px; border-radius: 50%;">&times;</button>
                    </div>
                </div>

                @if(in_array($products->type, ['core_services', 'featured_services'], true))
                    @php
                        $selectedItemIds = collect($products->selected_item_ids ?? [])->map(fn ($id) => (int) $id)->all();
                        $selectionLabel = $products->type === 'featured_services' ? translate('Featured_selection_items') : translate('Quick_browse_items');
                    @endphp
                    <div class="form-group mt-3">
                        <label>{{ $selectionLabel }}</label>
                        <select name="selected_item_ids[]" class="form-control js-select2-custom" multiple
                            data-placeholder="{{ translate('Select_showcase_items') }}">
                            @foreach($catalogueItems as $catalogueItem)
                                <option value="{{ $catalogueItem->id }}" {{ in_array((int) $catalogueItem->id, $selectedItemIds, true) ? 'selected' : '' }}>
                                    {{ $catalogueItem->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">
                            {{ translate('Leave_this_empty_to_use_the_default_showcase_order') }}
                        </small>
                    </div>
                @endif


                <!-- Submit Button -->
                <div class="form-group mt-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.content-management.services') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                        <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>



@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/select2/js/select2.min.js') }}"></script>
<script>
    $('.js-select2-custom').select2({
        width: '100%'
    });

    const imageInput = document.getElementById('image_input');
    const removeButton = document.getElementById('remove_btn');

    imageInput?.addEventListener('change', function (e) {
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

    removeButton?.addEventListener('click', function () {
        document.getElementById('image_input').value = '';
        document.getElementById('preview_img').src = '';
        document.getElementById('image-preview').style.display = 'none';
        var removeInput = document.getElementById('remove_image');
        if (removeInput) removeInput.value = 1;
    });
</script>


@endpush
