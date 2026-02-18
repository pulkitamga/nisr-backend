@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0]['code'] ?? 'en';

@endphp

@php
$content = $jsonData['section'] ?? [];
@endphp

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
<div class="card p-4 mb-4">
    <form action="{{ route('admin.content-management.why-choose.heading.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($languages as $lang)
        @php
        $headingTranslations = $translations[$lang]['section']['title'] ?? '';
        $subtitleTranslations =$translations[$lang]['section']['subtitle'] ?? '';
        $translatedTitle = $headingTranslations ?? '';
        $translatedSubtitle = $subtitleTranslations ?? '';
        @endphp

        <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
            id="{{ $lang }}-form">
            <div class="row">
                <input type="hidden" name="lang[]" value="{{ $lang }}">
                <div class="col-lg-6">
                    <label class="title-color">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $content['title'] : (is_array($translatedTitle) ? ($translatedTitle[$lang] ?? '') : $translatedTitle) }}"
                        {{ $lang == $defaultLanguage ? 'required' : '' }}
                        placeholder="{{ translate('enter_title') }}">
                </div>
                <div class="col-lg-6">
                    <label class="title-color">{{ translate('subtitle') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="subtitle[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? $content['subtitle'] : (is_array($translatedSubtitle) ? ($translatedSubtitle[$lang] ?? '') : $translatedSubtitle) }}"
                        {{ $lang == $defaultLanguage ? 'required' : '' }}
                        placeholder="{{ translate('enter_subtitle') }}">
                </div>

            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
            <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
        </div>
    </form>
</div>

<div class="card">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{translate('Title')}}</th>
                <th>{{translate('Description')}}</th>
                <th>{{translate('Icon')}}</th>
                <th>{{translate('Color')}}</th>
                <th>{{translate('Animation')}}</th>
                <th>{{translate('Action')}}</th>
            </tr>
        </thead>
        <tbody>
           @foreach($jsonData['section']['cards'] ?? [] as $index => $card)

    @php
        $titleLangMap = [];
        $descLangMap = [];

        foreach ($languages as $lang) {
            $titleLangMap[$lang] = $translations[$lang]['cards'][$index]['title'] ?? '';
            $descLangMap[$lang] = $translations[$lang]['cards'][$index]['description'] ?? '';
        }

        // Default language fallback from card JSON
        $titleLangMap[$defaultLanguage] = $card['title'] ?? '';
        $descLangMap[$defaultLanguage] = $card['description'] ?? '';
    @endphp

    <tr>
        <td>{{ $card['title'] }}</td>
        <td>{{ $card['description'] }}</td>
        <td>{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}</td>
        <td>{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}</td>
        <td>{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? '') }}</td>

        <td class="text-center d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm square-btn btn-edit-card"
                data-index="{{ $index }}"
                data-title='@json($titleLangMap)'
                data-description='@json($descLangMap)'
                data-icon-name="{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}"
                data-icon-color="{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}"
                data-icon-animation="{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? '') }}">
                <i class="tio-edit"></i>
            </button>
        </td>
    </tr>
@endforeach

        </tbody>
    </table>
</div>
<!-- Edit Card Modal -->
<div class="modal fade" id="editCardModal" tabindex="-1" aria-labelledby="editCardLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content"> <!-- This was missing -->
            <form id="editCardForm" method="POST" action="{{ route('admin.content-management.why-choose.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="index" id="edit-card-index">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link edit-modal-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="edit-{{ $lang }}-link"
                            data-target="#edit-{{ $lang }}-form">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="modal-body">
                    @foreach($languages as $lang)
                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                    <div class="form-group edit-modal-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                        id="edit-{{ $lang }}-form">
                        <div class="form-group">
                            <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text"
                                name="title[]"
                                class="form-control lang-title"
                                data-lang="{{ $lang }}"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}>

                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="description[]"
                                class="form-control lang-description"
                                data-lang="{{ $lang }}"
                                rows="3"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                        </div>
                    </div>
                    @endforeach


                    <div class="mb-3">
                        <label class="form-label">{{translate('Icon Name')}}</label>
                        <input type="text" name="icon_name" id="edit-card-icon-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{translate('Icon Color')}}</label>
                        <input type="text" name="icon_color" id="edit-card-icon-color" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{translate('Icon Animation')}}</label>
                        <input type="text" name="icon_animation" id="edit-card-icon-animation" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{translate('Save changes')}}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editCardModal = new bootstrap.Modal(document.getElementById('editCardModal'));
        const editButtons = document.querySelectorAll('.btn-edit-card');

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // set static values
                document.getElementById('edit-card-index').value = this.dataset.index;
                document.getElementById('edit-card-icon-name').value = this.dataset.iconName;
                document.getElementById('edit-card-icon-color').value = this.dataset.iconColor;
                document.getElementById('edit-card-icon-animation').value = this.dataset.iconAnimation;

                const titles = JSON.parse(this.dataset.title || '{}');
                const descriptions = JSON.parse(this.dataset.description || '{}');

                console.log("Parsed Titles:", titles);
                console.log("Parsed Descriptions:", descriptions);

                document.querySelectorAll('.lang-title').forEach(function(input) {
                    const lang = input.dataset.lang;
                    input.value = titles[lang] || '';
                });

                document.querySelectorAll('.lang-description').forEach(function(textarea) {
                    const lang = textarea.dataset.lang;
                    textarea.value = descriptions[lang] || '';
                });

                editCardModal.show();
            });
        });
    });

      $(document).ready(function () {
        $('#editCardModal').on('click', '.edit-modal-language-tab', function () {
            var $this = $(this);
            var targetForm = $this.data('target');

            // Toggle active tab
            $('#editCardModal .edit-modal-language-tab').removeClass('active');
            $this.addClass('active');

            // Hide all language forms
            $('#editCardModal .edit-modal-language-form').addClass('d-none');

            // Show selected language form
            $('#editCardModal ' + targetForm).removeClass('d-none');
        });
    });
</script>