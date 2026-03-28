@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
}
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



@php
$headingTranslations = $translations[$lang]['heading'] ?? [];
$paragraphTranslations = $translations[$lang]['paragraph'] ?? [];
$translatedHeading = $headingTranslations ?? '';
$translatedParagraph = $paragraphTranslations ?? '';
@endphp
<form action="{{ route('admin.content-management.category.update') }}" method="POST">
    @csrf
    @method('PUT')
    @foreach($languages as $lang)
    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
        id="{{ $lang }}-form">

        <div class="row">
            <input type="hidden" name="lang[]" value="{{ $lang }}">

            <div class="col-lg-6">
                <label class="title-color">{{ translate('heading') }}({{ strtoupper($lang) }})</label>
                <input type="text" name="heading[]" class="form-control" value="{{ $lang == $defaultLanguage ? $jsonData['heading'] : (is_array($translatedHeading) ? ($translatedHeading[$lang] ?? '') : $translatedHeading) }}"
                    placeholder="{{ translate('enter_heading') }}">
            </div>

            <div class="col-lg-6">
                <label class="title-color">{{ translate('sub_heading') }}({{ strtoupper($lang) }})</label>
                <input type="text" name="paragraph[]" class="form-control " value="{{ $lang == $defaultLanguage ? $jsonData['paragraph'] : (is_array($translatedParagraph) ? ($translatedParagraph[$lang] ?? '') : $translatedParagraph) }}"

                    placeholder="{{ translate('enter_paragraph') }}">
            </div>
        </div>

    </div>
    @endforeach
    <div>
        @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
        <label class="title-color d-flex justify-content-center">{{ translate('this_page_is_only_off_and_on_deals_page_in_home_page_you_can_add_deals_in_offer_&_deals_section')  }}</label>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
    </div>
</form>


