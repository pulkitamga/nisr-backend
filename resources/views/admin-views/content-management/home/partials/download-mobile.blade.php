@php
$content = $jsonData['content'] ?? [];

$getDownloadImage = function (?string $image): string {
    if (empty($image)) {
        return asset('assets/back-end/img/placeholder.png');
    }

    if (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) {
        return $image;
    }

    $normalized = ltrim($image, '/');
    if (\Illuminate\Support\Str::startsWith($normalized, ['storage/', 'uploads/'])) {
        return asset($normalized);
    }

    return asset('uploads/' . $normalized);
};

@endphp

@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
@endphp

@php
                    $activeLanguage = $errors->any() ? $defaultLanguage : (in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage);
                @endphp
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
<div class="card p-4 mb-4">
    <form action="{{ route('admin.content-management.download-app.heading.update') }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($languages as $lang)
    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
        id="{{ $lang }}-form">
        <div class="row">
            <input type="hidden" name="lang[]" value="{{ $lang }}">
            <div class="col-lg-6">
                <label class="title-color">{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="heading[]" class="form-control"
                    value="{{ $lang == $defaultLanguage ? ($content['heading'] ?? '') : ($translations[$lang]['heading'] ?? '') }}"
                    placeholder="{{ translate('enter_heading') }}">
            </div>

        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
    </div>
</form>
</div>

<div class="card">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>{{ __('Platform') }}</th>
            <th>{{ __('Alt') }}</th>
            <th>{{ __('Image') }}</th>
            <th class="text-center">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        {{-- Android Button --}}
        @if (!empty($content['android_button']))
        <tr>
            <td>{{ __('Android') }}</td>
            <td>{{ $content['android_button']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ $getDownloadImage($content['android_button']['image'] ?? '') }}"
                    alt="{{ $content['android_button']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="android_button"
                    data-alt="{{ $content['android_button']['alt'] ?? '' }}"
                    data-image="{{ $content['android_button']['image'] ?? '' }}"
                    data-image-url="{{ $getDownloadImage($content['android_button']['image'] ?? '') }}">
                    <i class="tio-edit"></i>
                </a>

               
            </td>
        </tr>
        @endif

        {{-- iOS Button --}}
        @if (!empty($content['ios_button']))
        <tr>
            <td>{{ __('iOS') }}</td>
            <td>{{ $content['ios_button']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ $getDownloadImage($content['ios_button']['image'] ?? '') }}"
                    alt="{{ $content['ios_button']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">

                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="ios_button"
                    data-alt="{{ $content['ios_button']['alt'] ?? '' }}"
                    data-image="{{ $content['ios_button']['image'] ?? '' }}"
                    data-image-url="{{ $getDownloadImage($content['ios_button']['image'] ?? '') }}">
                    <i class="tio-edit"></i>
                </a>
                
            </td>
        </tr>
        @endif

        {{-- Mockup Image --}}
        @if (!empty($content['mockup_image']))
        <tr>
            <td>{{ __('Mockup') }}</td>
            <td>{{ $content['mockup_image']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ $getDownloadImage($content['mockup_image']['image'] ?? '') }}"
                    alt="{{ $content['mockup_image']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="mockup_image"
                    data-alt="{{ $content['mockup_image']['alt'] ?? '' }}"
                    data-image="{{ $content['mockup_image']['image'] ?? '' }}"
                    data-image-url="{{ $getDownloadImage($content['mockup_image']['image'] ?? '') }}">
                    <i class="tio-edit"></i>
                </a>
                
            </td>
        </tr>
        @endif

        {{-- App Logo --}}
        @if (!empty($content['app_logo']))
        <tr>
            <td>{{ __('App Logo') }}</td>
            <td>{{ $content['app_logo']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ $getDownloadImage($content['app_logo']['image'] ?? '') }}"
                    alt="{{ $content['app_logo']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="app_logo"
                    data-alt="{{ $content['app_logo']['alt'] ?? '' }}"
                    data-image="{{ $content['app_logo']['image'] ?? '' }}"
                    data-image-url="{{ $getDownloadImage($content['app_logo']['image'] ?? '') }}">
                    <i class="tio-edit"></i>
                </a>
             
            </td>
        </tr>
        @endif
    </tbody>
</table>
</div>

<div class="modal fade" id="editDownloadModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.content-management.download-app.update') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="download_app">
            <input type="hidden" name="key" id="edit-key">
            <input type="hidden" name="remove_image" id="edit-download-remove-image" value="0">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">{{translate('Edit Download App Item')}}</h5>
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-alt" class="form-label">{{translate('Alt Text')}}</label>
                        <input type="text" class="form-control" name="alt" id="edit-alt" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-image" class="form-label">{{translate('Image')}} (optional)</label>
                        <div class="mt-2" style="max-width:220px;">
                            <div class="custom_upload_input position-relative border-dashed-2">
                                <input type="file" class="custom-upload-input-file" name="image" id="edit-image"
                                    data-imgpreview="current-image" accept=".jpg, .png, .jpeg, .webp|image/*">
                                <span id="edit-download-image-clear" class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                    <i class="tio-delete"></i>
                                </span>
                                <div class="img_area_with_preview position-absolute z-index-2">
                                    <img id="current-image" src="" alt="{{ translate('Image Preview') }}"
                                        class="h-auto bg-white d-none cms-image-preview">
                                </div>
                                <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <img alt="" class="w-75" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                        <h3 class="text-muted text-capitalize">{{ translate('Upload_Image') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{translate('Cancel')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('[data-bs-target="#editDownloadModal"]');
        const modalElement = document.getElementById('editDownloadModal');
        const editForm = modalElement?.querySelector('form');
        const imageInput = document.getElementById('edit-image');
        const imagePreview = document.getElementById('current-image');
        const clearButton = document.getElementById('edit-download-image-clear');
        const removeImageInput = document.getElementById('edit-download-remove-image');
        let currentImage = '';

        const clearFileInput = () => {
            if (imageInput) {
                imageInput.value = '';
            }
        };

        const setPreview = (src) => {
            if (src) {
                imagePreview.src = src;
                imagePreview.classList.remove('d-none');
                clearButton.classList.remove('d-none');
                clearButton.classList.add('d-flex');
                return;
            }

            imagePreview.src = '';
            imagePreview.classList.add('d-none');
            clearButton.classList.add('d-none');
            clearButton.classList.remove('d-flex');
        };

        imageInput.addEventListener('change', function () {
            const file = imageInput.files?.[0];
            if (!file) {
                if (removeImageInput.value === '1') {
                    setPreview('');
                    return;
                }
                setPreview(currentImage);
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                removeImageInput.value = '0';
                setPreview(e.target.result || '');
            };
            reader.readAsDataURL(file);
        });

        clearButton.addEventListener('click', function () {
            clearFileInput();
            currentImage = '';
            removeImageInput.value = '1';
            setPreview('');
        });

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-key').value = btn.getAttribute('data-key');
                document.getElementById('edit-alt').value = btn.getAttribute('data-alt');
                const imagePath = btn.getAttribute('data-image');
                const imageUrl = btn.getAttribute('data-image-url');

                currentImage = imagePath ? imageUrl : '';
                removeImageInput.value = '0';
                clearFileInput();
                setPreview(currentImage);
            });
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            if (editForm) {
                editForm.reset();
            }
            clearFileInput();
            currentImage = '';
            removeImageInput.value = '0';
            setPreview('');
        });
    });

</script>
