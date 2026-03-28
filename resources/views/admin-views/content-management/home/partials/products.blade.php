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

@php($activeLanguage = $errors->any() ? $defaultLanguage : (in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage))
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

@foreach($jsonData as $index => $item)


@php
$headingTranslations = $translations[$lang]['section_title'] ?? [];
$paragraphTranslations = $translations[$lang]['section_paragraph'] ?? [];
$translatedHeading = $headingTranslations ?? '';
$translatedParagraph = $paragraphTranslations ?? '';
@endphp
<form action="{{ route('admin.content-management.Products.update', ['index' => $index]) }}" method="POST">
    @csrf
    @method('PUT')
    @foreach($languages as $lang)
    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
        id="{{ $lang }}-form">

        <div class="row">
            <input type="hidden" name="lang[]" value="{{ $lang }}">

            <div class="col-lg-6">
                <label class="title-color">{{ translate('heading') }}({{ strtoupper($lang) }})</label>
                <input type="text" name="section_title[]" class="form-control"  value="{{ $lang == $defaultLanguage ? $item['section_title'] : (is_array($translatedHeading) ? ($translatedHeading[$lang] ?? '') : $translatedHeading) }}"
                    placeholder="{{ translate('enter_heading') }}">
            </div>

            <div class="col-lg-6">
                <label class="title-color">{{ translate('sub_heading') }}({{ strtoupper($lang) }})</label>
                <input type="text" name="section_paragraph[]" class="form-control "                     value="{{ $lang == $defaultLanguage ? $item['section_paragraph'] : (is_array($translatedParagraph) ? ($translatedParagraph[$lang] ?? '') : $translatedParagraph) }}"

                    placeholder="{{ translate('enter_paragraph') }}">
            </div>
        </div>

    </div>
    @endforeach

    <div>
        @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
        <label class="title-color d-flex justify-content-center">{{ translate('this_page_is_only_off_and_on_core_product_page_in_home_page_you_can_add_show_product_by_clicking_on_toggle_in_product_list') }}</label>

    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
    </div>
</form>
@endforeach
