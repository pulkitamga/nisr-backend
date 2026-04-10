@extends('layouts.front-end.app')

@section('title', translate('Our_Products'))

@push('css_or_js')
    @include('web-views.partials._premium-page-styles')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/swiper-bundle.min.css') }}">
    <style>
        .nisr-products-shell {
            display: grid;
            gap: 1.5rem;
        }

        .nisr-header-band {
            display: grid;
            gap: 1rem;
            padding: clamp(1.4rem, 2.4vw, 2rem);
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.8rem;
            background: linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(244,249,248,.98) 100%);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.07);
        }

        .nisr-header-band__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            color: var(--nisr-accent);
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .nisr-header-band__eyebrow::before {
            content: "";
            inline-size: 2.5rem;
            block-size: 1px;
            background: currentColor;
            opacity: .6;
        }

        .nisr-header-band__title {
            margin: 0;
            color: var(--nisr-ink);
            font-size: clamp(2rem, 4vw, 3.6rem);
            line-height: 1.02;
            letter-spacing: -.04em;
        }

        .nisr-header-band__copy,
        .nisr-support-body,
        .nisr-showcase-copy {
            color: var(--nisr-muted);
            line-height: 1.85;
        }

        .nisr-products-hero {
            position: relative;
            overflow: hidden;
            min-block-size: 28rem;
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.6rem;
            background:
                linear-gradient(90deg, rgba(8, 15, 18, 0.88) 0%, rgba(8, 15, 18, 0.42) 42%, rgba(8, 15, 18, 0.08) 100%),
                linear-gradient(180deg, #eff8f7 0%, #dceceb 100%);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.07);
        }

        .nisr-products-hero-slider {
            position: relative;
        }

        .nisr-products-hero-slider .swiper-slide {
            height: auto;
        }

        .nisr-products-hero__copy {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: min(34rem, calc(100% - 2.5rem));
            margin: clamp(1.3rem, 2.2vw, 1.8rem);
            margin-inline-start: auto;
            padding: clamp(1.3rem, 2.4vw, 2rem);
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(244,249,248,.96) 100%);
            box-shadow: 0 1.25rem 2.6rem rgba(9, 29, 37, 0.18);
        }

        .nisr-products-hero__visual {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .nisr-products-hero__visual img {
            inline-size: 100%;
            block-size: 100%;
            object-fit: cover;
        }

        .nisr-story-showcase {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
            gap: 1.25rem;
            align-items: stretch;
        }

        .nisr-showcase-filters {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin: 0 0 1.25rem;
        }

        .nisr-showcase-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: .7rem 1.15rem;
            border: 1px solid rgba(16, 47, 58, 0.14);
            border-radius: 999px;
            background: rgba(255,255,255,.9);
            color: var(--nisr-ink);
            font-size: .92rem;
            font-weight: 700;
            line-height: 1.2;
            transition: color .2s ease, border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .nisr-showcase-filter:hover {
            color: var(--nisr-accent);
            border-color: color-mix(in srgb, var(--nisr-accent) 50%, white);
            text-decoration: none;
            transform: translateY(-1px);
        }

        .nisr-showcase-filter.is-active {
            color: #fff;
            border-color: var(--nisr-accent);
            background: linear-gradient(135deg, var(--nisr-accent) 0%, color-mix(in srgb, var(--nisr-accent) 78%, #0e2430) 100%);
            box-shadow: 0 1rem 1.8rem rgba(16, 56, 62, 0.16);
        }

        .nisr-story-showcase__media,
        .nisr-story-showcase__content {
            min-width: 0;
        }

        .nisr-story-slider {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, #eff8f7 0%, #dceceb 100%);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.07);
        }

        .nisr-story-slider .swiper-slide {
            height: auto;
        }

        .nisr-story-slider__frame {
            min-block-size: 28rem;
        }

        .nisr-story-slider__frame img {
            inline-size: 100%;
            block-size: 100%;
            object-fit: cover;
        }

        .nisr-story-slider__nav {
            position: absolute;
            inset-inline: 1rem;
            inset-block-end: 1rem;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .9rem;
        }

        .nisr-story-slider__buttons {
            display: flex;
            gap: .6rem;
        }

        .nisr-story-slider__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.7rem;
            height: 2.7rem;
            border: 1px solid rgba(255,255,255,.56);
            border-radius: 999px;
            background: rgba(255,255,255,.92);
            color: var(--nisr-ink);
        }

        .nisr-story-slider__pagination {
            width: auto !important;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .7rem;
            border-radius: 999px;
            background: rgba(255,255,255,.92);
        }

        .nisr-story-slider__pagination .swiper-pagination-bullet {
            width: .6rem;
            height: .6rem;
            margin: 0 !important;
            background: rgba(16, 47, 58, 0.22);
            opacity: 1;
        }

        .nisr-story-slider__pagination .swiper-pagination-bullet-active {
            background: var(--nisr-accent);
        }

        .nisr-story-data {
            height: 100%;
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(244,249,248,.98) 100%);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.07);
        }

        .nisr-story-data__inner {
            display: flex;
            flex-direction: column;
            min-block-size: 100%;
            padding: clamp(1.3rem, 2.2vw, 1.8rem);
        }

        .nisr-story-meta {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            color: var(--nisr-accent);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .nisr-story-title {
            margin: .65rem 0 0;
            color: var(--nisr-ink);
            font-size: clamp(1.6rem, 2.6vw, 2.3rem);
            line-height: 1.12;
        }

        .nisr-products-hero__actions,
        .nisr-story-cta {
            margin-top: auto;
            padding-top: 1.25rem;
        }

        .nisr-products-hero__copy .nisr-richtext {
            color: var(--nisr-muted);
            line-height: 1.85;
        }

        .nisr-products-hero__copy .nisr-section-title {
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.05;
        }

        html[dir="rtl"] .nisr-products-hero__copy {
            margin-inline-start: clamp(1.3rem, 2.2vw, 1.8rem);
            margin-inline-end: auto;
        }

        .nisr-products-hero-slider__nav {
            position: absolute;
            inset-inline: 1rem;
            inset-block-end: 1rem;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .9rem;
            pointer-events: none;
        }

        .nisr-products-hero-slider__nav .nisr-story-slider__buttons,
        .nisr-products-hero-slider__nav .nisr-story-slider__pagination {
            pointer-events: auto;
        }

        .nisr-story-bullets {
            display: grid;
            gap: .65rem;
            margin: 1.1rem 0 0;
            padding: 0;
            list-style: none;
        }

        .nisr-story-bullets li {
            position: relative;
            padding-inline-start: 1.25rem;
            color: var(--nisr-ink);
            font-weight: 600;
            line-height: 1.7;
        }

        .nisr-story-bullets li::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            inset-block-start: .72rem;
            inline-size: .45rem;
            block-size: .45rem;
            border-radius: 50%;
            background: var(--nisr-accent);
        }

        .nisr-support-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .nisr-support-card {
            display: grid;
            gap: 1rem;
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(244,249,248,.98) 100%);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.07);
            overflow: hidden;
        }

        .nisr-support-media {
            min-block-size: 14rem;
            background: linear-gradient(180deg, #deefed 0%, #cfe6e3 46%, #a8c8c5 100%);
        }

        .nisr-support-media img {
            inline-size: 100%;
            block-size: 100%;
            object-fit: cover;
        }

        .nisr-support-content {
            padding: 0 1.15rem 1.15rem;
        }

        .nisr-support-title {
            margin: 0;
            color: var(--nisr-ink);
            font-size: 1.3rem;
            line-height: 1.2;
        }

        @media (max-width: 991.98px) {
            .nisr-products-hero,
            .nisr-story-showcase,
            .nisr-support-grid {
                grid-template-columns: 1fr;
            }

            .nisr-products-hero {
                min-block-size: 24rem;
            }

            .nisr-products-hero__copy {
                max-width: calc(100% - 2rem);
                margin: 1rem;
            }

            .nisr-story-slider__frame {
                min-block-size: 20rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $headerSection = $cmsData->firstWhere('type', 'main_banner');
        $heroSection = $cmsData->firstWhere('type', 'core_product_slider');
        $showcaseSection = $cmsData->firstWhere('type', 'feature_product');
        $supportCards = collect(['request_card_1', 'request_card_2', 'request_card_3'])->map(fn ($type) => $cmsData->firstWhere('type', $type))->filter();
    @endphp

    <div class="nisr-page-shell">
        <div class="container">
            <div class="nisr-products-shell">
                @if($headerSection)
                    @php
                        $headerEyebrow = trim((string) getTranslatedValue($headerSection, 'button_text', $headerSection->button_text ?? ''));
                        $headerTitle = trim((string) getTranslatedValue($headerSection, 'heading', $headerSection->heading ?? ''));
                        $headerDescription = \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($headerSection, 'description', $headerSection->description ?? ''));
                    @endphp
                    @if($headerEyebrow !== '' || $headerTitle !== '' || $headerDescription !== '')
                        <section class="nisr-header-band">
                            @if($headerEyebrow !== '')
                                <span class="nisr-header-band__eyebrow">{{ $headerEyebrow }}</span>
                            @endif
                            @if($headerTitle !== '')
                                <h1 class="nisr-header-band__title">{{ $headerTitle }}</h1>
                            @endif
                            @if($headerDescription !== '')
                                <div class="nisr-header-band__copy">{!! $headerDescription !!}</div>
                            @endif
                        </section>
                    @endif
                @endif

                @if($heroSection)
                    @php
                        $heroTitle = trim((string) getTranslatedValue($heroSection, 'heading', $heroSection->heading ?? ''));
                        $heroDescription = \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($heroSection, 'description', $heroSection->description ?? ''));
                        $heroButtonText = trim((string) getTranslatedValue($heroSection, 'button_text', $heroSection->button_text ?? ''));
                        $heroButtonLink = \App\Support\CmsContentSanitizer::sanitizeLink($heroSection->button_link ?? '');
                        $heroSlidesCollection = collect();

                        if ($heroTitle !== '' || $heroDescription !== '' || ($heroButtonText !== '' && $heroButtonLink !== '') || $heroSection->image) {
                            $heroSlidesCollection->push([
                                'title' => $heroTitle,
                                'description' => $heroDescription,
                                'button_text' => $heroButtonText,
                                'button_link' => $heroButtonLink,
                                'image' => $heroSection->image ? Storage::url($heroSection->image) : null,
                                'alt' => $heroTitle !== '' ? $heroTitle : translate('hero_image'),
                            ]);
                        }

                        foreach (($heroSlides ?? collect()) as $heroSlide) {
                            $slideTitle = trim((string) $heroSlide->getTranslatedField('title', null, $heroSlide->title ?? ''));
                            $slideDescription = \App\Support\CmsContentSanitizer::sanitizeRichText($heroSlide->getTranslatedField('description', null, $heroSlide->description ?? ''));
                            $slideButtonText = trim((string) $heroSlide->getTranslatedField('primary_button_text', null, $heroSlide->primary_button_text ?? ''));
                            $slideButtonLink = \App\Support\CmsContentSanitizer::sanitizeLink($heroSlide->primary_button_link ?? '');

                            if ($slideTitle === '' && $slideDescription === '' && ($slideButtonText === '' || $slideButtonLink === '') && !$heroSlide->image) {
                                continue;
                            }

                            $heroSlidesCollection->push([
                                'title' => $slideTitle,
                                'description' => $slideDescription,
                                'button_text' => $slideButtonText,
                                'button_link' => $slideButtonLink,
                                'image' => $heroSlide->image ? Storage::url($heroSlide->image) : null,
                                'alt' => $slideTitle !== '' ? $slideTitle : translate('hero_image'),
                            ]);
                        }
                    @endphp
                    @if($heroSlidesCollection->isNotEmpty())
                        <section class="nisr-surface">
                            <div class="swiper nisr-products-hero-slider js-product-hero-slider" data-loop="{{ $heroSlidesCollection->count() > 1 ? '1' : '0' }}">
                                <div class="swiper-wrapper">
                                    @foreach($heroSlidesCollection as $heroSlide)
                                        <div class="swiper-slide">
                                            <div class="nisr-products-hero">
                                                @if(!empty($heroSlide['image']))
                                                    <div class="nisr-products-hero__visual">
                                                        <img src="{{ $heroSlide['image'] }}" alt="{{ $heroSlide['alt'] }}">
                                                    </div>
                                                @endif
                                                <div class="nisr-products-hero__copy">
                                                    @if($heroSlide['title'] !== '')
                                                        <div class="nisr-surface-head mb-0">
                                                            <h2 class="nisr-section-title">{{ $heroSlide['title'] }}</h2>
                                                        </div>
                                                    @endif
                                                    @if($heroSlide['description'] !== '')
                                                        <div class="nisr-richtext">{!! $heroSlide['description'] !!}</div>
                                                    @endif
                                                    @if($heroSlide['button_text'] !== '' && $heroSlide['button_link'] !== '')
                                                        <div class="nisr-products-hero__actions">
                                                            <a href="{{ $heroSlide['button_link'] }}" class="btn btn--primary">{{ $heroSlide['button_text'] }}</a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($heroSlidesCollection->count() > 1)
                                    <div class="nisr-products-hero-slider__nav">
                                        <div class="swiper-pagination nisr-story-slider__pagination"></div>
                                        <div class="nisr-story-slider__buttons">
                                            <button type="button" class="nisr-story-slider__button js-product-hero-prev" aria-label="{{ translate('previous') }}"><i class="fa fa-arrow-left"></i></button>
                                            <button type="button" class="nisr-story-slider__button js-product-hero-next" aria-label="{{ translate('next') }}"><i class="fa fa-arrow-right"></i></button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif
                @endif

                @if($showcaseSection && $showcaseItems->isNotEmpty())
                    @php
                        $showcaseTitle = trim((string) getTranslatedValue($showcaseSection, 'heading', $showcaseSection->heading ?? ''));
                        $showcaseDescription = \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($showcaseSection, 'description', $showcaseSection->description ?? ''));
                        $usedCardTypes = $showcaseItems->pluck('card_type')->filter()->unique()->values();
                    @endphp
                    <section class="nisr-surface">
                        @if($showcaseTitle !== '' || $showcaseDescription !== '')
                            <div class="nisr-surface-head">
                                @if($showcaseTitle !== '')
                                    <h2 class="nisr-section-title">{{ $showcaseTitle }}</h2>
                                @endif
                                @if($showcaseDescription !== '')
                                    <div class="nisr-section-copy mb-0">{!! $showcaseDescription !!}</div>
                                @endif
                            </div>
                        @endif

                        @if($usedCardTypes->isNotEmpty())
                            <div class="nisr-showcase-filters" data-showcase-filters>
                                <button type="button" class="nisr-showcase-filter is-active" data-card-filter="all">{{ translate('all') }}</button>
                                @foreach($usedCardTypes as $cardType)
                                    <button type="button" class="nisr-showcase-filter" data-card-filter="{{ $cardType }}">
                                        {{ translate('showcase_type_' . $cardType) }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="nisr-story-showcase">
                            <div class="nisr-story-showcase__media">
                                <div class="swiper nisr-story-slider js-product-story-media" data-loop="{{ $showcaseItems->count() > 1 ? '1' : '0' }}">
                                    <div class="swiper-wrapper">
                                        @foreach($showcaseItems as $item)
                                            @php
                                                $title = trim((string) $item->getTranslatedField('title', null, $item->title ?? ''));
                                            @endphp
                                            <div class="swiper-slide" data-card-type="{{ $item->card_type }}">
                                                <div class="nisr-story-slider__frame">
                                                    @if($item->image)
                                                        <img src="{{ Storage::url($item->image) }}" alt="{{ $title !== '' ? $title : translate('showcase_cards') }}">
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="nisr-story-slider__nav">
                                        <div class="swiper-pagination nisr-story-slider__pagination"></div>
                                        <div class="nisr-story-slider__buttons">
                                            <button type="button" class="nisr-story-slider__button js-product-story-prev" aria-label="{{ translate('previous') }}"><i class="fa fa-arrow-left"></i></button>
                                            <button type="button" class="nisr-story-slider__button js-product-story-next" aria-label="{{ translate('next') }}"><i class="fa fa-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nisr-story-showcase__content">
                                <div class="swiper nisr-story-data js-product-story-content" data-loop="{{ $showcaseItems->count() > 1 ? '1' : '0' }}">
                                    <div class="swiper-wrapper">
                                        @foreach($showcaseItems as $item)
                                            @php
                                                $title = trim((string) $item->getTranslatedField('title', null, $item->title ?? ''));
                                                $description = \App\Support\CmsContentSanitizer::sanitizeRichText($item->getTranslatedField('description', null, $item->description ?? ''));
                                                $buttonText = trim((string) $item->getTranslatedField('primary_button_text', null, $item->primary_button_text ?? ''));
                                                $buttonLink = \App\Support\CmsContentSanitizer::sanitizeLink($item->primary_button_link ?? '');
                                            @endphp
                                            <div class="swiper-slide" data-card-type="{{ $item->card_type }}">
                                                <div class="nisr-story-data__inner">
                                                    <span class="nisr-story-meta">{{ translate('showcase_type_' . $item->card_type) }}</span>
                                                    @if($title !== '')
                                                        <h3 class="nisr-story-title">{{ $title }}</h3>
                                                    @endif
                                                    @if($description !== '')
                                                        <div class="nisr-showcase-copy">{!! $description !!}</div>
                                                    @endif
                                                    @if($buttonText !== '' && $buttonLink !== '')
                                                        <div class="nisr-story-cta">
                                                            <a href="{{ $buttonLink }}" class="btn btn--primary">{{ $buttonText }}</a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @elseif($showcaseSection)
                    <section class="nisr-surface">
                        <div class="nisr-empty">
                            <h2 class="nisr-section-title">{{ translate('No_showcase_cards_have_been_added_yet') }}</h2>
                            <p class="nisr-section-copy mb-0">{{ translate('Need_help_choosing_the_right_option') }}</p>
                        </div>
                    </section>
                @endif

                @if($supportCards->isNotEmpty())
                    <section class="nisr-surface">
                        <div class="nisr-surface-head">
                            <h2 class="nisr-section-title">{{ translate('support_cards') }}</h2>
                            <p class="nisr-section-copy mb-0">{{ translate('Contact_our_team_for_sales_technical_or_service_guidance') }}</p>
                        </div>
                        <div class="nisr-support-grid">
                            @foreach($supportCards as $card)
                                @php
                                    $title = trim((string) getTranslatedValue($card, 'heading', $card->heading ?? ''));
                                    $description = \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($card, 'description', $card->description ?? ''));
                                    $buttonText = trim((string) getTranslatedValue($card, 'button_text', $card->button_text ?? ''));
                                    $buttonLink = \App\Support\CmsContentSanitizer::sanitizeLink($card->button_link ?? '');
                                @endphp
                                @if($title !== '' || $description !== '' || $buttonText !== '' || $card->image)
                                    <article class="nisr-support-card">
                                        @if($card->image)
                                            <div class="nisr-support-media">
                                                <img src="{{ Storage::url($card->image) }}" alt="{{ $title !== '' ? $title : translate('support_cards') }}">
                                            </div>
                                        @endif
                                        <div class="nisr-support-content">
                                            @if($title !== '')
                                                <h3 class="nisr-support-title">{{ $title }}</h3>
                                            @endif
                                            @if($description !== '')
                                                <div class="nisr-support-body mt-3">{!! $description !!}</div>
                                            @endif
                                            @if($buttonText !== '')
                                                <div class="mt-4">
                                                    <a href="{{ $buttonLink !== '' ? $buttonLink : route('contacts') }}" class="btn btn--primary">{{ $buttonText }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/swiper-bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const heroSlider = document.querySelector('.js-product-hero-slider');
            const mediaSlider = document.querySelector('.js-product-story-media');
            const contentSlider = document.querySelector('.js-product-story-content');
            const filterBar = document.querySelector('[data-showcase-filters]');

            if (heroSlider) {
                const shouldLoopHero = heroSlider.getAttribute('data-loop') === '1';
                const heroPagination = heroSlider.querySelector('.nisr-story-slider__pagination');
                const heroNext = heroSlider.querySelector('.js-product-hero-next');
                const heroPrev = heroSlider.querySelector('.js-product-hero-prev');
                const heroConfig = {
                    slidesPerView: 1,
                    loop: shouldLoopHero,
                    speed: 700,
                };

                if (heroPagination) {
                    heroConfig.pagination = {
                        el: heroPagination,
                        clickable: true,
                    };
                }

                if (heroNext && heroPrev) {
                    heroConfig.navigation = {
                        nextEl: heroNext,
                        prevEl: heroPrev,
                    };
                }

                new Swiper(heroSlider, heroConfig);
            }

            if (!mediaSlider || !contentSlider) {
                return;
            }

            const mediaWrapper = mediaSlider.querySelector('.swiper-wrapper');
            const contentWrapper = contentSlider.querySelector('.swiper-wrapper');
            if (!mediaWrapper || !contentWrapper) {
                return;
            }

            const originalMediaSlides = Array.from(mediaWrapper.children).map((slide) => slide.outerHTML);
            const originalContentSlides = Array.from(contentWrapper.children).map((slide) => slide.outerHTML);
            const slideRecords = originalMediaSlides.map(function (mediaMarkup, index) {
                const mediaNode = document.createElement('div');
                mediaNode.innerHTML = mediaMarkup.trim();
                const type = mediaNode.firstElementChild?.getAttribute('data-card-type') || 'all';

                return {
                    type: type,
                    media: mediaMarkup,
                    content: originalContentSlides[index] || '',
                };
            });

            let mediaSwiper = null;
            let contentSwiper = null;

            function buildSwipers(filterType) {
                const filteredRecords = slideRecords.filter(function (record) {
                    return filterType === 'all' || record.type === filterType;
                });

                if (!filteredRecords.length) {
                    return;
                }

                if (mediaSwiper) {
                    mediaSwiper.destroy(true, true);
                }

                if (contentSwiper) {
                    contentSwiper.destroy(true, true);
                }

                mediaWrapper.innerHTML = filteredRecords.map((record) => record.media).join('');
                contentWrapper.innerHTML = filteredRecords.map((record) => record.content).join('');

                const shouldLoop = filteredRecords.length > 1;

                contentSwiper = new Swiper(contentSlider, {
                    slidesPerView: 1,
                    effect: 'fade',
                    fadeEffect: { crossFade: true },
                    allowTouchMove: false,
                    loop: shouldLoop,
                    autoHeight: true,
                });

                mediaSwiper = new Swiper(mediaSlider, {
                    slidesPerView: 1,
                    loop: shouldLoop,
                    speed: 700,
                    pagination: {
                        el: '.nisr-story-slider__pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.js-product-story-next',
                        prevEl: '.js-product-story-prev',
                    },
                    controller: {
                        control: contentSwiper,
                    },
                });

                contentSwiper.controller.control = mediaSwiper;
            }

            buildSwipers('all');

            if (!filterBar) {
                return;
            }

            filterBar.addEventListener('click', function (event) {
                const button = event.target.closest('[data-card-filter]');
                if (!button) {
                    return;
                }

                filterBar.querySelectorAll('[data-card-filter]').forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });

                buildSwipers(button.getAttribute('data-card-filter') || 'all');
            });
        });
    </script>
@endpush
