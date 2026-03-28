@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
}

@endphp

<form action="{{ route('admin.content-management.wholesaler_section.update') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php($activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage)
<ul class="nav nav-tabs mb-4">
        @foreach($languages as $lang)
        <li class="nav-item">
            <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                href="javascript:" id="{{ $lang }}-link">
                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
            </a>
        </li>
        @endforeach
    </ul>




    <div class="card border shadow-none mb-3">
        <div class="card-body">
            @foreach($languages as $lang)
            <input type="hidden" name="lang[]" value="{{ $lang }}">

            <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
                <div class="row">
                    <div class="col-lg-6">

                        <label class="form-label">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control" value="{{ $lang == $defaultLanguage ? ($jsonData['title']) :  ($translations[$lang]['title'] ?? '')  }}"
                            placeholder="{{ translate('Enter Title') }}">
                    </div>
                    <div class="col-lg-6">
                        <label class="title-color">{{ translate('Button Text') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="button_text[]" class="form-control"
                            value="{{ $lang == $defaultLanguage ? ( $jsonData['button']['text']) :  ($translations[$lang]['button_text'] ?? '')  }}"
                            placeholder="{{ translate('Enter Button Text') }}">
                    </div>
                    <div class="col-lg-12 mt-3">
                        <label class="title-color">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" class="form-control" rows="3"
                            placeholder="{{ translate('Enter Description') }}">{{ $lang == $defaultLanguage ? ($jsonData['description']) :  ($translations[$lang]['description'] ?? '')  }}</textarea>
                    </div>
                </div>
            </div>

            @endforeach

        </div>
    </div>


    <div class="card border shadow-none mb-3">
        <div class="card-body">
            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="title-color">{{ translate('Button Link') }}</label>
                        <input type="text" name="button_link" class="form-control"
                            value="{{ $jsonData['button']['link'] ?? '' }}"
                            placeholder="{{ translate('Enter Button Link') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border shadow-none">
        <div class="card-body">
            <div class="mx-auto max-w-400">
                <div class="mb-3 text-center">
                    <label class="title-color font-weight-bold mb-0">{{ translate('Image') }}</label>
                    <span class="badge badge-soft-info">({{ translate('Size') }}: 310px x 240px)</span>
                </div>

                <div class="custom_upload_input">
                    <input type="file" name="image" class="image-input meta-img" data-image-id="view-wholesaler-image"
                        accept="image/*">
                    <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                        <i class="tio-delete"></i>
                    </span>
                    <div class="img_area_with_preview position-absolute z-index-2">
                        @php($imagePath = $jsonData['image'] ?? '')
                        <img id="view-wholesaler-image"
                            src="{{ $imagePath ? asset($imagePath) : asset('assets/back-end/img/placeholder.png') }}"
                            class="bg-white" alt="">
                    </div>
                    <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <img alt="" class="w-75"
                                src="{{ dynamicAsset('public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                            <h3 class="text-muted text-capitalize">{{ translate('Upload Image') }}</h3>
                        </div>
                    </div>
                </div>

                <p class="text-muted text-center mt-2">
                    {{ translate('Image format') }}: jpg, png, jpeg, webp<br>
                    {{ translate('Image size') }}: {{ translate('Max') }} 2 MB
                </p>
            </div>
        </div>
    </div>

    <div class="row justify-content-end gap-3 mt-3 mx-1">
        <button type="reset" class="btn btn-secondary px-5">{{ translate('Reset') }}</button>
        <button type="submit" class="btn btn--primary px-5">{{ translate('Update') }}</button>
    </div>

</form>

