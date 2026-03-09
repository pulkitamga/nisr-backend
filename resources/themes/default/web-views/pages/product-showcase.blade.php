@extends('layouts.front-end.app')

@section('title', translate('Our_Products'))

@push('css')


@endpush

@section('content')

<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/cms/css/style.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/cms/css/swiper-bundle.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
@php
$slides = $products->toBase(); // Use base collection to improve performance slightly
$minSlidesNeeded = 10;
$total = $slides->count();

if ($total > 0 && $total < $minSlidesNeeded) {
    while ($slides->count() < $minSlidesNeeded) {
        $slides = $slides->concat($products);
    }
    $slides = $slides->take($minSlidesNeeded); // optional: restrict to exactly 10
}
@endphp

        @php
        $productSlide = $cmsData->where('type', 'main_banner')->where('is_active', 1)->first();
        @endphp

        @if($productSlide)

        <section class="home-pages-area home-pages space" id='homePage'>
            <div class="container th-container1744">
                <div class="row justify-content-center">

                </div>

            </div>
        </section>
        <section class="categorie-area categorie-style  ">
            <div class="container th-container1440 z-index-common">
                <div class="categorie-wrapp position-relative text-center text-xl-start d-xl-flex justify-content-between align-items-center">
                    <div class="title-area categorie-titlebox">
                        <h2 class="sec-title">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h2>
                        <p class="sec-text">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                    </div>
                    <div class="slider-area categorie-slider" style="overflow: hidden;">
                        <div class="mySwiperProduct th-slider text-end" id="innerSlider1"
                            data-slider-options='{"spaceBetween": 20,"breakpoints":{"0":{"slidesPerView":"3"},"576":{"slidesPerView":"5"},"768":{"slidesPerView":"6"},"992":{"slidesPerView":"6"},"1200":{"slidesPerView":"5"},"1400":{"slidesPerView":"6"}}}'>
                            <div class="swiper-wrapper">
                                @foreach($slides as $product)
                                <div class="swiper-slide">
                                    <div class="categorie-item">
                                        <a href="{{ route('product', $product->slug) }}">
                                            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                                alt="{{ $product->name }}" style="width: 75px">
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                    <div class="categorie-shape">
                        <img class="categorie-line d-none d-xl-block"
                            src="{{ asset('assets/front-end/img/placeholder/cate-shape.png') }}" alt="">
                        <button data-slider-next="#innerSlider1" class="categorie-icon d-none d-xl-block">
                            <i class="fa-solid fa-angle-right"></i> </button>
                    </div>
                </div>
            </div>
        </section>
        @endif


        @php
        $productSlide = $cmsData->where('type', 'core_product_slider')->where('is_active', 1)->first();
        @endphp

        @if($productSlide)

        <section class="pt-5 bg-white">
            <div class="container">
                <div>
                    <h1 class="font-bold text-center mb-3 mobile-head">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h1>
                    <p class=" font-small sec-text text-center mb-5">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                </div>
                <div class="swiper mySwiperProducts pb-5">
                    <div class="swiper-wrapper">
                        @foreach($slides as $product)
                        @php
                        $overallRating = getOverallRating($product->reviews);
                        @endphp
                        <div class="swiper-slide card text-white text-center position-relative shadow-sm bg-gradient shadow-border"
                            style=" padding: 1.5rem; border-radius: 1rem; color:#239e92 !important;"> <a href="{{ route('product', $product->slug) }}">
                                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                    class="card-img-top object-fit-contain" style="height: 200px;"
                                    alt="{{ $product->name }}">
                            </a>

                            <div class="card-body d-flex flex-column justify-content-between">
                                {{-- Product Name --}}
                                <h5 class="card-title text-truncate">
                                    <a href="{{ route('product', $product->slug) }}"
                                        class="text-decoration-none text-dark">
                                        {{ translate( $product->name) }}
                                    </a>
                                </h5>
                                <div class="d-flex flex-wrap align-items-center mb-2 pro">
                                    <div class="star-rating me-2">
                                        @for($inc=1;$inc<=5;$inc++) @if ($inc <=(int)$overallRating[0]) <i
                                            class="tio-star text-warning"></i>
                                            @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 &&
                                                $overallRating[0]> ((int)$overallRating[0]))
                                                <i class="tio-star-half text-warning"></i>
                                                @else
                                                <i class="tio-star-outlined text-warning"></i>
                                                @endif
                                                @endfor
                                    </div>
                                    <span class="d-inline-block align-middle mt-1 me-md-2 me-sm-0 fs-14 text-muted">({{$overallRating[0]}})</span>
                                    <span
                                        class="font-regular font-for-tab d-inline-block font-size-sm text-body align-middle mt-1 ms-1 me-md-2 me-1 ps-md-2 ps-sm-1 pe-md-2 pe-sm-1"><span
                                            class="web-text-primary">{{$overallRating[1]}}</span>
                                        {{translate('reviews')}}</span>

                                </div>

                                {{-- Price --}}
                                <div class="mb-2">
                                    @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                    <del class="text-muted small">
                                        {{ webCurrencyConverter($product->unit_price) }}
                                    </del>
                                    @endif
                                    <span class="fw-bold text-danger ms-1">
                                        {{ getProductPriceByType(product: $product, type: 'discounted_unit_price',
                                        result:
                                        'string') }}
                                    </span>
                                </div>



                                {{-- Optional: Description (shortened) --}}
                                @if($product->description)
                                <p class="card-text text-muted small mt-2 text-truncate"
                                    title="{{ strip_tags($product->description) }}">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 60) }}
                                </p>
                                @endif

                                {{-- Out of Stock --}}
                                @if($product->product_type === 'physical' && $product->current_stock <= 0) <span
                                    class="badge bg-warning text-dark mt-2">{{ translate('out_of_stock') }}</span>
                                    @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </section>

        @endif
        <div class="space bg-smoke py-4 py-lg-5" id="responsive">
            <div class="container">


                @php
                $productSlide = $cmsData->where('type', 'request_card_1')->where('is_active', 1)->first();
                @endphp

                @if($productSlide)
                <div class="feature-card style-2">
                    <div class="feature-card-bg bg-mask"
                        data-mask-src="{{ asset('assets/front-end/img/feature/fea-bg-mask.png') }}">

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="feature-img text-center text-lg-start">
                                    <img src="{{ Storage::url($productSlide->image) }}" alt="Filter Image">
                                </div>
                            </div>

                            <div class="col-lg-5 col-md-6 ms-xl-5 ps-xl-4 padd-mobile-service">
                                <div class="title-area feature2-titlebox">
                                    {{-- <span class="sec-subtitle">Lifetime Updates</span> --}}
                                    <h2 class="sec-title">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h2>
                                </div>
                                <p class="sec-text">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                                <div class="feature-action text-start mt-4">
                                    <a href="{{ $productSlide->button_link ?? '' }}"
                                        class="th-btn text-decoration-none"> {{translate('Request_Service') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @php
                $productSlide = $cmsData->where('type', 'request_card_2')->where('is_active', 1)->first();
                @endphp

                @if($productSlide)
                <div class="feature-card style-2">
                    <div class="feature-card-bg bg-mask"
                        data-mask-src="{{ asset('assets/front-end/img/feature/fea-bg-mask.png') }}">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-lg-5 order-1 order-md-0 padd-mobile-service">
                                <div class="title-area feature2-titlebox">
                                    {{-- <span class="sec-subtitle">24/7 Support</span> --}}
                                    <h2 class="sec-title">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h2>
                                </div>
                                <p class="sec-text  mt-lg-2">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                                <div class="feature-action text-start mt-4">
                                    <a href="{{ $productSlide->button_link ?? '' }}"
                                        class="th-btn text-decoration-none"> {{translate('Request_Service') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 order-0 order-md-1 text-center text-lg-end d-flex justify-content-end">
                                <div class="feature-img me-xl-4 pe-xl-5">
                                    <img src="{{ Storage::url($productSlide->image) }}" alt="Filter Image">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @php
                $productSlide = $cmsData->where('type', 'request_card_3')->where('is_active', 1)->first();
                @endphp

                @if($productSlide)
                <div class="feature-card style-2">
                    <div class="feature-card-bg bg-mask"
                        data-mask-src="{{ asset('assets/front-end/img/feature/fea-bg-mask.png') }}">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="feature-img text-center text-lg-start">
                                    <img src="{{ Storage::url($productSlide->image) }}" alt="Filter Image">

                                </div>
                            </div>
                            <div class="col-lg-5 col-md-6 ms-xl-5 ps-xl-4 padd-mobile-service">
                                <div class="title-area feature2-titlebox">
                                    {{-- <span class="sec-subtitle">Fully Responsive Design</span> --}}
                                    <h2 class="sec-title">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h2>
                                </div>
                                <p class="sec-text  mt-lg-2">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                                <div class="feature-action text-start mt-4">
                                    <a href="{{ $productSlide->button_link ?? '' }}"
                                        class="th-btn text-decoration-none"> {{translate('Request_Service') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>


        @php
        $productSlide = $cmsData->where('type', 'feature_product')->where('is_active', 1)->first();
        @endphp

        @if($productSlide)
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="mb-5 text-center fw-bold ">{{ getTranslatedValue($productSlide, 'heading', $productSlide->heading) }}</h2>
                <p class="mb-5 text-center fw-bold ">{{ getTranslatedValue($productSlide, 'description', $productSlide->description) }}</p>
                <div class="row g-4">
                    @foreach($featuredProducts->take(4) as $product)
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white text-center position-relative shadow-sm bg-gradient"
                            style=" padding: 1.5rem; border-radius: 1rem; color:#239e92 !important;"> <a href="{{ route('product', $product->slug) }}" class="d-block"
                                style="height: 200px; overflow: hidden;">
                                <img src="{{ getStorageImages($product->thumbnail_full_url, 'product') }}"
                                    class="w-100 h-100 object-fit-contain p-3" alt="{{ $product->name }}">
                            </a>
                            <div class="card-body px-3 pb-4 pt-2">
                                <a href="{{ route('product', $product->slug) }}" class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                                <p class="text-muted small mb-2">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 60) }}
                                </p>
                                <p class="fw-bold text-success mb-2">
                                    {{ getProductPriceByType($product, 'discounted_unit_price', 'string') }}
                                </p>

                                @php $rating = getOverallRating($product->reviews); @endphp
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-warning text-dark small">
                                        ★ {{ number_format($rating[0], 1) }}
                                    </span>
                                    <span class="text-muted small">({{ $rating[1] }} {{ translate('reviews') }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <script>
            document.querySelectorAll('.bg-mask').forEach(function(el) {
                const src = el.getAttribute('data-mask-src');
                if (src) {
                    el.style.maskImage = `url(${src})`;
                    el.style.webkitMaskImage = `url(${src})`; // For Safari
                    el.style.maskRepeat = 'no-repeat';
                    el.style.maskSize = 'cover';
                    el.style.maskPosition = 'center';
                    el.style.webkitMaskRepeat = 'no-repeat';
                    el.style.webkitMaskSize = 'cover';
                    el.style.webkitMaskPosition = 'center';
                }
            });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Swiper JS -->
        <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loopEnabled = {{
                        count($slides) > 6 ? 'true' : 'false'
                    }};
                    
                const swiperTwo = new Swiper(".mySwiperProduct", {
                    slidesPerView: 3,
                    spaceBetween: 15,
                    loop: loopEnabled,
                    autoplay: loopEnabled ? {
                        delay: 2500,
                        disableOnInteraction: false
                    } : false,
                    breakpoints: {
                        0: {
                            slidesPerView: 4
                        },
                        640: {
                            slidesPerView: 4
                        },
                        1024: {
                            slidesPerView: 4
                        }
                    }
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                 const loopEnabled = {{
                        count($slides) > 6 ? 'true' : 'false'
                    }};
                    
                const swiperOne = new Swiper(".mySwiperProducts", {
                    slidesPerView: 3,
                    spaceBetween: 15,
                    loop: loopEnabled,
                    autoplay: loopEnabled ? {
                        delay: 2500,
                        disableOnInteraction: false
                    } : false,
                    breakpoints: {
                        0: {
                            slidesPerView: 1
                        },
                        640: {
                            slidesPerView: 2
                        },
                        1024: {
                            slidesPerView: 4
                        }
                    }
                });
            });
        </script>
        @endsection
