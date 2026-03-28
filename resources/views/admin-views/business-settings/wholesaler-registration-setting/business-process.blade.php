@extends('layouts.back-end.app')

@section('title', translate('business_Process'))
@section('content')
@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0]['code'] ?? 'en';
}
@endphp
<div class="content container-fluid">
    @include('admin-views.business-settings.wholesaler-registration-setting.partial.inline-menu')

    <form action="{{route('admin.business-settings.wholesaler-registration-settings.business-process')}}" method="post"
        enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize">{{translate('business_process')}}</h5>
                <div class="form-group d-flex align-items-center gap-3">
                    <label class="switcher mx-auto">
                        <input type="checkbox" class="switcher_input status-toggle" data-type="wholesaler_process_main_section"
                            name="is_active" value="1" {{ ($type->is_active)== 1 ? 'checked' : '' }}>
                        <span class="switcher_control"></span>
                    </label>


                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)

                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                @foreach($languages as $lang)
                <input type="hidden" name="lang[]" value="{{ $lang }}">

                <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
                    <div class="card border shadow-none mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="title-color">{{translate('title')}} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="title[][{{ $lang }}]" class="form-control"
                                            value="{{$lang == $defaultLanguage ? $businessProcess?->title  :  ($translations[$lang]['title']?? '')}}" placeholder="{{translate('enter_title')}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="title-color">{{translate('sub_title')}} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="sub_title[][{{ $lang }}]" class="form-control"
                                            value="{{$lang == $defaultLanguage ? $businessProcess?->sub_title :  ($translations[$lang]['sub_title']?? '')}}"
                                            placeholder="{{translate('enter_sub_title')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    @for($index = 1 ;$index <=3 ;$index++) <div class="card border shadow-none mb-2">
                        <div class="card-body">
                            <h5 class="mb-4">{{translate('section').' '.$index}}</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('title')}} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="section_{{$index}}_title[{{ $lang }}]" class="form-control"
                                            value="{{ $lang == $defaultLanguage ? ($businessProcessStep[$index - 1]->title ?? '') : ($translations[$lang]['section_'.$index.'_title'] ?? '') }}" {{ $lang == $defaultLanguage ? 'required' : '' }}
                                            placeholder="{{translate('enter_title')}}">
                                    </div>

                                    <div class="form-group">
                                        <label
                                            class="form-label">{{translate('short_description')}}({{ strtoupper($lang) }})</label>
                                        <textarea name="section_{{$index}}_description[{{ $lang }}]" class="form-control" rows="4"
                                            placeholder="{{translate('write_description').'...'}}">{{ $lang == $defaultLanguage ? ($businessProcessStep[$index - 1]->description ?? '') : ($translations[$lang]['section_'.$index.'_description'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mx-auto max-w-150">
                                        @if($lang == $defaultLanguage)

                                        <div class="mb-3 text-center">
                                            <label for="name"
                                                class="title-color text-capitalize font-weight-bold mb-0">{{translate('image')}}</label>
                                            <span class="badge badge-soft-info">{{'('.translate('size').': 1:1'.')'}}</span>
                                        </div>

                                        <div class="custom_upload_input">
                                            <input type="file" name="section_{{$index}}_image" class="image-input"
                                                data-image-id="view-bp-logo-{{$index}}" accept="image/*">

                                            <span
                                                class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>
                                            <div class="img_area_with_preview position-absolute z-index-2">
                                                @php($imagePath =
                                                imagePathProcessing(imageData:isset($businessProcessStep[$index-1]) ?
                                                $businessProcessStep[$index-1]?->image : null, path:
                                                'vendor-registration-setting'))
                                                <img id="view-bp-logo-{{$index}}"
                                                    src="{{getStorageImages(path:$imagePath,type: 'backend-banner')}}"
                                                    class="bg-white" alt="">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt="" class="w-50"
                                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                    <h5 class="text-muted">{{ translate('Upload_Image') }}</h5>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-muted text-center fz-12 mt-2">
                                            {{ translate('image_format').' : Jpg, png, jpeg, webp,'}}
                                            <br>
                                            {{ translate('image_size').' : '.translate('max'). '2MB' }}
                                        </p>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                @endfor
            </div>
            @endforeach
            <div class="row justify-content-end gap-3 mt-3 mx-1">
                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                <button type="submit" class="btn btn--primary px-5">{{translate('submit')}}</button>
            </div>
        </div>
</div>
</form>
</div>
@endsection


@push('script')
<script>
    $(document).on('change', '.status-toggle', function() {
        let isChecked = $(this).is(':checked') ? 1 : 0;
        let type = $(this).data('type'); // e.g. 'wholesaler_registration_header'

        $.ajax({
            url: "{{ route('admin.business-settings.wholesaler-registration-settings.toggle-type-status') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                type: type,
                is_active: isChecked
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error(@json(__('Failed to update status')));
            }
        });
    });
</script>

@endpush

