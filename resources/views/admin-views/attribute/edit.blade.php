@extends('layouts.back-end.app')

@section('title', translate('Attribute'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/attribute.png') }}" class="mb-1 me-1" alt="">
                {{ translate('update_attribute') }}
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{route('admin.attribute.update', [$attribute['id']])}}" method="post">
                            @csrf

                            @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
                            <ul class="nav nav-tabs w-fit-content mb-4">
                                @foreach($language as $lang)
                                    <li class="nav-item text-capitalize">
                                        <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}" id="{{$lang}}-link">
                                            {{ getLanguageName($lang).' ('.strtoupper($lang).')' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            @foreach($language as $lang)

                                <div
                                    class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                                    id="{{$lang}}-form">
                                    <input type="hidden" id="id">
                                    <label class="title-color" for="name">{{ translate('attribute_Name') }}
                                        ({{strtoupper($lang)}})</label>
                                    <input type="text" name="name[]"
                                           value="{{$lang==$defaultLanguage ? $attribute->getRawOriginal('name') : $attribute->getTranslatedField('name', $lang, '') }}"
                                           class="form-control" id="name"
                                           placeholder="{{ translate('enter_Attribute_Name') }}">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}" id="lang">
                            @endforeach
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn px-4 btn-secondary">{{ translate('Reset') }}</button>
                                <button type="submit" class="btn px-4 btn--primary">{{ translate('Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
@endpush
