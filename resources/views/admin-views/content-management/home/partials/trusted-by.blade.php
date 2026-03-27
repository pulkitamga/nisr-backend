@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
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

@foreach($jsonData as $index => $item)
<form action="{{ route('admin.content-management.trusted_by.update', ['index' => $index]) }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($languages as $lang)
    @php
    $headingTranslations = $translations[$lang]['heading'] ?? [];
    $paragraphTranslations = $translations[$lang]['paragraph'] ?? [];
    $translatedHeading = $headingTranslations ?? '';
    $translatedParagraph = $paragraphTranslations ?? '';
    @endphp

    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
        id="{{ $lang }}-form">
        <div class="row">
            <input type="hidden" name="lang[]" value="{{ $lang }}">
            <div class="col-lg-6">
                <label class="title-color d-flex">{{ translate('heading') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="heading[]" class="form-control"
                    value="{{ $lang == $defaultLanguage ? $item['heading'] : (is_array($translatedHeading) ? ($translatedHeading[$lang] ?? '') : $translatedHeading) }}"
                    {{ $lang == $defaultLanguage ? 'required' : '' }}
                    placeholder="{{ translate('enter_heading') }}">
            </div>

            <div class="col-lg-6">
                <label class="title-color d-flex">{{ translate('paragraph') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="paragraph[]" class="form-control"
                    value="{{ $lang == $defaultLanguage ? $item['paragraph'] : (is_array($translatedParagraph) ? ($translatedParagraph[$lang] ?? '') : $translatedParagraph) }}"
                    {{ $lang == $defaultLanguage ? 'required' : '' }}
                    placeholder="{{ translate('enter_paragraph') }}">
            </div>
        </div>
    </div>
    @endforeach

    <div class="row">
        <div class="col-lg-6">
            <label class="title-color d-flex">{{ translate('year') }}</label>
            <input type="text" name="year" class="form-control" value="{{ $item['year'] }}"
                placeholder="{{ translate('enter_year') }}">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
    </div>
</form>
@endforeach

@push('script')
<script>
    $(document).ready(function() {
        $('.form-system-language-tab').on('click', function() {
            let lang = $(this).attr('id').replace('-link', '');
            $('.form-system-language-tab').removeClass('active');
            $('.form-system-language-form').addClass('d-none');
            $(this).addClass('active');
            $('#' + lang + '-form').removeClass('d-none');
        });
    });
</script>
@endpush
