@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
}

$isLegacyListFormat = is_array($jsonData) && array_key_exists(0, $jsonData) && is_array($jsonData[0]);
$data = $isLegacyListFormat ? [] : (is_array($jsonData) ? $jsonData : []);

$fallback = [
    'section_heading' => translate('find_perfect_match'),
    'hero_heading' => translate('find_perfect_match'),
    'hero_description' => translate('shop_by_vehicle_year_make_model'),
    'filter_title' => translate('filter_options'),
    'make_label' => translate('make'),
    'model_label' => translate('model'),
    'year_label' => translate('model_year'),
    'make_placeholder' => translate('select_make'),
    'model_placeholder' => translate('select_model'),
    'year_placeholder' => translate('select_year'),
    'apply_button_text' => translate('apply_filters'),
];
@endphp

<form action="{{ route('admin.content-management.find_perfect_match.update') }}" method="POST">
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
                            <label class="form-label">{{ translate('Section Heading') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="section_heading[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['section_heading'] ?? $fallback['section_heading']) : ($translations[$lang]['section_heading'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('Hero Heading') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="hero_heading[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['hero_heading'] ?? $fallback['hero_heading']) : ($translations[$lang]['hero_heading'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label class="form-label">{{ translate('Hero Description') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="hero_description[]" class="form-control" rows="3"
                                      {{ $lang == $defaultLanguage ? 'required' : '' }}>{{ $lang == $defaultLanguage ? ($data['hero_description'] ?? $fallback['hero_description']) : ($translations[$lang]['hero_description'] ?? '') }}</textarea>
                        </div>

                        <div class="col-lg-6 mt-3">
                            <label class="form-label">{{ translate('Filter Title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="filter_title[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['filter_title'] ?? $fallback['filter_title']) : ($translations[$lang]['filter_title'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-6 mt-3">
                            <label class="form-label">{{ translate('Apply Button Text') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="apply_button_text[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['apply_button_text'] ?? $fallback['apply_button_text']) : ($translations[$lang]['apply_button_text'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>

                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Make Label') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="make_label[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['make_label'] ?? $fallback['make_label']) : ($translations[$lang]['make_label'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Model Label') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="model_label[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['model_label'] ?? $fallback['model_label']) : ($translations[$lang]['model_label'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Model Year Label') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="year_label[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['year_label'] ?? $fallback['year_label']) : ($translations[$lang]['year_label'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>

                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Make Placeholder') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="make_placeholder[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['make_placeholder'] ?? $fallback['make_placeholder']) : ($translations[$lang]['make_placeholder'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Model Placeholder') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="model_placeholder[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['model_placeholder'] ?? $fallback['model_placeholder']) : ($translations[$lang]['model_placeholder'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                        <div class="col-lg-4 mt-3">
                            <label class="form-label">{{ translate('Model Year Placeholder') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="year_placeholder[]" class="form-control"
                                   value="{{ $lang == $defaultLanguage ? ($data['year_placeholder'] ?? $fallback['year_placeholder']) : ($translations[$lang]['year_placeholder'] ?? '') }}"
                                   {{ $lang == $defaultLanguage ? 'required' : '' }}>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row justify-content-end gap-3 mt-3 mx-1">
        <button type="reset" class="btn btn-secondary px-5">{{ translate('Reset') }}</button>
        <button type="submit" class="btn btn--primary px-5">{{ translate('Update') }}</button>
    </div>
</form>


