@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
}
$cards = $jsonData['section']['cards'] ?? [];
@endphp

@php
$content = $jsonData['section'] ?? [];
@endphp
<ul class="nav nav-tabs mb-4" id="language-switcher">
    @foreach($languages as $lang)
    <li class="nav-item">
        <a class="nav-link language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
            href="javascript:void(0);" data-lang="{{ $lang }}">
            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
        </a>
    </li>
    @endforeach
</ul>

<div class="card p-4 mb-4">


    <form action="{{ route('admin.content-management.why_join_us.heading.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($languages as $lang)
        @php
        $headingTranslations = $translations[$lang]['section']['title'] ?? '';
        $subtitleTranslations = $translations[$lang]['section']['subtitle'] ?? '';
        $translatedTitle = $headingTranslations ?? '';
        $translatedSubtitle = $subtitleTranslations ?? '';
        @endphp

        <div class="language-tab-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}" data-lang="{{ $lang }}">
            <div class="row">
                <input type="hidden" name="lang[]" value="{{ $lang }}">

                <div class="col-lg-6">
                    <label class="title-color">{{ translate('main_title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? ($content['title'] ?? '') : (is_array($translatedTitle) ? ($translatedTitle[$lang] ?? '') : $translatedTitle) }}"
                        {{ $lang == $defaultLanguage ? 'required' : '' }}
                        placeholder="{{ translate('enter_title') }}">
                </div>

                <div class="col-lg-6">
                    <label class="title-color">{{ translate('main_subtitle') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="subtitle[]" class="form-control"
                        value="{{ $lang == $defaultLanguage ? ($content['subtitle'] ?? '') : (is_array($translatedSubtitle) ? ($translatedSubtitle[$lang] ?? '') : $translatedSubtitle) }}"
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



<form action="{{ route('admin.content-management.why_join_us.update') }}" method="post"
    enctype="multipart/form-data">
    @csrf
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


    @foreach($languages as $lang)
    <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
        @foreach($cards as $index => $card)
        @php
        $titleTranslations = isset($translations[$lang]['cards'][0]['title']) ? explode('|||', $translations[$lang]['cards'][0]['title']) : [];
        $descTranslations = isset($translations[$lang]['cards'][0]['description']) ? explode('|||', $translations[$lang]['cards'][0]['description']) : [];
        $altTranslations = isset($translations[$lang]['cards'][0]['image_alt']) ? explode('|||', $translations[$lang]['cards'][0]['image_alt']) : [];

        $translatedTitle = $titleTranslations[$index] ?? '';
        $translatedDescription = $descTranslations[$index] ?? '';
        $translatedAlt = $altTranslations[$index] ?? '';
        @endphp



        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Title --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('card_title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text"
                            name="cards[{{ $index }}][title][{{ $lang }}]"
                            class="form-control"
                            value="{{ $lang == $defaultLanguage ? ($card['title']) : $translatedTitle }}"
                            {{ $lang == $defaultLanguage ? 'required' : '' }}>
                    </div>

                    {{-- Image Alt --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('image_alt') }} ({{ strtoupper($lang) }})</label>
                        <input type="text"
                            name="cards[{{ $index }}][image_alt][{{ $lang }}]"
                            class="form-control"
                            value="{{ $lang == $defaultLanguage ? ($card['image_alt'] ?? '') : $translatedAlt }}">
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12">
                        <label class="form-label">{{ translate('card_description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="cards[{{ $index }}][description][{{ $lang }}]"
                            class="form-control"
                            rows="3">{{ $lang == $defaultLanguage ? ($card['description'] ?? '') : $translatedDescription }}</textarea>
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    @if($lang == $defaultLanguage)
                    {{-- Image Upload --}}
                    <input type="file"
                        name="cards[{{ $index }}][image]"
                        class="d-none"
                        id="card-image-{{ $index }}"
                        accept="image/*">

                    {{-- Existing image fallback --}}
                    <input type="hidden"
                        name="cards[{{ $index }}][existing_image]"
                        value="{{ $card['image'] ?? '' }}">
                    @endif

                    <div class="col-md-12 text-center mt-3">
                        <img src="{{ asset($card['image']) }}"
                            id="preview-{{ $index }}"
                            alt="image"
                            width="180"
                            height="120"
                            class="img-thumbnail"
                            style="cursor: pointer;"
                            onclick="document.getElementById('card-image-{{ $index }}').click()">
                    </div>
                </div>


            </div>
        </div>
        @endforeach
    </div>
    @endforeach

    <div class="d-flex justify-content-end gap-4 mt-5">
        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
    </div>


</form>

<script>
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            const index = this.id.split('-')[2];
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-' + index).src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.language-tab');
        const forms = document.querySelectorAll('.language-tab-form');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const lang = this.dataset.lang;
                tabs.forEach(t => t.classList.remove('active'));
                forms.forEach(f => f.classList.add('d-none'));
                this.classList.add('active');
                document.querySelector(`.language-tab-form[data-lang="${lang}"]`)?.classList.remove('d-none');
            });
        });
    });
</script>

