@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
}

@endphp
<div class="d-flex justify-content-end my-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal" data-toggle="modal" data-target="#addBannerModal">
        {{ translate('Add Banner') }}
    </button>
</div>

<!-- Table -->
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>{{ translate('Heading') }}</th>
                <th>{{ translate('Paragraph') }}</th>
                <th class="text-nowrap">{{ translate('Button Text') }}</th>
                <th>{{ translate('Button Link') }}</th>
                <th>{{ translate('Image') }}</th>
                <th>{{ translate('Status') }}</th>
                <th>{{ translate('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jsonData as $index => $item)

            @php
            $headingLangMap = [];
            $paragraphLangMap = [];
            $buttonTextLangMap = [];

            foreach ($languages as $lang) {
            $headingLangMap[$lang] = $translations[$lang]['cards'][$index]['heading'] ?? '';
            $paragraphLangMap[$lang] = $translations[$lang]['cards'][$index]['paragraph'] ?? '';
            $buttonTextLangMap[$lang] = $translations[$lang]['cards'][$index]['buttonText'] ?? '';
            }

            $headingLangMap[$defaultLanguage] = $item['heading'] ?? '';
            $paragraphLangMap[$defaultLanguage] = $item['paragraph'] ?? '';
            $buttonTextLangMap[$defaultLanguage] = $item['buttonText'] ?? '';
            @endphp



            <tr>
                <td>{{ $headingLangMap[getDefaultLanguage()] ?? $item['heading'] ?? '' }}</td>
                <td>{{ $paragraphLangMap[getDefaultLanguage()] ?? $item['paragraph'] ?? '' }}</td>
                <td>{{ $buttonTextLangMap[getDefaultLanguage()] ?? $item['buttonText'] ?? '' }}</td>
                <td>{{ $item['buttonLink'] ?? '' }}</td>
                <td>
                    @if(!empty($item['image']))
                    <img src="{{ asset($item['image']) }}" alt="{{ translate('Banner') }}" width="100">
                    @endif
                </td>
                <td>
                    <label class="switcher mx-auto">
                        <input type="checkbox" class="switcher_input banner-toggle" data-index="{{ $index }}" {{
                            $item['is_active'] ? 'checked' : '' }}>
                        <span class="switcher_control"></span>
                    </label>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <button
                            class="btn btn-outline-primary btn-sm square-btn edit-banner-btn"
                            data-index="{{ $index }}"
                            data-heading='@json($headingLangMap)'
                            data-paragraph='@json($paragraphLangMap)'
                            data-buttontext='@json($buttonTextLangMap)'
                            data-buttonlink="{{ $item['buttonLink'] }}"
                            data-image="{{ $item['image'] }}"
                            data-status="{{ $item['is_active'] ?? 0 }}">
                            <i class="tio-edit"></i>
                        </button>


                        <button
                            class="btn btn-outline-danger btn-sm square-btn"
                            onclick="confirmAndDelete(this)"
                            data-url="{{ route('admin.content-management.banner.delete') }}"
                            data-index="{{ $index }}"
                            data-section="{{ $currentType }}">
                            <i class="tio-delete"></i>
                        </button>

                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">{{ translate('No data found for this section.') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" role="dialog">
    <form action="{{ route('admin.content-management.banner.update') }}" method="POST" enctype="multipart/form-data"
        id="editForm">
        @csrf
        <input type="hidden" name="index" id="editIndex">
        <input type="hidden" name="section" value="{{ $currentType }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Edit Banner') }}</h5>
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
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
                <div class="modal-body">
                    @foreach($languages as $lang)

                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">
                        <div class="form-group">

                            <label>{{ translate('Heading') }}({{ strtoupper($lang) }})</label>
                            <input name="heading[]" id="edit-heading"
                                placeholder="{{ translate('Heading') }}" class="form-control lang-heading"
                                data-lang="{{ $lang }}">

                            <label>{{ translate('Paragraph') }}({{ strtoupper($lang) }})</label>
                            <textarea name="paragraph[]" id="edit-paragraph"
                                placeholder="{{ translate('Paragraph') }}" class="form-control lang-paragraph"
                                data-lang="{{ $lang }}"
                                rows="3"></textarea>

                            <label>{{ translate('Button Text') }}({{ strtoupper($lang) }})</label>
                            <input name="buttonText[]" id="edit-buttonText"
                                placeholder="{{ translate('Button Text') }}" class="form-control lang-button"
                                data-lang="{{ $lang }}">
                        </div>
                    </div>
                    @endforeach
                    <label>{{ translate('Button Link') }}</label>
                    <input name="buttonLink" id="editButtonLink" class="form-control mb-2"
                        placeholder="{{ translate('Button Link') }}">

                    <label>{{ translate('Image') }}</label>
                    <input type="hidden" name="remove_image" id="editBannerRemoveImage" value="0">
                    <div class="mt-2 mb-1" style="max-width:220px;">
                        <div class="custom_upload_input position-relative border-dashed-2">
                            <input type="file" name="image" id="editBannerImageInput"
                                class="custom-upload-input-file" data-imgpreview="editBannerImagePreview"
                                accept=".jpg, .png, .jpeg, .webp|image/*">
                            <span id="editBannerImageClear" class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                <i class="tio-delete"></i>
                            </span>
                            <div class="img_area_with_preview position-absolute z-index-2">
                                <img id="editBannerImagePreview" class="h-auto bg-white d-none cms-image-preview" alt="{{ translate('Image Preview') }}" src="">
                            </div>
                            <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                <div class="d-flex flex-column justify-content-center align-items-center">
                                    <img alt="" class="w-75" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                    <h3 class="text-muted text-capitalize">{{ translate('Upload Image') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button class="btn btn-primary" type="submit">{{ translate('Update') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" role="dialog" aria-labelledby="addBannerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.content-management.banner.store') }}" id="bannerForm" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="modal-content">

                <input type="hidden" name="section" value="{{ $currentType }}">
                <div class="modal-header">
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
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
                <div class="modal-body row">


                    <div class="tab-content col-12">
                        @foreach($languages as $lang)
                        <input type="hidden" name="lang[]" value="{{ $lang }}">

                        <div class="form-group form-system-language-form {{ $lang }}-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                            id="{{ $lang }}-form">

                            <div class="form-group">
                                <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="heading[]" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Paragraph') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="paragraph[]" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Button Text') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="buttonText[]" class="form-control">
                            </div>
                        </div>
                        @endforeach

                    </div>

                    <div class="form-group col-md-6">
                        <label>{{ translate('Button Link') }}</label>
                        <input type="text" name="buttonLink" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>{{ translate('Image') }}</label>
                        <div class="mt-2 mb-1" style="max-width:220px;">
                            <div class="custom_upload_input position-relative border-dashed-2">
                                <input type="file" name="image" id="addBannerImageInput"
                                    class="custom-upload-input-file" data-imgpreview="imagePreview"
                                    accept=".jpg, .png, .jpeg, .webp|image/*" required>
                                <span id="addBannerImageClear" class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                    <i class="tio-delete"></i>
                                </span>
                                <div class="img_area_with_preview position-absolute z-index-2">
                                    <img id="imagePreview" src="" alt="{{ translate('Image Preview') }}"
                                        class="h-auto bg-white d-none cms-image-preview">
                                </div>
                                <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <img alt="" class="w-75" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                        <h3 class="text-muted text-capitalize">{{ translate('Upload Image') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>{{ translate('Status') }}</label><br>
                        <label class="switcher">
                            <input type="checkbox" name="is_active" class="switcher_input" checked>
                            <span class="switcher_control"></span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ translate('Cancel')
                        }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Save Banner') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>



@push('script')
<script>
    function confirmAndDelete(button) {
        const url = button.dataset.url;
        const index = button.dataset.index;
        const section = button.dataset.section;

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                index: index,
                section: section
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Or use remove() to delete row instantly
                } else {
                    alert(@json(__('Delete failed!')));
                }
            },
            error: function() {
                alert(@json(__('Something went wrong.')));
            }
        });
    }

    $(document).ready(function() {
        const editButtons = document.querySelectorAll('.edit-banner-btn');
        const editModalElement = document.getElementById('editModal');
        const addModalElement = document.getElementById('addBannerModal');
        const modal = new bootstrap.Modal(editModalElement);
        const addForm = document.getElementById('bannerForm');
        const addImageInput = document.getElementById('addBannerImageInput');
        const addImagePreview = document.getElementById('imagePreview');
        const addImageClear = document.getElementById('addBannerImageClear');
        const editImageInput = document.getElementById('editBannerImageInput');
        const editImagePreview = document.getElementById('editBannerImagePreview');
        const editImageClear = document.getElementById('editBannerImageClear');
        const editRemoveImageInput = document.getElementById('editBannerRemoveImage');
        let currentEditImage = '';

        const clearFileInput = (input) => {
            if (input) {
                input.value = '';
            }
        };

        const updateAddPreview = (src) => {
            if (src) {
                addImagePreview.src = src;
                addImagePreview.classList.remove('d-none');
                addImageClear.classList.remove('d-none');
                addImageClear.classList.add('d-flex');
                return;
            }

            addImagePreview.src = '';
            addImagePreview.classList.add('d-none');
            addImageClear.classList.add('d-none');
            addImageClear.classList.remove('d-flex');
        };

        const updateEditPreview = (src) => {
            if (src) {
                editImagePreview.src = src;
                editImagePreview.classList.remove('d-none');
                editImageClear.classList.remove('d-none');
                editImageClear.classList.add('d-flex');
                return;
            }

            editImagePreview.src = '';
            editImagePreview.classList.add('d-none');
            editImageClear.classList.add('d-none');
            editImageClear.classList.remove('d-flex');
        };

        const previewFromFileInput = (input, callback) => {
            const file = input && input.files ? input.files[0] : null;
            if (!file) {
                callback('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                callback(e.target.result);
            };
            reader.readAsDataURL(file);
        };

        addImageInput.addEventListener('change', function() {
            previewFromFileInput(addImageInput, updateAddPreview);
        });

        editImageInput.addEventListener('change', function() {
            previewFromFileInput(editImageInput, function(previewUrl) {
                if (previewUrl) {
                    editRemoveImageInput.value = '0';
                    updateEditPreview(previewUrl);
                    return;
                }
                if (editRemoveImageInput.value === '1') {
                    updateEditPreview('');
                    return;
                }
                updateEditPreview(currentEditImage);
            });
        });

        addImageClear.addEventListener('click', function() {
            clearFileInput(addImageInput);
            updateAddPreview('');
        });

        editImageClear.addEventListener('click', function() {
            clearFileInput(editImageInput);
            currentEditImage = '';
            editRemoveImageInput.value = '1';
            updateEditPreview('');
        });

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                const heading = JSON.parse(this.dataset.heading || '{}');
                const paragraph = JSON.parse(this.dataset.paragraph || '{}');
                const buttonText = JSON.parse(this.dataset.buttontext || '{}');
                const buttonLink = this.dataset.buttonlink || '';
                const image = this.dataset.image || '';

                // Set index hidden field
                document.getElementById('editIndex').value = index;

                // Fill language-specific fields
                document.querySelectorAll('.lang-heading').forEach(input => {
                    const lang = input.dataset.lang;
                    input.value = heading[lang] || '';
                });

                document.querySelectorAll('.lang-paragraph').forEach(input => {
                    const lang = input.dataset.lang;
                    input.value = paragraph[lang] || '';
                });

                document.querySelectorAll('.lang-button').forEach(input => {
                    const lang = input.dataset.lang;
                    input.value = buttonText[lang] || '';
                });

                // Set button link
                document.getElementById('editButtonLink').value = buttonLink;

                // Image preview
                currentEditImage = image
                    ? (image.startsWith('http') ? image : `{{ asset('') }}` + image.replace(/^\/+/, ''))
                    : '';
                editRemoveImageInput.value = '0';
                clearFileInput(editImageInput);
                updateEditPreview(currentEditImage);

                modal.show();
            });
        });

        editModalElement.addEventListener('hidden.bs.modal', function() {
            clearFileInput(editImageInput);
            currentEditImage = '';
            editRemoveImageInput.value = '0';
            updateEditPreview('');
        });

        addModalElement.addEventListener('hidden.bs.modal', function() {
            addForm.reset();
            clearFileInput(addImageInput);
            updateAddPreview('');
        });
    });

    $(document).on('change', '.banner-toggle', function() {
        let index = $(this).data('index');
        let section = "{{ $currentType }}";

        $.post("{{ route('admin.content-management.banner.toggle-status') }}", {
            _token: '{{ csrf_token() }}',
            index: index,
            section: section
        }, function(data) {
            if (data.success) {
                toastr.success(@json(__('Status updated!')));
            }
        });
    });
</script>
@endpush


