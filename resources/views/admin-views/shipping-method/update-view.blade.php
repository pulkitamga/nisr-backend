@extends('layouts.back-end.app')

@section('title', translate('shipping_method'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" alt="">
            {{translate('shipping_method_update')}}
        </h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.business-settings.shipping-method.update',[$method['id']])}}"
                          class="text-start"
                          method="post">
                        @csrf
                        @php
                            $activeLanguage = $defaultLanguage;
                            $_la = is_array($language ?? null) ? $language : [];
                            if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                        @endphp
                        <ul class="nav nav-tabs w-fit-content mb-4">
                            @foreach($language as $lang)
                                <li class="nav-item text-capitalize">
                                    <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $activeLanguage ? 'active' : '' }}" id="{{$lang}}-link">
                                        {{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="row">
                            <div class="col-md-6">
                                @foreach($language as $lang)
                                    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{$lang}}-form">
                                        <label class="title-color">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="title[]" value="{{$lang == $defaultLanguage ? $method->getRawOriginal('title') : $method->getTranslatedField('title', $lang, '') }}" class="form-control" placeholder="{{translate('title')}}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                @foreach($language as $lang)
                                    <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form" id="{{$lang}}-form">
                                        <label class="title-color">{{ translate('duration') }} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="duration[]" value="{{$lang == $defaultLanguage ? $method->getRawOriginal('duration') : $method->getTranslatedField('duration', $lang, '') }}" class="form-control" placeholder="{{translate('ex').' '.':'.' '.translate('4_to_6_days')}}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row ">
                                <div class="col-md-12">
                                    <label class="title-color" for="cost">{{translate('cost')}}</label>
                                    <input type="number" min="0" max="1000000" name="cost"
                                           value="{{usdToDefaultCurrency(amount: $method['cost'])}}"
                                           class="form-control"
                                           placeholder="{{translate('ex').' '.':'.' '.setCurrencySymbol(amount: usdToDefaultCurrency(amount: 10), currencyCode: getCurrencyCode())}}">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-10 flex-wrap justify-content-end">
                            <button type="submit" class="btn btn--primary px-4">{{translate('update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
