@extends('layouts.back-end.app')

@section('title', translate('update_Currency'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/coupon_setup.png')}}" alt="">
                {{translate('currency_update')}}
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="tio-money"></i>
                            {{translate('update_Currency')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.currency.update',[$currency['id']])}}" method="post"
                              style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                            @csrf
                            <div class="">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="table-responsive w-auto overflow-y-hidden">
                                            @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
                                            <ul class="nav nav-tabs lang_tab" id="currency-edit-language-tab" role="tablist">
                                                @foreach ($languages as $lang)
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ $lang == $activeLanguage ? 'active' : '' }}"
                                                           id="{{ $lang }}-currency-edit-link"
                                                           data-toggle="tab"
                                                           href="#{{ $lang }}-edit-form-currency"
                                                           role="tab">
                                                            {{ ucfirst(getLanguageName($lang)) . '(' . strtoupper($lang) . ')' }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="tab-content" id="currency-edit-language-tab-content">
                                            @foreach ($languages as $lang)
                                                @php($nameValue = $lang === $defaultLanguage ? $currency->getRawOriginal('name') : ($currency['translations']->first(fn($translation) => $translation->locale === $lang && $translation->key === 'name')?->value ?? ''))
                                                @php($symbolValue = $lang === $defaultLanguage ? $currency->getRawOriginal('symbol') : ($currency['translations']->first(fn($translation) => $translation->locale === $lang && $translation->key === 'symbol')?->value ?? ''))
                                                <div class="tab-pane fade {{ $lang == $activeLanguage ? 'show active' : '' }}"
                                                     id="{{ $lang }}-edit-form-currency"
                                                     role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="title-color text-capitalize">
                                                                {{translate('currency_name')}} ({{ strtoupper($lang) }}):
                                                                @if($lang == $defaultLanguage)<span class="text-danger">*</span>@endif
                                                            </label>
                                                            <input type="text" name="name[]"
                                                                   placeholder="{{translate('currency_name')}}"
                                                                   class="form-control"
                                                                   value="{{ $nameValue }}"
                                                                {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="title-color text-capitalize">
                                                                {{translate('currency_symbol')}} ({{ strtoupper($lang) }}):
                                                                @if($lang == $defaultLanguage)<span class="text-danger">*</span>@endif
                                                            </label>
                                                            <input type="text" name="symbol[]"
                                                                   placeholder="{{translate('currency_symbol')}}"
                                                                   class="form-control"
                                                                   value="{{ $symbolValue }}"
                                                                {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="title-color text-capitalize">{{translate('currency_code').':'}} </label>
                                        <input type="text" name="code"
                                               placeholder="{{translate('currency_code')}}"
                                               class="form-control" id="code"
                                               value="{{$currency['code']}}">
                                    </div>
                                    @if($currencyModel=='multi_currency')
                                        <div class="col-md-6 mb-3">
                                            <label class="title-color">{{translate('exchange_rate').':'}}</label>
                                            <input type="number" min="0" max="1000000"
                                                   name="exchange_rate" step="0.00000001"
                                                   placeholder="{{translate('exchange_Rate')}}"
                                                   class="form-control" id="exchange_rate"
                                                   value="{{$currency['exchange_rate']}}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-10 justify-content-end">
                                <button type="submit" id="add" class="btn btn--primary">{{translate('update')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
