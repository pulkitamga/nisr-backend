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

    .career-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.4rem;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #dfebe5;
        box-shadow: 0 14px 34px rgba(18, 48, 38, 0.06);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .career-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 38px rgba(18, 48, 38, 0.1);
        border-color: #c9ddd3;
    }

    .career-card__title {
        color: var(--primary-clr);
        margin-bottom: 1rem;
    }

    .career-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-bottom: 1rem;
    }

    .career-card__chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: #f5faf7;
        border: 1px solid #e2eee8;
        color: #355246;
        font-size: .8125rem;
        line-height: 1.2;
    }

    .career-card__label {
        font-weight: 700;
        color: #17352b;
    }

    .career-card__block {
        padding: .85rem 1rem;
        border-radius: 18px;
        background: #fbfdfc;
        border: 1px solid #edf3ef;
        margin-bottom: .85rem;
    }

    .career-card__block:last-of-type {
        margin-bottom: 1.25rem;
    }

    .career-card__block-title {
        display: block;
        margin-bottom: .45rem;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6d7f77;
    }

    .career-card__block p {
        margin-bottom: 0;
        color: #294137;
    }

    .career-card__actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-top: auto;
    }

    .career-card__cta.btn {
        min-width: 150px;
        border-radius: 999px;
    }

    .career-card__link {
        font-weight: 600;
        color: var(--primary-clr);
        text-decoration: none;
    }

    .career-card__link:hover,
    .career-card__link:focus {
        text-decoration: underline;
    }

    @media (max-width: 767.98px) {
        .career-card {
            padding: 1.1rem;
            border-radius: 18px;
        }

        .career-card__actions {
            flex-direction: column;
            align-items: stretch;
        }

        .career-card__cta.btn {
            width: 100%;
            min-width: 0;
        }
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
            @php
                $careerCardTitle = trim((string) getTranslatedValue($card, 'title', $card->title));
                $careerCardDescription = trim((string) richTextToPlainText(getTranslatedValue($card, 'description', $card->description)));
            @endphp
            <div class="col-12 col-sm-6 col-md-3 mb-4 mb-md-0">
                <div class="p-4 rounded shadow h-100 transition-all animate__animated animate__zoomIn {{ $index > 0 ? 'delay-' . ($index * 100) : '' }}"
                    style="transition: transform 0.3s ease;">

                    <div class="mb-3">
                        <i class="{{ $card->icon }} fs-2 text-primary"></i>
                    </div>

                    @if($careerCardTitle !== '')
                        <h5 class="fw-bold mb-2">{{ $careerCardTitle }}</h5>
                    @endif

                    @if($careerCardDescription !== '')
                        <p class="small">{{ $careerCardDescription }}</p>
                    @endif
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
            @php
                $careerJobTitle = trim((string) getTranslatedValue($job, 'title', $job->title));
                $careerJobExperience = trim((string) getTranslatedValue($job, 'experience', $job->experience));
                $careerJobLocation = trim((string) getTranslatedValue($job, 'location', $job->location));
                $careerJobSkills = trim((string) \Illuminate\Support\Str::words(richTextToPlainText(getTranslatedValue($job, 'skills', $job->skills)), 14, '...'));
                $careerJobDescription = trim((string) \Illuminate\Support\Str::words(richTextToPlainText(getTranslatedValue($job, 'job_description', $job->job_description ?? '')), 22, '...'));
            @endphp
            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="career-card animate__animated animate__fadeInUp">
                    @if($careerJobTitle !== '')
                        <h3 class="h5 fw-bold career-card__title">{{ $careerJobTitle }}</h3>
                    @endif

                    @if($careerJobExperience !== '' || $careerJobLocation !== '')
                        <div class="career-card__meta">
                            @if($careerJobExperience !== '')
                                <span class="career-card__chip">
                                    <span class="career-card__label">{{ translate('Experience') }}</span>
                                    <span>{{ $careerJobExperience }}</span>
                                </span>
                            @endif
                            @if($careerJobLocation !== '')
                                <span class="career-card__chip">
                                    <span class="career-card__label">{{ translate('Location') }}</span>
                                    <span>{{ $careerJobLocation }}</span>
                                </span>
                            @endif
                        </div>
                    @endif

                    @if($careerJobSkills !== '')
                        <div class="career-card__block">
                            <span class="career-card__block-title">{{ translate('Skills') }}</span>
                            <p class="small">{{ $careerJobSkills }}</p>
                        </div>
                    @endif

                    @if($careerJobDescription !== '')
                        <div class="career-card__block">
                            <span class="career-card__block-title">{{ translate('Description') }}</span>
                            <p class="small">{{ $careerJobDescription }}</p>
                        </div>
                    @endif

                    <div class="career-card__actions">
                        <a href="{{ route('career.job.detail', ['slug' => $job->id]) }}" class="career-card__link">
                            {{ translate('View Details') }}
                        </a>
                        <a href="{{ route('career.job.detail', ['slug' => $job->id]) }}" class="btn btn--primary career-card__cta">
                            {{ translate('Apply Now') }}
                        </a>
                    </div>
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
            @php
                $careerBenefitTitle = trim((string) getTranslatedValue($benefit, 'title', $benefit->title));
                $careerBenefitDescription = trim((string) richTextToPlainText(getTranslatedValue($benefit, 'description', $benefit->description)));
            @endphp
            <div class="col-12 col-md-3 mb-4 mb-md-0">
                <div
                    class="benefit-card bg-white p-4 rounded-4 shadow-sm text-center animate__animated animate__fadeInUp h-100"
                    data-index="{{ $index }}">
                    <i class="{{ $benefit->icon }} text-primary mb-3" style="font-size: 2.5rem;"></i>
                    @if($careerBenefitTitle !== '')
                        <h5 class="fw-semibold">{{ $careerBenefitTitle }}</h5>
                    @endif
                    @if($careerBenefitDescription !== '')
                        <p class="text-muted mt-2 small">{{ $careerBenefitDescription }}</p>
                    @endif
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
