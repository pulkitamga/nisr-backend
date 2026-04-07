@extends('layouts.front-end.app')


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
        background: linear-gradient(180deg, #f8fbfb 0%, #ffffff 18%, #f6fbfb 100%);
    }

    .home-shell {
        display: grid;
        gap: clamp(1rem, 2vw, 1.75rem);
    }

    .home-panel {
        position: relative;
        overflow: hidden;
        border: 1px solid #dcebea;
        border-radius: 1.6rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfd 100%);
        box-shadow: 0 1rem 2.4rem rgba(17, 56, 61, 0.06);
    }

    .home-panel--soft {
        background: linear-gradient(135deg, #f4fbfb 0%, #ffffff 55%, #eef8f6 100%);
    }

    .home-section {
        padding: clamp(1.35rem, 3vw, 2.2rem);
    }

    .home-section-head {
        max-width: 46rem;
        margin: 0 auto 1.6rem;
        text-align: center;
    }

    .home-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .85rem;
        padding: .44rem .9rem;
        border-radius: 999px;
        background: rgba(18, 157, 145, 0.09);
        color: #12857f;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .03em;
    }

    .home-title {
        margin: 0;
        color: #17393f;
        font-size: clamp(1.9rem, 4vw, 3.1rem);
        font-weight: 700;
        line-height: 1.12;
    }

    .home-title--section {
        font-size: clamp(1.65rem, 3vw, 2.35rem);
    }

    .home-subtitle {
        margin: .9rem 0 0;
        color: #597378;
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
        background: #129d91;
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 .85rem 1.7rem rgba(18, 157, 145, 0.18);
        transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease;
    }

    .home-cta:hover {
        background: #107f76;
        transform: translateY(-1px);
        box-shadow: 0 1rem 1.9rem rgba(18, 157, 145, 0.22);
        text-decoration: none;
    }

    .home-cta.bg-white {
        background: #ffffff !important;
        color: #12857f !important;
        box-shadow: none;
    }

    .home-cta.bg-white:hover {
        background: #eef8f6 !important;
        color: #107f76 !important;
        box-shadow: none;
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
        height: 100%;
        border: 1px solid #deebea;
        border-radius: 1.25rem;
        background: #ffffff;
        box-shadow: 0 .75rem 1.7rem rgba(17, 56, 61, 0.05);
    }

    .home-product-card,
    .home-category-card,
    .home-blog-card {
        overflow: hidden;
    }

    .home-product-card {
        padding: 0;
        color: #17393f;
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
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 255px;
        padding: 1.25rem 1rem 1rem;
        border-radius: 1.25rem 1.25rem 0 0;
        background: linear-gradient(180deg, #d6efee 0%, #c6e4e2 52%, #8db8b7 100%);
    }

    .home-product-thumb img {
        width: 100%;
        height: 220px;
        object-fit: contain;
    }

    .home-product-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 1rem 1rem 1.05rem;
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
        background: #f1f8f7;
        color: #547074;
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
        background: rgba(18, 157, 145, 0.12);
        color: #12857f;
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
        background: rgba(255, 255, 255, 0.92);
        color: #35595f !important;
        border: 1px solid #e4efee;
    }

    .home-card-meta {
        display: inline-flex;
        align-items: center;
        gap: .36rem;
        margin-bottom: .7rem;
        color: #12857f;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .home-product-card .home-card-meta,
    .home-category-card .home-card-meta {
        margin-bottom: 0;
        text-transform: none;
        letter-spacing: 0;
    }

    .home-card-title {
        display: block;
        margin: 0 0 .45rem;
        color: #17393f;
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
        color: #5b7479;
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
        border: 1px solid #e8f1f0;
        border-radius: 999px;
        background: #f8fbfb;
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
        color: #12857f;
        font-size: 1.05rem;
        font-weight: 700;
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
        transition: border-color .22s ease, box-shadow .22s ease;
    }

    .home-product-card:hover,
    .home-category-card:hover {
        border-color: #cfe2e0;
        box-shadow: 0 1rem 2rem rgba(17, 56, 61, 0.08);
    }

    .home-category-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 5rem;
        width: 5rem;
        height: 5rem;
        padding: .85rem;
        border: 1px solid #e3efee;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #f5fbfb 0%, #ffffff 100%);
    }

    .home-category-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        align-items: center;
        gap: .4rem;
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
        color: #12857f !important;
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
        padding: 1.2rem 1.05rem;
        text-align: center;
    }

    .home-value-card .icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        margin-bottom: 1rem;
        border-radius: 1.1rem;
        background: rgba(18, 157, 145, 0.08);
    }

    .home-value-card .icon-svg {
        width: 1.8rem;
        height: 1.8rem;
        color: #12857f !important;
    }

    .home-match-panel {
        background:
            linear-gradient(135deg, rgba(17, 57, 63, 0.94) 0%, rgba(17, 57, 63, 0.88) 35%, rgba(18, 157, 145, 0.78) 100%),
            url('{{ asset('assets/front-end/img/find-bg-img.jpg') }}') center/cover no-repeat;
    }

    .home-match-copy .home-title,
    .home-match-copy .home-subtitle {
        color: #ffffff;
    }

    .home-filter-card {
        padding: 1.35rem;
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 1.2rem 2.2rem rgba(15, 54, 58, 0.16);
    }

    .home-filter-card .form-label {
        color: #29474d;
        font-weight: 600;
    }

    .home-filter-card .form-select {
        min-height: 46px;
        border-color: #d8e8e6;
        border-radius: .95rem;
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
        border: 1px solid #deebea;
        border-radius: 1.25rem;
        background: #ffffff;
        box-shadow: 0 .75rem 1.7rem rgba(17, 56, 61, 0.05);
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
    }

    .home-review-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .home-review-card__body {
        padding: 1.3rem;
    }

    .home-quote {
        color: #12857f;
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

    @media (max-width: 991.98px) {
        .home-review-card,
        .home-blog-card {
            grid-template-columns: 1fr;
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
    }

    @media (max-width: 575.98px) {
        .home-product-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .home-category-topline {
            flex-wrap: wrap;
            justify-content: center;
        }

        .home-category-footer {
            justify-content: center;
        }
    }
</style>


@endpush

@section('content')
<div class="home-page py-4 py-lg-5">
    <div class="home-shell">

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

                                    <a href="{{ route('product', $product->slug) }}">
                                        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                            alt="{{ $product->name }}" class="w-full h-[180px] object-contain rounded" />
                                    </a>

                                    <!-- Quick View Button -->
                                    <div>
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
                                    </div>

                                    @if($product->product_type === 'physical' && $product->current_stock <= 0) <span
                                        class="home-badge" style="top:auto;bottom:.85rem;">
                                        {{ translate('out_of_stock') }}
                                        </span>
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

                                    @if($overallRating[0] != 0)
                                    <div class="home-product-metrics">
                                        <span class="home-product-metric">
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
                                        </span>
                                    </div>
                                    @endif

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


        @if(isset($sectionData['why_choose_us']) && $sectionData['why_choose_us']['is_active'] == 1)
        @php
        $section = $sectionData['why_choose_us']['data']['section'];
        @endphp

        <section class="text-center">
            <div class="container px-3">
                <div class="home-panel">
                <div class="home-section">
                <div class="home-section-head">
                <span class="home-eyebrow">{{ translate('why_choose_us') }}</span>
                <h2 class="home-title home-title--section why-chose mobile-head">{{ $section['title'] }}</h2>
                <p class="home-subtitle">
                    {!! nl2br(e($section['subtitle'])) !!}
                </p>
                </div>

                <div class="row gy-4 gx-3">
                    @foreach($section['cards'] as $card)
                    <div class="col-12 col-sm-6 col-lg-3 my-3 px-md-3">
                        <div class="home-value-card custom-card h-100 text-center">
                            <div class="d-flex justify-content-center gap-3 mb-4">
                                <div class="icon-wrapper {{ $card['icon']['animation'] ?? '' }}">
                                    {{-- SVG Icon --}}
                                    @switch($card['icon']['name'])
                                    @case('rocket')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon-svg text-{{ $card['icon']['color'] }}"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M4 20l5-5 3 3 5-5-3-3 5-5-9-3-3 9-5 5z"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    @break
                                    @case('cpu')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg text-{{ $card['icon']['color'] }}"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M9 9h6v6H9z" />
                                        <path d="M3 10h2v4H3zm16 0h2v4h-2zM10 3v2h4V3zm0 16v2h4v-2zM4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    @break
                                    @case('shield')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg text-{{ $card['icon']['color'] }}"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    @break
                                    @case('infinity')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg text-{{ $card['icon']['color'] }}"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M18.364 5.636a5 5 0 0 0-7.07 0L12 5.93l.707-.707a5 5 0 0 1 7.07 7.071l-.707.707.707.707a5 5 0 0 1-7.07 7.071l-.707-.707-.707.707a5 5 0 0 1-7.07-7.071l.707-.707-.707-.707a5 5 0 0 1 7.07-7.071l.707.707.707-.707z"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    @break
                                    @endswitch
                                </div>
                            </div>
                            <h3 class="home-card-title card-title mb-2">{{ $card['title'] }}</h3>
                            <p class="home-card-copy card-description">
                                {{ $card['description'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            </div>
            </div>
        </section>
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
        <section id="parent-bg" aria-label="{{ $findPerfectMatch['section_heading'] }}">
            <div class="container">
                <div class="home-panel home-match-panel">
                <div class="home-section">
                <div class="home-section-head">
                <span class="home-eyebrow bg-white text-primary">{{ translate('find_perfect_match') }}</span>
                <h2 class="home-title home-title--section text-white mobile-head">
                    {{ $findPerfectMatch['section_heading'] }}
                </h2>
                </div>

                <div id="left-bg" class="p-0 rounded option-bg-img">
                    <div class="row g-4 align-items-start">
                        <!-- Content Overlay -->
                        <div class="col-md-7 d-flex align-self-sm-center">
                            <div class="home-match-copy content-overlay w-100">
                                <h2 id="heading-find-match" class="home-title home-title--section mb-md-4 mb-2 lh-sm mobile-head">
                                    {{ $findPerfectMatch['hero_heading'] }}
                                </h2>
                                <p class="home-subtitle mb-0 mobile-p">
                                    {{ $findPerfectMatch['hero_description'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Filters -->
                        <aside id="right-filters" class="col-md-5" role="region" aria-labelledby="heading-filters">
                            <div class="home-filter-card">
                            <h2 id="heading-filters" class="filter-option text-center fw-bold mb-4 mobile-head">
                                {{ $findPerfectMatch['filter_title'] }}
                            </h2>

                            <form class="d-grid gap-3" aria-label="{{ translate('vehicle_filter_options') }}" action="{{ route('products') }}" method="GET">
                                <div class="mb-2">
                                    <label for="make" class="form-label">{{ $findPerfectMatch['make_label'] }}</label>
                                    <select id="make" name="make" class="form-select border my-1 vehicle-select2">
                                        <option value="">{{ $findPerfectMatch['make_placeholder'] }}</option>
                                        @foreach($makes as $make)
                                        <option value="{{ $make->getRawOriginal('name') }}" data-id="{{ $make->id }}" {{ ($selectedVehicleFilters['make'] ?? null) === $make->getRawOriginal('name') ? 'selected' : '' }}>{{ $make->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="mb-2">
                                    <label for="model" class="form-label">{{ $findPerfectMatch['model_label'] }}</label>
                                    <select id="model" name="model" class="form-select border my-1 vehicle-select2" disabled>
                                        <option value="">{{ $findPerfectMatch['model_placeholder'] }}</option>
                                    </select>

                                </div>

                                <div class="mb-2">
                                    <label for="year" class="form-label">{{ $findPerfectMatch['year_label'] }}</label>
                                    <select id="year" name="year" class="form-select border my-1 vehicle-select2" {{ !empty($selectedVehicleFilters['year']) ? '' : 'disabled' }}>
                                        <option value="">{{ $findPerfectMatch['year_placeholder'] }}</option>
                                        @foreach($years as $year)
                                        <option value="{{ $year->getRawOriginal('year') }}" {{ (string)($selectedVehicleFilters['year'] ?? $currentYear) === (string)$year->getRawOriginal('year') ? 'selected' : '' }}>{{ $year->year }}</option>
                                        @endforeach
                                    </select>
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
            </div>
        </section>


        @endif

        @if(isset($sectionData['why_join_us']) && $sectionData['why_join_us']['is_active'] == 1)
        @php
        $data = $sectionData['why_join_us']['data']['section'];
        @endphp

        <section class="">
            <div class="container">
                <div class="home-panel home-panel--soft">
                <div class="home-section">
                <div class="home-section-head">
                <h3 class="home-title home-title--section mobile-head tablet-head">
                    {{ $data['title'] }}
                </h3>

                <p class="home-subtitle">
                    {{ $data['subtitle'] }}
                </p>
                </div>
                <div class="mx-auto">
                    <div class="row">
                        <!-- Card 1 -->
                        @if(isset($data['cards'][0]))
                        <div class="col-md-6 mb-4">
                            <div class="home-story-card h-100">
                                <img src="{{ asset($data['cards'][0]['image']) }}" alt="{{ $data['cards'][0]['image_alt'] }}" class="img-fluid rounded mb-3">
                                <h4 class="home-card-title">{{ $data['cards'][0]['title'] }}</h4>
                                <p class="home-card-copy mb-0">{{ $data['cards'][0]['description'] }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Card 2 -->
                        @if(isset($data['cards'][1]))
                        <div class="col-md-6 mb-4">
                            <div class="home-story-card h-100">
                                <img src="{{ asset($data['cards'][1]['image']) }}" alt="{{ $data['cards'][1]['image_alt'] }}" class="img-fluid rounded mb-3">
                                <h4 class="home-card-title">{{ $data['cards'][1]['title'] }}</h4>
                                <p class="home-card-copy">{{ $data['cards'][1]['description'] }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if(isset($data['cards'][2]))
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="home-story-card home-story-card--split d-flex flex-column flex-md-row row-mobile-direction">
                                <!-- Left Text -->
                                <div class="col-md-6 p-4 d-flex flex-column justify-content-center flex-fill">
                                    <h4 class="home-card-title mb-3">{{ $data['cards'][2]['title'] }}</h4>
                                    <p class="home-card-copy">{{ $data['cards'][2]['description'] }}</p>
                                </div>

                                <!-- Right Image -->
                                <div class="col-md-6 become-dealer-img p-0">
                                    <img src="{{ asset($data['cards'][2]['image']) }}" alt="{{ $data['cards'][2]['image_alt'] }}" class="img-fluid h-100 object-fit-cover w-100">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            </div>
            </div>
        </section>

        @endif


        @php
        $wholesalerSection = $sectionData['wholesaler_section'] ?? null;
        @endphp
        @if($wholesalerSection && $wholesalerSection['is_active'] == 1)
        @php
        $data = $wholesalerSection['data'];
        $buttonText = $data['button_text'] ?? ($data['button']['text'] ?? '');

        @endphp

        <section class="py-5">
            <div class="container">
                <div class="home-panel home-wholesale-panel page-footer page-footer-mobile network-section">
                <div class="home-section" style="min-height:19rem">
                    <div class="row align-items-center">

                        <div class="col-md-6">
                            <span class="home-eyebrow bg-white text-primary">{{ translate('Dealer_network') }}</span>
                            <h1 class="home-title home-title--section mobile-head tablet-head">
                                {{ $data['title'] }}
                            </h1>
                            <p class="home-subtitle mb-4">
                                {{ $data['description'] }}
                            </p>
                            <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($data['button']['link'] ?? '') ?: '#' }}" class="home-cta bg-white text-primary shadow-none">
                                {{ $buttonText}}
                            </a>
                        </div>

                        <!-- Right Image -->
                        <div class="col-md-6 text-end position-relative hidden lg:block img-user-div home-wholesale-art">
                            <img src="{{ asset($data['image']) }}" alt="Wholeseller Image" class="img-fluid img-user-mobile"
                                style="max-height: 24rem;">
                        </div>

                    </div>
                </div>
                </div>
                </div>
        </section>
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
                <span class="home-eyebrow">{{ translate('trusted_by') }}</span>
                <h2 class="home-title home-title--section mobile-head tablet-head">{{translate('What Our Clients Say')}}</h2>
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
                        @endphp
                        <div class="swiper-slide ">
                            <div class="home-review-card">
                                <div class="p-4 d-flex align-items-center justify-content-center">
                                    <img src="{{ $clientImageSrc }}" class="img-fluid h-100 w-100 object-fit-cover"
                                        style="object-fit: cover; border-radius: 8px;" alt="client-img">
                                </div>
                                <div class="home-review-card__body d-flex flex-column justify-content-center">
                                    <span class="home-quote">"</span>
                                    <h5 class="home-card-meta mb-2">{{ $client['rating'] }}</h5>
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


        <!-- <section class="">
            <div class="container py-lg-5">
                <div class="d-flex flex-column gap-2 align-items-center text-center">
                    <h2 class="section-title mb-lg-5">{{translate('Frequently Asked Questions')}}</h2>

                </div>

                <div class="accordion__custom" id="accordion">
                    @foreach($helpTopics as $key=>$topic)
                    <div class="card">
                        <div class="card-header" id="heading-{{$key}}">
                            <h6 class="faq-title mb-0 py-2 collapsed" data-toggle="collapse" data-target="#collapse-{{$key}}"
                                aria-expanded="true" aria-controls="collapse-{{$key}}">
                                {{$topic->question}}
                            </h6>
                        </div>

                        <div id="collapse-{{$key}}" class="collapse" aria-labelledby="heading-{{$key}}"
                            data-parent="#accordion">
                            <div class="card-body">
                                {{$topic->answer}}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section> -->


        @php
        $downloadApp = $sectionData['download_app'] ?? null;
        $content = $downloadApp['data']['content'] ?? [];

        $resolveDownloadImage = function (?string $image) {
        if (empty($image)) {
        return '';
        }

        if (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) {
        return $image;
        }

        $normalized = ltrim($image, '/');
        if (\Illuminate\Support\Str::startsWith($normalized, ['storage/', 'uploads/'])) {
        return asset($normalized);
        }

        return asset('uploads/' . $normalized);
        };
        @endphp

        @if ($downloadApp && $downloadApp['is_active'] == 1)
        <section class="pt-lg-5">
            <div class="container">
                <div class="home-panel home-download-panel page-footer page-footer-mobile overflow-hidden">
                <div class="home-section">
                    <div class="row align-items-center">

                        <!-- Left Content -->
                        <div class="col-md-6">
                            <span class="home-eyebrow bg-white text-primary">{{ translate('Journey') }}</span>
                            <h1 class="home-title home-title--section mobile-head tablet-head text-center text-md-start">
                                {{ $content['heading']}}
                            </h1>

                            <div class="home-store-buttons d-flex gap-3 w-75 pt-3 mobile-w-100">
                                @if (!empty($content['android_button']['image']))
                                <a href="{{ $web_config['android']['link'] }}" role="button">
                                    <img src="{{ $resolveDownloadImage($content['android_button']['image'] ?? '') }}"
                                        alt="{{ $content['android_button']['alt'] ?? '' }}">
                                </a>
                                @endif

                                @if (!empty($content['ios_button']['image']))
                                <a class="" href="{{ $web_config['ios']['link'] }}" role="button">
                                    <img src="{{ $resolveDownloadImage($content['ios_button']['image'] ?? '') }}"
                                        alt="{{ $content['ios_button']['alt'] ?? '' }}">
                                </a>
                                @endif
                            </div>
                        </div>

                        <!-- Right Image -->
                        <div class="col-md-6 text-end position-relative hidden lg:block img-app-div home-device-art">
                            @if (!empty($content['mockup_image']['image']))
                            <img src="{{ $resolveDownloadImage($content['mockup_image']['image'] ?? '') }}"
                                alt="{{ $content['mockup_image']['alt'] ?? '' }}" class="img-fluid img-app"
                                style="max-height: 22rem;">
                            @endif
                        </div>

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
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/custom-slider.js') }}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/swiper-bundle.min.js') }}"></script>
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
            const modelPlaceholder = @json($findPerfectMatch['model_placeholder'] ?? 'Select Model');
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
