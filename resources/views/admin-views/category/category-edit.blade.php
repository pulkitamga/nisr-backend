@extends('layouts.back-end.app')

@section('title', translate('category'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand-setup.png') }}" class="mb-1 me-1" alt="">
            @if($category['position'] == 1)
            {{ translate('sub') }}
            @elseif($category['position'] == 2)
            {{ translate('sub_Sub') }}
            @endif
            {{ translate('category') }}
            {{ translate('update') }}
        </h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-body text-start">
                    <form action="{{ route('admin.category.update', [$category['id']]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
                        <ul class="nav nav-tabs w-fit-content mb-4">
                            @foreach($languages as $lang)
                            <li class="nav-item text-capitalize">
                                <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}" id="{{ $lang}}-link">
                                    {{ getLanguageName($lang).'('.strtoupper($lang).')'}}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                        <div class="row">
                            <div class="{{ $category['parent_id']==0 || $category['position'] == 1 ? 'col-lg-6':'col-12' }}">
                                @foreach($languages as $lang)
                                <div>
                                    <?php
                                    if (count($category['translations'])) {
                                        $translate = [];
                                        foreach ($category['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == "name") {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{ $lang}}-form">
                                        <label class="title-color">
                                            {{ translate('category_Name') }} ({{strtoupper($lang) }})
                                        </label>
                                        <input type="text" name="name[]" value="{{ $lang==$defaultLanguage?$category['name']:($translate[$lang]['name']??'') }}" class="form-control" placeholder="{{ translate('new_Category') }}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang}}">
                                    <input type="hidden" name="id" value="{{ $category['id']}}">
                                </div>
                                @endforeach

                                <div class="form-group">
                                    <label class="title-color" for="priority">{{ translate('priority') }}</label>
                                    <select class="form-control" name="priority" id="" required>
                                        @for ($index = 0; $index <= 10; $index++) <option value="{{ $index }}" {{ $category['priority']==$index?'selected':''}}>{{ $index }}</option>
                                            @endfor
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="charges">{{ translate('charges') }}</label>

                                    @if($categoryCharges->isEmpty())
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label>{{ translate('installation_charge') }}</label>
                                        <input type="number" name="charges[installation]" value="" class="form-control w-25" step="any" placeholder="{{ translate('enter_installation_charge') }}">
                                    </div>
                                    <br />
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label>{{ translate('exchange_charge') }}</label>
                                        <input type="number" name="charges[exchange]" value="" class="form-control w-25" step="any" placeholder="{{ translate('enter_exchange_charge') }}">
                                    </div>
                                    <br />
                                    @else
                                    @foreach($categoryCharges as $charge)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ translate($charge->type) }} ({{ $charge->charges }})</span>
                                        <input type="number" name="charges[{{ $charge->id }}]" value="{{ $charge->charges }}" class="form-control w-25" step="any" placeholder="{{ translate('new_charge_value') }}">
                                    </div>
                                    <br />
                                    @endforeach
                                    @endif
                                </div>
                                @if($category['parent_id']==0 || ($category['position'] == 1 && theme_root_path() == 'theme_aster'))
                                <div class="from_part_2">
                                    <label class="title-color">{{ translate('category_Logo') }}</label>
                                    <span class="text-info">({{ translate('ratio') }} 1:1)</span>
                                    <div class="custom-file text-left">
                                        <input type="file" name="image" id="category-image" class="custom-file-input image-preview-before-upload" data-preview="#viewer" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label custom-file-label-flex" for="category-image">{{ translate('choose_File') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-5 mt-lg-0 from_part_2">
                                <div class="form-group">
                                    <div class="text-center mx-auto">
                                        <img class="upload-img-view" id="viewer" src="{{ getStorageImages(path: $category->icon_full_url , type: 'backend-basic') }}" alt="" />
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($category['position'] == 2 || ($category['position'] == 1 && theme_root_path() != 'theme_aster'))
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset" class="btn btn-secondary px-4">
                                    {{ translate('reset') }}
                                </button>
                                <button type="submit" class="btn btn--primary px-4">
                                    {{ translate('update') }}
                                </button>
                            </div>
                            @endif
                        </div>
                        @if($category['parent_id']==0 || ($category['position'] == 1 && theme_root_path() == 'theme_aster'))
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" id="reset" class="btn btn-secondary px-4">{{ translate('reset') }}</button>
                            <button type="submit" class="btn btn--primary px-4">{{ translate('update') }}</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
@endpush
