@extends('layouts.back-end.app')

@section('title', translate('service_page_sections'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
    <style>
        body.cms-admin-theme #content .services-cms-page .cms-admin-heading {
            align-items: flex-start;
        }

        body.cms-admin-theme #content .services-cms-page .cms-admin-heading__note {
            margin: .35rem 0 0;
            max-width: 68ch;
            color: #60748a;
            line-height: 1.7;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-admin-shell {
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(24rem, 1fr));
            gap: 1.25rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel {
            border-radius: 1.25rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__header > div:first-child {
            min-width: 0;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__title {
            margin: 0;
            color: #1e3250;
            font-size: 1.06rem;
            font-weight: 700;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__note {
            margin: .35rem 0 0;
            color: #60748a;
            line-height: 1.65;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__body {
            display: grid;
            gap: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-card {
            display: grid;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e2ebf4;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-card__title {
            margin: 0;
            color: #1e3250;
            font-size: 1rem;
            font-weight: 700;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-card__note {
            margin: .25rem 0 0;
            color: #60748a;
            line-height: 1.6;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__body > form,
        body.cms-admin-theme #content .services-cms-page .catalogue-card > form,
        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__content,
        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__media {
            display: grid;
            gap: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-locale-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem;
            margin-bottom: .25rem;
            justify-content: flex-start;
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-locale-tab {
            display: inline-flex;
            flex: 0 0 auto;
            align-self: flex-start;
            width: auto;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            padding: .55rem 1rem;
            border: 1px solid #dbe6f3;
            border-radius: 999px;
            background: #eef5fb;
            color: #60748a;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            text-decoration: none;
            transition: all .2s ease;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-locale-tab:hover {
            color: #1455ac;
            border-color: #1455ac;
            text-decoration: none;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-locale-tab.active {
            color: #1455ac;
            border-color: #1455ac;
            background: #fff;
            box-shadow: 0 10px 20px rgba(20, 85, 172, 0.08);
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-card {
            display: grid;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e2ebf4;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-card__title {
            margin: 0;
            color: #1e3250;
            font-size: 1rem;
            font-weight: 700;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-card__note {
            margin: .25rem 0 0;
            color: #60748a;
            line-height: 1.6;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-locale-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: .65rem;
            margin-bottom: .25rem;
            justify-content: flex-start;
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-locale-tab {
            display: inline-flex;
            flex: 0 0 auto;
            align-self: flex-start;
            width: auto;
            align-items: center;
            justify-content: center;
            min-height: 2.3rem;
            padding: .45rem .9rem;
            border: 1px solid #dbe6f3;
            border-radius: 999px;
            background: #eef5fb;
            color: #60748a;
            font-size: .92rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            text-decoration: none;
            transition: all .2s ease;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-locale-tab:hover {
            color: #1455ac;
            border-color: #1455ac;
            text-decoration: none;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-locale-tab.active {
            color: #1455ac;
            border-color: #1455ac;
            background: #fff;
            box-shadow: 0 10px 20px rgba(20, 85, 172, 0.08);
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-image-preview {
            position: relative;
            display: flex;
            width: 100%;
            max-width: 20rem;
            margin-top: .5rem;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid #dfe9f3;
            border-radius: 1rem;
            background: #f8fbff;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-image-preview--wide {
            max-width: 27rem;
            aspect-ratio: 16 / 9;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 0;
            border-radius: 0;
            background: #f8fbff;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-image-remove {
            position: absolute;
            top: .65rem;
            inset-inline-end: .65rem;
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: rgba(220, 53, 69, .92);
            color: #fff;
            line-height: 1;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-image-preview {
            position: relative;
            display: flex;
            width: 100%;
            max-width: 20rem;
            margin-top: .5rem;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid #dfe9f3;
            border-radius: 1rem;
            background: #f8fbff;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-image-preview--wide {
            max-width: 27rem;
            aspect-ratio: 16 / 9;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 0;
            border-radius: 0;
            background: #f8fbff;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-image-remove {
            position: absolute;
            top: .65rem;
            inset-inline-end: .65rem;
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: rgba(220, 53, 69, .92);
            color: #fff;
            line-height: 1;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-status-chip,
        body.cms-admin-theme #content .services-cms-page .catalogue-type-chip {
            display: inline-flex;
            align-items: center;
            min-height: 2rem;
            padding: .35rem .72rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-status-chip {
            background: rgba(20, 85, 172, 0.08);
            color: #1455ac;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-status-chip--muted {
            background: #eef3f7;
            color: #60748a;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-type-chip {
            background: #eef5fb;
            color: #48627f;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-list,
        body.cms-admin-theme #content .services-cms-page .catalogue-support-stack {
            display: grid;
            gap: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item {
            display: grid;
            grid-template-columns: minmax(15rem, 18rem) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
            padding: 1rem;
            border: 1px solid #e2ebf4;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item > div:first-child {
            width: 100%;
            max-width: 18rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item > div:last-child {
            display: grid;
            gap: .75rem;
            align-content: start;
            min-width: 0;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__media,
        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__empty {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            min-height: 12rem;
            height: auto;
            border-radius: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__media {
            object-fit: cover;
            border: 1px solid #dfe9f3;
            background: #f8fbff;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__empty {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #c7d8ea;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            color: #60748a;
            text-align: center;
            padding: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__title {
            margin: 0;
            color: #1e3250;
            font-size: 1.12rem;
            line-height: 1.3;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item__copy {
            margin: 0;
            color: #60748a;
            line-height: 1.7;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            align-items: center;
            justify-content: flex-start;
            margin-top: 0;
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-actions .btn {
            min-height: 2.7rem;
            padding-inline: 1rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-actions form {
            margin: 0;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-empty {
            padding: 1.1rem 1.2rem;
            border: 1px dashed #c7d8ea;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            color: #60748a;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-support-form {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(18rem, .9fr);
            gap: 1rem;
            align-items: start;
            padding: 1rem;
            border: 1px solid #e2ebf4;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__head {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            align-items: start;
            justify-content: space-between;
            gap: 1rem;
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__content,
        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__media {
            min-width: 0;
            padding: 1rem;
            border: 1px solid #e8eef5;
            border-radius: 1rem;
            background: #f9fbff;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__media {
            align-content: start;
            justify-items: start;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-support-form__preview img {
            height: 100%;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-toggle-wrap {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            margin-inline-start: auto;
            flex-shrink: 0;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-switch-label {
            margin-inline-start: .45rem;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-panel__tools {
            margin-inline-start: auto;
            flex-shrink: 0;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-meta-row,
        body.cms-admin-theme #content .services-cms-page .catalogue-switch-field {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
            direction: inherit;
        }

        body.cms-admin-theme #content .services-cms-page .catalogue-switch-field {
            justify-content: flex-start;
            min-height: 100%;
        }

        body.cms-admin-theme #content .services-cms-page .note-editor.note-frame {
            border-color: #d7e3ef;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
        }

        body.cms-admin-theme #content .services-cms-page .note-editor .note-toolbar {
            background: #f7fbff;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-dialog {
            max-width: min(1140px, calc(100vw - 2rem));
            height: calc(100vh - 2rem);
            margin-block: 1rem;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            max-height: 100%;
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-content > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-header {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-shrink: 0;
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1.25rem;
            padding-bottom: 1.75rem;
            background: #f7faff;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: #bfd0e3 #eef5fb;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-body::-webkit-scrollbar {
            width: .75rem;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-body::-webkit-scrollbar-track {
            background: #eef5fb;
            border-radius: 999px;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-body::-webkit-scrollbar-thumb {
            background: #bfd0e3;
            border-radius: 999px;
            border: 2px solid #eef5fb;
        }

        body.cms-admin-theme #content .catalogue-modal .modal-footer {
            position: sticky;
            bottom: 0;
            z-index: 2;
            display: flex;
            justify-content: flex-end;
            gap: .65rem;
            background: #fff;
            flex-shrink: 0;
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal.modal .modal-dialog-scrollable .modal-body {
            max-height: none;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-switch-field {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
            justify-content: flex-start;
            min-height: 100%;
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal .catalogue-switch-label {
            margin-inline-start: .45rem;
        }

        body.cms-admin-theme #content .catalogue-modal__layout {
            direction: inherit;
        }

        body.cms-admin-theme #content .catalogue-modal__head {
            min-width: 0;
        }

        body.cms-admin-theme #content .catalogue-modal__close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.3rem;
            height: 2.3rem;
            border-radius: 50%;
            border: 1px solid #d6e1ee;
            background: #fff;
            color: #60748a;
            line-height: 1;
        }

        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-panel__header,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-card,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-showcase-item,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-support-form,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-support-form__content,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-support-form__media,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-meta-row,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-switch-field,
        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-card,
        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-meta-row,
        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-switch-field {
            direction: rtl;
            text-align: right;
        }

        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-toggle-wrap,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-panel__tools,
        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-actions,
        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-actions,
        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-panel__tools {
            justify-content: flex-end;
        }

        body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-toggle-wrap {
            margin-inline-start: 0;
            margin-inline-end: auto;
        }

        body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-modal__layout {
            flex-direction: row-reverse;
        }

        @media (max-width: 991.98px) {
            body.cms-admin-theme #content .services-cms-page .catalogue-grid-2,
            body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item,
            body.cms-admin-theme #content .services-cms-page .catalogue-support-form,
            body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-showcase-item,
            body.cms-admin-theme #content .services-cms-page[dir="rtl"] .catalogue-support-form,
            body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-showcase-item,
            body.cms-admin-theme #content .catalogue-modal[dir="rtl"] .catalogue-support-form {
                grid-template-columns: 1fr;
            }

            body.cms-admin-theme #content .services-cms-page .catalogue-showcase-item > div:first-child,
            body.cms-admin-theme #content .services-cms-page .catalogue-image-preview,
            body.cms-admin-theme #content .services-cms-page .catalogue-image-preview--wide {
                max-width: 100%;
            }

            body.cms-admin-theme #content .catalogue-modal .modal-content {
                max-height: 100%;
            }
        }
    </style>
@endpush

@php
    $language = getWebConfig(name: 'pnc_language') ?? [];
    if (empty($language)) {
        $language = ['en'];
    }

    $direction = function_exists('get_direction') ? get_direction() : 'ltr';

    $defaultLanguage = getConfiguredDefaultLanguage();
    if (!in_array($defaultLanguage, $language, true)) {
        $defaultLanguage = $language[0];
    }

    $mapTranslations = static function ($model) {
        $mapped = [];
        foreach ($model?->translations ?? [] as $translation) {
            $mapped[$translation->locale][$translation->key] = $translation->value;
        }
        return $mapped;
    };

    $showcaseTypeLabels = [
        'product' => translate('showcase_type_product'),
        'category' => translate('showcase_type_category'),
        'case' => translate('showcase_type_case'),
        'problem' => translate('showcase_type_problem'),
        'situation' => translate('showcase_type_situation'),
    ];

    $supportTitles = [
        'request_card_1' => translate('support_card_1'),
        'request_card_2' => translate('support_card_2'),
        'request_card_3' => translate('support_card_3'),
    ];

    $heroSlides = $heroSection?->showcaseItems ?? collect();
    $showcaseItems = $showcaseSection?->showcaseItems ?? collect();
@endphp

@section('content')
    <div class="content container-fluid services-cms-page" dir="{{ $direction }}">
        <div class="catalogue-admin-shell">
            <div class="cms-admin-heading">
                <div>
                    <h1 class="cms-admin-heading__title h3">{{ translate('service_page_sections') }}</h1>
                    <p class="cms-admin-heading__note mb-0">{{ translate('Manage_the_services_page_header_hero_showcase_slider_and_support_cards_from_one_screen') }}</p>
                </div>
            </div>

            <div class="catalogue-grid-2">
                @if($headerSection)
                    @php
                        $headerTranslations = $mapTranslations($headerSection);
                    @endphp
                    <section class="card catalogue-panel">
                        <div class="card-header catalogue-panel__header">
                            <div>
                                <h2 class="catalogue-panel__title">{{ translate('page_header') }}</h2>
                                <p class="catalogue-panel__note">{{ translate('Controls_the_page_header_content_shown_at_the_top_of_the_services_page') }}</p>
                            </div>
                            <div class="catalogue-toggle-wrap">
                                <span class="{{ $headerSection->is_active ? 'catalogue-status-chip' : 'catalogue-status-chip catalogue-status-chip--muted' }}">
                                    {{ $headerSection->is_active ? translate('active') : translate('inactive') }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body catalogue-panel__body">
                            <form action="{{ route('admin.content-management.services.update', ['id' => $headerSection->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="catalogue-locale-tabs" data-locale-scope="header-section">
                                    @foreach($language as $lang)
                                        <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="header-section">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                    @endforeach
                                </div>

                                @foreach($language as $lang)
                                    <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="header-section">
                                        <div class="form-group">
                                            <label>{{ translate('eyebrow') }} ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="button_text[]" class="form-control" value="{{ $lang === $defaultLanguage ? ($headerSection->button_text ?? '') : ($headerTranslations[$lang]['button_text'] ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="heading[]" class="form-control" value="{{ $lang === $defaultLanguage ? $headerSection->heading : ($headerTranslations[$lang]['heading'] ?? '') }}">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                            <textarea name="description[]" class="form-control cms-summernote" rows="6">{{ $lang === $defaultLanguage ? $headerSection->description : ($headerTranslations[$lang]['description'] ?? '') }}</textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach

                                <div class="catalogue-actions">
                                    <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                </div>
                            </form>
                        </div>
                    </section>
                @endif

                @if($heroSection)
                    @php
                        $heroTranslations = $mapTranslations($heroSection);
                    @endphp
                    <section class="card catalogue-panel">
                        <div class="card-header catalogue-panel__header">
                            <div>
                                <h2 class="catalogue-panel__title">{{ translate('hero_section') }}</h2>
                                <p class="catalogue-panel__note">{{ translate('Controls_the_main_hero_copy_and_base_slide_for_the_services_page') }}</p>
                            </div>
                            <div class="catalogue-toggle-wrap">
                                <label class="switcher mb-0">
                                    <input type="checkbox" class="switcher_input status-toggle" data-id="{{ $heroSection->id }}" {{ $heroSection->is_active ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <div class="card-body catalogue-panel__body">
                            <form action="{{ route('admin.content-management.services.update', ['id' => $heroSection->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="catalogue-locale-tabs" data-locale-scope="hero-section">
                                    @foreach($language as $lang)
                                        <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="hero-section">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                    @endforeach
                                </div>

                                @foreach($language as $lang)
                                    <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="hero-section">
                                        <div class="form-group">
                                            <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="heading[]" class="form-control" value="{{ $lang === $defaultLanguage ? $heroSection->heading : ($heroTranslations[$lang]['heading'] ?? '') }}">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                            <textarea name="description[]" class="form-control cms-summernote" rows="8">{{ $lang === $defaultLanguage ? $heroSection->description : ($heroTranslations[$lang]['description'] ?? '') }}</textarea>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="button_text[]" class="form-control" value="{{ $lang === $defaultLanguage ? ($heroSection->button_text ?? '') : ($heroTranslations[$lang]['button_text'] ?? '') }}">
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach

                                <div class="form-group mt-4">
                                    <label>{{ translate('button_link') }}</label>
                                    <input type="text" name="button_link" class="form-control" value="{{ $heroSection->button_link ?? '' }}">
                                </div>

                                <div class="form-group mb-0">
                                    <label>{{ translate('hero_image') }}</label>
                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#heroImagePreview" data-preview-wrapper="#heroImagePreviewWrap" data-remove-target="#heroRemoveImage">
                                    <input type="hidden" name="remove_image" id="heroRemoveImage" value="0">
                                    <div id="heroImagePreviewWrap" class="catalogue-image-preview catalogue-image-preview--wide {{ $heroSection->image ? '' : 'd-none' }}">
                                        <img id="heroImagePreview" src="{{ $heroSection->image ? Storage::url($heroSection->image) : '' }}" alt="{{ $heroSection->heading }}">
                                        <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#heroImagePreview" data-preview-wrapper="#heroImagePreviewWrap" data-remove-target="#heroRemoveImage">&times;</button>
                                    </div>
                                </div>

                                <div class="catalogue-actions">
                                    <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                </div>
                            </form>

                            <div class="catalogue-card mt-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                    <div>
                                        <h3 class="catalogue-card__title">{{ translate('hero_slides') }}</h3>
                                        <p class="catalogue-card__note">{{ translate('Manage_the_additional_hero_slides_that_rotate_in_the_services_page_banner') }}</p>
                                    </div>
                                    <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#createHeroSlideModal">
                                        <i class="tio-add-circle-outlined"></i> {{ translate('add_hero_slide') }}
                                    </button>
                                </div>

                                @if($heroSlides->isEmpty())
                                    <div class="catalogue-empty">{{ translate('No_hero_slides_have_been_added_yet') }}</div>
                                @else
                                    <div class="catalogue-showcase-list">
                                        @foreach($heroSlides as $item)
                                            @php
                                                $itemTranslations = $mapTranslations($item);
                                                $itemTitle = trim((string) $item->getTranslatedField('title', null, $item->title ?? ''));
                                                $itemDescription = trim((string) richTextToPlainText($item->getTranslatedField('description', null, $item->description ?? '')));
                                            @endphp
                                            <article class="catalogue-showcase-item">
                                                <div>
                                                    @if($item->image)
                                                        <img class="catalogue-showcase-item__media" src="{{ Storage::url($item->image) }}" alt="{{ $itemTitle !== '' ? $itemTitle : translate('hero_section') }}">
                                                    @else
                                                        <div class="catalogue-showcase-item__empty">{{ translate('Upload_the_image_that_should_appear_in_this_hero_slide') }}</div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="catalogue-meta-row">
                                                        <span class="{{ $item->is_active ? 'catalogue-status-chip' : 'catalogue-status-chip catalogue-status-chip--muted' }}">{{ $item->is_active ? translate('active') : translate('inactive') }}</span>
                                                    </div>
                                                    <h3 class="catalogue-showcase-item__title">{{ $itemTitle !== '' ? $itemTitle : '-' }}</h3>
                                                    <div class="catalogue-showcase-item__copy">{{ $itemDescription !== '' ? \Illuminate\Support\Str::limit($itemDescription, 220) : translate('No_description_added_yet') }}</div>
                                                    <div class="catalogue-actions">
                                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#editHeroSlideModal{{ $item->id }}">
                                                            <i class="tio-edit"></i> {{ translate('edit_hero_slide') }}
                                                        </button>
                                                        <form action="{{ route('admin.content-management.services.showcase-items.destroy', ['id' => $item->id]) }}" method="POST" onsubmit="return confirm('{{ translate('Are_you_sure_delete_this') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger"><i class="tio-delete-outlined"></i> {{ translate('Delete') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            @if($showcaseSection)
                @php
                    $showcaseTranslations = $mapTranslations($showcaseSection);
                @endphp
                <section class="card catalogue-panel">
                    <div class="card-header catalogue-panel__header">
                        <div>
                            <h2 class="catalogue-panel__title">{{ translate('showcase_slider') }}</h2>
                            <p class="catalogue-panel__note">{{ translate('Manage_the_manual_showcase_cards_that_feed_the_slider_and_the_data_panel') }}</p>
                        </div>
                        <div class="catalogue-actions catalogue-panel__tools mt-0">
                            <label class="switcher mb-0">
                                <input type="checkbox" class="switcher_input status-toggle" data-id="{{ $showcaseSection->id }}" {{ $showcaseSection->is_active ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                            <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#createShowcaseCardModal">
                                <i class="tio-add-circle-outlined"></i> {{ translate('add_showcase_card') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body catalogue-panel__body">
                        <div class="catalogue-card mb-4">
                            <h3 class="catalogue-card__title">{{ translate('showcase_slider_intro') }}</h3>
                            <p class="catalogue-card__note">{{ translate('This_content_controls_the_headline_and_supporting_copy_above_the_manual_showcase_slider') }}</p>

                            <form action="{{ route('admin.content-management.services.update', ['id' => $showcaseSection->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="catalogue-locale-tabs" data-locale-scope="showcase-section">
                                    @foreach($language as $lang)
                                        <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="showcase-section">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                    @endforeach
                                </div>

                                @foreach($language as $lang)
                                    <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="showcase-section">
                                        <div class="form-group">
                                            <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="heading[]" class="form-control" value="{{ $lang === $defaultLanguage ? $showcaseSection->heading : ($showcaseTranslations[$lang]['heading'] ?? '') }}">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                            <textarea name="description[]" class="form-control cms-summernote" rows="7">{{ $lang === $defaultLanguage ? $showcaseSection->description : ($showcaseTranslations[$lang]['description'] ?? '') }}</textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach

                                <div class="catalogue-actions">
                                    <button type="submit" class="btn btn-outline-primary">{{ translate('save_changes') }}</button>
                                </div>
                            </form>
                        </div>

                        @if($showcaseItems->isEmpty())
                            <div class="catalogue-empty">{{ translate('No_showcase_cards_have_been_added_yet') }}</div>
                        @else
                            <div class="catalogue-showcase-list">
                                @foreach($showcaseItems as $item)
                                    @php
                                        $itemTranslations = $mapTranslations($item);
                                        $itemTitle = trim((string) $item->getTranslatedField('title', null, $item->title ?? ''));
                                        $itemDescription = trim((string) richTextToPlainText($item->getTranslatedField('description', null, $item->description ?? '')));
                                    @endphp
                                    <article class="catalogue-showcase-item">
                                        <div>
                                            @if($item->image)
                                                <img class="catalogue-showcase-item__media" src="{{ Storage::url($item->image) }}" alt="{{ $itemTitle !== '' ? $itemTitle : translate('showcase_cards') }}">
                                            @else
                                                <div class="catalogue-showcase-item__empty">{{ translate('Upload_an_image_to_show_this_card_in_the_slider') }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="catalogue-meta-row">
                                                <span class="catalogue-type-chip">{{ $showcaseTypeLabels[$item->card_type] ?? ucfirst((string) $item->card_type) }}</span>
                                                <span class="{{ $item->is_active ? 'catalogue-status-chip' : 'catalogue-status-chip catalogue-status-chip--muted' }}">{{ $item->is_active ? translate('active') : translate('inactive') }}</span>
                                            </div>
                                            <h3 class="catalogue-showcase-item__title">{{ $itemTitle !== '' ? $itemTitle : '-' }}</h3>
                                            <div class="catalogue-showcase-item__copy">{{ $itemDescription !== '' ? \Illuminate\Support\Str::limit($itemDescription, 220) : translate('No_description_added_yet') }}</div>
                                            <div class="catalogue-actions">
                                                <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#editShowcaseCardModal{{ $item->id }}">
                                                    <i class="tio-edit"></i> {{ translate('edit_showcase_card') }}
                                                </button>
                                                <form action="{{ route('admin.content-management.services.showcase-items.destroy', ['id' => $item->id]) }}" method="POST" onsubmit="return confirm('{{ translate('Are_you_sure_delete_this') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"><i class="tio-delete-outlined"></i> {{ translate('Delete') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if($supportSections->isNotEmpty())
                <section class="card catalogue-panel">
                    <div class="card-header catalogue-panel__header">
                        <div>
                            <h2 class="catalogue-panel__title">{{ translate('support_cards') }}</h2>
                            <p class="catalogue-panel__note">{{ translate('Keep_the_existing_three_support_cards_but_make_them_easier_to_edit_from_one_screen') }}</p>
                        </div>
                    </div>
                    <div class="card-body catalogue-panel__body">
                        <div class="catalogue-support-stack">
                            @foreach($supportSections as $section)
                                @php
                                    $sectionTranslations = $mapTranslations($section);
                                @endphp
                                <form class="catalogue-card catalogue-support-form" action="{{ route('admin.content-management.services.update', ['id' => $section->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="catalogue-support-form__head">
                                        <div>
                                            <h3 class="catalogue-card__title">{{ $supportTitles[$section->type] ?? translate('support_cards') }}</h3>
                                            <p class="catalogue-card__note">{{ translate('Controls_one_of_the_support_story_cards_on_the_services_page') }}</p>
                                        </div>
                                        <div class="catalogue-toggle-wrap">
                                            <label class="switcher mb-0">
                                                <input type="checkbox" class="switcher_input status-toggle" data-id="{{ $section->id }}" {{ $section->is_active ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="catalogue-support-form__content">
                                        <div class="catalogue-locale-tabs" data-locale-scope="support-section-{{ $section->id }}">
                                            @foreach($language as $lang)
                                                <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="support-section-{{ $section->id }}">{{ strtoupper($lang) }}</a>
                                            @endforeach
                                        </div>

                                        @foreach($language as $lang)
                                            <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="support-section-{{ $section->id }}">
                                                <div class="form-group">
                                                    <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="heading[]" class="form-control" value="{{ $lang === $defaultLanguage ? $section->heading : ($sectionTranslations[$lang]['heading'] ?? '') }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                                    <textarea name="description[]" class="form-control cms-summernote" rows="6">{{ $lang === $defaultLanguage ? $section->description : ($sectionTranslations[$lang]['description'] ?? '') }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="button_text[]" class="form-control" value="{{ $lang === $defaultLanguage ? ($section->button_text ?? '') : ($sectionTranslations[$lang]['button_text'] ?? '') }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach

                                        <div class="form-group mb-0">
                                            <label>{{ translate('button_link') }}</label>
                                            <input type="text" name="button_link" class="form-control" value="{{ $section->button_link ?? '' }}">
                                        </div>

                                        <div class="catalogue-actions">
                                            <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                        </div>
                                    </div>

                                    <div class="catalogue-support-form__media">
                                        <div class="form-group mb-0">
                                            <label>{{ translate('Image') }}</label>
                                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#supportPreview{{ $section->id }}" data-preview-wrapper="#supportPreviewWrap{{ $section->id }}" data-remove-target="#supportRemove{{ $section->id }}">
                                            <input type="hidden" name="remove_image" id="supportRemove{{ $section->id }}" value="0">
                                            <div id="supportPreviewWrap{{ $section->id }}" class="catalogue-image-preview catalogue-support-form__preview {{ $section->image ? '' : 'd-none' }}">
                                                <img id="supportPreview{{ $section->id }}" src="{{ $section->image ? Storage::url($section->image) : '' }}" alt="{{ $section->heading }}">
                                                <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#supportPreview{{ $section->id }}" data-preview-wrapper="#supportPreviewWrap{{ $section->id }}" data-remove-target="#supportRemove{{ $section->id }}">&times;</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if($heroSection)
                <div class="modal fade catalogue-modal" id="createHeroSlideModal" tabindex="-1" aria-hidden="true" dir="{{ $direction }}">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="catalogue-modal__head">
                                    <h5 class="modal-title mb-1">{{ translate('add_hero_slide') }}</h5>
                                    <p class="text-muted mb-0">{{ translate('Each_slide_controls_one_hero_image_and_one_hero_story') }}</p>
                                </div>
                                <button type="button" class="close catalogue-modal__close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('admin.content-management.services.showcase-items.store', ['id' => $heroSection->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="row g-4 catalogue-modal__layout">
                                        <div class="col-lg-5">
                                            <div class="catalogue-card h-100">
                                                <h3 class="catalogue-card__title">{{ translate('slider_media') }}</h3>
                                                <p class="catalogue-card__note">{{ translate('Upload_the_image_that_should_appear_in_this_hero_slide') }}</p>
                                                <div class="form-group mb-0">
                                                    <label>{{ translate('Image') }}</label>
                                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#newHeroSlidePreview" data-preview-wrapper="#newHeroSlidePreviewWrap" data-remove-target="#newHeroSlideRemove">
                                                    <input type="hidden" name="remove_image" id="newHeroSlideRemove" value="0">
                                                    <div id="newHeroSlidePreviewWrap" class="catalogue-image-preview catalogue-image-preview--wide d-none">
                                                        <img id="newHeroSlidePreview" src="" alt="{{ translate('hero_section') }}">
                                                        <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#newHeroSlidePreview" data-preview-wrapper="#newHeroSlidePreviewWrap" data-remove-target="#newHeroSlideRemove">&times;</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="catalogue-card h-100">
                                                <h3 class="catalogue-card__title">{{ translate('data_panel') }}</h3>
                                                <p class="catalogue-card__note">{{ translate('This_content_appears_inside_the_services_page_hero_slide') }}</p>

                                                <div class="catalogue-locale-tabs" data-locale-scope="new-hero-slide">
                                                    @foreach($language as $lang)
                                                        <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="new-hero-slide">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                                    @endforeach
                                                </div>

                                                @foreach($language as $lang)
                                                    <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="new-hero-slide">
                                                        <div class="form-group">
                                                            <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                                            <input type="text" class="form-control" name="title[]" value="">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                                            <textarea class="form-control cms-summernote" name="description[]" rows="7"></textarea>
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                                            <input type="text" class="form-control" name="primary_button_text[]" value="">
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    </div>
                                                @endforeach

                                                <div class="form-group mb-0">
                                                    <label>{{ translate('button_link') }}</label>
                                                    <input type="text" class="form-control" name="primary_button_link" value="">
                                                </div>

                                                <div class="row g-3 mt-1">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0">
                                                            <label>{{ translate('sort_order') }}</label>
                                                            <input type="number" class="form-control" name="sort_order" min="0" value="{{ $heroSlides->count() + 1 }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 catalogue-switch-field">
                                                        <label class="switcher mb-2">
                                                            <input type="checkbox" class="switcher_input" name="is_active" value="1" checked>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                        <span class="catalogue-switch-label">{{ translate('active') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                                    <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @foreach($heroSlides as $item)
                    @php
                        $itemTranslations = $mapTranslations($item);
                    @endphp
                    <div class="modal fade catalogue-modal" id="editHeroSlideModal{{ $item->id }}" tabindex="-1" aria-hidden="true" dir="{{ $direction }}">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="catalogue-modal__head">
                                        <h5 class="modal-title mb-1">{{ translate('edit_hero_slide') }}</h5>
                                        <p class="text-muted mb-0">{{ translate('Each_slide_controls_one_hero_image_and_one_hero_story') }}</p>
                                    </div>
                                    <button type="button" class="close catalogue-modal__close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('admin.content-management.services.showcase-items.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row g-4 catalogue-modal__layout">
                                            <div class="col-lg-5">
                                                <div class="catalogue-card h-100">
                                                    <h3 class="catalogue-card__title">{{ translate('slider_media') }}</h3>
                                                    <p class="catalogue-card__note">{{ translate('Upload_the_image_that_should_appear_in_this_hero_slide') }}</p>
                                                    <div class="form-group mb-0">
                                                        <label>{{ translate('Image') }}</label>
                                                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#heroSlidePreview{{ $item->id }}" data-preview-wrapper="#heroSlidePreviewWrap{{ $item->id }}" data-remove-target="#heroSlideRemove{{ $item->id }}">
                                                        <input type="hidden" name="remove_image" id="heroSlideRemove{{ $item->id }}" value="0">
                                                        <div id="heroSlidePreviewWrap{{ $item->id }}" class="catalogue-image-preview catalogue-image-preview--wide {{ $item->image ? '' : 'd-none' }}">
                                                            <img id="heroSlidePreview{{ $item->id }}" src="{{ $item->image ? Storage::url($item->image) : '' }}" alt="{{ $item->title }}">
                                                            <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#heroSlidePreview{{ $item->id }}" data-preview-wrapper="#heroSlidePreviewWrap{{ $item->id }}" data-remove-target="#heroSlideRemove{{ $item->id }}">&times;</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="catalogue-card h-100">
                                                    <h3 class="catalogue-card__title">{{ translate('data_panel') }}</h3>
                                                    <p class="catalogue-card__note">{{ translate('This_content_appears_inside_the_services_page_hero_slide') }}</p>

                                                    <div class="catalogue-locale-tabs" data-locale-scope="hero-slide-{{ $item->id }}">
                                                        @foreach($language as $lang)
                                                            <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="hero-slide-{{ $item->id }}">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                                        @endforeach
                                                    </div>

                                                    @foreach($language as $lang)
                                                        <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="hero-slide-{{ $item->id }}">
                                                            <div class="form-group">
                                                                <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                                                <input type="text" class="form-control" name="title[]" value="{{ $lang === $defaultLanguage ? ($item->title ?? '') : ($itemTranslations[$lang]['title'] ?? '') }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                                                <textarea class="form-control cms-summernote" name="description[]" rows="7">{{ $lang === $defaultLanguage ? ($item->description ?? '') : ($itemTranslations[$lang]['description'] ?? '') }}</textarea>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                                                <input type="text" class="form-control" name="primary_button_text[]" value="{{ $lang === $defaultLanguage ? ($item->primary_button_text ?? '') : ($itemTranslations[$lang]['primary_button_text'] ?? '') }}">
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                        </div>
                                                    @endforeach

                                                    <div class="form-group mb-0">
                                                        <label>{{ translate('button_link') }}</label>
                                                        <input type="text" class="form-control" name="primary_button_link" value="{{ $item->primary_button_link ?? '' }}">
                                                    </div>

                                                    <div class="row g-3 mt-1">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label>{{ translate('sort_order') }}</label>
                                                                <input type="number" class="form-control" name="sort_order" min="0" value="{{ $item->sort_order }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 catalogue-switch-field">
                                                            <label class="switcher mb-2">
                                                                <input type="checkbox" class="switcher_input" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                            <span class="catalogue-switch-label">{{ translate('active') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                                        <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if($showcaseSection)
                <div class="modal fade catalogue-modal" id="createShowcaseCardModal" tabindex="-1" aria-hidden="true" dir="{{ $direction }}">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="catalogue-modal__head">
                                    <h5 class="modal-title mb-1">{{ translate('add_showcase_card') }}</h5>
                                    <p class="text-muted mb-0">{{ translate('Each_card_controls_one_slider_image_and_one_data_panel_story') }}</p>
                                </div>
                                <button type="button" class="close catalogue-modal__close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('admin.content-management.services.showcase-items.store', ['id' => $showcaseSection->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="row g-4 catalogue-modal__layout">
                                        <div class="col-lg-5">
                                            <div class="catalogue-card h-100">
                                                <h3 class="catalogue-card__title">{{ translate('slider_media') }}</h3>
                                                <p class="catalogue-card__note">{{ translate('Upload_the_image_that_should_appear_in_the_slider_for_this_card') }}</p>
                                                <div class="form-group">
                                                    <label>{{ translate('showcase_card_type') }}</label>
                                                    <select name="card_type" class="form-control">
                                                        @foreach($showcaseCardTypes as $type)
                                                            <option value="{{ $type }}">{{ $showcaseTypeLabels[$type] ?? ucfirst($type) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label>{{ translate('Image') }}</label>
                                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#newShowcaseCardPreview" data-preview-wrapper="#newShowcaseCardPreviewWrap" data-remove-target="#newShowcaseCardRemove">
                                                    <input type="hidden" name="remove_image" id="newShowcaseCardRemove" value="0">
                                                    <div id="newShowcaseCardPreviewWrap" class="catalogue-image-preview catalogue-image-preview--wide d-none">
                                                        <img id="newShowcaseCardPreview" src="" alt="{{ translate('showcase_cards') }}">
                                                        <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#newShowcaseCardPreview" data-preview-wrapper="#newShowcaseCardPreviewWrap" data-remove-target="#newShowcaseCardRemove">&times;</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="catalogue-card h-100">
                                                <h3 class="catalogue-card__title">{{ translate('data_panel') }}</h3>
                                                <p class="catalogue-card__note">{{ translate('This_content_appears_next_to_the_slider_image_in_the_showcase_section') }}</p>

                                                <div class="catalogue-locale-tabs" data-locale-scope="new-showcase-card">
                                                    @foreach($language as $lang)
                                                        <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="new-showcase-card">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                                    @endforeach
                                                </div>

                                                @foreach($language as $lang)
                                                    <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="new-showcase-card">
                                                        <div class="form-group">
                                                            <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                                            <input type="text" class="form-control" name="title[]" value="">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                                            <textarea class="form-control cms-summernote" name="description[]" rows="7"></textarea>
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                                            <input type="text" class="form-control" name="primary_button_text[]" value="">
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    </div>
                                                @endforeach

                                                <div class="form-group mb-0">
                                                    <label>{{ translate('button_link') }}</label>
                                                    <input type="text" class="form-control" name="primary_button_link" value="">
                                                </div>

                                                <div class="row g-3 mt-1">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0">
                                                            <label>{{ translate('sort_order') }}</label>
                                                            <input type="number" class="form-control" name="sort_order" min="0" value="{{ $showcaseItems->count() + 1 }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 catalogue-switch-field">
                                                        <label class="switcher mb-2">
                                                            <input type="checkbox" class="switcher_input" name="is_active" value="1" checked>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                        <span class="catalogue-switch-label">{{ translate('active') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                                    <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @foreach($showcaseItems as $item)
                    @php
                        $itemTranslations = $mapTranslations($item);
                    @endphp
                    <div class="modal fade catalogue-modal" id="editShowcaseCardModal{{ $item->id }}" tabindex="-1" aria-hidden="true" dir="{{ $direction }}">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="catalogue-modal__head">
                                        <h5 class="modal-title mb-1">{{ translate('edit_showcase_card') }}</h5>
                                        <p class="text-muted mb-0">{{ translate('Each_card_controls_one_slider_image_and_one_data_panel_story') }}</p>
                                    </div>
                                    <button type="button" class="close catalogue-modal__close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('admin.content-management.services.showcase-items.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row g-4 catalogue-modal__layout">
                                            <div class="col-lg-5">
                                                <div class="catalogue-card h-100">
                                                    <h3 class="catalogue-card__title">{{ translate('slider_media') }}</h3>
                                                    <p class="catalogue-card__note">{{ translate('Upload_the_image_that_should_appear_in_the_slider_for_this_card') }}</p>
                                                    <div class="form-group">
                                                        <label>{{ translate('showcase_card_type') }}</label>
                                                        <select name="card_type" class="form-control">
                                                            @foreach($showcaseCardTypes as $type)
                                                                <option value="{{ $type }}" {{ $item->card_type === $type ? 'selected' : '' }}>{{ $showcaseTypeLabels[$type] ?? ucfirst($type) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label>{{ translate('Image') }}</label>
                                                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview-target="#showcaseCardPreview{{ $item->id }}" data-preview-wrapper="#showcaseCardPreviewWrap{{ $item->id }}" data-remove-target="#showcaseCardRemove{{ $item->id }}">
                                                        <input type="hidden" name="remove_image" id="showcaseCardRemove{{ $item->id }}" value="0">
                                                        <div id="showcaseCardPreviewWrap{{ $item->id }}" class="catalogue-image-preview catalogue-image-preview--wide {{ $item->image ? '' : 'd-none' }}">
                                                            <img id="showcaseCardPreview{{ $item->id }}" src="{{ $item->image ? Storage::url($item->image) : '' }}" alt="{{ $item->title }}">
                                                            <button type="button" class="catalogue-image-remove" data-clear-file data-preview-target="#showcaseCardPreview{{ $item->id }}" data-preview-wrapper="#showcaseCardPreviewWrap{{ $item->id }}" data-remove-target="#showcaseCardRemove{{ $item->id }}">&times;</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="catalogue-card h-100">
                                                    <h3 class="catalogue-card__title">{{ translate('data_panel') }}</h3>
                                                    <p class="catalogue-card__note">{{ translate('This_content_appears_next_to_the_slider_image_in_the_showcase_section') }}</p>

                                                    <div class="catalogue-locale-tabs" data-locale-scope="showcase-card-{{ $item->id }}">
                                                        @foreach($language as $lang)
                                                            <a href="javascript:" class="catalogue-locale-tab {{ $lang === $defaultLanguage ? 'active' : '' }}" data-language="{{ $lang }}" data-scope="showcase-card-{{ $item->id }}">{{ getLanguageName($lang) }} ({{ strtoupper($lang) }})</a>
                                                        @endforeach
                                                    </div>

                                                    @foreach($language as $lang)
                                                        <div class="catalogue-locale-pane {{ $lang !== $defaultLanguage ? 'd-none' : '' }}" data-language="{{ $lang }}" data-scope="showcase-card-{{ $item->id }}">
                                                            <div class="form-group">
                                                                <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                                                                <input type="text" class="form-control" name="title[]" value="{{ $lang === $defaultLanguage ? ($item->title ?? '') : ($itemTranslations[$lang]['title'] ?? '') }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                                                <textarea class="form-control cms-summernote" name="description[]" rows="7">{{ $lang === $defaultLanguage ? ($item->description ?? '') : ($itemTranslations[$lang]['description'] ?? '') }}</textarea>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>{{ translate('Button_Text') }} ({{ strtoupper($lang) }})</label>
                                                                <input type="text" class="form-control" name="primary_button_text[]" value="{{ $lang === $defaultLanguage ? ($item->primary_button_text ?? '') : ($itemTranslations[$lang]['primary_button_text'] ?? '') }}">
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                        </div>
                                                    @endforeach

                                                    <div class="form-group mb-0">
                                                        <label>{{ translate('button_link') }}</label>
                                                        <input type="text" class="form-control" name="primary_button_link" value="{{ $item->primary_button_link ?? '' }}">
                                                    </div>

                                                    <div class="row g-3 mt-1">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label>{{ translate('sort_order') }}</label>
                                                                <input type="number" class="form-control" name="sort_order" min="0" value="{{ $item->sort_order }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 catalogue-switch-field">
                                                            <label class="switcher mb-2">
                                                                <input type="checkbox" class="switcher_input" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                            <span class="catalogue-switch-label">{{ translate('active') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                                        <button type="submit" class="btn btn--primary">{{ translate('save_changes') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    <script>
        (function ($) {
            'use strict';
            const pageDirection = @json($direction);

            function initializeSummernote($root) {
                $root.find('.cms-summernote').each(function () {
                    const $editor = $(this);
                    if ($editor.next('.note-editor').length) {
                        return;
                    }

                    $editor.summernote({
                        height: 180,
                        dialogsInBody: true,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['codeview']]
                        ]
                    });

                    const $noteEditor = $editor.next('.note-editor');
                    $noteEditor.attr('dir', pageDirection);
                    $noteEditor.find('.note-editable')
                        .attr('dir', pageDirection)
                        .css('text-align', pageDirection === 'rtl' ? 'right' : 'left');
                });
            }

            initializeSummernote($(document));
            $(document).on('shown.bs.modal', '.modal', function () { initializeSummernote($(this)); });

            $(document).on('click', '.catalogue-locale-tab', function () {
                const $tab = $(this);
                const scope = $tab.data('scope');
                const language = $tab.data('language');
                $('[data-scope="' + scope + '"].catalogue-locale-tab').removeClass('active');
                $tab.addClass('active');
                $('.catalogue-locale-pane[data-scope="' + scope + '"]').addClass('d-none');
                $('.catalogue-locale-pane[data-scope="' + scope + '"][data-language="' + language + '"]').removeClass('d-none');
            });

            $(document).on('change', '.status-toggle', function () {
                const $toggle = $(this);
                const previousState = !$toggle.prop('checked');
                $.ajax({
                    url: "{{ route('admin.content-management.services.status') }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', id: $toggle.data('id') },
                    success: function (response) { toastr.success(response.message); },
                    error: function () {
                        $toggle.prop('checked', previousState);
                        toastr.error(@json(translate('Something_went_wrong')));
                    }
                });
            });

            $(document).on('change', 'input[type="file"][data-preview-target]', function (event) {
                const input = event.currentTarget;
                const file = input.files && input.files[0];
                if (!file) return;
                const previewTarget = input.getAttribute('data-preview-target');
                const previewWrapper = input.getAttribute('data-preview-wrapper');
                const removeTarget = input.getAttribute('data-remove-target');
                const reader = new FileReader();
                reader.onload = function (loadEvent) {
                    $(previewTarget).attr('src', loadEvent.target.result);
                    $(previewWrapper).removeClass('d-none');
                    if (removeTarget) $(removeTarget).val('0');
                };
                reader.readAsDataURL(file);
            });

            $(document).on('click', '[data-clear-file]', function () {
                const $button = $(this);
                const previewTarget = $button.data('preview-target');
                const previewWrapper = $button.data('preview-wrapper');
                const removeTarget = $button.data('remove-target');
                $('input[type="file"][data-preview-target="' + previewTarget + '"]').first().val('');
                $(previewTarget).attr('src', '');
                $(previewWrapper).addClass('d-none');
                if (removeTarget) $(removeTarget).val('1');
            });
        })(jQuery);
    </script>
@endpush
