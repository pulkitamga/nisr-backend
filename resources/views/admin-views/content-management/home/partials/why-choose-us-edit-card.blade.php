@extends('layouts.back-end.app')

@section('title', translate('Edit Card'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $languages ?? [], true)) {
    $baseLanguage = $languages[0] ?? 'en';
}
$activeLanguage = $baseLanguage;
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h1 mb-0">{{ translate('Edit Card') }}</h2>
            <a href="{{ route('admin.content-management.home', ['section' => 'why_choose_us']) }}" class="btn btn-secondary">
                {{ translate('back') }}
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.content-management.why-choose.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="index" value="{{ $index }}">

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
                @php
                $translatedTitle = $translations[$lang]['title'] ?? '';
                $translatedDescription = $translations[$lang]['description'] ?? '';
                @endphp
                <input type="hidden" name="lang[]" value="{{ $lang }}">
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">
                    <div class="form-group mb-3">
                        <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control"
                            value="{{ $lang == $baseLanguage ? ($card['title'] ?? '') : $translatedTitle }}"
                            placeholder="{{ translate('enter_title') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" class="form-control summernote" rows="3">{{ $lang == $baseLanguage ? ($card['description'] ?? '') : $translatedDescription }}</textarea>
                    </div>
                </div>
                @endforeach

                <hr>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Icon Name') }}</label>
                            <input type="text" name="icon_name" class="form-control" required
                                value="{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Icon Color') }}</label>
                            <input type="text" name="icon_color" class="form-control"
                                value="{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Icon Animation') }}</label>
                            <input type="text" name="icon_animation" class="form-control"
                                value="{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.content-management.home', ['section' => 'why_choose_us']) }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                    <button type="submit" class="btn btn--primary">{{ translate('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        });
    });
</script>
@endpush
