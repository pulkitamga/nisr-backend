@extends('layouts.front-end.app')

@section('title', translate('home'))

@push('css_or_js')
<meta name="robots" content="index, follow">
<meta property="og:image" content="{{$web_config['web_logo']['path']}}" />
<meta property="og:title" content="{{ translate('welcome_to') }} {{$web_config['company_name']}} {{ translate('home') }}" />
<meta property="og:url" content="{{env('APP_URL')}}">
<meta name="description"
    content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<meta property="og:description"
    content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">

<meta property="twitter:card" content="{{$web_config['web_logo']['path']}}" />
<meta property="twitter:title" content="{{ translate('welcome_to') }} {{$web_config['company_name']}} {{ translate('home') }}" />
<meta property="twitter:url" content="{{env('APP_URL')}}">
<meta property="twitter:description"
    content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">

<link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/home.css')}}" />
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}">

<style>
    .home-page {
        --home-ink: #153940;
        --home-ink-soft: #4f6d73;
        --home-accent: #12857f;
        --home-accent-deep: #0f4d58;
        --home-accent-soft: #eaf7f5;
        --home-accent-mist: #f3fbfa;
        --home-accent-line: rgba(18, 133, 127, 0.12);
        --home-accent-line-strong: rgba(18, 133, 127, 0.2);
        --home-panel-shadow: 0 1rem 2.4rem rgba(17, 56, 61, 0.055);
        --home-card-shadow: 0 .85rem 1.9rem rgba(17, 56, 61, 0.055);
        position: relative;
        isolation: isolate;
        background:
            radial-gradient(circle at top left, rgba(18, 133, 127, 0.06), transparent 24%),
            radial-gradient(circle at 92% 14%, rgba(15, 77, 88, 0.05), transparent 18%),
            linear-gradient(180deg, #f8fbfb 0%, #ffffff 18%, #f4fafa 100%);
    }

    .home-page::before,
    .home-page::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: -1;
        filter: blur(22px);
        opacity: .55;
    }

    .home-page::before {
        inset-inline-start: -8rem;
        inset-block-start: 14rem;
        width: 20rem;
        height: 20rem;
        background: radial-gradient(circle, rgba(18, 133, 127, 0.12) 0%, rgba(18, 133, 127, 0) 70%);
    }

    .home-page::after {
        inset-inline-end: -7rem;
        inset-block-start: 58rem;
        width: 18rem;
        height: 18rem;
        background: radial-gradient(circle, rgba(15, 77, 88, 0.1) 0%, rgba(15, 77, 88, 0) 72%);
    }

    .home-shell {
        display: grid;
        gap: clamp(1.2rem, 2.3vw, 2rem);
    }

    .home-panel {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.7rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(251, 253, 253, 0.98) 100%);
        box-shadow: var(--home-panel-shadow);
    }

    .home-panel::before {
        content: '';
        position: absolute;
        inset-inline: clamp(1.3rem, 4vw, 2.3rem);
        inset-block-start: 0;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(18, 133, 127, 0) 0%, rgba(18, 133, 127, 0.26) 20%, rgba(18, 133, 127, 0.14) 80%, rgba(18, 133, 127, 0) 100%);
        opacity: .8;
    }

    .home-panel--soft {
        background: linear-gradient(135deg, #f3fafa 0%, #ffffff 52%, #edf8f6 100%);
    }

    .home-section {
        padding: clamp(1.45rem, 3vw, 2.35rem);
    }

    .home-section-head,
    .home-corporate-head {
        max-width: 48rem;
        margin: 0 auto 1.9rem;
        text-align: center;
    }

    .home-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .85rem;
        padding: .45rem .92rem;
        border-radius: 999px;
        border: 1px solid rgba(18, 133, 127, 0.08);
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.12) 0%, rgba(255, 255, 255, 0.8) 100%);
        color: var(--home-accent);
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .03em;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .home-title {
        margin: 0;
        color: var(--home-ink);
        font-size: clamp(1.9rem, 4vw, 3.1rem);
        font-weight: 700;
        line-height: 1.12;
    }

    .home-title--section {
        font-size: clamp(1.65rem, 3vw, 2.35rem);
    }

    .home-subtitle {
        margin: .9rem 0 0;
        color: var(--home-ink-soft);
        font-size: clamp(.98rem, 1.4vw, 1.08rem);
        line-height: 1.75;
    }

    .home-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: .78rem 1.35rem;
        border-radius: 999px;
        background: var(--home-accent);
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 .9rem 1.6rem rgba(18, 133, 127, 0.18);
        transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .home-cta:hover {
        background: #107a74;
        transform: translateY(-1px);
        box-shadow: 0 1rem 1.9rem rgba(18, 133, 127, 0.24);
        text-decoration: none;
    }

    .home-cta.bg-white {
        background: #ffffff !important;
        color: var(--home-accent) !important;
        border: 1px solid var(--home-accent-line);
        box-shadow: 0 .5rem 1rem rgba(17, 56, 61, 0.04);
    }

    .home-cta.bg-white:hover {
        background: #eef8f6 !important;
        color: #107a74 !important;
        box-shadow: 0 .65rem 1.15rem rgba(17, 56, 61, 0.06);
    }

    .home-hero-panel {
        padding: 0;
    }

    .main-banner-slider {
        min-height: clamp(25rem, 48vw, 33rem);
        max-height: none !important;
        border-radius: 1.6rem;
        overflow: hidden;
    }

    .banner-slide {
        border-radius: 1.6rem;
    }

    .banner-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(12, 36, 42, 0.36) 0%, rgba(12, 36, 42, 0.18) 40%, rgba(12, 36, 42, 0.06) 100%),
            linear-gradient(180deg, rgba(18, 157, 145, 0.08) 0%, rgba(18, 157, 145, 0.12) 100%);
    }

    .carousel-indicators {
        position: absolute;
        inset-inline-end: 1.3rem;
        inset-inline-start: auto;
        bottom: 1.2rem;
        z-index: 4;
        display: flex;
        gap: .55rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .carousel-indicators li {
        width: .72rem;
        height: .72rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.45);
    }

    .carousel-indicators li.active {
        background: #ffffff;
    }

    .home-hero-copy {
        max-width: min(100%, 35rem);
        padding: clamp(1.25rem, 2vw, 1.8rem);
        border: 1px solid rgba(255, 255, 255, 0.66);
        border-radius: 1.35rem;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 1.2rem 2.4rem rgba(15, 54, 58, 0.14);
        backdrop-filter: blur(8px);
    }

    .home-hero-copy .home-title,
    .home-hero-copy .home-subtitle {
        color: #17393f;
    }

    .home-trust-line {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-top: 1rem;
        color: #547074;
        font-size: .88rem;
        font-weight: 600;
    }

    .home-products-slider,
    .home-categories-slider,
    .home-reviews-slider {
        padding-bottom: .35rem;
    }

    .home-product-card,
    .home-category-card,
    .home-value-card,
    .home-story-card,
    .home-blog-card,
    .home-review-card {
        position: relative;
        height: 100%;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.3rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(251, 253, 253, 0.98) 100%);
        box-shadow: var(--home-card-shadow);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
    }

    .home-product-card::after,
    .home-category-card::after,
    .home-value-card::after,
    .home-story-card::after,
    .home-blog-card::after,
    .home-review-card::after {
        content: '';
        position: absolute;
        inset-inline: 1rem;
        inset-block-start: 0;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(18, 133, 127, 0) 0%, rgba(18, 133, 127, 0.24) 35%, rgba(18, 133, 127, 0.1) 100%);
        opacity: .7;
        pointer-events: none;
    }

    .home-product-card,
    .home-category-card,
    .home-blog-card {
        overflow: hidden;
    }

    .home-product-card {
        padding: 0;
        color: var(--home-ink);
        display: flex;
        flex-direction: column;
        gap: 0;
        transition: border-color .22s ease, box-shadow .22s ease;
    }

    .home-product-card>.relative {
        display: flex;
        flex: 1;
        flex-direction: column;
    }

    .home-product-thumb {
        position: relative;
        min-height: clamp(14.5rem, 25vw, 17rem);
        border-radius: 1.3rem 1.3rem 0 0;
        overflow: hidden;
        background:
            radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.38) 0, rgba(255, 255, 255, 0) 28%),
            linear-gradient(180deg, #deefed 0%, #cfe6e3 46%, #a8c8c5 100%);
    }

    .home-product-thumb__media {
        position: absolute;
        inset: 0;
        display: block;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, rgba(15, 77, 88, 0.16) 100%);
    }

    .home-product-thumb__media::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(11, 53, 58, 0) 42%, rgba(11, 53, 58, 0.28) 100%);
        pointer-events: none;
    }

    .home-product-thumb img,
    .home-product-thumb__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        transform: scale(1.05);
        transition: transform .28s ease;
    }

    .home-product-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 1.05rem 1.05rem 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
    }

    .home-product-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .6rem;
    }

    .home-product-status,
    .home-category-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .32rem .62rem;
        border-radius: 999px;
        background: var(--home-accent-soft);
        color: #4f6e73;
        font-size: .73rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .home-badge {
        position: absolute;
        top: .85rem;
        inset-inline-start: .85rem;
        padding: .35rem .6rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.86);
        color: var(--home-accent);
        border: 1px solid rgba(255, 255, 255, 0.5);
        font-size: .72rem;
        font-weight: 700;
    }

    .home-icon-button {
        position: absolute;
        top: .85rem;
        inset-inline-end: .85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
        color: #35595f !important;
        border: 1px solid #e4efee;
        box-shadow: 0 .4rem .9rem rgba(17, 56, 61, 0.08);
    }

    .home-card-meta {
        display: inline-flex;
        align-items: center;
        gap: .36rem;
        margin-bottom: .7rem;
        color: var(--home-accent);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .home-product-card .home-card-meta {
        margin-bottom: 0;
        letter-spacing: .08em;
        text-transform: uppercase;
        font-size: .72rem;
    }

    .home-category-card .home-card-meta {
        margin-bottom: 0;
        text-transform: none;
        letter-spacing: 0;
    }

    .home-card-title {
        display: block;
        margin: 0 0 .45rem;
        color: var(--home-ink);
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .home-card-title--clamp {
        display: -webkit-box;
        min-height: 2.85rem;
        overflow: hidden;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .home-card-copy {
        color: var(--home-ink-soft);
        font-size: .9rem;
        line-height: 1.65;
    }

    .home-product-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        margin-bottom: .7rem;
    }

    .home-product-metric {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .32rem .62rem;
        border: 1px solid #e5efee;
        border-radius: 999px;
        background: #f7fbfb;
        color: #587176;
        font-size: .78rem;
        font-weight: 600;
    }

    .home-price-line {
        display: flex;
        align-items: baseline;
        gap: .5rem;
        margin-top: .85rem;
    }

    .home-price-line del {
        color: #97aab0;
        font-size: .88rem;
    }

    .home-price-current {
        color: var(--home-accent);
        font-size: 1.05rem;
        font-weight: 700;
    }

    .home-product-rating {
        position: absolute;
        inset-inline-start: 1rem;
        inset-block-end: .95rem;
        display: inline-flex;
        align-items: center;
        gap: .48rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 .65rem 1.25rem rgba(18, 54, 58, 0.12);
        color: #5a7378;
        font-size: .8rem;
        font-weight: 700;
        z-index: 1;
    }

    .home-product-rating .text-warning {
        gap: .1rem !important;
    }

    .home-product-footer,
    .home-category-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .9rem;
        margin-top: auto;
        padding-top: .75rem;
        border-top: 1px solid #edf4f3;
    }

    .home-category-card {
        padding: 1.15rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfb 100%);
    }

    .home-product-card:hover,
    .home-category-card:hover,
    .home-value-card:hover,
    .home-story-card:hover,
    .home-blog-card:hover,
    .home-review-card:hover {
        transform: translateY(-4px);
        border-color: var(--home-accent-line-strong);
        box-shadow: 0 1.15rem 2.35rem rgba(17, 56, 61, 0.09);
    }

    .home-product-card:hover .home-product-thumb__image {
        transform: scale(1.1);
    }

    .home-category-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 5rem;
        width: 5rem;
        height: 5rem;
        padding: .85rem;
        border: 1px solid rgba(18, 133, 127, 0.12);
        border-radius: 1.15rem;
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.08) 0%, #ffffff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .home-category-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        align-items: center;
        gap: .4rem;
        width: 100%;
        text-align: center;
    }

    .home-category-topline {
        display: flex;
        align-items: center;
        justify-content: center;
        width: auto;
        gap: .75rem;
    }

    .home-category-card img {
        width: 100%;
        height: 3rem;
        object-fit: contain;
        margin-bottom: 0;
    }

    .home-link-cta {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .75rem;
        color: var(--home-accent) !important;
        font-weight: 700;
        text-decoration: none;
    }

    .home-link-cta--quiet {
        margin-top: 0;
        font-size: .88rem;
    }

    .home-category-footer {
        justify-content: center;
        width: 100%;
    }

    html[dir="rtl"] .home-link-cta .bi-arrow-right {
        transform: rotate(180deg);
    }

    .home-value-card {
        padding: 1.25rem 1.05rem;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfb 100%);
    }

    .home-value-card .icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        margin-bottom: 1rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(18, 133, 127, 0.12);
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.1) 0%, rgba(255, 255, 255, 0.92) 100%);
    }

    .home-value-card .icon-svg {
        width: 1.8rem;
        height: 1.8rem;
        color: #12857f !important;
    }

    .home-match-panel {
        position: relative;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(242, 250, 249, 0.94) 0%, rgba(255, 255, 255, 0.98) 48%, rgba(239, 249, 247, 0.98) 100%);
    }

    .home-match-panel::after {
        content: '';
        position: absolute;
        inset-inline: 8%;
        inset-block-start: 0;
        height: 9rem;
        background: radial-gradient(circle at 50% 0%, rgba(18, 133, 127, 0.12) 0%, rgba(18, 133, 127, 0) 72%);
        pointer-events: none;
    }

    .home-match-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.04fr) minmax(20rem, .96fr);
        gap: clamp(1rem, 2vw, 1.55rem);
        align-items: stretch;
    }

    .home-match-copy,
    .home-filter-card {
        min-height: 100%;
        padding: clamp(1.35rem, 2.4vw, 2rem);
        border: 1px solid var(--home-accent-line);
        border-radius: 1.45rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(247, 251, 250, 0.98) 100%);
        box-shadow: var(--home-card-shadow);
    }

    .home-match-copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 1.35rem;
        text-align: center;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(243, 250, 249, 0.96) 100%);
    }

    .home-match-copy .home-title,
    .home-match-copy .home-subtitle {
        color: var(--home-ink);
    }

    .home-match-intro {
        max-width: 34rem;
    }

    .home-match-intro .home-title {
        margin-bottom: .9rem;
    }

    .home-match-chip-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: .7rem;
        width: 100%;
    }

    .home-match-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 7.25rem;
        padding: .72rem 1rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.08) 0%, rgba(255, 255, 255, 0.96) 100%);
        color: var(--home-accent-deep);
        font-size: .92rem;
        font-weight: 700;
        line-height: 1.2;
        box-shadow: 0 .55rem 1rem rgba(15, 54, 58, 0.04);
    }

    .home-match-flow {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        width: 100%;
        max-width: 34rem;
    }

    .home-match-flow__item {
        display: grid;
        justify-items: center;
        gap: .55rem;
        padding: .95rem .85rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(243, 250, 249, 0.96) 100%);
    }

    .home-match-flow__index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: var(--home-accent);
        color: #ffffff;
        font-size: .84rem;
        font-weight: 700;
    }

    .home-match-flow__label {
        color: var(--home-ink);
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .home-match-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .8rem;
        max-width: 32rem;
        padding: .95rem 1rem;
        border: 1px solid rgba(15, 77, 88, 0.1);
        border-radius: 1.05rem;
        background: linear-gradient(135deg, rgba(241, 250, 249, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
        text-align: start;
    }

    .home-match-note__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: .8rem;
        background: rgba(18, 133, 127, 0.1);
        color: var(--home-accent);
        flex: 0 0 auto;
    }

    .home-match-note__icon svg {
        width: 1rem;
        height: 1rem;
    }

    .home-match-note strong {
        display: block;
        margin-bottom: .18rem;
        color: var(--home-ink);
        font-size: .95rem;
        font-weight: 700;
    }

    .home-match-note span {
        color: var(--home-ink-soft);
        font-size: .9rem;
        line-height: 1.65;
    }

    .home-filter-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .home-filter-card .form-label {
        color: var(--home-ink);
        font-weight: 700;
        margin-bottom: .4rem;
    }

    .home-filter-card .form-select {
        min-height: 3.2rem;
        border-color: var(--home-accent-line);
        border-radius: 1rem;
        background-color: #fff;
        box-shadow: none;
    }

    .home-filter-card .form-select:focus {
        border-color: rgba(18, 133, 127, 0.45);
        box-shadow: 0 0 0 .22rem rgba(18, 133, 127, 0.12);
    }

    .home-filter-card .home-cta {
        width: 100%;
        margin-top: .1rem;
    }

    .home-filter-card__eyebrow {
        align-self: center;
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.12) 0%, rgba(255, 255, 255, 0.82) 100%);
    }

    .home-filter-card__intro {
        text-align: center;
    }

    .home-filter-card__intro .home-subtitle {
        color: var(--home-ink-soft);
        margin-bottom: 0;
    }

    .home-filter-fields {
        display: grid;
        gap: .85rem;
    }

    .home-filter-field {
        padding: .9rem;
        border: 1px solid rgba(18, 133, 127, 0.1);
        border-radius: 1.1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(244, 250, 249, 0.92) 100%);
    }

    .home-filter-title {
        margin: 0;
        color: var(--home-ink);
        font-size: 1.18rem;
        font-weight: 700;
        line-height: 1.35;
        text-align: center;
    }

    .home-story-card {
        padding: 1.15rem;
    }

    .home-story-card img {
        width: 100%;
        height: 210px;
        object-fit: cover;
        border-radius: 1rem;
        margin-bottom: 1rem;
    }

    .home-story-card--split {
        overflow: hidden;
    }

    .home-story-card--split img {
        height: 100%;
        min-height: 100%;
        margin-bottom: 0;
        border-radius: 0;
    }

    .home-wholesale-panel,
    .home-download-panel {
        background: linear-gradient(135deg, #163b41 0%, #1b5a61 48%, #129d91 100%);
        color: #ffffff;
    }

    .home-wholesale-panel .home-title,
    .home-wholesale-panel .home-subtitle,
    .home-download-panel .home-title,
    .home-download-panel .home-subtitle {
        color: #ffffff;
    }

    .home-blog-feature {
        overflow: hidden;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.3rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: var(--home-card-shadow);
    }

    .home-blog-feature img {
        width: 100%;
        height: 23rem;
        object-fit: cover;
    }

    .home-blog-feature__body,
    .home-blog-card__body {
        padding: 1.1rem 1.15rem 1.2rem;
    }

    .home-blog-card {
        display: grid;
        grid-template-columns: 9.5rem minmax(0, 1fr);
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
    }

    .home-blog-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .home-review-card {
        display: grid;
        grid-template-columns: minmax(150px, .9fr) minmax(0, 1.1fr);
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
    }

    .home-review-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .home-review-card__body {
        padding: 1.3rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(245, 251, 250, 0.94) 100%);
    }

    .home-review-rating {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .65rem;
        padding: .35rem .7rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 999px;
        background: var(--home-accent-mist);
        color: #577074;
        font-size: .82rem;
        font-weight: 700;
    }

    .home-review-rating .text-warning {
        gap: .12rem !important;
    }

    .home-quote {
        color: var(--home-accent);
        font-size: 1.8rem;
        line-height: 1;
    }

    .home-store-buttons img {
        max-height: 48px;
        width: auto;
    }

    .home-device-art {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        min-height: 100%;
    }

    .home-device-art img,
    .home-wholesale-art img {
        max-width: 100%;
        object-fit: contain;
    }

    .home-corporate-grid {
        display: grid;
        gap: clamp(1rem, 2vw, 1.5rem);
    }

    .home-family-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: clamp(1rem, 2vw, 1.4rem);
    }

    .home-family-card {
        display: grid;
        grid-template-rows: minmax(18rem, clamp(18rem, 30vw, 22rem)) minmax(0, 1fr);
        min-height: 100%;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.35rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: var(--home-card-shadow);
        overflow: hidden;
        transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
    }

    .home-family-card:hover,
    .home-capability-item:hover {
        transform: translateY(-4px);
        border-color: var(--home-accent-line-strong);
        box-shadow: 0 1.2rem 2.4rem rgba(17, 56, 61, 0.1);
    }

    .home-family-media {
        position: relative;
        min-height: clamp(18rem, 30vw, 22rem);
        overflow: hidden;
        background:
            radial-gradient(circle at 50% 36%, rgba(177, 228, 224, 0.44) 0%, rgba(177, 228, 224, 0.16) 26%, rgba(255, 255, 255, 0) 74%),
            linear-gradient(180deg, #f4faf9 0%, #fafdfd 100%);
    }

    .home-family-media-link {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        text-decoration: none;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    .home-family-media img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transform: scale(1.22);
        transform-origin: center center;
        transition: transform .24s ease;
    }

    .home-family-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 1.25rem 1.25rem 1.35rem;
    }

    .home-family-note {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e5efee;
        color: var(--home-accent-deep);
        font-size: .92rem;
        font-weight: 700;
        line-height: 1.6;
    }

    .home-family-card:hover .home-family-media img {
        transform: scale(1.28);
    }

    .home-family-title-link {
        display: inline-block;
        width: 100%;
        color: inherit;
        text-decoration: none;
        cursor: pointer;
    }

    .home-family-title-link:hover {
        color: var(--home-accent);
    }

    .home-family-card .home-card-title {
        font-size: 1.22rem;
        line-height: 1.45;
    }

    .home-family-card .home-card-meta {
        font-size: .98rem;
        font-weight: 800;
    }

    .home-capabilities-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(0, .98fr);
        gap: clamp(1rem, 2vw, 1.4rem);
        align-items: stretch;
    }

    .home-capabilities-lead {
        height: 100%;
        padding: clamp(1.5rem, 3vw, 2.1rem);
        border: 1px solid var(--home-accent-line);
        border-radius: 1.45rem;
        background: linear-gradient(180deg, #fbfdfd 0%, #f4faf9 100%);
    }

    .home-capabilities-line {
        width: min(100%, 18rem);
        height: .48rem;
        margin: 1.7rem 0 1.35rem;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(18, 133, 127, 0.16) 0%, rgba(18, 133, 127, 0.48) 50%, rgba(18, 133, 127, 0.1) 100%);
    }

    .home-capability-list {
        display: grid;
        gap: 1rem;
    }

    .home-capability-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
        padding: 1.25rem 1.3rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.35rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: var(--home-card-shadow);
        transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
    }

    .home-capability-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.2rem;
        height: 3.2rem;
        border-radius: .95rem;
        border: 1px solid rgba(18, 133, 127, 0.1);
        background: linear-gradient(180deg, rgba(18, 133, 127, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
        color: var(--home-accent);
    }

    .home-capability-icon svg {
        width: 1.4rem;
        height: 1.4rem;
    }

    .home-lifecycle-grid {
        display: grid;
        grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
        gap: clamp(1.25rem, 3vw, 2rem);
        align-items: center;
    }

    .home-lifecycle-steps {
        display: grid;
        gap: 1rem;
        margin-top: 1.4rem;
    }

    .home-lifecycle-step {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
        padding: 1rem 1rem 1rem 1.05rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 1.2rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 251, 250, 0.96) 100%);
        box-shadow: var(--home-card-shadow);
    }

    .home-lifecycle-step__index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: .9rem;
        background: var(--home-accent);
        color: #ffffff;
        font-size: .86rem;
        font-weight: 800;
        box-shadow: 0 .8rem 1.4rem rgba(18, 133, 127, 0.18);
    }

    .home-lifecycle-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        padding: .5rem;
    }

    .home-lifecycle-orbit {
        position: relative;
        width: min(100%, 32rem);
        aspect-ratio: 1;
        border: 1px solid rgba(18, 133, 127, 0.12);
        border-radius: 50%;
        background:
            radial-gradient(circle at 50% 50%, rgba(167, 225, 220, 0.42) 0%, rgba(167, 225, 220, 0.18) 18%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(180deg, #fbfdfd 0%, #f7fbfb 100%);
    }

    .home-lifecycle-center {
        position: absolute;
        inset: 50% auto auto 50%;
        display: grid;
        place-items: center;
        width: 8.25rem;
        height: 8.25rem;
        padding: 1rem;
        border-radius: 50%;
        background: #ffffff;
        color: var(--home-ink);
        font-size: .95rem;
        font-weight: 800;
        letter-spacing: .12em;
        line-height: 1.45;
        text-align: center;
        text-transform: uppercase;
        transform: translate(-50%, -50%);
        box-shadow: 0 1rem 2.2rem rgba(17, 56, 61, 0.08);
    }

    .home-lifecycle-node {
        position: absolute;
        display: grid;
        gap: .3rem;
        width: clamp(9rem, 18vw, 10.8rem);
        padding: .95rem 1rem;
        border: 1px solid var(--home-accent-line);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: var(--home-card-shadow);
        overflow-wrap: anywhere;
    }

    .home-lifecycle-node strong {
        color: var(--home-ink);
        font-size: clamp(.82rem, .9vw, .96rem);
        font-weight: 800;
        letter-spacing: .04em;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .home-lifecycle-node span {
        color: var(--home-ink-soft);
        font-size: clamp(.82rem, .84vw, .9rem);
        line-height: 1.5;
    }

    .home-lifecycle-node--1 {
        top: 6%;
        left: 50%;
        transform: translateX(-50%);
    }

    .home-lifecycle-node--2 {
        top: 50%;
        right: 1%;
        transform: translateY(-50%);
    }

    .home-lifecycle-node--3 {
        bottom: 6%;
        left: 50%;
        transform: translateX(-50%);
    }

    .home-lifecycle-node--4 {
        top: 50%;
        left: 1%;
        transform: translateY(-50%);
    }

    html[dir="rtl"] .home-lifecycle-center,
    html[dir="rtl"] .home-lifecycle-node strong {
        letter-spacing: 0;
    }

    html[dir="rtl"] .home-lifecycle-node strong {
        text-transform: none;
        line-height: 1.45;
    }

    html[dir="rtl"] .home-lifecycle-node span,
    html[dir="rtl"] .home-lifecycle-center {
        line-height: 1.6;
    }

    .home-next-step-panel {
        background: linear-gradient(135deg, #f3fafa 0%, #ffffff 58%, #edf8f6 100%);
    }

    .home-next-step-grid {
        display: grid;
        grid-template-columns: minmax(0, .92fr) minmax(0, 1.08fr);
        gap: clamp(1rem, 3vw, 2rem);
        align-items: center;
    }

    .home-next-step-copy {
        max-width: 35rem;
    }

    .home-next-step-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        margin-top: 1.5rem;
    }

    .home-cta--secondary {
        background: rgba(255, 255, 255, 0.92);
        color: var(--home-ink) !important;
        border: 1px solid var(--home-accent-line);
        box-shadow: 0 .55rem 1rem rgba(17, 56, 61, 0.04);
    }

    .home-cta--secondary:hover {
        background: #eef8f6;
        color: #107f76 !important;
        box-shadow: 0 .65rem 1.15rem rgba(17, 56, 61, 0.06);
    }

    .home-next-step-visual {
        position: relative;
        min-height: clamp(14rem, 26vw, 20rem);
    }

    .home-next-step-glow {
        position: absolute;
        inset: 8% 12%;
        background: radial-gradient(circle, rgba(118, 199, 193, 0.44) 0%, rgba(118, 199, 193, 0.18) 28%, rgba(255, 255, 255, 0) 72%);
        filter: blur(4px);
    }

    .home-next-step-visual img {
        position: absolute;
        inset-inline-end: 0;
        bottom: 0;
        width: min(100%, 25rem);
        height: 100%;
        object-fit: contain;
    }

    @media (max-width: 991.98px) {
        .home-family-grid,
        .home-capabilities-grid,
        .home-lifecycle-grid,
        .home-next-step-grid {
            grid-template-columns: 1fr;
        }

        .home-lifecycle-orbit {
            width: min(100%, 26rem);
        }

        .home-review-card,
        .home-blog-card {
            grid-template-columns: 1fr;
        }

        .home-match-grid {
            grid-template-columns: 1fr;
        }

        .home-match-flow {
            max-width: 100%;
        }

        .home-product-thumb {
            min-height: 15rem;
        }

        .main-banner-slider {
            min-height: 23rem;
        }

        .home-blog-feature img {
            height: 17rem;
        }

        .home-review-card img {
            min-height: 220px;
        }

        .home-wholesale-art,
        .home-device-art {
            justify-content: center;
            margin-top: 1.5rem;
        }

        .home-next-step-visual {
            min-height: 12.5rem;
        }
    }

    @media (max-width: 575.98px) {
        .home-family-grid {
            grid-template-columns: 1fr;
        }

        .home-match-flow {
            grid-template-columns: 1fr;
        }

        .home-match-note {
            align-items: flex-start;
            justify-content: flex-start;
        }

        .home-match-chip {
            width: 100%;
        }

        .home-product-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .home-match-copy,
        .home-filter-card {
            padding: 1.1rem;
        }

        .home-product-rating {
            inset-inline-start: .8rem;
            inset-block-end: .8rem;
            padding-inline: .6rem;
        }

        .home-category-topline {
            flex-wrap: wrap;
            justify-content: center;
        }

        .home-category-footer {
            justify-content: center;
        }

        .home-lifecycle-orbit {
            width: min(100%, 20rem);
        }

        .home-lifecycle-center {
            width: 6.85rem;
            height: 6.85rem;
            font-size: .8rem;
        }

        .home-lifecycle-node {
            width: clamp(7.4rem, 34vw, 8.8rem);
            padding: .7rem .75rem;
        }

        .home-lifecycle-node strong {
            font-size: .78rem;
        }

        .home-lifecycle-node span {
            font-size: .78rem;
        }
    }
</style>


@endpush

@section('content')
<div class="home-page py-4 py-lg-5">
    <div class="home-shell">
        @php
            $resolveHomeCmsImage = function (?string $path) {
                if (blank($path)) {
                    return '';
                }

                if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
                    return $path;
                }

                return asset(ltrim($path, '/'));
            };
        @endphp

@if(isset($sectionData['main_banner']) && $sectionData['main_banner']['is_active'] == 1)
@php
$banners = array_filter($sectionData['main_banner']['data'], fn($banner) => $banner['is_active'] ?? false);
@endphp

@if(count($banners) > 0)
<section class="carousel-mobile">
    <div class="container">
        <div class="home-panel home-hero-panel position-relative w-100 overflow-hidden rounded">
        <ol class="carousel-indicators">
            @foreach($banners as $index => $banner)
            <li data-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></li>
            @endforeach
        </ol>

        <div class="position-relative main-banner-slider" style="max-height: 30rem; position: relative;">
            @foreach($banners as $index => $banner)
            <div class="banner-slide position-absolute top-0 start-0 w-100 h-100 transition-opacity"
                style="background-image: url('{{ asset($banner['image']) }}'); background-size: cover; background-position: center; background-repeat: no-repeat; opacity: {{ $index === 0 ? '1' : '0' }}; transition: opacity 0.7s ease;">
                <div class="banner-overlay"></div>
                <div class="d-flex align-items-center h-100 px-3 px-md-5 position-relative pb-4 pb-md-0">
                    <div class="container">
                        <div class="col-lg-6">
                            <div class="home-hero-copy">
                                <h1 class="home-title banner-head">{{ $banner['heading'] }}</h1>
                                <p class="home-subtitle banner-p">{{ $banner['paragraph'] }}</p>
                                <div class="home-trust-line">
                                    <i class="fa fa-shield" aria-hidden="true"></i>
                                    <span>{{ translate('Serving_customers_through_quality_reliability_and_long_term_partnerships') }}</span>
                                </div>
                                <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($banner['buttonLink'] ?? '') ?: '#' }}" class="home-cta mt-4">
                                {{ $banner['buttonText'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    </div>
</section>
@endif
@endif





@if(
isset($sectionData['trusted_by']) &&
$sectionData['trusted_by']['is_active'] &&
!empty($sectionData['trusted_by']['data']) &&
$sectionData['trusted_by']['data'][0]['is_active']
)
@php
$trusted = $sectionData['trusted_by']['data'][0];

@endphp

<section class="text-center">
    <div class="container rtl container-mobile">
        <div class="home-panel home-panel--soft home-section">
            <div class="home-section-head">
            <span class="home-eyebrow">{{ translate('trusted_by') }}</span>
            <h2 class="home-title home-title--section trusted-by">
                {{ $trusted['heading'] }}
                <span class="text-primary">{{ $trusted['year'] }}</span>
            </h2>
            <p class="home-subtitle">
                {{ $trusted['paragraph'] }}
            </p>
            </div>
        </div>
    </div>
</section>

@endif



@if(isset($sectionData['products']) && $sectionData['products']['is_active'] == 1 && $products->count() != 0)
@php
$trusted = $sectionData['products']['data'][0];
@endphp

@php
$slides = $products;
if($products->count() < 8) { $slides=$products->concat($products);
    }
    @endphp

    <section>
        <div class="container container-mobile">
            <div class="home-panel">
            <div class="home-section">
            <div class="home-section-head">
            <span class="home-eyebrow">{{ translate('Product_portfolio') }}</span>
            <h2 class="home-title home-title--section mobile-head">{{ $trusted['section_title'] }}</h2>
            <p class="home-subtitle">
                {{ $trusted['section_paragraph'] }}
            </p>
            </div>
            <div class="swiper mySwiperThree home-products-slider">
                <div class="swiper-wrapper">
                    @foreach($slides as $product)
                    @php
                    $overallRating = getOverallRating($product->reviews);
                    $reviewCount = count($product->reviews);
                    $hasDiscount = getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0;
                    $isInStock = $product->product_type !== 'physical' || $product->current_stock > 0;
                    @endphp
                    <div class="swiper-slide swiper-home">
                        <article class="home-product-card product-single-hover h-100">
                            <div class="relative">
                                <div class="inline_product home-product-thumb relative">
                                    @if($hasDiscount)
                                    <span class="home-badge">
                                        -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                                    </span>
                                    @endif

                                    <a href="{{ route('product', $product->slug) }}" class="home-product-thumb__media">
                                        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                            alt="{{ $product->name }}" class="home-product-thumb__image" />
                                    </a>

                                    <a class="home-icon-button action-product-quick-view"
                                        href="javascript:" data-product-id="{{ $product->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if($overallRating[0] != 0)
                                    <div class="home-product-rating">
                                        <span class="text-warning d-inline-flex align-items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if ($i <= (int)$overallRating[0])
                                                    <i class="tio-star"></i>
                                                @elseif ($i <= (int)$overallRating[0] + 1 && $overallRating[0] > (int)$overallRating[0])
                                                    <i class="tio-star-half"></i>
                                                @else
                                                    <i class="tio-star-outlined"></i>
                                                @endif
                                            @endfor
                                        </span>
                                        <span>{{ $reviewCount }}</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="home-product-body">
                                    <div class="home-product-topline">
                                        <span class="home-card-meta">{{ translate('Product') }}</span>
                                        <span class="home-product-status">
                                            {{ $isInStock ? translate('Available_now') : translate('out_of_stock') }}
                                        </span>
                                    </div>

                                    <a href="{{ route('product', $product->slug) }}"
                                        class="home-card-title home-card-title--clamp text-decoration-none">
                                        {{ $product->name }}
                                    </a>

                                    <div class="home-product-footer">
                                        <div class="home-price-line mt-0">
                                            @if($hasDiscount)
                                            <del>
                                                {{ webCurrencyConverter(amount: $product->unit_price) }}
                                            </del>
                                            @endif
                                            <span class="home-price-current">
                                                {{ getProductPriceByType(product: $product, type: 'discounted_unit_price',
                                            result: 'string') }}
                                            </span>
                                        </div>
                                        <a href="{{ route('product', $product->slug) }}"
                                            class="home-link-cta home-link-cta--quiet">
                                            {{ translate('View') }}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                class="bi bi-arrow-right ms-1" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 1 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        </div>
        </div>

    </section>
    @endif

    @if(isset($sectionData['categories']) && $sectionData['categories']['is_active'] == 1)
    @php
    $trustedCategory = $sectionData['categories']['data'];

    @endphp
    @php
    $categoryCount = count($categories);
    $repeatCount = $categoryCount > 0 ? ($categoryCount < 4 ? ceil(8 / $categoryCount) : 1) : 0;
        $repeatedCategories=[];

        for ($i=0; $i < $repeatCount; $i++) {
        foreach ($categories as $category) {
        $repeatedCategories[]=$category;
        }
        }
        $finalCategories=array_slice($repeatedCategories, 0, 8);
        @endphp

        <section>
        <div class="container">
            <div class="home-panel home-panel--soft">
            <div class="home-section">
            <div class="home-section-head">
            <span class="home-eyebrow">{{ translate('categories') }}</span>
            <h2 class="home-title home-title--section mobile-head">{{ $trustedCategory['heading'] }}</h2>
            <p class="home-subtitle">
                {{ $trustedCategory['paragraph'] }}
            </p>
            </div>

            <div class="swiper mySwiperOne home-categories-slider">
                <div class="swiper-wrapper">

                    @foreach($finalCategories as $category)
                    <div class="swiper-slide">
                        <article class="home-category-card">
                        <a class="home-category-icon" href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}">
                            <img src="{{ getStorageImages(path:$category->icon_full_url, type:'category') }}"
                                alt="{{ $category->name }}"
                                class="w-100">
                        </a>
                            <div class="home-category-body">
                                <div class="home-category-topline">
                                    <span class="home-card-meta">{{ translate('Shop_by_category') }}</span>
                                    <span class="home-category-index">{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <h3 class="home-card-title mb-0">{{ $category->name }}</h3>
                                <div class="home-category-footer">
                                    <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}"
                                        class="home-link-cta home-link-cta--quiet">
                                        {{ translate('View') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-arrow-right ms-1" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 1 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
        </div>
        </div>
        </section>


        @endif


        @if(isset($sectionData['flagship_battery_families']) && $sectionData['flagship_battery_families']['is_active'] == 1)
            @php
                $familySection = $sectionData['flagship_battery_families']['data']['section'] ?? [];
                $familyCards = $familySection['cards'] ?? [];
                $familyFallbackProducts = $products->take(max(count($familyCards), 1))->values();
            @endphp
            @if(count($familyCards) > 0)
                <section>
                    <div class="container">
                        <div class="home-section">
                            <div class="home-corporate-head">
                                @if(!empty($familySection['label']))
                                    <span class="home-eyebrow">{{ $familySection['label'] }}</span>
                                @endif
                                <h2 class="home-title home-title--section">{{ $familySection['title'] ?? '' }}</h2>
                                @if(!empty(trim((string) ($familySection['description'] ?? ''))))
                                    <p class="home-subtitle">{{ $familySection['description'] }}</p>
                                @endif
                            </div>

                            <div class="home-family-grid">
                                @foreach($familyCards as $index => $card)
                                    @php
                                        $fallbackProduct = $familyFallbackProducts[$index] ?? $familyFallbackProducts->first();
                                        $familyImage = !empty($card['image'])
                                            ? $resolveHomeCmsImage($card['image'])
                                            : ($fallbackProduct ? getStorageImages(path: $fallbackProduct->thumbnail_full_url, type: 'product') : '');
                                        $familyCardLink = \App\Support\CmsContentSanitizer::sanitizeLink($card['redirect_link'] ?? '');
                                        $familyTag = trim((string) ($card['tag'] ?? ''));
                                        $familyTitle = trim((string) ($card['title'] ?? ''));
                                        $familyDescription = trim((string) ($card['description'] ?? ''));
                                        $familyNote = trim((string) ($card['note'] ?? ''));
                                        $familyImageAlt = trim((string) ($card['image_alt'] ?? ''));
                                        $familyHasVisibleContent = $familyImage !== ''
                                            || $familyTag !== ''
                                            || $familyTitle !== ''
                                            || $familyDescription !== ''
                                            || $familyNote !== '';
                                    @endphp
                                    @if($familyHasVisibleContent)
                                        <article class="home-family-card">
                                            @if($familyImage !== '')
                                                <div class="home-family-media">
                                                    @if($familyCardLink !== '')
                                                        <a href="{{ $familyCardLink }}" class="home-family-media-link">
                                                            <img src="{{ $familyImage }}" alt="{{ $familyImageAlt !== '' ? $familyImageAlt : ($familyTitle !== '' ? $familyTitle : translate('Product')) }}">
                                                        </a>
                                                    @else
                                                        <div class="home-family-media-link">
                                                            <img src="{{ $familyImage }}" alt="{{ $familyImageAlt !== '' ? $familyImageAlt : ($familyTitle !== '' ? $familyTitle : translate('Product')) }}">
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($familyTag !== '' || $familyTitle !== '' || $familyDescription !== '' || $familyNote !== '')
                                                <div class="home-family-body">
                                                    @if($familyTag !== '')
                                                        <span class="home-card-meta">{{ $familyTag }}</span>
                                                    @endif
                                                    @if($familyTitle !== '')
                                                        @if($familyCardLink !== '')
                                                            <h3 class="home-card-title">
                                                                <a href="{{ $familyCardLink }}" class="home-family-title-link">{{ $familyTitle }}</a>
                                                            </h3>
                                                        @else
                                                            <h3 class="home-card-title">{{ $familyTitle }}</h3>
                                                        @endif
                                                    @endif
                                                    @if($familyDescription !== '')
                                                        <p class="home-card-copy">{{ $familyDescription }}</p>
                                                    @endif
                                                    @if($familyNote !== '')
                                                        <div class="home-family-note">{{ $familyNote }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </article>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        @if(isset($sectionData['core_capabilities']) && $sectionData['core_capabilities']['is_active'] == 1)
            @php
                $capabilitiesSection = $sectionData['core_capabilities']['data']['section'] ?? [];
                $capabilitiesCards = collect($capabilitiesSection['cards'] ?? [])->filter(function ($card) {
                    return trim((string) ($card['title'] ?? '')) !== ''
                        || trim((string) ($card['description'] ?? '')) !== '';
                })->values();
            @endphp
            @if($capabilitiesCards->count() > 0)
                <section>
                    <div class="container">
                        <div class="home-panel home-panel--soft">
                            <div class="home-section">
                                <div class="home-capabilities-grid">
                                    <div class="home-capabilities-lead">
                                        @if(!empty($capabilitiesSection['label']))
                                            <span class="home-eyebrow">{{ $capabilitiesSection['label'] }}</span>
                                        @endif
                                        <h2 class="home-title home-title--section">{{ $capabilitiesSection['title'] ?? '' }}</h2>
                                        <div class="home-capabilities-line" aria-hidden="true"></div>
                                        <p class="home-subtitle mb-0">{{ $capabilitiesSection['description'] ?? '' }}</p>
                                    </div>

                                    <div class="home-capability-list">
                                        @foreach($capabilitiesCards as $index => $card)
                                            @php
                                                $capabilityTitle = trim((string) ($card['title'] ?? ''));
                                                $capabilityDescription = trim((string) ($card['description'] ?? ''));
                                            @endphp
                                            <article class="home-capability-item">
                                                <div class="home-capability-icon" aria-hidden="true">
                                                    @if($index === 0)
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path d="M3 21h18M5 21V10l5 3V8l5 3V6l6 4v11"/>
                                                            <path d="M9 21v-4m4 4v-4m4 4v-4"/>
                                                        </svg>
                                                    @elseif($index === 1)
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path d="M7 7h5l-2-3"/>
                                                            <path d="M17 17h-5l2 3"/>
                                                            <path d="M7 7l-2 4m12 6 2-4"/>
                                                            <path d="M7 17h-2a3 3 0 0 1-2-5l1-1"/>
                                                            <path d="M17 7h2a3 3 0 0 1 2 5l-1 1"/>
                                                        </svg>
                                                    @else
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                                                            <path d="M9.5 12.5l1.7 1.7 3.3-4"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($capabilityTitle !== '')
                                                        <h3 class="home-card-title">{{ $capabilityTitle }}</h3>
                                                    @endif
                                                    @if($capabilityDescription !== '')
                                                        <p class="home-card-copy mb-0">{{ $capabilityDescription }}</p>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif
        @if(isset($sectionData['find_perfect_match']) && $sectionData['find_perfect_match']['is_active'] == 1)
        @php
            $findPerfectMatchRaw = $sectionData['find_perfect_match']['data'] ?? [];
            $findPerfectMatchFallback = [
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

            if (is_array($findPerfectMatchRaw) && array_key_exists(0, $findPerfectMatchRaw) && is_array($findPerfectMatchRaw[0])) {
                $legacyHeading = $findPerfectMatchRaw[0]['heading'] ?? $findPerfectMatchFallback['section_heading'];
                $legacyParagraph = $findPerfectMatchRaw[0]['paragraph'] ?? $findPerfectMatchFallback['hero_description'];
                $findPerfectMatchRaw = [
                    'section_heading' => $legacyHeading,
                    'hero_heading' => $legacyHeading,
                    'hero_description' => $legacyParagraph,
                ];
            }

            $findPerfectMatch = array_merge(
                $findPerfectMatchFallback,
                is_array($findPerfectMatchRaw) ? $findPerfectMatchRaw : []
            );
        @endphp
        <section aria-label="{{ $findPerfectMatch['section_heading'] }}">
            <div class="container">
                <div class="home-panel home-match-panel">
                    <div class="home-section">
                        <div class="home-section-head">
                            <span class="home-eyebrow">{{ translate('find_perfect_match') }}</span>
                            <h2 class="home-title home-title--section mobile-head">
                                {{ $findPerfectMatch['section_heading'] }}
                            </h2>
                            <p class="home-subtitle mb-0">
                                {{ $findPerfectMatch['hero_description'] }}
                            </p>
                        </div>

                        <div class="home-match-grid">
                        <div class="home-match-copy">
                            <div class="home-match-intro">
                                <h3 id="heading-find-match" class="home-title home-title--section mobile-head">
                                    {{ $findPerfectMatch['hero_heading'] }}
                                </h3>
                            </div>

                            <div class="home-match-chip-row" aria-hidden="true">
                                <span class="home-match-chip">{{ $findPerfectMatch['make_label'] }}</span>
                                <span class="home-match-chip">{{ $findPerfectMatch['model_label'] }}</span>
                                <span class="home-match-chip">{{ $findPerfectMatch['year_label'] }}</span>
                            </div>

                            <div class="home-match-flow" aria-hidden="true">
                                <div class="home-match-flow__item">
                                    <span class="home-match-flow__index">1</span>
                                    <span class="home-match-flow__label">{{ $findPerfectMatch['make_label'] }}</span>
                                </div>
                                <div class="home-match-flow__item">
                                    <span class="home-match-flow__index">2</span>
                                    <span class="home-match-flow__label">{{ $findPerfectMatch['model_label'] }}</span>
                                </div>
                                <div class="home-match-flow__item">
                                    <span class="home-match-flow__index">3</span>
                                    <span class="home-match-flow__label">{{ $findPerfectMatch['year_label'] }}</span>
                                </div>
                            </div>

                            <div class="home-match-note">
                                <span class="home-match-note__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M7 7h10M7 12h10M7 17h6"/>
                                        <path d="M5 4h14a2 2 0 0 1 2 2v12l-4-3H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>{{ $findPerfectMatch['filter_title'] }}</strong>
                                    <span>{{ translate('shop_by_vehicle_year_make_model') }}</span>
                                </div>
                            </div>
                        </div>

                        <aside role="region" aria-labelledby="heading-filters">
                            <div class="home-filter-card">
                                <span class="home-eyebrow home-filter-card__eyebrow">{{ translate('vehicle_filter_options') }}</span>
                                <div class="home-filter-card__intro">
                                    <h2 id="heading-filters" class="home-filter-title mobile-head">
                                        {{ $findPerfectMatch['filter_title'] }}
                                    </h2>
                                    <p class="home-subtitle">{{ $findPerfectMatch['hero_heading'] }}</p>
                                </div>

                                <form class="d-grid gap-3" aria-label="{{ translate('vehicle_filter_options') }}" action="{{ route('products') }}" method="GET">
                                    <div class="home-filter-fields">
                                        <div class="home-filter-field">
                                            <label for="make" class="form-label">{{ $findPerfectMatch['make_label'] }}</label>
                                            <select id="make" name="make" class="form-select border my-1 vehicle-select2">
                                                <option value="">{{ $findPerfectMatch['make_placeholder'] }}</option>
                                                @foreach($makes as $make)
                                                <option value="{{ $make->getRawOriginal('name') }}" data-id="{{ $make->id }}" {{ ($selectedVehicleFilters['make'] ?? null) === $make->getRawOriginal('name') ? 'selected' : '' }}>{{ $make->name }}</option>
                                                @endforeach
                                            </select>

                                        </div>

                                        <div class="home-filter-field">
                                            <label for="model" class="form-label">{{ $findPerfectMatch['model_label'] }}</label>
                                            <select id="model" name="model" class="form-select border my-1 vehicle-select2" disabled>
                                                <option value="">{{ $findPerfectMatch['model_placeholder'] }}</option>
                                            </select>

                                        </div>

                                        <div class="home-filter-field">
                                            <label for="year" class="form-label">{{ $findPerfectMatch['year_label'] }}</label>
                                            <select id="year" name="year" class="form-select border my-1 vehicle-select2" {{ !empty($selectedVehicleFilters['year']) ? '' : 'disabled' }}>
                                                <option value="">{{ $findPerfectMatch['year_placeholder'] }}</option>
                                                @foreach($years as $year)
                                                <option value="{{ $year->getRawOriginal('year') }}" {{ (string)($selectedVehicleFilters['year'] ?? $currentYear) === (string)$year->getRawOriginal('year') ? 'selected' : '' }}>{{ $year->year }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <button type="submit" class="home-cta border-0 w-100">
                                        {{ $findPerfectMatch['apply_button_text'] }}
                                    </button>
                                </form>
                            </div>
                        </aside>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        @endif

        @if(isset($sectionData['closed_loop_lifecycle']) && $sectionData['closed_loop_lifecycle']['is_active'] == 1)
            @php
                $lifecycleSection = $sectionData['closed_loop_lifecycle']['data']['section'] ?? [];
                $lifecycleCards = collect($lifecycleSection['cards'] ?? [])->filter(function ($card) {
                    return trim((string) ($card['title'] ?? '')) !== ''
                        || trim((string) ($card['description'] ?? '')) !== ''
                        || trim((string) ($card['label'] ?? '')) !== ''
                        || trim((string) ($card['note'] ?? '')) !== '';
                })->values();
            @endphp
            @if($lifecycleCards->count() > 0)
                <section>
                    <div class="container">
                        <div class="home-panel">
                            <div class="home-section">
                                <div class="home-lifecycle-grid">
                                    <div>
                                        @if(!empty($lifecycleSection['label']))
                                            <span class="home-eyebrow">{{ $lifecycleSection['label'] }}</span>
                                        @endif
                                        <h2 class="home-title home-title--section">{{ $lifecycleSection['title'] ?? '' }}</h2>
                                        <p class="home-subtitle">{{ $lifecycleSection['description'] ?? '' }}</p>

                                        <div class="home-lifecycle-steps">
                                            @foreach($lifecycleCards as $index => $card)
                                                @php
                                                    $lifecycleTitle = trim((string) ($card['title'] ?? ''));
                                                    $lifecycleDescription = trim((string) ($card['description'] ?? ''));
                                                @endphp
                                                <div class="home-lifecycle-step">
                                                    <span class="home-lifecycle-step__index">{{ $index + 1 }}</span>
                                                    <div>
                                                        @if($lifecycleTitle !== '')
                                                            <h3 class="home-card-title mb-2">{{ $lifecycleTitle }}</h3>
                                                        @endif
                                                        @if($lifecycleDescription !== '')
                                                            <p class="home-card-copy mb-0">{{ $lifecycleDescription }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="home-lifecycle-visual" aria-hidden="true">
                                        <div class="home-lifecycle-orbit">
                                            @if(!empty(trim((string) ($lifecycleSection['value'] ?? ''))))
                                                <div class="home-lifecycle-center">{{ $lifecycleSection['value'] }}</div>
                                            @endif
                                            @foreach($lifecycleCards as $index => $card)
                                                @php
                                                    $lifecycleLabel = trim((string) ($card['label'] ?? ''));
                                                    $lifecycleNote = trim((string) ($card['note'] ?? ''));
                                                @endphp
                                                <div class="home-lifecycle-node home-lifecycle-node--{{ $index + 1 }}">
                                                    @if($lifecycleLabel !== '')
                                                        <strong>{{ $lifecycleLabel }}</strong>
                                                    @endif
                                                    @if($lifecycleNote !== '')
                                                        <span>{{ $lifecycleNote }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif



        @if(getWebConfig('blog_feature_active_status') == 1)
        @if(isset($sectionData['blog']) && $sectionData['blog']['is_active'] == 1)
        @php
        $trusted = $sectionData['blog']['data'];
        @endphp
        <section class="bg-white">
            <div class="container">
                <div class="home-panel">
                <div class="home-section">
                <div class="home-section-head">
                <span class="home-eyebrow">{{ translate('insights') }}</span>
                <h2 class="home-title home-title--section mobile-head">{{ $trusted['heading'] }}</h2>
                <p class="home-subtitle">
                    {{ $trusted['paragraph'] }}
                </p>
                </div>

                <div class="row g-4">
                    <!-- Left Featured Blog -->
                    @if($latestPosts->count() > 0)
                    @php $featured = $latestPosts->first(); @endphp

                    <div class="col-md-7 col-lg-8">
                        <div class="home-blog-feature h-100">
                            <div class="overflow-hidden">
                                <img src="{{ asset('storage/blog/image/' . $featured->image) }}"
                                    class="w-100 img-fluid object-fit-cover rounded-top"
                                    alt="{{ $featured->heading }}">
                            </div>
                            <div class="home-blog-feature__body">
                                <span class="home-card-meta">{{ translate('Blog') }}</span>
                                <a href="{{ route('frontend.blog.details', ['slug' => $featured?->slug]) }}"
                                    class="home-card-title text-decoration-none d-block mb-2 blog-card-head">
                                    {{ \Illuminate\Support\Str::limit(strip_tags( $featured->title ), 80) }}
                                </a>
                                <p class="home-card-copy">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($featured->description), 200) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-5 col-lg-4 d-flex flex-column gap-3">
                        @foreach($latestPosts->skip(1)->take(3) as $post)
                        <div class="home-blog-card">
                            <div class="overflow-hidden rounded-start w-fit-content" style="min-width: 9.5rem;">
                                <img src="{{ asset('storage/blog/image/' . $post->image) }}" alt="{{ $post->heading }}"
                                    class="img-fluid h-100 object-fit-cover rounded-sm">
                            </div>
                            <div class="home-blog-card__body d-flex flex-column justify-content-between">
                                <div>
                                    <span class="home-card-meta mb-2">{{ translate('Blog') }}</span>
                                    <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}"
                                        class="home-card-title text-decoration-none d-block">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->title), 50) }}
                                    </a>
                                    <p class="home-card-copy small mb-2">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->description), 50) }}
                                    </p>
                                </div>
                                <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}" class="home-link-cta small">
                                    {{ translate('Read More') }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Button -->
                <div class="text-center mt-4">
                    <a href="{{ route('frontend.blog.index') }}"
                        class="home-cta">
                        {{ translate('Read More') }}
                    </a>
                </div>
            </div>
            </div>
            </div>
        </section>
        @endif

        @endif






        @if(isset($sectionData['client_review']) && $sectionData['client_review']['is_active'] == 1)
        <section class="">
            <div class="container">
                <div class="home-panel home-panel--soft">
                <div class="home-section">
                <div class="home-section-head">
                <span class="home-eyebrow">{{ translate('client_review') }}</span>
                <h2 class="home-title home-title--section mobile-head tablet-head">{{ translate('What Our Clients Say') }}</h2>
                </div>

                <div class="swiper mySwiperTwo home-reviews-slider pb-1">
                    <div class="swiper-wrapper">
                        @foreach($sectionData['client_review']['data']['clients'] as $client)
                        @php
                        $clientImage = $client['image'] ?? '';
                        if (\Illuminate\Support\Str::startsWith($clientImage, ['http://', 'https://'])) {
                        $clientImageSrc = $clientImage;
                        } else {
                        $clientImageSrc = asset(ltrim($clientImage, '/'));
                        }
                        $clientRating = max(0, min(5, (float) ($client['rating'] ?? 0)));
                        @endphp
                        <div class="swiper-slide ">
                            <div class="home-review-card">
                                <div class="p-4 d-flex align-items-center justify-content-center">
                                    <img src="{{ $clientImageSrc }}" class="img-fluid h-100 w-100 object-fit-cover"
                                        style="object-fit: cover; border-radius: 8px;" alt="client-img">
                                </div>
                                <div class="home-review-card__body d-flex flex-column justify-content-center">
                                    <span class="home-quote">"</span>
                                    <div class="home-review-rating">
                                        <span class="text-warning d-inline-flex align-items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if ($i <= (int) $clientRating)
                                                    <i class="tio-star"></i>
                                                @elseif ($i <= (int) $clientRating + 1 && $clientRating > (int) $clientRating)
                                                    <i class="tio-star-half"></i>
                                                @else
                                                    <i class="tio-star-outlined"></i>
                                                @endif
                                            @endfor
                                        </span>
                                        <span>{{ number_format($clientRating, 1) }}</span>
                                    </div>
                                    <h6 class="home-card-title mb-2">{{ $client['name'] }}</h6>
                                    <p class="home-card-copy mb-0">{{ $client['review'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            </div>
            </div>
        </section>
        @endif


        @if(isset($sectionData['next_step']) && $sectionData['next_step']['is_active'] == 1)
            @php
                $nextStepSection = $sectionData['next_step']['data']['section'] ?? [];
                $nextStepImage = !empty($nextStepSection['image'])
                    ? $resolveHomeCmsImage($nextStepSection['image'])
                    : (($products->first()) ? getStorageImages(path: $products->first()->thumbnail_full_url, type: 'product') : '');
                $primaryLink = \App\Support\CmsContentSanitizer::sanitizeLink($nextStepSection['button_link'] ?? '') ?: route('contacts');
                $secondaryLink = \App\Support\CmsContentSanitizer::sanitizeLink($nextStepSection['secondary_button_link'] ?? '') ?: route('warranty.track.page');
                $nextStepTitle = trim((string) ($nextStepSection['title'] ?? ''));
                $nextStepDescription = trim((string) ($nextStepSection['description'] ?? ''));
                $nextStepPrimaryText = trim((string) ($nextStepSection['button_text'] ?? ''));
                $nextStepSecondaryText = trim((string) ($nextStepSection['note'] ?? ''));
                $nextStepImageAlt = trim((string) ($nextStepSection['image_alt'] ?? ''));
            @endphp
            <section>
                <div class="container">
                    <div class="home-panel home-next-step-panel">
                        <div class="home-section">
                            <div class="home-next-step-grid">
                                <div class="home-next-step-copy">
                                    @if(!empty($nextStepSection['label']))
                                        <span class="home-eyebrow">{{ $nextStepSection['label'] }}</span>
                                    @endif
                                    @if($nextStepTitle !== '')
                                        <h2 class="home-title">{{ $nextStepTitle }}</h2>
                                    @endif
                                    @if($nextStepDescription !== '')
                                        <p class="home-subtitle">{{ $nextStepDescription }}</p>
                                    @endif

                                    <div class="home-next-step-actions">
                                        @if($nextStepPrimaryText !== '')
                                            <a href="{{ $primaryLink }}" class="home-cta">
                                                {{ $nextStepPrimaryText }}
                                            </a>
                                        @endif
                                        @if($nextStepSecondaryText !== '')
                                            <a href="{{ $secondaryLink }}" class="home-cta home-cta--secondary">
                                                {{ $nextStepSecondaryText }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                    <div class="home-next-step-visual" aria-hidden="true">
                                        <div class="home-next-step-glow"></div>
                                        @if($nextStepImage)
                                        <img src="{{ $nextStepImage }}" alt="{{ $nextStepImageAlt !== '' ? $nextStepImageAlt : translate('Product') }}">
                                        @endif
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif




        @php($companyReliability = getWebConfig(name: 'company_reliability'))
        @if($companyReliability != null)
        <!-- @include('web-views.partials._company-reliability') -->
        @endif
    </div>
</div>

        <span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>
        @endsection

        @push('script')

        <script src="{{theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/swiper-bundle.min.js') }}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/custom-slider.js') }}"></script>
        <script src="{{ dynamicAsset(path: 'public/assets/select2/js/select2.min.js') }}"></script>

        @php($serializedModels = $models->map(function ($model) {
            return [
                'id' => $model->id,
                'make_id' => $model->make_id,
                'value' => $model->getRawOriginal('name'),
                'label' => $model->name,
            ];
        })->values())

        <script>
            const models = @json($serializedModels);
            const modelPlaceholder = @json($findPerfectMatch['model_placeholder'] ?? translate('select_model'));
            const selectedMake = @json($selectedVehicleFilters['make'] ?? null);
            const selectedModel = @json($selectedVehicleFilters['model'] ?? null);
            const selectedYear = @json($selectedVehicleFilters['year'] ?? $currentYear);

            function populateHomeModels(makeName, preferredModel = null) {
                const makeId = $('#make option').filter(function() {
                    return $(this).val() === makeName;
                }).data('id');
                const filteredModels = models.filter(model => model.make_id == makeId);
                $('#model').empty().prop('disabled', false).append('<option value="">' + modelPlaceholder + '</option>');
                filteredModels.forEach(model => {
                    const isSelected = model.value === preferredModel ? 'selected' : '';
                    $('#model').append(`<option value="${model.value}" ${isSelected}>${model.label}</option>`);
                });
                $('#model').trigger('change.select2');
            }

            $(document).ready(function () {
                $('.vehicle-select2').select2({
                    width: '100%',
                    dir: @json(session('direction') ?? 'ltr')
                });

                if (selectedMake) {
                    populateHomeModels(selectedMake, selectedModel);
                }

                if (selectedModel || selectedYear) {
                    $('#year').prop('disabled', false);
                }
            });

            $('#make').on('change', function() {
                const makeId = $(this).find('option:selected').data('id');
                const filteredModels = models.filter(model => model.make_id == makeId);
                $('#model').empty().prop('disabled', false).append('<option value="">' + modelPlaceholder + '</option>');
                filteredModels.forEach(model => {
                    $('#model').append(`<option value="${model.value}">${model.label}</option>`);
                });
                $('#model').val(null).trigger('change');
                $('#year').prop('disabled', true).val(null).trigger('change');

            });

            $('#model').on('change', function() {
                const selectedModel = $(this).val();

                // Enable year dropdown only if model is selected
                if (selectedModel) {
                    $('#year').prop('disabled', false);
                } else {
                    $('#year').prop('disabled', true);
                }
            });
        </script>


        @endpush
