@extends('layouts.front-end.app')

@section('title', __('Careers at NISR'))

@push('css_or_js')
<style>
    .collapse {
        visibility: visible !important;
    }

    .navbar-collapse {

        flex-grow: 0 !important;
    }

    .career-job-shell {
        border: 1px solid #e6ece8;
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(18, 48, 38, 0.08) !important;
        overflow: hidden;
        background: #fff;
    }

    .career-job-shell .card-header,
    .career-job-shell .card-footer {
        background: transparent;
        border: 0;
    }

    .career-job-shell .card-header {
        padding-bottom: .5rem;
    }

    .career-job-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin: 1.25rem 0 1.5rem;
    }

    .career-job-summary__item {
        min-width: 170px;
        padding: .9rem 1rem;
        border-radius: 18px;
        background: #f7faf8;
        border: 1px solid #e5eeea;
    }

    .career-job-summary__label {
        display: block;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6d7f77;
        margin-bottom: .35rem;
    }

    .career-job-summary__value {
        color: #123026;
        font-weight: 600;
        line-height: 1.45;
    }

    .career-detail-cta {
        margin-top: 1.5rem;
    }

    .career-detail-cta .btn {
        min-width: 200px;
    }

    .career-apply-modal .modal-dialog {
        max-width: 960px;
    }

    .career-apply-modal .modal-content {
        border: 0;
        border-radius: 26px;
        overflow: hidden;
        box-shadow: 0 28px 72px rgba(11, 30, 24, 0.22);
    }

    .career-apply-modal .modal-header {
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #edf2ef;
        background: linear-gradient(180deg, #fbfdfc 0%, #f5f9f7 100%);
    }

    .career-apply-modal .modal-title {
        font-size: 1.45rem;
        font-weight: 700;
        color: #123026;
    }

    .career-apply-modal .close {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        padding: 0;
        margin: 0;
        background: #edf4f1;
        color: #395348;
        opacity: 1;
        text-shadow: none;
        transition: background-color .2s ease, color .2s ease, transform .2s ease;
    }

    .career-apply-modal .close:hover,
    .career-apply-modal .close:focus {
        background: #dce8e2;
        color: #123026;
        transform: scale(1.04);
        outline: none;
    }

    .career-apply-form .modal-body {
        padding: 1.5rem;
        background: #fff;
    }

    .career-apply-form__section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--primary-clr);
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .career-apply-form__hint {
        font-size: .8125rem;
        color: #6c757d;
        margin-top: .35rem;
        line-height: 1.45;
    }

    .career-apply-form .form-group {
        margin-bottom: 1.1rem;
    }

    .career-apply-form label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-bottom: .45rem;
        font-size: .875rem;
        font-weight: 600;
        color: #244437;
    }

    .career-apply-form .form-control {
        min-height: 52px;
        border-radius: 16px;
        border: 1px solid #cfddd6;
        box-shadow: none !important;
        background: #fcfefd;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .career-apply-form select.form-control {
        padding-inline-end: 2.5rem;
    }

    .career-apply-form .form-control:focus {
        border-color: #1f8f68;
        background: #fff;
        box-shadow: 0 0 0 .2rem rgba(31, 143, 104, 0.12) !important;
    }

    .career-apply-form input[type="file"].form-control {
        min-height: 56px;
        padding: .75rem 1rem;
        background: #f6fbf8;
        border-style: dashed;
    }

    .career-apply-form input[type="file"].form-control::-webkit-file-upload-button {
        margin-inline-end: .85rem;
        border: 0;
        border-radius: 999px;
        padding: .45rem .95rem;
        background: #1f8f68;
        color: #fff;
        cursor: pointer;
    }

    .career-apply-form .form-control.is-invalid,
    .career-apply-form .custom-select.is-invalid {
        border-color: #dc3545;
        background: #fffafa;
    }

    .career-apply-form .invalid-feedback {
        display: block;
        font-size: .8125rem;
    }

    .career-apply-form .alert {
        border-radius: 16px;
    }

    .career-apply-form .modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 2;
        display: flex;
        justify-content: flex-end;
        gap: .75rem;
        padding: 1rem 1.5rem calc(1rem + env(safe-area-inset-bottom));
        border-top: 1px solid #edf2ef;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px);
    }

    .career-apply-form .modal-footer .btn {
        min-width: 180px;
        min-height: 48px;
        border-radius: 999px;
        margin-top: 0 !important;
    }

    @media (max-width: 767.98px) {
        .career-job-shell {
            border-radius: 20px;
        }

        .career-job-summary__item {
            width: 100%;
            min-width: 0;
        }

        .career-detail-cta .btn {
            width: 100%;
        }

        .career-apply-modal .modal-dialog {
            margin: .75rem;
        }

        .career-apply-modal .modal-content {
            border-radius: 20px;
        }

        .career-apply-modal .modal-header,
        .career-apply-form .modal-body,
        .career-apply-form .modal-footer {
            padding-inline: 1rem;
        }

        .career-apply-modal .modal-title {
            font-size: 1.15rem;
        }

        .career-apply-form .modal-footer .btn {
            width: 100%;
            min-width: 0;
        }
    }
</style>
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


<section>
    <div class="container py-5">
        <div class="row card p-4 mb-4 career-job-shell">
            <div class="">
                <div class="card-header">
                    <h1 class="text-primary mb-0">{{ getTranslatedValue($job, 'title', $job->title ?? '') }}</h1>
                </div>
                <div class="card-body">
                    <div class="career-job-summary">
                        <div class="career-job-summary__item">
                            <span class="career-job-summary__label">{{ translate('Location') }}</span>
                            <span class="career-job-summary__value">{{ getTranslatedValue($job, 'location', $job->location ?? '') }}</span>
                        </div>
                        <div class="career-job-summary__item">
                            <span class="career-job-summary__label">{{ translate('Experience') }}</span>
                            <span class="career-job-summary__value">{{ getTranslatedValue($job, 'experience', $job->experience ?? '') }}</span>
                        </div>
                        <div class="career-job-summary__item">
                            <span class="career-job-summary__label">{{ translate('Skills') }}</span>
                            <span class="career-job-summary__value">{{ \App\Support\CmsContentSanitizer::sanitizePlainText(getTranslatedValue($job, 'skills', $job->skills ?? '')) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h5 class="mb-2">{{ translate('Job_Description') }}</h5>
                    {!! \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($job, 'job_description', $job->job_description ?? '')) !!}
                    <div class="career-detail-cta">
                        <button class="btn btn--primary" type="button" data-toggle="modal" data-target="#exampleModal">{{ translate('Apply for this Job') }}</button>
                    </div>
                </div>
            </div>

        </div>



        <div class="modal fade career-apply-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ translate('career_apply_modal_title') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('career.store') }}" method="POST" enctype="multipart/form-data" class="career-apply-form">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

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
                                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>{{ translate('Male') }}</option>
                                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>{{ translate('Female') }}</option>
                                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>{{ translate('Other') }}</option>
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
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

@if ($errors->any() || $errors->has('career'))
    @push('script')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const activeJobId = @json((string) old('job_id'));
                const currentJobId = @json((string) $job->id);

                if (activeJobId === currentJobId && typeof window.$ !== 'undefined') {
                    window.$('#exampleModal').modal('show');
                }
            });
        </script>
    @endpush
@endif


@endsection
