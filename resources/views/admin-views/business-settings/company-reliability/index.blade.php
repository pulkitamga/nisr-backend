@extends('layouts.back-end.app')

@section('title', translate('company_Reliability'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}

@endphp
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: '/public/assets/back-end/img/Pages.png')}}" alt="">
            {{ translate('Pages') }}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row gy-2">
                        @foreach (json_decode($companyReliabilityData->value) as $key => $value)
                        <form action="{{ route('admin.business-settings.company-reliability') }}" method="POST" enctype="multipart/form-data" class="col-xxl-3 col-lg-4 col-sm-6">
                            @csrf
                            <input type="hidden" name="item" value="{{ $value->item }}">
                            <input type="hidden" name="index" value="{{ $key }}">

                            <div class="card">
                                <div class="card-header align-items-center justify-content-between d-flex">
                                    <span class="title-color">
                                        {{ translate($value->item) }}
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ translate('if_enabled,_the_'.$value->item.'_will_be_available_on_the_system.') }}.">
                                            <img width="16" src="{{ dynamicAsset(path: '/public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </span>
                                    <label class="switcher" for="status_{{ $key }}">
                                        <input type="checkbox" class="switcher_input toggle-switch-message" name="status" id="status_{{ $key }}" value="1" {{ $value->status == 1 ? 'checked' : '' }}
                                            data-modal-id="toggle-modal"
                                            data-toggle-id="{{ $value->item }}"
                                            data-on-image=""
                                            data-off-image=""
                                            data-on-title="{{ translate('want_to_Turn_ON_the_'.$value->item.'_option').'?'}}"
                                            data-off-title="{{ translate('want_to_Turn_OFF_the_'.$value->item.'_option').'?'}}"
                                            data-on-message="<p>{{ translate('if_enabled_customers_can_see_'.$value->item) }}</p>"
                                            data-off-message="<p>{{ translate('if_disabled_the_'.$value->item.'_will_be_hidden_from_customer') }}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                                <div class="card-body">
                                    @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
                                    <ul class="nav nav-tabs" role="tablist">
                                        @foreach($language as $lang)
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $lang == $activeLanguage ? 'active' : '' }}" id="tab-{{ $value->item }}-{{ $lang }}" data-toggle="tab" href="#content-{{ $value->item }}-{{ $lang }}" role="tab" aria-controls="content-{{ $value->item }}-{{ $lang }}" aria-selected="{{ $lang == $activeLanguage ? 'true' : 'false' }}">
                                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="tab-content mt-3">
                                        @foreach($language as $lang)
                                        @php
                                        $translation = $companyReliabilityData->translations
                                        ->where('locale', $lang)
                                        ->where('key', 'title')
                                        ->where('item_index', $key)
                                        ->first();
                                        $titleValue = $lang == $defaultLanguage
                                        ? ($value->title ?? '')
                                        : ($translation ? $translation->value : '');
                                        @endphp
                                        <div class="tab-pane fade {{ $lang == $activeLanguage ? 'show active' : '' }}" id="content-{{ $value->item }}-{{ $lang }}" role="tabpanel" aria-labelledby="tab-{{ $value->item }}-{{ $lang }}">
                                            <div class="mb-3">
                                                <label for="title_{{ $value->item }}_{{ $lang }}">{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                                                <input type="text"
                                                    name="title[]"
                                                    id="title_{{ $value->item }}_{{ $lang }}"
                                                    value="{{ old('title.'.$lang, $titleValue) }}"
                                                    class="form-control"
                                                    placeholder="{{ translate('type_your_title_text') }}">
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">

                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- Image upload section --}}
                                    <div class="custom_upload_input position-relative mt-3">
                                        <input type="file" name="image" class="custom-upload-input-file aspect-ratio-3-15 upload-color-image" data-imgpreview="pre_img_{{ $key }}" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                            <i class="tio-delete"></i>
                                        </span>

                                        <div class="img_area_with_preview position-absolute z-index-2">
                                            @php
                                            $imageArray = [
                                            'image_name' => $value?->image->image_name ?? $value?->image,
                                            'storage' => $value?->image?->storage ?? 'public',
                                            ];
                                            $imagePath = storageLink('company-reliability', $imageArray['image_name'], $imageArray['storage']);
                                            @endphp
                                            <img id="pre_img_{{ $key }}" class="h-auto aspect-ratio-3-15 bg-white" onerror="this.classList.add('d-none')"
                                                src="{{ $imagePath['path'] }}" alt="">
                                        </div>
                                        <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                            <div class="d-flex flex-column justify-content-center align-items-center">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="w-50" alt="">
                                                <h3 class="text-muted text-capitalize">{{ translate('upload_icon') }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn--primary mb-3 mx-4 px-3 text-uppercase">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </form>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/business-setting/features-and-company-reliability-section.js')}}"></script>
<script>
    onErrorImage()
</script>
@endpush
