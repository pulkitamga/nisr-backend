@extends('layouts.front-end.app')

@section('title', translate('about_us'))

@push('css_or_js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

<style>
    .collapse {
        visibility: visible !important;
    }

    .navbar-collapse {
        flex-grow: 0 !important;
    }

    .about-page {
        background: linear-gradient(180deg, #f8fbfb 0%, #ffffff 18%, #f7fbfb 100%);
    }

    .about-shell {
        display: grid;
        gap: clamp(1.25rem, 2vw, 2rem);
    }

    .about-panel {
        position: relative;
        overflow: hidden;
        border: 1px solid #dcebea;
        border-radius: 1.6rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: 0 1rem 2.4rem rgba(17, 56, 61, 0.06);
    }

    .about-panel--soft {
        background: linear-gradient(135deg, #f4fbfb 0%, #ffffff 55%, #eef8f6 100%);
    }

    .about-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .9rem;
        padding: .45rem .9rem;
        border-radius: 999px;
        background: rgba(18, 157, 145, 0.09);
        color: #12857f;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .03em;
    }

    .about-title {
        margin: 0;
        color: #17393f;
        font-size: clamp(2rem, 4vw, 3.45rem);
        font-weight: 700;
        line-height: 1.1;
    }

    .about-subtitle {
        margin: 1rem 0 0;
        color: #557076;
        font-size: clamp(1rem, 1.5vw, 1.12rem);
        line-height: 1.75;
    }

    .about-hero {
        position: relative;
        min-height: clamp(25rem, 48vw, 33rem);
        border-radius: 1.6rem;
    }

    .about-hero__media,
    .about-hero__overlay,
    .about-hero__content {
        position: absolute;
        inset: 0;
    }

    .about-hero__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.03);
    }

    .about-hero__overlay {
        background:
            linear-gradient(90deg, rgba(12, 36, 42, 0.2) 0%, rgba(12, 36, 42, 0.1) 38%, rgba(12, 36, 42, 0.02) 100%),
            linear-gradient(180deg, rgba(18, 157, 145, 0.08) 0%, rgba(18, 157, 145, 0.16) 100%);
    }

    .about-hero__content {
        z-index: 1;
        display: flex;
        align-items: center;
        padding: clamp(1.5rem, 4vw, 3rem);
    }

    .about-hero__copy {
        max-width: min(100%, 32rem);
        padding: clamp(1.25rem, 2vw, 1.75rem);
        border: 1px solid rgba(255, 255, 255, 0.68);
        border-radius: 1.35rem;
        background: rgba(255, 255, 255, 0.82);
        box-shadow: 0 1.2rem 2.4rem rgba(15, 54, 58, 0.14);
        backdrop-filter: blur(8px);
    }

    .about-hero__meta {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin-top: 1rem;
        color: #567075;
        font-size: .88rem;
        font-weight: 600;
    }

    .about-hero__meta i {
        color: #129d91;
    }

    .about-swiper .swiper-pagination {
        bottom: 1.25rem !important;
    }

    .about-swiper .swiper-pagination-bullet {
        width: .75rem;
        height: .75rem;
        background: rgba(255, 255, 255, 0.45);
        opacity: 1;
    }

    .about-swiper .swiper-pagination-bullet-active {
        background: #ffffff;
    }

    .about-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0;
        padding: 0;
        border-top: 1px solid #e4efee;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 251, 251, 0.95) 100%);
    }

    .about-summary-card {
        position: relative;
        padding: 1rem 1.2rem 1.1rem;
        background: transparent;
    }

    .about-summary-card:not(:last-child)::after {
        content: '';
        position: absolute;
        inset-inline-end: 0;
        top: 20%;
        bottom: 20%;
        width: 1px;
        background: #dfebea;
    }

    .about-summary-card__value {
        display: block;
        color: #12857f;
        font-size: clamp(1.35rem, 2vw, 1.7rem);
        font-weight: 700;
        line-height: 1;
    }

    .about-summary-card__label {
        display: block;
        margin-top: .38rem;
        color: #29474d;
        font-size: .86rem;
        font-weight: 600;
    }

    .about-summary-card__caption {
        display: block;
        margin-top: .32rem;
        color: #6b8287;
        font-size: .8rem;
        line-height: 1.45;
    }

    .about-section {
        padding: clamp(1.4rem, 3vw, 2.3rem);
    }

    .about-section-grid {
        display: grid;
        grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr);
        gap: clamp(1.25rem, 3vw, 2.5rem);
        align-items: start;
    }

    .about-copy-card {
        padding: clamp(1.3rem, 2vw, 1.7rem);
        border: 1px solid #dcebea;
        border-radius: 1.3rem;
        background: #ffffff;
    }

    .about-richtext {
        color: #5b7479;
        font-size: 1rem;
        line-height: 1.9;
    }

    .about-richtext p:last-child {
        margin-bottom: 0;
    }

    .about-mission-card {
        height: 100%;
        padding: clamp(1.4rem, 2vw, 1.9rem);
        border-radius: 1.4rem;
        background: linear-gradient(135deg, rgba(18, 157, 145, 0.08) 0%, rgba(18, 157, 145, 0.02) 100%);
        border: 1px solid rgba(18, 157, 145, 0.14);
    }

    .about-products-grid,
    .about-dealers-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .about-product-card,
    .about-dealer-card {
        height: 100%;
        border: 1px solid #deebea;
        border-radius: 1.25rem;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 .75rem 1.7rem rgba(17, 56, 61, 0.05);
        transition: border-color .25s ease, box-shadow .25s ease;
    }

    .about-product-card:hover,
    .about-dealer-card:hover {
        border-color: #c7e4e1;
        box-shadow: 0 .95rem 1.9rem rgba(17, 56, 61, 0.07);
    }

    .about-product-card img,
    .about-dealer-card img {
        width: 100%;
        height: 190px;
        object-fit: cover;
    }

    .about-product-card__body,
    .about-dealer-card__body {
        padding: 1.05rem 1.1rem 1.2rem;
    }

    .about-card-meta {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        margin-bottom: .7rem;
        color: #12857f;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .about-card-title {
        margin: 0 0 .5rem;
        color: #17393f;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .about-card-text {
        margin: 0;
        color: #5b7479;
        font-size: .88rem;
        line-height: 1.65;
    }

    .about-product-card__body .about-card-text {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .about-product-card__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        margin-top: 1rem;
        padding-top: .9rem;
        border-top: 1px solid #ecf3f2;
        color: #6a8287;
        font-size: .82rem;
    }

    .about-dealers-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .about-dealer-filters {
        display: grid;
        gap: 1rem;
        margin-top: 1.35rem;
        padding: 1rem 1.1rem;
        border: 1px solid #deebea;
        border-radius: 1.2rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfb 100%);
    }

    .about-dealer-filters__head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
    }

    .about-dealer-filters__title {
        margin: 0;
        color: #17393f;
        font-size: .98rem;
        font-weight: 700;
    }

    .about-dealer-filters__groups {
        display: grid;
        gap: .85rem;
    }

    .about-dealer-filter-group {
        display: grid;
        gap: .55rem;
    }

    .about-dealer-filter-group__label {
        color: #5d777c;
        font-size: .82rem;
        font-weight: 700;
    }

    .about-dealer-filter-group__chips {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
    }

    .about-dealer-filter-chip {
        appearance: none;
        border: 1px solid #d7e7e5;
        border-radius: 999px;
        background: #ffffff;
        color: #2d4e54;
        padding: .52rem .92rem;
        font-size: .86rem;
        font-weight: 600;
        line-height: 1;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease;
    }

    .about-dealer-filter-chip:hover,
    .about-dealer-filter-chip:focus-visible {
        border-color: #8fcfc9;
        color: #14625f;
        box-shadow: 0 0 0 .18rem rgba(18, 157, 145, 0.12);
        outline: none;
    }

    .about-dealer-filter-chip.is-active {
        border-color: #129d91;
        background: #129d91;
        color: #ffffff;
        box-shadow: 0 .55rem 1.1rem rgba(18, 157, 145, 0.16);
    }

    .about-dealer-filter-clear {
        width: fit-content;
        padding: 0;
        border: 0;
        background: transparent;
        color: #12857f;
        font-size: .86rem;
        font-weight: 700;
    }

    .about-dealer-filter-clear:hover,
    .about-dealer-filter-clear:focus-visible {
        color: #0f6c67;
        text-decoration: underline;
        outline: none;
    }

    .about-dealer-filter-empty {
        display: none;
    }

    .about-dealers-pagination {
        margin-top: 1.35rem;
    }

    .about-dealers-pagination nav {
        display: flex;
        justify-content: center;
    }

    .about-dealer-card {
        display: grid;
        grid-template-columns: 282px minmax(0, 1fr);
        align-items: stretch;
        border-radius: 1.45rem;
    }

    .about-dealer-card img {
        height: 100%;
        min-height: 220px;
        object-fit: cover;
    }

    .about-dealer-card__body {
        display: grid;
        gap: 1rem;
        align-items: start;
        padding: 1.25rem 1.25rem 1.35rem;
    }

    .about-dealer-card__header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: .9rem 1rem;
    }

    .about-dealer-card__lead {
        display: grid;
        gap: .45rem;
    }

    .about-dealer-card__location {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: #47656b;
        font-size: .92rem;
        font-weight: 600;
        line-height: 1.5;
    }

    .about-dealer-card__location::before {
        content: '';
        width: .55rem;
        height: .55rem;
        border-radius: 999px;
        background: #129d91;
        box-shadow: 0 0 0 .22rem rgba(18, 157, 145, 0.12);
    }

    .about-dealer-card__tags {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        justify-content: flex-start;
    }

    .about-dealer-card__stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .about-dealer-stat {
        min-width: 0;
        padding: .8rem .9rem;
        border: 1px solid #e5efee;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
    }

    .about-dealer-stat__label {
        display: block;
        margin-bottom: .3rem;
        color: #688085;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .about-dealer-stat__value {
        display: block;
        color: #1f4449;
        font-size: .92rem;
        font-weight: 600;
        line-height: 1.55;
        word-break: break-word;
    }

    .about-dealer-card__description {
        padding: .95rem 1rem;
        border: 1px solid #e9f1f0;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(18, 157, 145, 0.05) 0%, rgba(18, 157, 145, 0.02) 100%);
    }

    .about-dealer-card__description .about-card-text {
        color: #567176;
    }

    .about-dealer-card__description .about-card-text:last-child {
        margin-bottom: 0;
    }

    .about-timeline {
        position: relative;
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        padding-inline-start: 1.3rem;
    }

    .about-timeline::before {
        content: '';
        position: absolute;
        inset-inline-start: .32rem;
        top: .4rem;
        bottom: .4rem;
        width: 2px;
        background: linear-gradient(180deg, rgba(18, 157, 145, 0.12) 0%, rgba(18, 157, 145, 0.42) 45%, rgba(18, 157, 145, 0.12) 100%);
    }

    .about-timeline-item {
        position: relative;
        display: grid;
        grid-template-columns: 130px minmax(0, 1fr);
        gap: 1rem;
        min-height: 100%;
        padding: 0 0 0 1rem;
    }

    .about-timeline-item::before {
        content: '';
        position: absolute;
        inset-inline-start: -1.28rem;
        top: 1rem;
        width: .85rem;
        height: .85rem;
        border-radius: 999px;
        background: #129d91;
        box-shadow: 0 0 0 .28rem rgba(18, 157, 145, 0.1);
    }

    .about-timeline-item__year {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 72px;
        padding: .7rem .8rem;
        border-radius: 1.05rem;
        background: linear-gradient(180deg, rgba(18, 157, 145, 0.09) 0%, rgba(18, 157, 145, 0.03) 100%);
        border: 1px solid rgba(18, 157, 145, 0.14);
        color: #12857f;
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1;
    }

    .about-timeline-item__content {
        padding: 1rem 1rem 1.05rem;
        border: 1px solid #deebea;
        border-radius: 1.15rem;
        background: #ffffff;
        box-shadow: 0 .7rem 1.6rem rgba(17, 56, 61, 0.05);
    }

    .about-timeline-item__title {
        margin: 0 0 .45rem;
        color: #17393f;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .about-timeline-item__desc {
        margin: 0;
        color: #5b7479;
        font-size: .9rem;
        line-height: 1.7;
    }

    .about-empty {
        padding: 1.2rem 1.3rem;
        border: 1px dashed #cfe0de;
        border-radius: 1rem;
        color: #6f868b;
        text-align: center;
        background: #fbfdfd;
    }

    @media (max-width: 991.98px) {
        .about-summary-grid,
        .about-products-grid,
        .about-dealers-grid,
        .about-section-grid {
            grid-template-columns: 1fr;
        }

        .about-summary-grid {
            gap: 1px;
            background: #dfebea;
        }

        .about-summary-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 251, 0.98) 100%);
        }

        .about-summary-card:not(:last-child)::after {
            display: none;
        }

        .about-hero {
            min-height: 23rem;
        }

        .about-timeline {
            padding-inline-start: 1rem;
        }

        .about-timeline-item {
            grid-template-columns: 1fr;
            gap: .7rem;
            padding-inline-start: .7rem;
        }

        .about-timeline-item::before {
            inset-inline-start: -0.95rem;
            top: 1.1rem;
        }

        .about-timeline-item__year {
            width: fit-content;
            min-height: auto;
        }

        .about-dealer-card {
            grid-template-columns: 1fr;
        }

        .about-dealer-card img {
            height: 190px;
        }

        .about-dealer-card__body {
            grid-template-columns: 1fr;
        }

        .about-dealer-card__stats {
            grid-template-columns: 1fr;
        }

        .about-dealer-filters__head {
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="about-page py-4 py-lg-5">
    <div class="container">
        <div class="about-shell">
            @if($heroItems && count($heroItems) > 0)
                <section class="about-panel about-swiper">
                    <div class="swiper aboutHeroSwiper">
                        <div class="swiper-wrapper">
                            @foreach($heroItems as $item)
                                <div class="swiper-slide">
                                    <div class="about-hero">
                                        <div class="about-hero__media">
                                            <img src="{{ asset('storage/' . $item->image) }}"
                                                alt="{{ getTranslatedValue($item, 'heading', $item->heading) }}">
                                        </div>
                                        <div class="about-hero__overlay"></div>
                                        <div class="about-hero__content">
                                            <div class="about-hero__copy">
                                                <h1 class="about-title">{{ getTranslatedValue($item, 'heading', $item->heading) }}</h1>
                                                <p class="about-subtitle">
                                                    {{ getTranslatedValue($item, 'subheading', $item->subheading) }}
                                                </p>
                                                <div class="about-hero__meta">
                                                    <i class="fa fa-shield" aria-hidden="true"></i>
                                                    <span>{{ translate('Serving_customers_through_quality_reliability_and_long_term_partnerships') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                </section>
            @endif

            @if($whoWeAre)
                <section class="about-panel about-panel--soft">
                    <div class="about-section about-section-grid">
                        <div>
                            <span class="about-eyebrow">{{ translate('Who_we_are') }}</span>
                            <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                                {{ getTranslatedValue($whoWeAre, 'title', $whoWeAre->title) }}
                            </h2>
                            <p class="about-subtitle">{{ translate('Serving_customers_through_quality_reliability_and_long_term_partnerships') }}</p>
                        </div>
                        <div class="about-copy-card">
                            <div class="about-richtext">
                                {!! \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($whoWeAre, 'content', $whoWeAre->content)) !!}
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if($mission)
                <section class="about-panel">
                    <div class="about-section about-section-grid">
                        <div>
                            <span class="about-eyebrow">{{ translate('Mission_and_direction') }}</span>
                            <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                                {{ getTranslatedValue($mission, 'title', $mission->title) }}
                            </h2>
                        </div>
                        <div class="about-mission-card">
                            <div class="about-richtext">
                                {!! \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($mission, 'content', $mission->content)) !!}
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if($products->count())
                <section class="about-panel">
                    <div class="about-section">
                        <span class="about-eyebrow">{{ translate('Product_portfolio') }}</span>
                        <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                            {{ translate('our_core_products') }}
                        </h2>
                        <p class="about-subtitle">{{ translate('Built_for_reliable_growth_and_long_term_partnerships') }}</p>

                        <div class="about-products-grid mt-4">
                            @foreach($products as $product)
                                @php
                                    $aboutProductTitle = trim((string) getTranslatedValue($product, 'title', $product->title));
                                    $aboutProductDescription = trim((string) getTranslatedValue($product, 'description', $product->description));
                                    $aboutProductLabel = trim((string) getTranslatedValue($product, 'card_label', $product->card_label ?? ''));
                                    $aboutProductNote = trim((string) getTranslatedValue($product, 'card_note', $product->card_note ?? ''));
                                @endphp
                                <article class="about-product-card">
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $aboutProductTitle }}">
                                    <div class="about-product-card__body">
                                        <span class="about-card-meta">{{ translate('Product_portfolio') }}</span>
                                        @if($aboutProductTitle !== '')
                                            <h3 class="about-card-title">{{ $aboutProductTitle }}</h3>
                                        @endif
                                        @if($aboutProductDescription !== '')
                                            <p class="about-card-text">{{ $aboutProductDescription }}</p>
                                        @endif
                                        @if($aboutProductLabel !== '' || $aboutProductNote !== '')
                                            <div class="about-product-card__footer">
                                                @if($aboutProductLabel !== '')
                                                    <span>{{ $aboutProductLabel }}</span>
                                                @endif
                                                @if($aboutProductNote !== '')
                                                    <strong class="text-primary">{{ $aboutProductNote }}</strong>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if($timelines->count())
                <section class="about-panel about-panel--soft">
                    <div class="about-section">
                        <span class="about-eyebrow">{{ translate('Journey') }}</span>
                        <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                            {{ translate('milestones_over_the_years') }}
                        </h2>
                        <p class="about-subtitle">{{ translate('Executive_summary') }}</p>

                        <div class="about-timeline mt-4">
                            @foreach ($timelines as $timeline)
                                <article class="about-timeline-item">
                                    <span class="about-timeline-item__year">{{ $timeline->year }}</span>
                                    <div class="about-timeline-item__content">
                                        @if(getTranslatedValue($timeline, 'label', $timeline->label ?? ''))
                                            <div class="about-card-meta mb-2">{{ getTranslatedValue($timeline, 'label', $timeline->label ?? '') }}</div>
                                        @endif
                                        <h3 class="about-timeline-item__title">{{ getTranslatedValue($timeline, 'title', $timeline->title ?? '') }}</h3>
                                        <p class="about-timeline-item__desc">
                                            {{ getTranslatedValue($timeline, 'description', $timeline->description ?? '') }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <section class="about-panel">
                <div class="about-section">
                    @php
                        $dealerQuickFilterSource = $dealerFilterSource ?? collect();
                        $quickPartnerTypes = $dealerQuickFilterSource
                            ->filter(fn ($dealer) => $dealer->show_partner_type_filter)
                            ->map(fn ($dealer) => trim((string) getTranslatedValue($dealer, 'partner_type', $dealer->partner_type ?? '')))
                            ->filter()
                            ->unique()
                            ->values();
                        $quickLocations = $dealerQuickFilterSource
                            ->filter(fn ($dealer) => $dealer->show_location_filter)
                            ->map(fn ($dealer) => trim((string) getTranslatedValue($dealer, 'location', $dealer->location ?? '')))
                            ->filter()
                            ->unique()
                            ->values();
                        $hasQuickFilters = $quickPartnerTypes->isNotEmpty() || $quickLocations->isNotEmpty();
                    @endphp
                    <span class="about-eyebrow">{{ translate('Dealer_network') }}</span>
                    <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                        {{ translate('our_trusted_dealers') }}
                    </h2>
                    <p class="about-subtitle">{{ translate('Trusted_partner_locations_across_key_markets') }}</p>

                    @if($dealers->count())
                        @if($hasQuickFilters)
                            <div class="about-dealer-filters" data-dealer-filters>
                                <div class="about-dealer-filters__head">
                                    <p class="about-dealer-filters__title">{{ translate('Quick_filters') }}</p>
                                    <button type="button" class="about-dealer-filter-clear" data-filter-clear>{{ translate('clear') }}</button>
                                </div>
                                <div class="about-dealer-filters__groups">
                                    @if($quickPartnerTypes->isNotEmpty())
                                        <div class="about-dealer-filter-group">
                                            <span class="about-dealer-filter-group__label">{{ translate('partner_type') }}</span>
                                            <div class="about-dealer-filter-group__chips">
                                                <button type="button" class="about-dealer-filter-chip is-active" data-filter-group="partner_type" data-filter-value="">
                                                    {{ translate('all') }}
                                                </button>
                                                @foreach($quickPartnerTypes as $partnerType)
                                                    <button type="button" class="about-dealer-filter-chip" data-filter-group="partner_type" data-filter-value="{{ $partnerType }}">
                                                        {{ $partnerType }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if($quickLocations->isNotEmpty())
                                        <div class="about-dealer-filter-group">
                                            <span class="about-dealer-filter-group__label">{{ translate('location') }}</span>
                                            <div class="about-dealer-filter-group__chips">
                                                <button type="button" class="about-dealer-filter-chip is-active" data-filter-group="location" data-filter-value="">
                                                    {{ translate('all') }}
                                                </button>
                                                @foreach($quickLocations as $location)
                                                    <button type="button" class="about-dealer-filter-chip" data-filter-group="location" data-filter-value="{{ $location }}">
                                                        {{ $location }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="about-dealers-grid mt-4">
                            @foreach ($dealers as $dealer)
                                @php
                                    $dealerName = trim((string) getTranslatedValue($dealer, 'dealer_name', $dealer->dealer_name ?? ''));
                                    $dealerPartnerType = trim((string) getTranslatedValue($dealer, 'partner_type', $dealer->partner_type ?? ''));
                                    $dealerLocation = trim((string) getTranslatedValue($dealer, 'location', $dealer->location ?? ''));
                                    $dealerCoverageArea = trim((string) getTranslatedValue($dealer, 'coverage_area', $dealer->coverage_area ?? ''));
                                    $dealerDescriptionHtml = (string) getTranslatedValue($dealer, 'description', $dealer->description ?? '');
                                    $dealerDescriptionPlain = richTextToPlainText($dealerDescriptionHtml);
                                @endphp
                                <article class="about-dealer-card" data-dealer-card data-partner-type="{{ $dealerPartnerType }}" data-location="{{ $dealerLocation }}">
                                    <img src="{{ $dealer->image ? asset('storage/' . $dealer->image) : asset('images/default-dealer.jpg') }}"
                                        alt="{{ $dealerName }}">
                                    <div class="about-dealer-card__body">
                                        <div class="about-dealer-card__header">
                                            <div class="about-dealer-card__lead">
                                                <span class="about-card-meta">{{ translate('Dealer_network') }}</span>
                                                @if($dealerName !== '')
                                                    <h3 class="about-card-title">{{ $dealerName }}</h3>
                                                @endif
                                                @if($dealerLocation !== '')
                                                    <span class="about-dealer-card__location">{{ $dealerLocation }}</span>
                                                @endif
                                            </div>
                                            <div class="about-dealer-card__tags">
                                                <span class="about-dealer-badge">{{ translate('Trusted_dealers') }}</span>
                                                @if($dealerPartnerType !== '')
                                                    <span class="about-dealer-badge">{{ $dealerPartnerType }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="about-dealer-card__stats">
                                            @if($dealerPartnerType !== '')
                                                <div class="about-dealer-stat">
                                                    <span class="about-dealer-stat__label">{{ translate('partner_type') }}</span>
                                                    <span class="about-dealer-stat__value">{{ $dealerPartnerType }}</span>
                                                </div>
                                            @endif
                                            @if($dealerCoverageArea !== '')
                                                <div class="about-dealer-stat">
                                                    <span class="about-dealer-stat__label">{{ translate('coverage_area') }}</span>
                                                    <span class="about-dealer-stat__value">{{ $dealerCoverageArea }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($dealerDescriptionPlain !== '')
                                            <div class="about-dealer-card__description">
                                                <span class="about-dealer-stat__label">{{ translate('Details') }}</span>
                                                <div class="about-card-text mb-0">{!! \App\Support\CmsContentSanitizer::sanitizeRichText($dealerDescriptionHtml) !!}</div>
                                            </div>
                                        @endif

                                        @if($dealerLocation === '' && $dealerPartnerType === '' && $dealerCoverageArea === '' && $dealerDescriptionPlain === '')
                                            <p class="about-card-text mb-0">{{ translate('Trusted_partner_locations_across_key_markets') }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div class="about-empty about-dealer-filter-empty mt-4" data-dealer-filter-empty>{{ translate('no_matching_dealers_found') }}</div>
                        @if(method_exists($dealers, 'links'))
                            <div class="about-dealers-pagination">
                                {{ $dealers->withQueryString()->links() }}
                            </div>
                        @endif
                    @else
                        <div class="about-empty mt-4">{{ translate('no_dealers_found') }}</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const heroSwiper = document.querySelector('.aboutHeroSwiper');
        if (heroSwiper) {
            new Swiper(heroSwiper, {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                speed: 850,
                effect: 'fade',
                fadeEffect: {
                    crossFade: true,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                }
            });
        }

        const filterShell = document.querySelector('[data-dealer-filters]');
        const dealerCards = Array.from(document.querySelectorAll('[data-dealer-card]'));
        const emptyState = document.querySelector('[data-dealer-filter-empty]');

        if (!filterShell || dealerCards.length === 0) {
            return;
        }

        const activeFilters = {
            partner_type: '',
            location: '',
        };

        const applyDealerFilters = () => {
            let hasVisibleCards = false;

            dealerCards.forEach((card) => {
                const partnerType = card.dataset.partnerType || '';
                const location = card.dataset.location || '';
                const matchesPartnerType = !activeFilters.partner_type || partnerType === activeFilters.partner_type;
                const matchesLocation = !activeFilters.location || location === activeFilters.location;

                const isVisible = matchesPartnerType && matchesLocation;
                card.style.display = isVisible ? '' : 'none';

                if (isVisible) {
                    hasVisibleCards = true;
                }
            });

            if (emptyState) {
                emptyState.style.display = hasVisibleCards ? 'none' : 'block';
            }
        };

        filterShell.querySelectorAll('[data-filter-group]').forEach((button) => {
            button.addEventListener('click', function() {
                const group = this.dataset.filterGroup;
                const value = this.dataset.filterValue || '';

                activeFilters[group] = value;

                filterShell.querySelectorAll(`[data-filter-group="${group}"]`).forEach((chip) => {
                    chip.classList.toggle('is-active', chip === this);
                });

                applyDealerFilters();
            });
        });

        const clearButton = filterShell.querySelector('[data-filter-clear]');
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                Object.keys(activeFilters).forEach((group) => {
                    activeFilters[group] = '';
                    filterShell.querySelectorAll(`[data-filter-group="${group}"]`).forEach((chip, index) => {
                        chip.classList.toggle('is-active', index === 0);
                    });
                });

                applyDealerFilters();
            });
        }
    });
</script>
@endpush
