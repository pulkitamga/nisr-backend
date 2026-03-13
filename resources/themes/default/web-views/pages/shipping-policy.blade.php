@extends('layouts.front-end.app')

@section('title',translate('shipping_policy'))

@section('content')
 @php
    $setting = getWebConfig('shipping-policy');

    $settingValue = json_decode($setting->value ?? '{}', true);

    $translatedContent = getBusinessSettingTranslation(
        'shipping-policy',
        'value',
        $settingValue['content'] ?? ''
    );
@endphp
<div class="container py-5 rtl text-align-direction">
    <h2 class="text-center mb-3 headerTitle">{{ translate('shipping-policy') }}</h2>
    <div class="card __card">
        <div class="card-body text-justify">
            {!! $translatedContent !!}
        </div>
    </div>
</div>
@endsection
