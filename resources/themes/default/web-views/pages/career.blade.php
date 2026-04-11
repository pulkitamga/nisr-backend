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

    .career-apply-modal .modal-dialog {
        max-width: min(900px, calc(100% - 2rem));
        margin: 1.75rem auto;
    }

    .career-apply-modal .modal-content {
        border: none;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(18, 48, 38, 0.18);
    }

    .career-apply-modal .modal-header {
        padding: 1.4rem 1.5rem;
        border-bottom: 1px solid #eef4f0;
        background: #fff;
    }

    .career-apply-modal .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #18392f;
    }

    .career-apply-modal .close {
        margin: 0;
        padding: 0;
        background: transparent;
        border: 0;
        color: #527266;
        opacity: 1;
    }

    .career-apply-form .modal-body {
        padding: 1.5rem;
        background: #f9fcfa;
    }

    .career-apply-form__section-title {
        margin-bottom: 1rem;
        font-size: .92rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #527266;
    }

    .career-apply-form__hint {
        margin-top: .35rem;
        font-size: .78rem;
        color: #6d7f77;
    }

    .career-apply-form .form-group {
        margin-bottom: 1rem;
    }

    .career-apply-form label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-bottom: .45rem;
        font-weight: 600;
        color: #1f4236;
    }

    .career-apply-form .form-control {
        height: auto;
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid #dbe8e1;
        padding: .85rem 1rem;
        box-shadow: none;
    }

    .career-apply-form .form-control:focus {
        border-color: #7cb5a0;
        box-shadow: 0 0 0 .2rem rgba(20, 122, 116, 0.12);
    }

    .career-apply-form .form-control.is-invalid,
    .career-apply-form .custom-select.is-invalid {
        border-color: #dc3545;
    }

    .career-apply-form .invalid-feedback {
        font-size: .8rem;
    }

    .career-apply-form .alert {
        border-radius: 16px;
    }

    .career-apply-form .modal-footer {
        border-top: 1px solid #eef4f0;
        padding: 1.1rem 1.5rem 1.5rem;
        background: #fff;
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

        .career-apply-modal .modal-content {
            border-radius: 20px;
        }

        .career-apply-modal .modal-header,
        .career-apply-form .modal-body,
        .career-apply-form .modal-footer {
            padding-inline: 1rem;
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
                        <button
                            type="button"
                            class="btn btn--primary career-card__cta"
                            data-toggle="modal"
                            data-target="#careerApplyModal"
                            data-job-id="{{ $job->id }}"
                            data-job-title="{{ e($careerJobTitle) }}"
                        >
                            {{ translate('Apply Now') }}
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="modal fade career-apply-modal" id="careerApplyModal" tabindex="-1" aria-labelledby="careerApplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="careerApplyModalLabel">{{ translate('career_apply_modal_title') }}</h5>
                    <div class="career-apply-form__hint mb-0" data-career-apply-job-title></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('career.store') }}" method="POST" enctype="multipart/form-data" class="career-apply-form">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="job_id" id="career-apply-job-id" value="{{ old('job_id') }}">

                    <div class="row">
                        @if ($errors->has('career'))
                            <div class="col-12">
                                <div class="alert alert-danger mb-4" role="alert">
                                    {{ $errors->first('career') }}
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <h6 class="career-apply-form__section-title">{{ translate('Personal_Information') }}</h6>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-first-name">{{ translate('First_Name') }} <span class="text-danger">*</span></label>
                                <input id="career-first-name" type="text" name="first_name" value="{{ old('first_name') }}" class="form-control border-base @error('first_name') is-invalid @enderror" placeholder="{{ translate('First_Name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-last-name">{{ translate('Last_Name') }} <span class="text-danger">*</span></label>
                                <input id="career-last-name" type="text" name="last_name" value="{{ old('last_name') }}" class="form-control border-base @error('last_name') is-invalid @enderror" placeholder="{{ translate('Last_Name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-email">{{ translate('Email') }} <span class="text-danger">*</span></label>
                                <input id="career-email" type="email" name="email" value="{{ old('email') }}" class="form-control border-base @error('email') is-invalid @enderror" placeholder="{{ translate('Email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-phone">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                <input id="career-phone" type="tel" name="phone" value="{{ old('phone') }}" class="form-control border-base @error('phone') is-invalid @enderror" placeholder="{{ translate('Phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-gender">{{ translate('Gender') }} <span class="text-danger">*</span></label>
                                <select id="career-gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                    <option value="">{{ translate('Select_Gender') }}</option>
                                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>{{ translate('Male') }}</option>
                                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>{{ translate('Female') }}</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <h6 class="career-apply-form__section-title">{{ translate('Location_Details') }}</h6>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-country">{{ translate('Country') }} <span class="text-danger">*</span></label>
                                <input id="career-country" type="text" name="country" value="{{ old('country') }}" class="form-control border-base @error('country') is-invalid @enderror" placeholder="{{ translate('Country') }}" required>
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-state">{{ translate('State') }} <span class="text-danger">*</span></label>
                                <input id="career-state" type="text" name="state" value="{{ old('state') }}" class="form-control border-base @error('state') is-invalid @enderror" placeholder="{{ translate('State') }}" required>
                                @error('state')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="career-city">{{ translate('City') }} <span class="text-danger">*</span></label>
                                <input id="career-city" type="text" name="city" value="{{ old('city') }}" class="form-control border-base @error('city') is-invalid @enderror" placeholder="{{ translate('City') }}" required>
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-area">{{ translate('Area') }} <span class="text-muted">({{ translate('Optional') }})</span></label>
                                <input id="career-area" type="text" name="area" value="{{ old('area') }}" class="form-control border-base @error('area') is-invalid @enderror" placeholder="{{ translate('Area') }}">
                                @error('area')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <h6 class="career-apply-form__section-title">{{ translate('Application_Details') }}</h6>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-resume">{{ translate('career_resume_label') }} <span class="text-danger">*</span></label>
                                <input id="career-resume" type="file" name="resume" class="form-control border-base @error('resume') is-invalid @enderror" accept=".pdf,.doc,.docx" required>
                                <div class="career-apply-form__hint">{{ translate('career_resume_hint') }}</div>
                                @error('resume')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-notice-period">{{ translate('career_notice_period_label') }} <span class="text-muted">({{ translate('Optional') }})</span></label>
                                <input id="career-notice-period" type="text" name="notice_period" value="{{ old('notice_period') }}" class="form-control border-base @error('notice_period') is-invalid @enderror" placeholder="{{ translate('career_notice_period_label') }}">
                                <div class="career-apply-form__hint">{{ translate('career_notice_period_hint') }}</div>
                                @error('notice_period')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="career-last-ctc">{{ translate('career_last_salary_label') }} <span class="text-muted">({{ translate('Optional') }})</span></label>
                                <input id="career-last-ctc" type="number" name="last_ctc" value="{{ old('last_ctc') }}" class="form-control border-base @error('last_ctc') is-invalid @enderror" placeholder="{{ translate('career_last_salary_label') }}" min="0" step="0.01" inputmode="decimal">
                                <div class="career-apply-form__hint">{{ translate('career_last_ctc_hint') }}</div>
                                @error('last_ctc')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>





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

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('careerApplyModal');
        const jobIdInput = document.getElementById('career-apply-job-id');
        const jobTitleLabel = modal ? modal.querySelector('[data-career-apply-job-title]') : null;

        if (!modal || !jobIdInput) {
            return;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const jobId = trigger?.getAttribute('data-job-id') || jobIdInput.value;
            const jobTitle = trigger?.getAttribute('data-job-title') || '';

            jobIdInput.value = jobId || '';

            if (jobTitleLabel) {
                jobTitleLabel.textContent = jobTitle;
            }
        });

        @if ($errors->any() || $errors->has('career'))
            const activeJobId = @json((string) old('job_id'));
            const activeButton = activeJobId
                ? document.querySelector('[data-target="#careerApplyModal"][data-job-id="' + activeJobId + '"]')
                : null;

            if (jobTitleLabel && activeButton) {
                jobTitleLabel.textContent = activeButton.getAttribute('data-job-title') || '';
            }

            if (typeof window.$ !== 'undefined') {
                window.$('#careerApplyModal').modal('show');
            }
        @endif
    });
</script>
@endpush




@endsection
