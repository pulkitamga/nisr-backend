@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0]['code'] ?? 'en';

@endphp
<div class="d-flex justify-content-end my-3">
    <button class="btn btn-primary" data-toggle="modal" data-target="#addBannerModal">
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
                <td>{{ $item['heading'] ?? '' }}</td>
                <td>{{ $item['paragraph'] ?? '' }}</td>
                <td>{{ $item['buttonText'] ?? '' }}</td>
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
                    <button type="button" class="close custom-close" data-dismiss="modal" aria-label="Close">
                        &times;
                    </button>
                </div>
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="modal-body">
                    @foreach($languages as $lang)

                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">
                        <div class="form-group">

                            <label>{{ translate('Heading') }}({{ strtoupper($lang) }})</label>
                            <input name="heading[]" id="edit-heading"
                                placeholder="{{ translate('Heading') }}" class="form-control lang-heading"
                                data-lang="{{ $lang }}"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}>

                            <label>{{ translate('Paragraph') }}({{ strtoupper($lang) }})</label>
                            <textarea name="paragraph[]" id="edit-paragraph"
                                placeholder="{{ translate('Paragraph') }}" class="form-control lang-paragraph"
                                data-lang="{{ $lang }}"
                                rows="3"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>

                            <label>{{ translate('Button Text') }}({{ strtoupper($lang) }})</label>
                            <input name="buttonText[]" id="edit-buttonText"
                                placeholder="{{ translate('Button Text') }}" class="form-control lang-button"
                                data-lang="{{ $lang }}"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                    </div>
                    @endforeach
                    <label>{{ translate('Button Link') }}</label>
                    <input name="buttonLink" id="editButtonLink" class="form-control mb-2"
                        placeholder="{{ translate('Button Link') }}">

                    <label>{{ translate('Image') }}</label>
                    <input type="file" name="image" class="form-control mb-2">
                    <img src="" width="150" class="img-thumbnail mb-2">
                </div>
                <div class="modal-footer">
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
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

                        <div class="form-group form-system-language-form {{ $lang }}-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                            id="{{ $lang }}-form">

                            <div class="form-group">
                                <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="heading[]" class="form-control" {{ $lang == $defaultLanguage ? 'required' : '' }}>
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Paragraph') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="paragraph[]" class="form-control" rows="3" {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Button Text') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="buttonText[]" class="form-control" {{ $lang == $defaultLanguage ? 'required' : '' }}>
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
                        <input type="file" name="image" class="form-control-file" onchange="previewImage(this)">
                        <img id="imagePreview" src="#" alt="{{ translate('Image Preview') }}" class="mt-2"
                            style="display:none; max-width: 150px;">
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel')
                        }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Save Banner') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    function previewImage(input) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }

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
                    alert('Delete failed!');
                }
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    }



    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-banner-btn');
        const modal = new bootstrap.Modal(document.getElementById('editModal'));

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                const heading = JSON.parse(this.dataset.heading || '{}');
                const paragraph = JSON.parse(this.dataset.paragraph || '{}');
                const buttonText = JSON.parse(this.dataset.buttontext || '{}');
                const buttonLink = this.dataset.buttonlink || '';
                const image = this.dataset.image || '';
                const isActive = this.dataset.status === '1';

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
                const preview = document.querySelector('#editModal img.img-thumbnail');
                if (image) {
                    preview.src = image.startsWith('http') ? image : `{{ asset('') }}` + image;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }



                modal.show();
            });
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
                toastr.success("Status updated!");
            }
        });
    });
</script>