@extends('layouts.back-end.app')
@section('title', translate('create_dealer_section'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
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
            <h2 class="h1 text-capitalize mb-3">{{ translate('create_dealer_section') }}</h2>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.content-management.about-us.store', ['section' => 'dealers']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h4 class="mb-2">{{ translate('Quick_filters') }}</h4>
                        <p class="text-muted mb-3">{{ translate('Enable_the_values_you_want_to_appear_as_quick_filters_on_the_about_page') }}</p>
                        <div class="d-flex flex-wrap gap-4">
                            <label class="d-flex align-items-center gap-2 mb-0">
                                <input type="checkbox" name="show_partner_type_filter" value="1" {{ old('show_partner_type_filter') ? 'checked' : '' }}>
                                <span>{{ translate('Show_partner_type_in_quick_filters') }}</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 mb-0">
                                <input type="checkbox" name="show_location_filter" value="1" {{ old('show_location_filter') ? 'checked' : '' }}>
                                <span>{{ translate('Show_location_in_quick_filters') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Language Tabs --}}
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

                @foreach($language as $lang)
                <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('partner_type') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="partner_type[]" class="form-control"
                            placeholder="{{ translate('Enter_partner_type') }}">
                        <small class="text-muted">{{ translate('Example_authorized_dealer_service_partner_or_regional_partner') }}</small>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('dealer_name') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="dealer_name[]" class="form-control"
                            placeholder="{{ translate('Enter Dealer Name') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ translate('location') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="location[]" class="form-control"
                            placeholder="{{ translate('Enter Location') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ translate('coverage_area') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="coverage_area[]" class="form-control"
                            placeholder="{{ translate('Enter_coverage_area') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" class="form-control summernote" rows="6">{!! old('description.' . $loop->index, '') !!}</textarea>
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                {{-- Image Upload --}}
                <div class="form-group">
                    <label>{{ translate('image') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*"
                        onchange="previewImage(event)">
                </div>

                {{-- Image Preview Box --}}
                <div class="image-preview-box mt-4" style="width: 600px; height: 250px; background-color: #f0f0f0; 
            border: 2px dashed #139d91; overflow: hidden; 
            position: relative; display: flex; justify-content: center; align-items: center;">
                    <img id="imagePreview" src="#" alt="Image Preview"
                        style="width: 100%; height: 100%; object-fit: cover; display: none; position: absolute; top: 0; inset-inline-start: 0;">
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    // Language tab switching logic
    document.querySelectorAll('.form-system-language-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var lang = this.id.replace('-link', '');

            document.querySelectorAll('.form-system-language-tab').forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.form-system-language-form').forEach(function(formDiv) {
                if(formDiv.id === lang + '-form') {
                    formDiv.classList.remove('d-none');
                } else {
                    formDiv.classList.add('d-none');
                }
            });
        });
    });

    // Image preview function
    function previewImage(event) {
        var file = event.target.files[0];
        if(!file) return;
        
        var reader = new FileReader();

        reader.onload = function() {
            var preview = document.getElementById('imagePreview');
            preview.style.display = 'block';
            preview.src = reader.result;
        }

        reader.readAsDataURL(file);
    }

    $(document).ready(function () {
        $('.summernote').each(function () {
            const $editor = $(this);
            if ($editor.next('.note-editor').length) {
                return;
            }

            $editor.summernote({
                height: 220,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['codeview']]
                ]
            });
        });
    });
</script>
@endpush
@endsection
