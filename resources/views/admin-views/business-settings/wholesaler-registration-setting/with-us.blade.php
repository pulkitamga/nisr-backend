@extends('layouts.back-end.app')

@section('title', translate('with_us'))
@section('content')


@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = getConfiguredDefaultLanguage();
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}

@endphp
<div class="content container-fluid">
    @include('admin-views.business-settings.wholesaler-registration-setting.partial.inline-menu')

    <form action="{{route('admin.business-settings.wholesaler-registration-settings.with-us')}}" method="post"
        enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{translate('Why_Join_Us')}}</h5>
                <div class="form-group d-flex align-items-center gap-3">
                    <label class="switcher mx-auto">
                        <input type="checkbox" class="switcher_input status-toggle" data-type="wholesaler_registration_sell_with_us"
                            name="is_active" value="1" {{ ($type->is_active)== 1 ? 'checked' : '' }}>
                        <span class="switcher_control"></span>
                    </label>


                </div>
            </div>
            <div class="card-body">
                @php
                    $activeLanguage = in_array(getDefaultLanguage(), $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage;
                @endphp
<ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                @foreach($languages as $lang)
                <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">
                    <div class="card border shadow-none mb-3">
                        <div class="card-body">
                            <div class="row">
                                <input type="hidden" name="lang[]" value="{{ $lang }}">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="title-color">{{translate('title')}} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="title[][{{ $lang }}]" class="form-control" value="{{ $lang == $defaultLanguage ? $sellWithUs?->title :  ($translations[$lang]['title']?? '')}}"
                                            placeholder="{{translate('enter_title')}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="title-color text-capitalize">{{translate('sub_title')}}  ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="sub_title[][{{ $lang }}]" class="form-control"
                                            value="{{$lang == $defaultLanguage ? $sellWithUs?->sub_title :  ($translations[$lang]['sub_title']?? '')}}"
                                            placeholder="{{translate('enter_sub_title')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="mx-auto max-w-400">
                            <div class="mb-3 text-center">
                                <label for="name"
                                    class="title-color text-capitalize font-weight-bold mb-0">{{translate('image')}}</label>
                                <span class="badge badge-soft-info">{{translate('size').' : '.'310px x 240px'}}</span>
                            </div>

                            <div class="custom_upload_input">
                                <input type="file" name="image" class="image-input meta-img"
                                    data-image-id="view-header-logo" accept="image/*">
                                <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                    <i class="tio-delete"></i>
                                </span>
                                <div class="img_area_with_preview position-absolute z-index-2">
                                    @php($imagePath = imagePathProcessing(imageData:$sellWithUs?->image, path:
                                    'vendor-registration-setting'))
                                    <img id="view-header-logo"
                                        src="{{ getStorageImages(path:$imagePath,type: 'backend-banner')}}"
                                        class="bg-white" alt="">
                                </div>
                                <div
                                    class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <img alt="" class="w-75"
                                            src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                        <h3 class="text-muted text-capitalize">{{ translate('upload_image') }}</h3>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted text-center mt-2">
                                {{ translate('image_format').' : '.'Jpg, png, jpeg, webp,'}}
                                <br>
                                {{ translate('image_size').' : '.translate('max').' ' .'2 MB'}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-end gap-3 mt-3 mx-1">
                    <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn--primary px-5">{{translate('submit')}}</button>
                </div>
            </div>
        </div>
    </form>
    @include('admin-views.business-settings.wholesaler-registration-setting.add-reason')
</div>
@endsection
@push('script')
<script
    src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/vendor-registration-setting.js')}}">
</script>

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

