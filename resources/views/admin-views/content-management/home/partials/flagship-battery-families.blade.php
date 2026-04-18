@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages, true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}

$activeLanguage = $errors->any() ? $defaultLanguage : getDefaultLanguage();
$activeLanguage = in_array($activeLanguage, $languages, true) ? $activeLanguage : $defaultLanguage;

$section = $jsonData['section'] ?? [];
$cards = $section['cards'] ?? [];
if ($cards === []) {
    $cards = [
        ['tag' => '', 'title' => '', 'description' => '', 'note' => '', 'image' => '', 'image_alt' => '', 'redirect_link' => ''],
        ['tag' => '', 'title' => '', 'description' => '', 'note' => '', 'image' => '', 'image_alt' => '', 'redirect_link' => ''],
        ['tag' => '', 'title' => '', 'description' => '', 'note' => '', 'image' => '', 'image_alt' => '', 'redirect_link' => ''],
    ];
}

$previewFallback = dynamicAsset(path: 'public/assets/back-end/img/400x400/img3.png');
$resolvePreview = function (?string $path) use ($previewFallback) {
    if (blank($path)) {
        return $previewFallback;
    }

    if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
        return $path;
    }

    return asset(ltrim($path, '/'));
};
@endphp

<form action="{{ route('admin.content-management.flagship_battery_families.update') }}" method="POST" enctype="multipart/form-data">
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

    @foreach($languages as $langIndex => $lang)
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
                            <label class="form-label">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="title[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['title'] ?? '') : ($translations[$lang]['section']['title'] ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="description[]" class="form-control" rows="2">{{ $lang === $defaultLanguage ? ($section['description'] ?? '') : ($translations[$lang]['section']['description'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($cards as $index => $card)
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ translate('card_title') }} {{ $index + 1 }}</h5>
                        <span class="badge badge-soft-secondary">{{ translate('flagship_battery_families') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">{{ translate('tag') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="cards[{{ $index }}][tag][{{ $lang }}]" class="form-control"
                                    value="{{ $lang === $defaultLanguage ? ($card['tag'] ?? '') : ($translations[$lang]['cards'][$index]['tag'] ?? '') }}">
                            </div>

                            <div class="col-lg-8">
                                <label class="form-label">{{ translate('card_title') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="cards[{{ $index }}][title][{{ $lang }}]" class="form-control"
                                    value="{{ $lang === $defaultLanguage ? ($card['title'] ?? '') : ($translations[$lang]['cards'][$index]['title'] ?? '') }}">
                            </div>

                            <div class="col-lg-8">
                                <label class="form-label">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="cards[{{ $index }}][description][{{ $lang }}]" class="form-control" rows="4">{{ $lang === $defaultLanguage ? ($card['description'] ?? '') : ($translations[$lang]['cards'][$index]['description'] ?? '') }}</textarea>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ translate('card_note') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="cards[{{ $index }}][note][{{ $lang }}]" class="form-control" rows="4">{{ $lang === $defaultLanguage ? ($card['note'] ?? '') : ($translations[$lang]['cards'][$index]['note'] ?? '') }}</textarea>
                            </div>

                            <div class="col-lg-8">
                                <label class="form-label">{{ translate('image_alt') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="cards[{{ $index }}][image_alt][{{ $lang }}]" class="form-control"
                                    value="{{ $lang === $defaultLanguage ? ($card['image_alt'] ?? '') : ($translations[$lang]['cards'][$index]['image_alt'] ?? '') }}">
                            </div>

                            @if($lang === $defaultLanguage)
                                <div class="col-lg-4">
                                    <label class="form-label d-block">{{ translate('Image') }}</label>
                                    <input type="hidden" name="cards[{{ $index }}][existing_image]" value="{{ $card['image'] ?? '' }}">
                                    <input type="file" class="d-none js-family-image-input" id="family-image-{{ $index }}"
                                        name="cards[{{ $index }}][image]" accept="image/*" data-preview="#family-image-preview-{{ $index }}">
                                    <img src="{{ $resolvePreview($card['image'] ?? '') }}"
                                        id="family-image-preview-{{ $index }}"
                                        alt="{{ $card['image_alt'] ?? 'family preview' }}"
                                        class="img-thumbnail cms-image-preview"
                                        width="220"
                                        height="140"
                                        style="cursor:pointer;"
                                        onclick="document.getElementById('family-image-{{ $index }}').click()">
                                </div>

                                <div class="col-lg-8">
                                    <label class="form-label">{{ translate('redirect_link') }}</label>
                                    <input type="text" name="cards[{{ $index }}][redirect_link]" class="form-control"
                                        value="{{ $card['redirect_link'] ?? '' }}"
                                        placeholder="{{ translate('Enter_URL') }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="d-flex justify-content-end gap-3 mt-4">
        <button type="reset" class="btn btn-secondary px-4">{{ translate('Reset') }}</button>
        <button type="submit" class="btn btn--primary px-4">{{ translate('Update') }}</button>
    </div>
</form>

<script>
    document.querySelectorAll('.js-family-image-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const previewSelector = this.dataset.preview;
            const preview = document.querySelector(previewSelector);
            const file = this.files[0];

            if (!preview || !file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
</script>
