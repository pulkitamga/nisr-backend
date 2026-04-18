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
        ['title' => '', 'description' => '', 'label' => '', 'note' => ''],
        ['title' => '', 'description' => '', 'label' => '', 'note' => ''],
        ['title' => '', 'description' => '', 'label' => '', 'note' => ''],
        ['title' => '', 'description' => '', 'label' => '', 'note' => ''],
    ];
}
@endphp

<form action="{{ route('admin.content-management.closed_loop_lifecycle.update') }}" method="POST">
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
                            <label class="form-label">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="title[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['title'] ?? '') : ($translations[$lang]['section']['title'] ?? '') }}">
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="description[]" class="form-control" rows="3">{{ $lang === $defaultLanguage ? ($section['description'] ?? '') : ($translations[$lang]['section']['description'] ?? '') }}</textarea>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('value') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="value[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? ($section['value'] ?? '') : ($translations[$lang]['section']['value'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            @foreach($cards as $index => $card)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('closed_loop_lifecycle') }} {{ $index + 1 }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="cards[{{ $index }}][title][{{ $lang }}]" class="form-control"
                                    value="{{ $lang === $defaultLanguage ? ($card['title'] ?? '') : ($translations[$lang]['cards'][$index]['title'] ?? '') }}">
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">{{ translate('orbit_title') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="cards[{{ $index }}][label][{{ $lang }}]" class="form-control"
                                    value="{{ $lang === $defaultLanguage ? ($card['label'] ?? '') : ($translations[$lang]['cards'][$index]['label'] ?? '') }}">
                            </div>

                            <div class="col-lg-8">
                                <label class="form-label">{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="cards[{{ $index }}][description][{{ $lang }}]" class="form-control" rows="4">{{ $lang === $defaultLanguage ? ($card['description'] ?? '') : ($translations[$lang]['cards'][$index]['description'] ?? '') }}</textarea>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">{{ translate('orbit_description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="cards[{{ $index }}][note][{{ $lang }}]" class="form-control" rows="4">{{ $lang === $defaultLanguage ? ($card['note'] ?? '') : ($translations[$lang]['cards'][$index]['note'] ?? '') }}</textarea>
                            </div>
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
