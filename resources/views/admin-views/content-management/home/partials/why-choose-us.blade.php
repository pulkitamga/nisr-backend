@php
$languages = getWebConfig(name: 'pnc_language') ?? [];
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $languages, true)) {
    $baseLanguage = $languages[0] ?? 'en';
}
$activeLanguage = $errors->any() ? $baseLanguage : getDefaultLanguage();
$activeLanguage = in_array($activeLanguage, $languages, true) ? $activeLanguage : $baseLanguage;

@endphp

@php
$content = $jsonData['section'] ?? [];
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

        <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
            id="{{ $lang }}-form">
            <div class="row">
                <input type="hidden" name="lang[]" value="{{ $lang }}">
                <div class="col-lg-6">
                    <label class="title-color">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="title[]" class="form-control"
                        value="{{ $lang == $baseLanguage ? ($content['title'] ?? '') : (is_array($translatedTitle) ? ($translatedTitle[$lang] ?? '') : $translatedTitle) }}"
                        placeholder="{{ translate('enter_title') }}">
                </div>
                <div class="col-lg-6">
                    <label class="title-color">{{ translate('subtitle') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="subtitle[]" class="form-control"
                        value="{{ $lang == $baseLanguage ? ($content['subtitle'] ?? '') : (is_array($translatedSubtitle) ? ($translatedSubtitle[$lang] ?? '') : $translatedSubtitle) }}"
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
        $titleLangMap[$baseLanguage] = $card['title'] ?? '';
        $descLangMap[$baseLanguage] = $card['description'] ?? '';
        $displayTitle = $titleLangMap[$activeLanguage] ?: ($titleLangMap[$baseLanguage] ?? '');
        $displayDescription = $descLangMap[$activeLanguage] ?: ($descLangMap[$baseLanguage] ?? '');
    @endphp

    <tr>
        <td>{{ $displayTitle }}</td>
        <td>{{ $displayDescription }}</td>
        <td>{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}</td>
        <td>{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}</td>
        <td>{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? '') }}</td>

        <td class="text-center d-flex gap-2">
            <a href="{{ route('admin.content-management.why-choose.card.edit', ['index' => $index]) }}"
                class="btn btn-outline-primary btn-sm square-btn">
                <i class="tio-edit"></i>
            </a>
        </td>
    </tr>
@endforeach

        </tbody>
    </table>
</div>
