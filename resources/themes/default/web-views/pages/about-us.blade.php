@extends('layouts.front-end.app')

@section('title', translate('About Us'))

@section('content')
<style>
    .collapse {
        visibility: visible !important;
    }

    .navbar-collapse {

        flex-grow: 0 !important;
    }
    .bg-web-primary {
    background-color: var(--web-primary);
}

.border-web-primary{
    border:  var(--web-primary);
}

</style>

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

 @if($heroItems && count($heroItems) > 0)
<section class="py-4">
    <div class="container">
        <div class="position-relative rounded overflow-hidden">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @foreach($heroItems as $item)
                    <div class="swiper-slide position-relative" style="height: 30rem;">
                        <img src="{{ asset('storage/' . $item->image) }}"
                            class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 blur-xs z-n1"
                            alt="{{ getTranslatedValue($item, 'heading', $item->heading) }}"
                            style="filter: blur(2px); border-radius: 1rem;">

                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-white text-center"
                            style="background-color: rgba(0,0,0,0.4); z-index: 1; border-radius: 1rem;">
                            <div class="px-3">
                                <h1 class="display-4 fw-bold mb-3 banner-head-about">
                                    {{ getTranslatedValue($item, 'heading', $item->heading) }}
                                </h1>
                                <p class="lead banner-p">
                                    {{ getTranslatedValue($item, 'subheading', $item->subheading) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="swiper-pagination position-absolute justify-content-center bottom-0 start-50 translate-middle-x d-flex gap-2 z-3 mt-3"></div>
            </div>
        </div>
    </div>
</section>
@endif



<!-- About Company -->
@if($whoWeAre)
<section class="py-lg-5 py-4 bg-white">
    <div class="container text-center">
        <h2 class="fw-bold text-primary mb-4 display-5 mobile-head">
            {{ getTranslatedValue($whoWeAre, 'title', $whoWeAre->title) }}
        </h2>
        <div class="col-lg-10 mx-auto">
            <p class="text-muted fs-5">
                {!! getTranslatedValue($whoWeAre, 'content', $whoWeAre->content) !!}
            </p>
        </div>
    </div>
</section>
@endif




<!-- Our Products -->
@if($products->count())
<section class="py-lg-5 py-4 bg-white">
    <div class="container">
        <h2 class="text-center text-primary fw-bold display-6 mb-lg-5 mb-4 mobile-head">
            {{ translate('our_core_products') }}
        </h2>

        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow h-100 transition-all" style="border: 1px solid rgba(19, 157, 145, 0.2);">
                    <img src="{{ asset('storage/' . $product->image) }}"
                        class="card-img-top"
                        alt="{{ getTranslatedValue($product, 'title', $product->title) }}"
                        style="height: 250px; object-fit: cover; border-top-left-radius: .75rem; border-top-right-radius: .75rem;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary fw-semibold">
                            {{ getTranslatedValue($product, 'title', $product->title) }}
                        </h5>
                        <p class="card-text small mt-2">
                            {{ getTranslatedValue($product, 'description', $product->description) }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif





<!-- Our Mission -->
@if($mission)
<section class="py-lg-5 bg-white py-4 text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-11">
                <h2 class="fw-bold text-primary display-5 mb-4 mobile-head">
                    {{ getTranslatedValue($mission, 'title', $mission->title) }}
                </h2>
                <p class="fs-5 text-muted">
                    {!! getTranslatedValue($mission, 'content', $mission->content) !!}
                </p>
            </div>
        </div>
    </div>
</section>
@endif





<!-- Our Journey Timeline -->
<section class="py-lg-5 py-4 bg-white">
    <div class="container">
        <h2 class="text-center text-primary fw-bold display-5 mb-lg-5 mb-4 mobile-head">
            {{ translate('milestones_over_the_years') }}
        </h2>

        <div class="row g-4">
            @foreach ($timelines as $index => $timeline)
            @php
            $isColored = $index % 2 === 0;
            @endphp

            <div class="col-12 col-md-6 col-lg-3">
                <div class="p-4 h-100 rounded-4 shadow-lg d-flex flex-column rounded-10 align-items-center justify-content-center text-center transition milestone-card
                   {{ $isColored ? 'text-white bg-web-primary' : 'border border-web-primary text-primary' }}">
                    @if($timeline->image)
                    <img src="{{ asset('storage/' . $timeline->image) }}"
                        class="mb-3"
                        style="width: 60px; height: 60px;"
                        alt="{{ getTranslatedValue($timeline, 'title', $timeline->title) }} Icon">
                    @endif
                    <h3 class="fs-4 fw-semibold mb-2 {{ $isColored ? 'text-white' : 'text-primary' }}">{{ $timeline->year }}</h3>
                    <p class="small mb-0">
                        {{ getTranslatedValue($timeline, 'description', $timeline->description) }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .milestone-card {
        transition: transform 0.3s ease;
    }

    .milestone-card:hover {
        transform: scale(1.05);
    }
</style>



<section class="py-lg-5 py-4 bg-white">
    <div class="container">
        <h2 class="text-center text-primary fw-bold display-5 mb-lg-5 mb-4 mobile-head">
            {{ translate('our_trusted_dealers') }}
        </h2>

        <div class="row g-4">
            @forelse ($dealers as $dealer)
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-4 dealer-card transition">
                    <img src="{{ $dealer->image ? asset('storage/' . $dealer->image) : asset('images/default-dealer.jpg') }}"
                        class="card-img-top rounded-top-4"
                        style="height: 180px; object-fit: cover;"
                        alt="{{ getTranslatedValue($dealer, 'dealer_name', $dealer->dealer_name) }}">

                    <div class="card-body text-center">
                        <h5 class="card-title text-primary fw-bold text-truncate">
                            {{ getTranslatedValue($dealer, 'dealer_name', $dealer->dealer_name) }}
                        </h5>
                        <p class="card-subtitle  small mb-1">
                            {{ getTranslatedValue($dealer, 'location', $dealer->location ?? 'N/A') }}
                        </p>
                        <p class="card-text text-muted small">
                            {{ getTranslatedValue($dealer, 'description', $dealer->description ?? 'N/A') }}
                        </p>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">
                    {{ translate('no_dealers_found') }}
                </p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<style>
    .dealer-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .dealer-card:hover {
        transform: scale(1.05);
        box-shadow: 0 0 25px rgba(19, 157, 145, 0.2);
    }
</style>



<!-- Swiper Init Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper(".mySwiper", {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            effect: 'coverflow',

            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            }
        });
    });
</script>

@endsection