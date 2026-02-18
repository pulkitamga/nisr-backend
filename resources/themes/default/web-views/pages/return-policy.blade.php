@extends('layouts.front-end.app')

@section('title',translate('return_policy'))

@section('content')
 @php
    $setting = getWebConfig('return-policy');

    $settingValue = json_decode($setting->value ?? '{}', true);

    $translatedContent = getBusinessSettingTranslation(
        'return-policy',
        'value',
        $settingValue['content'] ?? ''
    );
@endphp
<div class="container py-5 rtl text-align-direction">
    <h2 class="text-center mb-3 headerTitle">{{ translate('return-policy') }}</h2>
    <div class="card __card">
        <div class="card-body text-justify">
            {!! $translatedContent !!}
        </div>
    </div>
</div>
@endsection
