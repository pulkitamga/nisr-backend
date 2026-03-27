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
        <div class="row card p-4 mb-4 shadow-lg">
            <div class="">
                <div class="card-header">
                    <h1 class="text-primary">{{ getTranslatedValue($job, 'title', $job->title ?? '') }}</h1>
                </div>
                <div class="card-body">
                    <p><strong>{{ translate('Location') }}:</strong> {{ getTranslatedValue($job, 'location', $job->location ?? '') }}</p>
                    <p><strong>{{ translate('Experience') }}:</strong> {{ getTranslatedValue($job, 'experience', $job->experience ?? '') }}</p>
                    <p><strong>{{ translate('Skills') }}:</strong> {{ \App\Support\CmsContentSanitizer::sanitizePlainText(getTranslatedValue($job, 'skills', $job->skills ?? '')) }}</p>
                </div>
                <div class="card-footer">
                    <h5 class="mb-2">{{ translate('Job_Description') }}</h5>
                    {!! \App\Support\CmsContentSanitizer::sanitizeRichText(getTranslatedValue($job, 'job_description', $job->job_description ?? '')) !!}
                    <button class="btn btn--primary" data-toggle="modal" data-target="#exampleModal">{{ __('Apply Now') }}</button>
                </div>
            </div>

        </div>



        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ translate('Apply_for_this_Job') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('career.store') }}" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('First_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control border-base shadow-lg" placeholder="{{ translate('First_Name') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Last_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control border-base shadow-lg" placeholder="{{ translate('Last_Name') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control border-base shadow-lg" placeholder="{{ translate('Email') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control border-base shadow-lg" placeholder="{{ translate('Phone') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Gender') }} <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-control" required>
                                        <option value="">{{ translate('Select_Gender') }}</option>
                                        <option value="male">{{ translate('Male') }}</option>
                                        <option value="female">{{ translate('Female') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Country') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="country" class="form-control border-base shadow-lg" placeholder="{{ translate('Country') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('State') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control border-base shadow-lg" placeholder="{{ translate('State') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('City') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control border-base shadow-lg" placeholder="{{ translate('City') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Area') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="area" class="form-control border-base shadow-lg" placeholder="{{ translate('Area') }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Notice_Period') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="notice_period" class="form-control border-base shadow-lg" placeholder="{{ translate('Notice_Period') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Last_CTC') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="last_ctc" class="form-control border-base shadow-lg" placeholder="{{ translate('Last_CTC') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>{{ translate('Upload_Resume_PDF_DOC') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="resume" class="form-control border-base shadow-lg" accept=".pdf,.doc,.docx" required>
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


@endsection
