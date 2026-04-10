@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages, true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}

$activeLanguage = $errors->any() ? $defaultLanguage : getDefaultLanguage();
$activeLanguage = in_array($activeLanguage, $languages, true) ? $activeLanguage : $defaultLanguage;

$section = $jsonData['section'] ?? [];
$previewFallback = dynamicAsset(path: 'public/assets/back-end/img/400x400/img3.png');
$previewImage = blank($section['image'] ?? null) ? $previewFallback : asset(ltrim($section['image'], '/'));
@endphp

<form action="{{ route('admin.content-management.next_step.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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

    @foreach($languages as $lang)
        <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
            <input type="hidden" name="lang[]" value="{{ $lang }}">

            <div class="card border shadow-none mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('label') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="label[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['label'] ?? '') : ($translations[$lang]['section']['label'] ?? '') }}">
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="title[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['title'] ?? '') : ($translations[$lang]['section']['title'] ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="description[]" class="form-control" rows="3">{{ $lang === $defaultLanguage ? ($section['description'] ?? '') : ($translations[$lang]['section']['description'] ?? '') }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('primary_button_text') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="button_text[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['button_text'] ?? '') : ($translations[$lang]['section']['button_text'] ?? '') }}">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('secondary_button_text') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="note[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['note'] ?? '') : ($translations[$lang]['section']['note'] ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ translate('image_alt') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="image_alt[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['image_alt'] ?? '') : ($translations[$lang]['section']['image_alt'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-start">
                <div class="col-lg-4">
                    <label class="form-label d-block">{{ translate('image') }}</label>
                    <input type="hidden" name="existing_image" value="{{ $section['image'] ?? '' }}">
                    <input type="file" class="d-none" id="next-step-image" name="image" accept="image/*">
                    <img src="{{ $previewImage }}"
                        id="next-step-image-preview"
                        alt="{{ $section['image_alt'] ?? 'next step preview' }}"
                        class="img-thumbnail cms-image-preview"
                        width="240"
                        height="150"
                        style="cursor:pointer;"
                        onclick="document.getElementById('next-step-image').click()">
                </div>

                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('primary_button_link') }}</label>
                            <input type="text" name="button_link" class="form-control"
                                value="{{ $section['button_link'] ?? route('contacts') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ translate('secondary_button_link') }}</label>
                            <input type="text" name="secondary_button_link" class="form-control"
                                value="{{ $section['secondary_button_link'] ?? route('warranty.track.page') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-3 mt-4">
        <button type="reset" class="btn btn-secondary px-4">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary px-4">{{ translate('update') }}</button>
    </div>
</form>

<script>
    document.getElementById('next-step-image')?.addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('next-step-image-preview');

        if (!file || !preview) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>
