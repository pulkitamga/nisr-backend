@extends('layouts.front-end.app')

@section('title', 'Careers at NISR')

@push('css_or_js')

<style>
    .collapse {
        visibility: visible !important;
    }

    .navbar-collapse {

        flex-grow: 0 !important;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
@endpush

@section('content')



<section>
    <div class="container">

        <div class="rounded-10 my-4 text-center d-sm-block position-relative blog-banner-container">
            <div class="text--primary w-100 position-absolute">
                <img class="blog-banner-svg svg" src="{{ theme_asset(path: 'public/assets/front-end/img/blogs/background.svg') }}" alt="">
            </div>
            <div class="py-5 px-3">
                <h1 class="mb-2 fw-semibold h2">
                    {{translate('Career') }}
                </h1>
                <p class="fs-20 mb-0">
                    {{ translate('Career') }}
                </p>
            </div>
        </div>
        <!-- <div class="d-block d-sm-none">
            <h2 class="fs-16 fw-semibold my-3 text-center">
                {{ translate('Contact_us') }}
            </h2>
             <p class="fs-20 mb-0">
                {{ translate('Contact_us') }}
                        </p>
        </div> -->
    </div>
</section>



<section class="bg-white py-5">
    <div class="container text-center">
        <h2 class="mb-5 fw-bold animate__animated animate__fadeInUp mobile-head">
            {{ translate('Why Work With NISR?') }}
        </h2>

        <div class="row">
            @foreach ($careerCards as $index => $card)
            <div class="col-12 col-sm-6 col-md-3 mb-4 mb-md-0">
                <div class="p-4 rounded shadow h-100 transition-all animate__animated animate__zoomIn {{ $index > 0 ? 'delay-' . ($index * 100) : '' }}"
                    style="transition: transform 0.3s ease;">

                    <div class="mb-3">
                        <i class="{{ $card->icon }} fs-2 text-primary"></i>
                    </div>

                    <h5 class="fw-bold mb-2">
                        {{ getTranslatedValue($card, 'title', $card->title) }}
                    </h5>

                    <p class=" small">
                        {{ richTextToPlainText(getTranslatedValue($card, 'description', $card->description)) }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>




<section id="open-positions" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 display-5 fw-bold animate__animated animate__fadeInUp mobile-head">
            {{ translate('Current Openings') }}
        </h2>

        <!-- Job Cards -->
        <div class="row ">
            @foreach ($careerJobs as $job)
            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="job-card p-4 bg-white rounded-4 shadow-sm border border-success-subtle h-100 animate__animated animate__fadeInUp transition-all position-relative ">
                    <h3 class="h5 fw-bold text-primary mb-2">
                        {{ getTranslatedValue($job, 'title', $job->title) }}
                    </h3>

                    <p class="text-muted mb-3 small">
                        <strong>{{ translate('Experience') }}:</strong> {{ getTranslatedValue($job, 'experience', $job->experience) }} |
                        <strong>{{ translate('Location') }}:</strong> {{ getTranslatedValue($job, 'location', $job->location) }}
                    </p>

                    <p class="small text-dark mb-2">
                        <strong>{{ translate('Skills') }}:</strong><br>
                        {{ \Illuminate\Support\Str::words(richTextToPlainText(getTranslatedValue($job, 'skills', $job->skills)), 20, '...') }}
                    </p>

                    <p class="small text-dark mb-3">
                        <strong>{{ translate('Description') }}:</strong><br>
                        {{ \Illuminate\Support\Str::words(richTextToPlainText(getTranslatedValue($job, 'job_description', $job->job_description ?? '')), 30, '...') }}
                    </p>

                    <a href="{{ route('career.job.detail', ['slug' => $job->id]) }}"
                        class="stretched-link text-decoration-none fw-bold text-primary hover-underline transition">
                        {{ translate('Apply Now') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>





<section class="bg-white py-5">
    <div class="container">
        <h2 class="text-center mb-5 display-6 fw-bold animate__animated animate__fadeInUp mobile-head">
            {{ translate('Perks_&_Benefits') }}
        </h2>

        <div class="row">
            @foreach($careerBenefits as $index => $benefit)
            <div class="col-12 col-md-3 mb-4 mb-md-0">
                <div
                    class="benefit-card bg-white p-4 rounded-4 shadow-sm text-center animate__animated animate__fadeInUp h-100"
                    data-index="{{ $index }}">
                    <i class="{{ $benefit->icon }} text-primary mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="fw-semibold ">
                        {{ getTranslatedValue($benefit, 'title', $benefit->title) }}
                    </h5>
                    <p class="text-muted mt-2 small">
                        {{ richTextToPlainText(getTranslatedValue($benefit, 'description', $benefit->description)) }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>



<script>
    // Function to generate a random color
    function generateRandomColor() {
        const colors = ['#ff4081', '#4caf50', '#3f51b5', '#f44336', '#2196f3', '#ff9800'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    // Apply random color to each icon after the page has loaded
    document.addEventListener('DOMContentLoaded', function() {
        const icons = document.querySelectorAll('.icon'); // Target all icon elements

        icons.forEach(function(icon) {
            const randomColor = generateRandomColor();
            icon.style.color = randomColor; // Apply the random color to the icon
        });
    });
</script>


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
