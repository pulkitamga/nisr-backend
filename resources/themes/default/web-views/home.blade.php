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



@endpush

@section('content')

@if(isset($sectionData['main_banner']) && $sectionData['main_banner']['is_active'] == 1)
@php
$banners = array_filter($sectionData['main_banner']['data'], fn($banner) => $banner['is_active'] ?? false);
$direction = Session::get('direction');
@endphp

@if(count($banners) > 0)
<section class="py-4 carousel-mobile">
    <div class="container">
        <div class="position-relative w-100 overflow-hidden rounded ">
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
                <div class="d-flex align-items-center h-100 text-white px-3 px-md-5 position-relative pb-4 pb-md-0">
                    <div class="container">
                        <div class="col-lg-6">
                            <h1 class="h3 h1-md fw-bold mb-3 text-white banner-head">{{ $banner['heading'] }}</h1>
                            <p class="lead mb-4 banner-p">{{ $banner['paragraph'] }}</p>
                            <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($banner['buttonLink'] ?? '') ?: '#' }}" class="btn btn-primary rounded-pill px-4 py-2">
                                {{ $banner['buttonText'] }}
                            </a>
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

<section class="py-md-5 py-4 text-center">
    <div class="container rtl pb-md-4 pb-0 px-0 px-md-3 container-mobile">
        <div class="mx-auto" style="max-width: 48rem;">
            <h2 class="display-5 fw-bold text-dark mb-3 trusted-by">
                {{ $trusted['heading'] }}
                <span class="text-primary">{{ $trusted['year'] }}</span>
            </h2>
            <p class="lead text-muted font-semi-bold">
                {{ $trusted['paragraph'] }}
            </p>
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
        <div class="container  mx-auto px-4 my-lg-5 container-mobile">
            <h2 class="text-4xl font-extrabold text-teal-600 mb-3 text-center mobile-head">{{ $trusted['section_title'] }}</h2>
            <p class="text-lg sm:text-xl text-gray-500 text-center mb-3">
                {{ $trusted['section_paragraph'] }}
            </p>
            <div class="swiper mySwiperThree">
                <div class="swiper-wrapper">
                    @foreach($slides as $product)
                    @php
                    $overallRating = getOverallRating($product->reviews);
                    @endphp
                    <div class="swiper-slide card text-white text-center position-relative swiper-home"
                        style=" padding: 1.5rem; border-radius: 1rem; color:#239e92 !important;">
                        <div
                            class="product-single-hover rounded-lg h-[420px] flex flex-col justify-between">
                            <div class="relative">
                                <div class="inline_product relative">
                                    @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                    <span
                                        class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded z-10">
                                        -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                                    </span>
                                    @endif

                                    <a href="{{ route('product', $product->slug) }}">
                                        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                            alt="{{ $product->name }}" class="w-full h-[180px] object-contain rounded" />
                                    </a>

                                    <!-- Quick View Button -->
                                    <div class="absolute top-2 right-2">
                                        <a class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-full action-product-quick-view"
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
                                        class="absolute bottom-2 left-2 bg-yellow-400 text-xs px-2 py-1 rounded text-white">
                                        {{ translate('out_of_stock') }}
                                        </span>
                                        @endif
                                </div>

                                <div class="py-3">
                                    @if($overallRating[0] != 0)
                                    <div class="flex items-center space-x-1 text-yellow-400 text-sm">
                                        @for($i = 1; $i <= 5; $i++) @if ($i <=(int)$overallRating[0]) <i class="tio-star">
                                            </i>
                                            @elseif ($i <= (int)$overallRating[0] + 1 && $overallRating[0]>
                                                (int)$overallRating[0])
                                                <i class="tio-star-half"></i>
                                                @else
                                                <i class="tio-star-outlined"></i>
                                                @endif
                                                @endfor
                                                <span class="text-gray-600 text-xs ms-1">({{ count($product->reviews)
                                                }})</span>
                                    </div>
                                    @endif

                                    <a href="{{ route('product', $product->slug) }}"
                                        class="block text-sm font-semibold mt-2 text-gray-800">
                                        {{ $product->name }}
                                    </a>

                                    <div class="mt-1">
                                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                        <del class="text-gray-400 text-sm">
                                            {{ webCurrencyConverter(amount: $product->unit_price) }}
                                        </del>
                                        @endif
                                        <span class="text-red-600 font-bold ms-1">
                                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price',
                                        result: 'string') }}
                                        </span>
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

        <section class="bg-light py-5">
        <div class="container">
            <h2 class="text-4xl font-extrabold text-teal-600 mb-3 text-center mobile-head">{{ $trustedCategory['heading'] }}</h2>
            <p class="text-lg sm:text-xl text-gray-500 text-center mb-3">
                {{ $trustedCategory['paragraph'] }}
            </p>

            <div class="swiper mySwiperOne">
                <div class="swiper-wrapper text-center">

                    @foreach($finalCategories as $category)
                    <div class="swiper-slide card text-white text-center position-relative shadow-sm bg-gradient"
                        style=" padding: 1.5rem; border-radius: 1rem; color:#239e92 !important;">
                        <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}">
                            <img src="{{ getStorageImages(path:$category->icon_full_url, type:'category') }}"
                                alt="{{ $category->name }}"
                                class="w-100 mb-3" style="height: 160px; object-fit: contain;">
                        </a>

                        <p class="text-uppercase font-semi-bold mb-1"> {{translate('CATCH_BIG')}}
                        </p>
                        <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}" class="bg-danger text-white text-uppercase fw-bold px-5 py-2 rounded-pill mb-2"
                            style="font-size: 1rem;"> {{translate('DEALS')}}
                        </a>
                        <p class="text-uppercase font-semi-bold mb-3"> {{ strtoupper($category->name) }}</p>

                        <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}"
                            class="btn btn-sm btn-light text-primary fw-semibold d-flex align-items-center justify-content-center">
                            {{translate('Shop now')}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-right ms-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 1 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                            </svg>
                        </a>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
        </section>


        @endif


        @if(isset($sectionData['why_choose_us']) && $sectionData['why_choose_us']['is_active'] == 1)
        @php
        $section = $sectionData['why_choose_us']['data']['section'];
        @endphp

        <section class="py-5 text-center">
            <div class="container px-3">
                <h2 class="display-5 fw-bold mb-2 mb-md-3 why-chose mobile-head">{{ $section['title'] }}</h2>
                <p class="font-size-lg mb-md-5 mb-3  fs-5 mobile-p">
                    {!! nl2br(e($section['subtitle'])) !!}
                </p>

                <div class="row gy-4 gx-3">
                    @foreach($section['cards'] as $card)
                    <div class="col-12 col-sm-6 col-lg-3 my-3 px-md-3">
                        <div class="custom-card h-100  text-center">
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
                            <h3 class="h5 fw-bold text-dark card-title mb-2">{{ $card['title'] }}</h3>
                            <p class="text-muted font-size-md  card-description">
                                {{ $card['description'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach
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
        <section id="parent-bg" aria-label="{{ $findPerfectMatch['section_heading'] }}" class="pb-4 pb-md-0">
            <div class="container py-lg-5">
                <h2 class="text-center fw-bold mb-5 display-6 display-md-5 mobile-head">
                    {{ $findPerfectMatch['section_heading'] }}
                </h2>

                <div id="left-bg" class="bg-light p-4 p-md-5 rounded option-bg-img" style=" background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('{{ asset('assets/front-end/img/find-bg-img.jpg') }}');">
                    <div class="row g-4 align-items-start">
                        <!-- Content Overlay -->
                        <div class="col-md-7 d-flex align-self-sm-center">
                            <div class="content-overlay w-100">
                                <h2 id="heading-find-match" class="display-4 fw-bold mb-md-4 mb-2 lh-sm mobile-head">
                                    {{ $findPerfectMatch['hero_heading'] }}
                                </h2>
                                <p class="fs-4 mb-0 font-size-lg text-find-match text-white mobile-p">
                                    {{ $findPerfectMatch['hero_description'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Filters -->
                        <aside id="right-filters" class="col-md-5 bg-white p-lg-5 p-3 shadow rounded" role="region" aria-labelledby="heading-filters">
                            <h2 id="heading-filters" class="filter-option text-center fw-bold mb-4 text-shadow mobile-head">
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

                                <button type="submit" class="btn btn-dark w-100">
                                    {{ $findPerfectMatch['apply_button_text'] }}
                                </button>
                            </form>
                        </aside>
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
            <div class="container py-5">
                <h3 class="text-center h1 fw-bold text-dark mb-3 mobile-head tablet-head">
                    {{ $data['title'] }}
                </h3>

                <p class="text-center text-muted mb-4">
                    {{ $data['subtitle'] }}
                </p>
                <div class="mx-auto">
                    <div class="row">
                        <!-- Card 1 -->
                        @if(isset($data['cards'][0]))
                        <div class="col-md-6 mb-4">
                            <div class="bg-white p-4 rounded shadow-sm border-top border-4 h-100">
                                <img src="{{ asset($data['cards'][0]['image']) }}" alt="{{ $data['cards'][0]['image_alt'] }}" class="img-fluid rounded mb-3">
                                <h4 class=" fw-bold">{{ $data['cards'][0]['title'] }}</h4>
                                <p class="text-muted mb-0">{{ $data['cards'][0]['description'] }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Card 2 -->
                        @if(isset($data['cards'][1]))
                        <div class="col-md-6 mb-4">
                            <div class="bg-white p-4 rounded shadow-sm border-start border-4 border-indigo h-100">
                                <img src="{{ asset($data['cards'][1]['image']) }}" alt="{{ $data['cards'][1]['image_alt'] }}" class="img-fluid rounded mb-3">
                                <h4 class=" fw-bold">{{ $data['cards'][1]['title'] }}</h4>
                                <p class="text-muted">{{ $data['cards'][1]['description'] }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if(isset($data['cards'][2]))
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-md-row bg-white rounded shadow overflow-hidden row-mobile-direction">
                                <!-- Left Text -->
                                <div class="col-md-6 p-4 d-flex flex-column justify-content-center flex-fill">
                                    <h4 class="fw-bold mb-3">{{ $data['cards'][2]['title'] }}</h4>
                                    <p class="text-muted">{{ $data['cards'][2]['description'] }}</p>
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
            <div class="container py-lg-5">
                <div style="color: white; padding: 65px 30px; position: relative; overflow: visible; border-radius: 12px; min-height:19rem"
                    class="page-footer page-footer-mobile network-section">
                    <div class="row align-items-center">

                        <div class="col-md-6">
                            <h1 class="fw-bold mb-3  text-white font-extrabold text-9xl mobile-head tablet-head">
                                {{ $data['title'] }}
                            </h1>
                            <p class="mb-4">
                                {{ $data['description'] }}
                            </p>
                            <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($data['button']['link'] ?? '') ?: '#' }}" class="btn btn-light px-4 rounded-pill fw-semibold">
                                {{ $buttonText}}
                            </a>
                        </div>

                        <!-- Right Image -->
                        <div class="col-md-6 text-end position-relative hidden lg:block img-user-div">
                            <img src="{{ asset($data['image']) }}" alt="Wholeseller Image" class="img-fluid img-user-mobile"
                                style="max-height: 28rem; position: absolute; top: -18rem; inset-inline-end: 0; object-fit: contain;">
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
            <div class="container py-md-5 py-4">
                <h2 class="text-4xl font-extrabold text-teal-600 mb-3 text-center mobile-head">{{ $trusted['heading'] }}</h2>
                <p class="text-lg sm:text-xl text-gray-500 text-center mb-3">
                    {{ $trusted['paragraph'] }}
                </p>

                <div class="row g-4">
                    <!-- Left Featured Blog -->
                    @if($latestPosts->count() > 0)
                    @php $featured = $latestPosts->first(); @endphp

                    <div class="col-md-7 col-lg-8">
                        <div class="card shadow border-0 h-100">
                            <div class="overflow-hidden" style="max-height: 23rem;">
                                <img src="{{ asset('storage/blog/image/' . $featured->image) }}"
                                    class="w-100 img-fluid object-fit-cover rounded-top"
                                    style="height: 100%; object-fit: cover;" alt="{{ $featured->heading }}">
                            </div>
                            <div class="card-body">
                                <a href="{{ route('frontend.blog.details', ['slug' => $featured?->slug]) }}"
                                    class="fw-semibold font-size-xl text-dark text-decoration-none d-block mb-2 blog-card-head">
                                    {{ \Illuminate\Support\Str::limit(strip_tags( $featured->title ), 80) }}
                                </a>
                                <p class="text-muted font-size-base ">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($featured->description), 200) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-5 col-lg-4 d-flex flex-column gap-3">
                        @foreach($latestPosts->skip(1)->take(3) as $post)
                        <div class="card flex-row shadow-sm border-0">
                            <div class="overflow-hidden rounded-start w-fit-content" style="min-width: 10rem;">
                                <img src="{{ asset('storage/blog/image/' . $post->image) }}" alt="{{ $post->heading }}"
                                    class="img-fluid h-100 object-fit-cover rounded-sm">
                            </div>
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}"
                                        class="fw-semibold text-dark text-decoration-none d-block">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->title), 50) }}
                                    </a>
                                    <p class="text-muted small mb-2">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->description), 50) }}
                                    </p>
                                </div>
                                <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}" class="small text-primary fw-bold">
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
                        class="btn px-4 py-2 text-white fw-semibold shadow-sm"
                        style="background-color: #129d91; transition: background-color 0.3s;"
                        onmouseover="this.style.backgroundColor='#10534d';"
                        onmouseout="this.style.backgroundColor='#129d91';">
                        {{ translate('Read More') }}
                    </a>
                </div>
            </div>
        </section>
        @endif

        @endif






        @if(isset($sectionData['client_review']) && $sectionData['client_review']['is_active'] == 1)
        <section class="">
            <div class="container py-lg-5">
                <h2 class="fw-bold mb-4 text-center pb-3 mobile-head tablet-head">{{translate('What Our Clients Say')}}</h2>

                <div class="swiper mySwiperTwo pb-5">
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
                            <div class="card d-flex flex-row overflow-hidden shadow"
                                style="width: 100%; height: 300px; background-color: #fff; border-radius: 12px;">
                                <div class="w-50 p-4 d-flex align-items-center justify-content-center">
                                    <img src="{{ $clientImageSrc }}" class="img-fluid h-100 w-100 object-fit-cover"
                                        style="object-fit: cover; border-radius: 8px;" alt="client-img">
                                </div>
                                <div class="w-50 p-4 d-flex flex-column justify-content-center">
                                    <h5 class="mb-1">{{ $client['rating'] }}</h5>
                                    <h6 class="mb-2 fw-bold">{{ $client['name'] }}</h6>
                                    <p>{{ $client['review'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
            <div class="container py-lg-5">

                <div style="color: white; padding: 82px 30px; position: relative; overflow: visible; border-radius: 12px;"
                    class="page-footer page-footer-mobile overflow-hidden">
                    <div class="row align-items-center">

                        <!-- Left Content -->
                        <div class="col-md-6">
                            <h1 class="fw-bold mb-3 text-white mobile-head tablet-head text-center text-md-start" style="font-size: xxx-large;">
                                {{ $content['heading']}}
                            </h1>

                            <div class="Chat d-flex gap-8 w-75 pt-3 mobile-w-100">
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
                        <div class="col-md-6 text-end position-relative hidden lg:block img-app-div">
                            @if (!empty($content['mockup_image']['image']))
                            <img src="{{ $resolveDownloadImage($content['mockup_image']['image'] ?? '') }}"
                                alt="{{ $content['mockup_image']['alt'] ?? '' }}" class="img-fluid img-app"
                                style="max-height: 20rem; position: absolute; top: -9rem; inset-inline-end: 5rem; object-fit: contain; z-index: 1;">
                            @endif
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
