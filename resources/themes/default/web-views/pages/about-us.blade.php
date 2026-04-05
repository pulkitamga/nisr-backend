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
        gap: .85rem;
    }

    .about-dealer-card {
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr);
        align-items: stretch;
    }

    .about-dealer-card img {
        height: 100%;
        min-height: 100%;
        object-fit: cover;
    }

    .about-dealer-card__body {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(220px, .9fr);
        gap: 1rem;
        align-items: start;
    }

    .about-dealer-directory {
        display: grid;
        gap: .55rem;
        margin-top: .7rem;
    }

    .about-dealer-directory__row {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: .35rem .65rem;
        align-items: start;
        color: #5b7479;
        font-size: .87rem;
        line-height: 1.55;
    }

    .about-dealer-directory__label {
        color: #29474d;
        font-weight: 700;
        white-space: nowrap;
    }

    .about-dealer-card__footer {
        display: grid;
        gap: .75rem;
        align-content: start;
        padding-inline-start: 1rem;
        border-inline-start: 1px solid #ecf3f2;
    }

    .about-dealer-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .7rem;
        border-radius: 999px;
        background: rgba(18, 157, 145, 0.09);
        color: #12857f;
        font-size: .8rem;
        font-weight: 700;
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

        .about-dealer-card__footer {
            padding-inline-start: 0;
            padding-top: .9rem;
            border-inline-start: 0;
            border-top: 1px solid #ecf3f2;
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

                    <div class="about-summary-grid">
                        <div class="about-summary-card">
                            <span class="about-summary-card__value">{{ $products->count() }}</span>
                            <span class="about-summary-card__label">{{ translate('Active_solutions') }}</span>
                            <span class="about-summary-card__caption">{{ translate('Product_portfolio') }}</span>
                        </div>
                        <div class="about-summary-card">
                            <span class="about-summary-card__value">{{ $timelines->count() }}</span>
                            <span class="about-summary-card__label">{{ translate('Key_milestones') }}</span>
                            <span class="about-summary-card__caption">{{ translate('Journey') }}</span>
                        </div>
                        <div class="about-summary-card">
                            <span class="about-summary-card__value">{{ $dealers->count() }}</span>
                            <span class="about-summary-card__label">{{ translate('Trusted_dealers') }}</span>
                            <span class="about-summary-card__caption">{{ translate('Dealer_network') }}</span>
                        </div>
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
                                <article class="about-product-card">
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ getTranslatedValue($product, 'title', $product->title) }}">
                                    <div class="about-product-card__body">
                                        <span class="about-card-meta">{{ translate('Product_portfolio') }}</span>
                                        <h3 class="about-card-title">{{ getTranslatedValue($product, 'title', $product->title) }}</h3>
                                        <p class="about-card-text">
                                            {{ getTranslatedValue($product, 'description', $product->description) }}
                                        </p>
                                        <div class="about-product-card__footer">
                                            <span>{{ getTranslatedValue($product, 'card_label', $product->card_label ?? '') ?: translate('Active_solutions') }}</span>
                                            <strong class="text-primary">{{ getTranslatedValue($product, 'card_note', $product->card_note ?? '') ?: translate('Built_for_reliable_growth_and_long_term_partnerships') }}</strong>
                                        </div>
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
                    <span class="about-eyebrow">{{ translate('Dealer_network') }}</span>
                    <h2 class="about-title" style="font-size: clamp(1.8rem, 3vw, 2.7rem);">
                        {{ translate('our_trusted_dealers') }}
                    </h2>
                    <p class="about-subtitle">{{ translate('Trusted_partner_locations_across_key_markets') }}</p>

                    @if($dealers->count())
                        <div class="about-dealers-grid mt-4">
                            @foreach ($dealers as $dealer)
                                <article class="about-dealer-card">
                                    <img src="{{ $dealer->image ? asset('storage/' . $dealer->image) : asset('images/default-dealer.jpg') }}"
                                        alt="{{ getTranslatedValue($dealer, 'dealer_name', $dealer->dealer_name ?? '') }}">
                                    <div class="about-dealer-card__body">
                                        <div>
                                            <span class="about-card-meta">{{ translate('Dealer_network') }}</span>
                                            <h3 class="about-card-title">{{ getTranslatedValue($dealer, 'dealer_name', $dealer->dealer_name ?? '') }}</h3>

                                            <div class="about-dealer-directory">
                                                <div class="about-dealer-directory__row">
                                                    <span class="about-dealer-directory__label">{{ translate('partner_type') }}</span>
                                                    <span>{{ getTranslatedValue($dealer, 'partner_type', $dealer->partner_type ?? '') ?: translate('Trusted_dealers') }}</span>
                                                </div>
                                                <div class="about-dealer-directory__row">
                                                    <span class="about-dealer-directory__label">{{ translate('Location') }}</span>
                                                    <span>{{ getTranslatedValue($dealer, 'location', $dealer->location ?? '') ?: translate('not_available') }}</span>
                                                </div>
                                                <div class="about-dealer-directory__row">
                                                    <span class="about-dealer-directory__label">{{ translate('coverage_area') }}</span>
                                                    <span>{{ getTranslatedValue($dealer, 'coverage_area', $dealer->coverage_area ?? '') ?: translate('not_available') }}</span>
                                                </div>
                                                <div class="about-dealer-directory__row">
                                                    <span class="about-dealer-directory__label">{{ translate('Details') }}</span>
                                                    <span>{{ getTranslatedValue($dealer, 'description', $dealer->description ?? '') ?: translate('not_available') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="about-dealer-card__footer">
                                            <span class="about-dealer-badge">{{ translate('Trusted_dealers') }}</span>
                                            <span class="about-card-text">{{ translate('Trusted_partner_locations_across_key_markets') }}</span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
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
        if (!heroSwiper) {
            return;
        }

        new Swiper(heroSwiper, {
            loop: true,
            autoplay: {
                delay: 5000,
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
    });
</script>
@endpush
