@extends('layouts.front-end.app')

@section('title',translate('cancellation_policy'))

@section('content')
@php
    $setting = getWebConfig('cancellation-policy');

    $settingValue = json_decode($setting->value ?? '{}', true);

    $translatedContent = getBusinessSettingTranslation(
        'cancellation-policy',
        'value',
        $settingValue['content'] ?? ''
    );
@endphp
<div class="container py-5 rtl text-align-direction">
    <h2 class="text-center mb-3 headerTitle">{{ translate('cancellation_policy') }}</h2>
    <div class="card __card">
        <div class="card-body text-justify">
            {!! $translatedContent !!}
        </div>
    </div>
</div>

@endsection